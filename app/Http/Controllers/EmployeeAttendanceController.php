<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDevice;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function show(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Employee record not found for this account.');

        $deviceId = $request->query('device_id') ?: $request->cookie('attendance_device_id');
        $deviceToken = (string) ($request->query('device_token') ?: $request->cookie('attendance_device_token', ''));
        $device = $deviceId && Schema::hasTable('attendance_devices')
            ? AttendanceDevice::where('device_id', $deviceId)->first()
            : null;

        return view('employee.attendance', compact('employee', 'deviceId', 'deviceToken', 'device'));
    }

    public function punch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_identifier' => 'required|string|max:50',
            'action' => 'required|in:clock_in,clock_out',
            'device_id' => 'required|string',
            'device_token' => 'required|string',
            'captured_photo' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'submission_uuid' => 'required|uuid',
        ]);

        if ((int) $request->user()->employee_id !== (int) optional($this->attendanceService->resolveEmployee($data['employee_identifier']))->id) {
            throw ValidationException::withMessages([
                'employee_identifier' => ['Use the employee number linked to your signed-in account.'],
            ]);
        }

        $result = $this->attendanceService->recordTerminalEvent([
            ...$data,
            'attendance_method' => 'manual',
            'submitted_at' => now()->toIso8601String(),
            'attendance_date' => now()->toDateString(),
            'terminal_location' => $data['device_id'],
        ], $request);

        return response()->json([
            'message' => $result['message'],
            'attendance' => [
                'check_in' => $result['attendance']->check_in,
                'check_out' => $result['attendance']->check_out,
                'status' => $result['attendance']->status,
            ],
        ]);
    }
}