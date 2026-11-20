<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEnrollment;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceEnrollmentController extends Controller
{
    public function index(): View
    {
        $employees = Employee::active()->with('department')
            ->orderBy('first_name')
            ->get();

        $enrolledIds = Schema::hasTable('attendance_enrollments')
            ? AttendanceEnrollment::pluck('employee_id')->toArray()
            : [];

        $enrollments = Schema::hasTable('attendance_enrollments')
            ? AttendanceEnrollment::with('employee.department')->latest()->get()
            : collect();

        return view('attendance.enrollments', compact('employees', 'enrollments', 'enrolledIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('attendance_enrollments')) {
            return back()->withInput()->with('error', 'Attendance enrollment table is missing. Please run migrations.');
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'allowed_methods' => 'nullable|array',
            'allowed_methods.*' => 'in:manual,qr,pin,fingerprint,face,rfid,nfc',
            'face_photo' => 'nullable|image|max:5120',
            'face_photo_base64' => 'nullable|string',
            'face_descriptor_json' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $allowedMethods = $data['allowed_methods'] ?? ['manual', 'qr', 'pin', 'face', 'fingerprint', 'rfid', 'nfc'];
        $employee = Employee::findOrFail($data['employee_id']);

        // Strict Anti-Duplicate Enrollment Check
        $existingEnrollment = AttendanceEnrollment::where('employee_id', $employee->id)->first();
        if ($existingEnrollment && !$request->has('force_update')) {
            return back()->withInput()->with('error', "DUPLICATE ENROLLMENT BLOCKED: {$employee->full_name} (#{$employee->employee_no}) is ALREADY enrolled in the Attendance System (Enrolled on " . ($existingEnrollment->enrolled_at ? $existingEnrollment->enrolled_at->format('M d, Y') : 'record') . "). Administrators cannot register the same person twice.");
        }

        $plainPin = Str::upper(Str::random(6));
        $qrToken = 'QR-' . Str::upper(Str::random(12));
        $facePhotoPath = $existingEnrollment?->face_photo_path;
        $faceDescriptor = $existingEnrollment?->face_descriptor;

        if ($request->filled('face_photo_base64')) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('face_photo_base64'));
            $fileName = 'attendance/faces/' . uniqid() . '.jpg';
            Storage::disk('public')->put($fileName, base64_decode($imageData));
            $facePhotoPath = $fileName;
        } elseif ($request->hasFile('face_photo')) {
            $facePhotoPath = $request->file('face_photo')->store('attendance/faces', 'public');
        }

        if ($request->filled('face_descriptor_json')) {
            $decoded = json_decode($request->input('face_descriptor_json'), true);
            if (is_array($decoded) && count($decoded) === 128 && count(array_filter($decoded, 'is_numeric')) === 128) {
                $faceDescriptor = array_values(array_map('floatval', $decoded));
            }
        }

        AttendanceEnrollment::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'allowed_methods' => $allowedMethods,
                'face_photo_path' => $facePhotoPath,
                'face_descriptor' => $faceDescriptor,
                'qr_token' => $qrToken,
                'pin_hash' => bcrypt($plainPin),
                'status' => 'active',
                'enrolled_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with([
            'success' => "{$employee->full_name} enrolled for attendance successfully.",
            'generated_pin' => $plainPin,
            'generated_qr_token' => $qrToken,
        ]);
    }
}
