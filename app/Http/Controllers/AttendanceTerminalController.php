<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AttendanceTerminalController extends Controller
{
    public function show(Request $request): View
    {
        $device = null;
        $deviceId = $request->query('device_id');
        $deviceToken = (string) $request->query('device_token', '');

        if ($deviceId && Schema::hasTable('attendance_devices')) {
            $device = AttendanceDevice::with('department')->where('device_id', $deviceId)->first();
        }

        return view('attendance.terminal', [
            'device' => $device,
            'deviceId' => $deviceId,
            'deviceToken' => $deviceToken,
            'terminalName' => $device?->name ?? 'Ministry Attendance Terminal',
        ]);
    }
}
