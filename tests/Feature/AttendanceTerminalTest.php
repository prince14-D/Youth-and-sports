<?php

namespace Tests\Feature;

use App\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\AttendanceEnrollment;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class AttendanceTerminalTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_authenticated_clock_in_is_recorded_and_idempotent(): void
    {
        $submittedAt = Carbon::parse(now()->toDateString() . ' 08:00:00');

        $canvas = imagecreatetruecolor(32, 32);
        $background = imagecolorallocate($canvas, 24, 91, 179);
        imagefilledrectangle($canvas, 0, 0, 31, 31, $background);
        $facePhotoBinary = null;
        ob_start();
        imagepng($canvas);
        $facePhotoBinary = ob_get_clean();
        imagedestroy($canvas);

        if ($facePhotoBinary === false) {
            $this->fail('Unable to generate the test face photo.');
        }

        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_no' => 'MYS-2026-0001',
            'first_name' => 'Ava',
            'last_name' => 'Cole',
            'gender' => 'Female',
            'designation' => 'Officer',
            'employment_type' => 'Permanent',
            'employment_status' => 'Active',
            'date_joined' => now()->toDateString(),
            'department_id' => $department->id,
        ]);

        $deviceToken = 'terminal-secret-token';

        AttendanceDevice::create([
            'device_id' => 'ATD-LIB-0001',
            'name' => 'Main Lobby Terminal',
            'device_type' => 'tablet',
            'location' => 'Ministry Lobby',
            'department_id' => $department->id,
            'status' => 'active',
            'token_hash' => hash_hmac('sha256', $deviceToken, config('app.key')),
            'software_version' => '1.0.0',
        ]);

        Storage::disk('public')->put('attendance/faces/ava-cole.png', $facePhotoBinary);

        AttendanceEnrollment::create([
            'employee_id' => $employee->id,
            'allowed_methods' => ['face'],
            'face_photo_path' => 'attendance/faces/ava-cole.png',
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $payload = [
            'action' => 'clock_in',
            'device_id' => 'ATD-LIB-0001',
            'device_token' => $deviceToken,
            'attendance_method' => 'face',
            'captured_photo' => 'data:image/png;base64,' . base64_encode($facePhotoBinary),
            'terminal_location' => 'Ministry Lobby',
            'attendance_date' => $submittedAt->toDateString(),
            'submitted_at' => $submittedAt->toIso8601String(),
            'submission_uuid' => (string) Str::uuid(),
        ];

        $service = app(AttendanceService::class);
        $request = Request::create('/', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $result = $service->recordTerminalEvent($payload, $request);
        $storedAttendanceDate = $submittedAt->copy()->startOfDay()->format('Y-m-d H:i:s');

        $this->assertSame('Clock-in recorded successfully.', $result['message']);
        $this->assertFalse($result['idempotent']);
        $this->assertSame('Present', $result['attendance']->status);

        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employee->id,
            'attendance_date' => $storedAttendanceDate,
            'check_in' => $submittedAt->format('H:i:s'),
            'device_id' => 'ATD-LIB-0001',
            'sync_status' => 'synced',
        ]);

        $repeat = $service->recordTerminalEvent($payload, $request);
        $this->assertTrue($repeat['idempotent']);

        $this->assertCount(1, Attendance::all());
    }
}
