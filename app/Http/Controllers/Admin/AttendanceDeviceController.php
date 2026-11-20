<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceDeviceController extends Controller
{
    public function index(): View
    {
        $devices = Schema::hasTable('attendance_devices')
            ? AttendanceDevice::with('department')->latest()->get()
            : collect();

        $departments = Department::active()->orderBy('name')->get();

        return view('attendance.devices', compact('devices', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('attendance_devices')) {
            return back()->withInput()->with('error', 'Attendance device registration is not ready yet. Run the attendance_devices migration first.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'device_type' => 'required|in:tablet,kiosk,desktop,biometric',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius_meters' => 'nullable|integer|min:25|max:5000',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:active,inactive,maintenance',
            'software_version' => 'nullable|string|max:50',
        ]);

        $plainToken = Str::random(40);

        $device = AttendanceDevice::create([
            'device_id' => $deviceId = AttendanceDevice::generateDeviceId(),
            'name' => $data['name'],
            'device_type' => $data['device_type'],
            'location' => $data['location'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'geofence_radius_meters' => $data['geofence_radius_meters'] ?? 150,
            'department_id' => $data['department_id'] ?? null,
            'status' => $data['status'],
            'token_hash' => hash_hmac('sha256', $plainToken, config('app.key')),
            'software_version' => $data['software_version'] ?? null,
        ]);

        return back()->with([
            'success' => 'Attendance device registered successfully.',
            'generated_device_token' => $plainToken,
            'generated_device_id' => $device->device_id,
        ]);
    }
}
