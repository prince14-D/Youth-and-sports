<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function punch(Request $request): JsonResponse
    {
        $request->validate([
            'employee_identifier' => 'nullable|string',
            'action'              => 'required|in:clock_in,clock_out',
            'device_id'           => 'required|string',
            'device_token'        => 'required|string',
            'submission_uuid'     => 'required|string',
            'attendance_method'   => 'required|in:manual,qr,pin,fingerprint,face,rfid,nfc',
            'captured_photo'      => 'required|string',
            'face_descriptor'     => 'nullable|array|size:128',
            'face_descriptor.*'   => 'numeric',
            'terminal_location'   => 'nullable|string|max:255',
            'attendance_date'     => 'nullable|date',
            'submitted_at'        => 'nullable|date',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'notes'               => 'nullable|string|max:255',
        ]);

        $result = $this->attendanceService->recordTerminalEvent($request->all(), $request);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'idempotent' => $result['idempotent'],
            'employee' => [
                'id' => $result['attendance']->employee?->id,
                'name' => $result['attendance']->employee?->full_name,
                'employee_no' => $result['attendance']->employee?->employee_no,
                'department' => $result['attendance']->employee?->department?->name,
                'photo_url' => $result['attendance']->employee?->photo_url,
            ],
            'attendance' => [
                'id' => $result['attendance']->id,
                'employee' => $result['attendance']->employee?->full_name,
                'employee_no' => $result['attendance']->employee?->employee_no,
                'attendance_date' => $result['attendance']->attendance_date?->toDateString(),
                'check_in' => $result['attendance']->check_in,
                'check_out' => $result['attendance']->check_out,
                'clock_in_photo_path' => $result['attendance']->clock_in_photo_path,
                'clock_out_photo_path' => $result['attendance']->clock_out_photo_path,
                'status' => $result['attendance']->status,
                'device_id' => $result['attendance']->device_id,
                'sync_status' => $result['attendance']->sync_status,
            ],
        ]);
    }
}
