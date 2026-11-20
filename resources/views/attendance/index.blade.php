@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')
<div class="container-fluid py-4">
    <x-ui.page-header
        title="Attendance Dashboard"
        subtitle="HRMIS attendance management and corrections"
    >
        <x-slot:actions>
            <x-ui.badge>{{ now()->format('l, d F Y') }}</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <x-ui.kpi-card
                title="Present Today"
                :value="number_format($stats['presentToday'])"
                icon="bi bi-check-circle-fill"
                badgeColor="#166534"
                badgeBg="#DCFCE7"
            />
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <x-ui.kpi-card
                title="Absent Today"
                :value="number_format($stats['absentToday'])"
                icon="bi bi-x-circle-fill"
                badgeColor="#DC2626"
                badgeBg="#FEE2E2"
            />
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <x-ui.kpi-card
                title="Late Employees"
                :value="number_format($stats['lateCount'])"
                icon="bi bi-clock-fill"
                badgeColor="#92400E"
                badgeBg="#FEF3C7"
            />
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <x-ui.kpi-card
                title="Employees on Leave"
                :value="number_format($stats['leaveCount'])"
                icon="bi bi-airplane-fill"
                badgeColor="#3547D5"
                badgeBg="#E0E7FF"
            />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <x-ui.card title="Attendance Records" :subtitle="$date" bodyClass="p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Source</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $record->employee?->full_name }}</div>
                                    <div class="text-muted small">{{ $record->employee?->employee_no }}</div>
                                </td>
                                <td>{{ $record->employee?->department?->name ?? 'N/A' }}</td>
                                <td>{{ $record->check_in ?? '—' }}</td>
                                <td>{{ $record->check_out ?? '—' }}</td>
                                <td>
                                    <x-ui.badge :variant="$record->status === 'Late' ? 'warning' : ($record->status === 'Present' ? 'success' : 'default')">
                                        {{ $record->status }}
                                    </x-ui.badge>
                                </td>
                                <td>{{ $record->source }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state
                                        title="No attendance records"
                                        message="No attendance records for this date."
                                        icon="bi bi-calendar-x"
                                    />
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="col-12 col-xl-4">
            <x-ui.card title="Manual Correction" class="mb-4">
                <form method="POST" action="{{ route('attendance.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }} ({{ $employee->employee_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Attendance Date</label>
                        <input type="date" name="attendance_date" value="{{ $date }}" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Check In</label><input type="time" name="check_in" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Check Out</label><input type="time" name="check_out" class="form-control"></div>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option>Present</option>
                            <option>Late</option>
                            <option>Absent</option>
                            <option>Half Day</option>
                            <option>On Leave</option>
                            <option>Holiday</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control"></textarea>
                    </div>
                    <x-ui.button variant="navy" type="submit" class="w-100">Save Correction</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Attendance Rules Snapshot" class="mb-4" bodyClass="p-4 small text-muted">
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Official start</span><strong>{{ config('attendance.official_start') }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Official end</span><strong>{{ config('attendance.official_end') }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Half-day threshold</span><strong>{{ config('attendance.half_day_hours') }} hours</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Weekend days</span><strong>{{ implode(', ', config('attendance.weekend_days')) }}</strong></div>
            </x-ui.card>

            <x-ui.card title="Registered Devices" bodyClass="p-0">
                <div class="list-group list-group-flush">
                    @forelse($devices as $device)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $device->name }}</div>
                                <div class="text-muted small">{{ $device->device_id }} · {{ $device->location ?? 'No location' }}</div>
                            </div>
                            <div class="text-end small">
                                <x-ui.badge :variant="$device->status === 'active' ? 'success' : 'default'">{{ ucfirst($device->status) }}</x-ui.badge>
                                <div class="text-muted mt-1">{{ $device->last_synced_at?->diffForHumans() ?? 'Never synced' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No attendance devices registered yet.</div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
