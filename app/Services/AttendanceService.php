<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\AttendanceEnrollment;
use App\Models\Employee;
use App\Models\LeaveApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function resolveEmployee(string $identifier): ?Employee
    {
        return Employee::with('department')
            ->where('employee_no', $identifier)
            ->orWhere('personnel_number', $identifier)
            ->orWhere('id', $identifier)
            ->first();
    }

    public function authenticateDevice(string $deviceId, string $deviceToken): AttendanceDevice
    {
        $device = AttendanceDevice::where('device_id', $deviceId)->first();

        if (!$device || !$device->isActive() || !$device->matchesToken($deviceToken)) {
            throw ValidationException::withMessages([
                'device_id' => ['Unauthorized attendance device.'],
            ]);
        }

        return $device;
    }

    public function recordTerminalEvent(array $payload, Request $request): array
    {
        $submissionUuid = Arr::get($payload, 'submission_uuid') ?: (string) Str::uuid();

        return DB::transaction(function () use ($payload, $request, $submissionUuid) {
            $device = $this->authenticateDevice(
                (string) Arr::get($payload, 'device_id', ''),
                (string) Arr::get($payload, 'device_token', '')
            );

            $capturedPhoto = (string) Arr::get($payload, 'captured_photo', '');
            $faceDescriptor = Arr::get($payload, 'face_descriptor');
            $employeeIdentifier = trim((string) Arr::get($payload, 'employee_identifier', ''));
            $employee = $employeeIdentifier !== ''
                ? $this->resolveEmployee($employeeIdentifier)
                : $this->identifyEmployeeFromPhoto($capturedPhoto, is_array($faceDescriptor) ? $faceDescriptor : null);

            if (!$employee) {
                throw ValidationException::withMessages([
                    'captured_photo' => ['No matching employee could be detected from the live photo.'],
                ]);
            }

            $enrollment = AttendanceEnrollment::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                throw ValidationException::withMessages([
                    'employee_identifier' => ['Employee is not enrolled for attendance.'],
                ]);
            }

            $action = Arr::get($payload, 'action');
            $attendanceDate = Carbon::parse(Arr::get($payload, 'attendance_date', now()))->toDateString();
            $submittedAt = Carbon::parse(Arr::get($payload, 'submitted_at', now()));
            $time = $submittedAt->format('H:i:s');
            $method = Arr::get($payload, 'attendance_method', 'manual');
            $terminalLocation = Arr::get($payload, 'terminal_location', $device->location);

            $this->validateGeofence($device, $payload);

            if (!in_array($method, $enrollment->allowed_methods ?? [], true)) {
                throw ValidationException::withMessages([
                    'attendance_method' => ['This employee is not enrolled for the selected attendance method.'],
                ]);
            }

            if ($action === 'clock_in' && $capturedPhoto === '') {
                throw ValidationException::withMessages([
                    'captured_photo' => ['A live photo is required for attendance punches.'],
                ]);
            }

            $existingBySubmission = Attendance::where('submission_uuid', $submissionUuid)->first();
            if ($existingBySubmission) {
                return [
                    'attendance' => $existingBySubmission->load('employee.department', 'device'),
                    'employee' => $employee,
                    'device' => $device,
                    'message' => 'Submission already processed.',
                    'idempotent' => true,
                ];
            }

            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'attendance_date' => $attendanceDate,
            ]);

            $officialStart = Carbon::createFromFormat('H:i:s', config('attendance.official_start'));
            $clockInTime = Carbon::createFromFormat('H:i:s', $time);

            if ($action === 'clock_in') {
                if ($attendance->exists && $attendance->check_in) {
                    return [
                        'attendance' => $attendance->load('employee.department', 'device'),
                        'employee' => $employee,
                        'device' => $device,
                        'message' => 'Clock-in already recorded for today.',
                        'idempotent' => true,
                    ];
                }

                $attendance->check_in = $time;
                $attendance->clock_in_photo_path = $this->storePunchPhoto($capturedPhoto, $employee, $attendanceDate, 'clock-in');
                $attendance->status = $clockInTime->greaterThan($officialStart) ? 'Late' : 'Present';
            } elseif ($action === 'clock_out') {
                if (!$attendance->exists || !$attendance->check_in) {
                    throw ValidationException::withMessages([
                        'action' => ['Clock-in must be recorded before clock-out.'],
                    ]);
                }

                if ($attendance->check_out) {
                    return [
                        'attendance' => $attendance->load('employee.department', 'device'),
                        'employee' => $employee,
                        'device' => $device,
                        'message' => 'Clock-out already recorded for today.',
                        'idempotent' => true,
                    ];
                }

                $attendance->check_out = $time;
                $attendance->clock_out_photo_path = null;
                $attendance->overtime_hours = $this->calculateOvertimeHours($attendance->check_in, $attendance->check_out);
            } else {
                throw ValidationException::withMessages([
                    'action' => ['The selected attendance action is invalid.'],
                ]);
            }

            $attendance->source = 'terminal';
            $attendance->recorded_by = $request->user()?->id;
            $attendance->device_id = $device->device_id;
            $attendance->verification_method = $method;
            $attendance->attendance_method = $method;
            $attendance->terminal_location = $terminalLocation;
            $attendance->submission_uuid = $submissionUuid;
            $attendance->sync_status = 'synced';
            $attendance->synced_at = now();
            $attendance->latitude = Arr::get($payload, 'latitude');
            $attendance->longitude = Arr::get($payload, 'longitude');
            $attendance->notes = Arr::get($payload, 'notes');
            $attendance->save();

            $device->forceFill([
                'last_synced_at' => now(),
                'last_ip_address' => $request->ip(),
            ])->save();

            AuditService::log(
                $action === 'clock_in' ? 'created' : 'updated',
                Attendance::class,
                $attendance->id,
                [],
                $attendance->fresh()->toArray(),
                sprintf(
                    'Terminal %s recorded for %s (%s) at %s via %s',
                    $action,
                    $employee->full_name,
                    $employee->employee_no,
                    $attendanceDate . ' ' . $time,
                    $method
                )
            );

            return [
                'attendance' => $attendance->load('employee.department', 'device'),
                'employee' => $employee,
                'device' => $device,
                'message' => $action === 'clock_in'
                    ? 'Clock-in recorded successfully.'
                    : 'Clock-out recorded successfully.',
                'idempotent' => false,
            ];
        });
    }

    private function validateGeofence(AttendanceDevice $device, array $payload): void
    {
        if ($device->latitude === null || $device->longitude === null) {
            return;
        }

        $latitude = Arr::get($payload, 'latitude');
        $longitude = Arr::get($payload, 'longitude');

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw ValidationException::withMessages([
                'location' => ['Location permission is required for this registered attendance device.'],
            ]);
        }

        $earthRadius = 6371000;
        $latDelta = deg2rad((float) $latitude - $device->latitude);
        $lonDelta = deg2rad((float) $longitude - $device->longitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($device->latitude)) * cos(deg2rad((float) $latitude)) * sin($lonDelta / 2) ** 2;
        $distance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

        if ($distance > $device->geofence_radius_meters) {
            throw ValidationException::withMessages([
                'location' => ['You must be within the registered attendance geofence to clock in or out.'],
            ]);
        }
    }

    /**
     * Simple self-service kiosk punch: employee types their employee number,
     * takes a photo to clock in, and just re-enters the number to clock out.
     * No device registration or facial enrollment is required.
     */
    public function recordKioskPunch(string $employeeIdentifier, string $action, ?string $capturedPhoto, Request $request): array
    {
        return DB::transaction(function () use ($employeeIdentifier, $action, $capturedPhoto, $request) {
            $employee = $this->resolveEmployee($employeeIdentifier);

            if (!$employee) {
                throw ValidationException::withMessages([
                    'employee_identifier' => ['No employee found with that employee number.'],
                ]);
            }

            if ($employee->employment_status !== 'Active') {
                throw ValidationException::withMessages([
                    'employee_identifier' => ['This employee is not active and cannot record attendance.'],
                ]);
            }

            $attendanceDate = now()->toDateString();
            $time = now()->format('H:i:s');

            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'attendance_date' => $attendanceDate,
            ]);

            if ($action === 'clock_in') {
                if ($attendance->exists && $attendance->check_in) {
                    return [
                        'attendance' => $attendance->load('employee.department'),
                        'employee' => $employee,
                        'message' => 'Clock-in already recorded for today.',
                        'idempotent' => true,
                    ];
                }

                if (!$capturedPhoto) {
                    throw ValidationException::withMessages([
                        'captured_photo' => ['A live photo is required to clock in.'],
                    ]);
                }

                $officialStart = Carbon::createFromFormat('H:i:s', config('attendance.official_start'));
                $clockInTime = Carbon::createFromFormat('H:i:s', $time);

                $attendance->check_in = $time;
                $attendance->clock_in_photo_path = $this->storePunchPhoto($capturedPhoto, $employee, $attendanceDate, 'clock-in');
                $attendance->status = $clockInTime->greaterThan($officialStart) ? 'Late' : 'Present';
            } elseif ($action === 'clock_out') {
                if (!$attendance->exists || !$attendance->check_in) {
                    throw ValidationException::withMessages([
                        'action' => ['Clock-in must be recorded before clock-out.'],
                    ]);
                }

                if ($attendance->check_out) {
                    return [
                        'attendance' => $attendance->load('employee.department'),
                        'employee' => $employee,
                        'message' => 'Clock-out already recorded for today.',
                        'idempotent' => true,
                    ];
                }

                $attendance->check_out = $time;
                $attendance->overtime_hours = $this->calculateOvertimeHours($attendance->check_in, $attendance->check_out);
            } else {
                throw ValidationException::withMessages([
                    'action' => ['The selected attendance action is invalid.'],
                ]);
            }

            $attendance->source = 'kiosk';
            $attendance->verification_method = 'manual';
            $attendance->attendance_method = 'manual';
            $attendance->sync_status = 'synced';
            $attendance->synced_at = now();
            $attendance->save();

            AuditService::log(
                $action === 'clock_in' ? 'created' : 'updated',
                Attendance::class,
                $attendance->id,
                [],
                $attendance->fresh()->toArray(),
                sprintf(
                    'Kiosk %s recorded for %s (%s) at %s',
                    $action,
                    $employee->full_name,
                    $employee->employee_no,
                    $attendanceDate . ' ' . $time
                )
            );

            return [
                'attendance' => $attendance->load('employee.department'),
                'employee' => $employee,
                'message' => $action === 'clock_in'
                    ? 'Clock-in recorded successfully.'
                    : 'Clock-out recorded successfully.',
                'idempotent' => false,
            ];
        });
    }

    private function storePunchPhoto(string $capturedPhoto, Employee $employee, string $attendanceDate, string $label): string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $capturedPhoto);
        $image = str_replace(' ', '+', $image);

        $binary = base64_decode($image, true);
        if ($binary === false) {
            throw ValidationException::withMessages([
                'captured_photo' => ['The attendance photo could not be processed.'],
            ]);
        }

        $directory = 'attendance/photos/' . $employee->employee_no;
        $fileName = $attendanceDate . '_' . $label . '_' . Str::uuid() . '.jpg';
        $path = $directory . '/' . $fileName;

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function identifyEmployeeFromPhoto(string $capturedPhoto, ?array $faceDescriptor = null): ?Employee
    {
        // Prefer real face-descriptor matching (128-d embedding, Euclidean
        // distance) when the client submitted one. Enrollments without a
        // descriptor yet (not re-captured since this upgrade) are simply
        // skipped here and picked up by the hash fallback below — this is
        // the mixed-mode transition path, not an error condition.
        if ($faceDescriptor !== null && count($faceDescriptor) === 128) {
            $employee = $this->matchEmployeeByDescriptor($faceDescriptor);

            if ($employee) {
                return $employee;
            }
        }

        return $this->identifyEmployeeByHash($capturedPhoto);
    }

    private function matchEmployeeByDescriptor(array $descriptor): ?Employee
    {
        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;

        AttendanceEnrollment::query()
            ->with(['employee.department'])
            ->where('status', 'active')
            ->whereNotNull('face_descriptor')
            ->chunkById(50, function ($enrollments) use ($descriptor, &$bestMatch, &$bestDistance) {
                foreach ($enrollments as $enrollment) {
                    $reference = $enrollment->face_descriptor;

                    if (!is_array($reference) || count($reference) !== count($descriptor)) {
                        continue;
                    }

                    $distance = $this->euclideanDistance($descriptor, $reference);

                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestMatch = $enrollment->employee;
                    }
                }
            });

        // 0.6 is the face-api.js community convention for a same-person
        // match on 128-d faceRecognitionNet embeddings.
        return $bestDistance <= 0.6 ? $bestMatch : null;
    }

    private function euclideanDistance(array $left, array $right): float
    {
        $sum = 0.0;

        foreach ($left as $index => $value) {
            $diff = $value - ($right[$index] ?? 0);
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    private function identifyEmployeeByHash(string $capturedPhoto): ?Employee
    {
        $capturedHash = $this->imageHashFromDataUri($capturedPhoto);

        if ($capturedHash === null) {
            throw ValidationException::withMessages([
                'captured_photo' => ['The live photo could not be processed.'],
            ]);
        }

        $bestMatch = null;
        $bestDistance = PHP_INT_MAX;

        AttendanceEnrollment::query()
            ->with(['employee.department'])
            ->where('status', 'active')
            ->whereNotNull('face_photo_path')
            ->chunkById(50, function ($enrollments) use ($capturedHash, &$bestMatch, &$bestDistance) {
                foreach ($enrollments as $enrollment) {
                    $photoPath = $enrollment->face_photo_path;

                    if (!$photoPath || !Storage::disk('public')->exists($photoPath)) {
                        continue;
                    }

                    $referenceHash = $this->imageHashFromPath(Storage::disk('public')->path($photoPath));

                    if ($referenceHash === null) {
                        continue;
                    }

                    $distance = $this->hammingDistance($capturedHash, $referenceHash);

                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestMatch = $enrollment->employee;
                    }
                }
            });

        return $bestDistance <= 20 ? $bestMatch : null;
    }

    private function imageHashFromDataUri(string $capturedPhoto): ?string
    {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $capturedPhoto);
        $image = str_replace(' ', '+', $image);

        $binary = base64_decode($image, true);
        if ($binary === false) {
            return null;
        }

        return $this->imageHashFromBinary($binary);
    }

    private function imageHashFromPath(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $binary = file_get_contents($path);

        if ($binary === false) {
            return null;
        }

        return $this->imageHashFromBinary($binary);
    }

    private function imageHashFromBinary(string $binary): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring($binary);

        if (!$source) {
            return null;
        }

        $size = 8;
        $resized = imagecreatetruecolor($size, $size);

        if (!$resized) {
            imagedestroy($source);
            return null;
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));
        imagefilter($resized, IMG_FILTER_GRAYSCALE);

        $pixels = [];
        $total = 0;

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $shade = ($rgb >> 16) & 0xFF;
                $pixels[] = $shade;
                $total += $shade;
            }
        }

        $average = $total / count($pixels);
        $hash = '';

        foreach ($pixels as $pixel) {
            $hash .= $pixel >= $average ? '1' : '0';
        }

        imagedestroy($source);
        imagedestroy($resized);

        return $hash;
    }

    private function hammingDistance(string $left, string $right): int
    {
        $length = min(strlen($left), strlen($right));
        $distance = 0;

        for ($index = 0; $index < $length; $index++) {
            if ($left[$index] !== $right[$index]) {
                $distance++;
            }
        }

        return $distance + abs(strlen($left) - strlen($right));
    }

    public function dashboardStats(?Carbon $date = null): array
    {
        $date = $date ?: now();
        $dateString = $date->toDateString();

        $activeCount = Employee::active()->count();
        $presentToday = Attendance::whereDate('attendance_date', $dateString)
            ->whereIn('status', ['Present', 'Late'])
            ->count();

        $leaveCount = LeaveApplication::where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->distinct('employee_id')
            ->count('employee_id');

        $lateCount = Attendance::whereDate('attendance_date', $dateString)
            ->where('status', 'Late')
            ->count();

        $earlyDepartures = Attendance::whereDate('attendance_date', $dateString)
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->whereRaw('TIME(check_out) < ?', [config('attendance.official_end')])
            ->count();

        $absentToday = max(0, $activeCount - $presentToday - $leaveCount);

        return [
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'leaveCount' => $leaveCount,
            'lateCount' => $lateCount,
            'earlyDepartures' => $earlyDepartures,
            'activeCount' => $activeCount,
        ];
    }

    private function calculateOvertimeHours(?string $checkIn, ?string $checkOut): float
    {
        if (!$checkIn || !$checkOut) {
            return 0.0;
        }

        $hoursWorked = Carbon::createFromFormat('H:i:s', $checkOut)
            ->diffInMinutes(Carbon::createFromFormat('H:i:s', $checkIn)) / 60;

        $officialStart = Carbon::createFromFormat('H:i:s', config('attendance.official_start'));
        $officialEnd = Carbon::createFromFormat('H:i:s', config('attendance.official_end'));
        $expectedHours = $officialStart->diffInMinutes($officialEnd) / 60;

        return max(0, round($hoursWorked - $expectedHours, 2));
    }
}
