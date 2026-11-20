@extends('layouts.app')

@section('title', 'Attendance Devices')
@section('page-title', 'Attendance Devices')
@section('breadcrumb', 'HRMIS › Attendance › Devices')

@section('content')
<div class="animate-in">
    <x-ui.page-header
        title="Attendance Devices"
        subtitle="Register and manage terminals that are allowed to submit attendance records."
    />

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('generated_device_token'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start gap-2 mb-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>
                    <div class="fw-bold">Device registered successfully!</div>
                    <div class="small text-muted">Save this device token — it is shown only once for security.</div>
                </div>
            </div>
            <div class="mb-2"><strong>Device Token:</strong> <code class="user-select-all">{{ session('generated_device_token') }}</code></div>
            @if(session('generated_device_id'))
                <div class="mb-2"><strong>Device ID:</strong> <code>{{ session('generated_device_id') }}</code></div>
                <div class="mt-3">
                    <strong>Terminal Launch URL:</strong><br>
                    <a href="{{ route('attendance.terminal') }}?device_id={{ session('generated_device_id') }}&device_token={{ session('generated_device_token') }}"
                       target="_blank"
                       class="btn btn-navy btn-sm mt-1">
                        <i class="bi bi-display me-1"></i> Open Terminal Now
                    </a>
                    <div class="small text-muted mt-1">Copy and bookmark this link for your attendance kiosk device.</div>
                </div>
            @endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <x-ui.card title="Register Device" icon="bi bi-device-hdd" class="h-100">
                    <form method="POST" action="{{ route('attendance.devices.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label">Device Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Entrance kiosk" required>
                        </div>
                        <div>
                            <label class="form-label">Device Type</label>
                            <select name="device_type" class="form-select" required>
                                <option value="tablet">Tablet</option>
                                <option value="kiosk">Kiosk</option>
                                <option value="desktop">Desktop</option>
                                <option value="biometric">Biometric</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="Ministry main entrance">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Latitude</label>
                                <input type="number" name="latitude" class="form-control" step="0.0000001" placeholder="6.3000">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Longitude</label>
                                <input type="number" name="longitude" class="form-control" step="0.0000001" placeholder="-10.8000">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Geofence Radius (metres)</label>
                            <input type="number" name="geofence_radius_meters" class="form-control" min="25" max="5000" value="150" required>
                            <div class="form-text">Employees must be within this distance of the device location.</div>
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">All departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Software Version</label>
                                <input type="text" name="software_version" class="form-control" placeholder="1.0.0">
                            </div>
                        </div>
                        <button class="btn btn-navy" type="submit">Register Device</button>
                    </form>
            </x-ui.card>
        </div>

        <div class="col-12 col-lg-7">
            <x-ui.card title="Registered Devices" icon="bi bi-list-check" class="h-100" bodyClass="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Device ID</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Last Sync</th>
                                    <th>Terminal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $device->name }}</div>
                                            <div class="text-muted small">{{ $device->location ?? 'No location' }}</div>
                                        </td>
                                        <td class="font-monospace small">{{ $device->device_id }}</td>
                                        <td>{{ ucfirst($device->device_type) }}</td>
                                        <td>
                                            <x-ui.badge :variant="$device->status === 'active' ? 'success' : ($device->status === 'maintenance' ? 'warning' : 'default')">
                                                {{ ucfirst($device->status) }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="text-muted">{{ $device->last_synced_at?->diffForHumans() ?? 'Never' }}</td>
                                        <td>
                                            <a href="{{ route('attendance.terminal') }}?device_id={{ $device->device_id }}"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm rounded-pill px-2"
                                               title="Open terminal for this device (token required in URL)">
                                                <i class="bi bi-display"></i> Launch
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <x-ui.empty-state
                                                title="No attendance devices"
                                                message="No attendance devices registered yet."
                                                icon="bi bi-device-hdd"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
