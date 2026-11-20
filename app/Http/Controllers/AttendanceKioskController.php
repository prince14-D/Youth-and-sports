<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceKioskController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function show(): View
    {
        return view('attendance.kiosk');
    }

    public function punch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_identifier' => 'required|string',
            'action' => 'required|in:clock_in,clock_out',
            'captured_photo' => 'nullable|string',
        ]);

        $result = $this->attendanceService->recordKioskPunch(
            trim($data['employee_identifier']),
            $data['action'],
            $data['captured_photo'] ?? null,
            $request
        );

        return response()->json([
            'message' => $result['message'],
            'employee' => [
                'name' => $result['employee']->full_name,
                'employee_no' => $result['employee']->employee_no,
                'department' => $result['employee']->department?->name,
            ],
            'attendance' => [
                'check_in' => $result['attendance']->check_in,
                'check_out' => $result['attendance']->check_out,
                'status' => $result['attendance']->status,
            ],
        ]);
    }
}
