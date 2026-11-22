@extends('layouts.app')
@section('title', 'External Attendance Sync')
@section('page-title', 'External Attendance Summary Bridge')
@section('breadcrumb', 'HRMS › Finance & Payroll › External Attendance Sync')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-3">
        <div>
            <h1>External Attendance Summary Bridge</h1>
            <p>Import monthly attendance totals (present, absent, late, overtime) from your existing attendance system into HR reports &amp; Draft payroll adjustments.</p>
        </div>
        <a href="{{ route('external-attendance.template') }}" class="btn btn-outline-navy fw-semibold">
            <i class="bi bi-download me-1"></i>Download Pre-Filled CSV Template
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Imported Staff ({{ $selectedMonth }})</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ $stats['total_staff'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Average Attendance Rate</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $stats['avg_rate'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Absent Days</div>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ $stats['total_absent'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Overtime Hours</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ number_format((float)$stats['total_overtime'], 1) }} hrs</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Upload CSV Form -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-cloud-upload-fill text-primary me-2"></i>Import Monthly Attendance Summary CSV</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('external-attendance.import') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Month (YYYY-MM) <span class="text-danger">*</span></label>
                            <input type="month" name="period_month" class="form-control" value="{{ old('period_month', $selectedMonth) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Source System Name</label>
                            <input type="text" name="source_system" class="form-control" value="{{ old('source_system', 'Ministry Biometric System') }}" placeholder="e.g. ZKTeco / External">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Link Draft Payroll <span class="text-muted">(optional)</span></label>
                            <select name="payroll_period_id" class="form-select">
                                <option value="">None</option>
                                @foreach($draftPeriods as $dp)
                                    <option value="{{ $dp->id }}">{{ $dp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Attendance Summary CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <div class="form-text">Columns: <code>employee_no, working_days, days_present, days_absent, days_late, overtime_hours, notes</code></div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-navy w-100"><i class="bi bi-upload me-1"></i>Import CSV</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Optional Sync to Draft Payroll -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-cash-coin text-success me-2"></i>Sync to Draft Payroll (Optional)</h5>
                </div>
                <div class="card-body">
                    @if($draftPeriods->isEmpty())
                        <div class="text-muted small py-3">
                            <i class="bi bi-info-circle me-1"></i>No <strong>Draft</strong> payroll cycles exist right now. Create a Draft cycle in <a href="{{ route('payroll.index') }}">Payroll Runs</a> to push overtime or absenteeism adjustments.
                        </div>
                    @else
                        <form method="POST" action="{{ route('external-attendance.apply-payroll') }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="period_month" value="{{ $selectedMonth }}">
                            <div class="col-12">
                                <label class="form-label">Target Draft Payroll Cycle <span class="text-danger">*</span></label>
                                <select name="payroll_period_id" class="form-select" required>
                                    @foreach($draftPeriods as $dp)
                                        <option value="{{ $dp->id }}">{{ $dp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="apply_overtime" value="1" id="applyOtCheck" checked>
                                    <label class="form-check-label small fw-semibold" for="applyOtCheck">Add Overtime Pay</label>
                                </div>
                                <input type="number" step="1" min="0" name="overtime_rate_lrd" class="form-control form-control-sm mt-1" value="500" placeholder="LRD per OT hour">
                                <div class="form-text" style="font-size:11px;">LRD per overtime hour</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="apply_absent_deduction" value="1" id="applyAbsCheck">
                                    <label class="form-check-label small fw-semibold" for="applyAbsCheck">Deduct Absent Days</label>
                                </div>
                                <div class="form-text mt-1" style="font-size:11px;">Uses daily rate (Basic LRD ÷ Working Days) per absent day.</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100 btn-sm fw-semibold" {{ $summaries->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-repeat me-1"></i>Apply {{ $selectedMonth }} Adjustments to Payroll
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Imported Summaries Table -->
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-table text-primary me-2"></i>Imported Attendance Summaries ({{ $selectedMonth }})</h5>
            @if($availableMonths->isNotEmpty())
                <form method="GET" action="{{ route('external-attendance.index') }}" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($availableMonths as $m)
                            <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department</th>
                        <th class="text-center">Present / Working</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">Overtime</th>
                        <th class="text-center">Rate</th>
                        <th class="pe-4">Source / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $s)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-navy">{{ $s->employee?->full_name }}</div>
                                <small class="text-muted font-monospace">{{ $s->employee?->employee_no }}</small>
                            </td>
                            <td class="small">{{ $s->employee?->department?->name ?? '—' }}</td>
                            <td class="text-center fw-semibold">{{ $s->days_present }} / {{ $s->working_days }}</td>
                            <td class="text-center"><span class="badge {{ $s->days_absent > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted' }}">{{ $s->days_absent }}</span></td>
                            <td class="text-center"><span class="badge {{ $s->days_late > 0 ? 'bg-warning-subtle text-dark' : 'bg-light text-muted' }}">{{ $s->days_late }}</span></td>
                            <td class="text-center font-monospace fw-semibold text-primary">{{ number_format((float)$s->overtime_hours, 1) }}h</td>
                            <td class="text-center">
                                <span class="badge {{ $s->attendance_rate >= 90 ? 'bg-success' : ($s->attendance_rate >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $s->attendance_rate }}%</span>
                            </td>
                            <td class="pe-4 small text-muted">
                                <div>{{ $s->source_system }}</div>
                                @if($s->notes)<div>{{ $s->notes }}</div>@endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cloud-upload fs-2 d-block mb-2 opacity-50"></i>
                                No external attendance summaries imported for <strong>{{ $selectedMonth }}</strong> yet. Download the pre-filled template above to import.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
