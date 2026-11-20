@extends('layouts.app')

@section('title', 'Employee Dashboard')
@section('page-title', 'Employee Dashboard')
@section('breadcrumb', 'HRMS › My Dashboard')

@section('content')
<div class="animate-in">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $pendingLeave = $leaveApplications->where('status', 'Pending')->count();
        $pendingExcuse = $excuseApplications->where('status', 'Pending')->count();
    @endphp
    <div class="card border-0 shadow-sm mb-4 text-white" style="background:linear-gradient(135deg,#0b1f4b,#1d3f8f);">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                @if($employee->photo_url)
                    <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle border border-2 border-white" style="width:84px;height:84px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:84px;height:84px;font-size:26px;">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="small opacity-75">{{ now()->format('l, d F Y') }}</div>
                    <h3 class="fw-bold mb-1">{{ $greeting }}, {{ $employee->first_name }}!</h3>
                    <div class="opacity-90">Welcome to your HRMS dashboard. Here is a summary of your records at the Ministry.</div>
                    <div class="small mt-1 opacity-75">{{ $employee->designation }} &bull; {{ $employee->department?->name ?? 'Unassigned Department' }}</div>
                </div>
                <span class="badge bg-success align-self-md-start px-3 py-2">{{ $employee->employment_status }}</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Leave Status</div>
                <div class="fs-5 fw-bold {{ $leaveCount > 0 ? 'text-warning' : 'text-success' }}">{{ $leaveCount > 0 ? 'On Leave' : 'Available' }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Pending Leave</div>
                <div class="fs-5 fw-bold text-navy">{{ $pendingLeave }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Pending Excuses</div>
                <div class="fs-5 fw-bold text-navy">{{ $pendingExcuse }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Years of Service</div>
                <div class="fs-5 fw-bold text-navy">{{ $employee->date_joined ? number_format($employee->date_joined->diffInYears(now()), 1) : '—' }}</div>
            </div></div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @can('apply leave')<a href="{{ route('leave.apply') }}" class="btn btn-navy"><i class="bi bi-calendar-plus me-1"></i>Apply for Leave</a>@endcan
        @can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-outline-secondary"><i class="bi bi-shield-exclamation me-1"></i>Submit Excuse</a>@endcan
        <a href="{{ route('employee.profile') }}" class="btn btn-outline-secondary"><i class="bi bi-person me-1"></i>My Profile</a>
        <a href="{{ route('employee.payslips') }}" class="btn btn-outline-secondary"><i class="bi bi-receipt me-1"></i>Payslips</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold text-navy mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>My Profile Snapshot</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Employee ID</label>
                    <div class="fw-bold text-navy">{{ $employee->employee_no }}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Official Email</label>
                    <div class="fw-bold text-navy">{{ $employee->official_email ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Department</label>
                    <div class="fw-bold text-navy">{{ $employee->department?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small d-block mb-1">Date Joined</label>
                    <div class="fw-bold text-navy">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0"><i class="bi bi-calendar2-check me-2 text-primary"></i>My Leave Requests</h5>
                @can('apply leave')
                    <a href="{{ route('leave.apply') }}" class="btn btn-sm btn-navy"><i class="bi bi-plus-lg me-1"></i>Apply</a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($leaveApplications as $application)
                        <tr>
                            <td>{{ $application->leaveType?->name ?? 'Leave' }}</td>
                            <td>{{ $application->start_date?->format('d M Y') }} - {{ $application->end_date?->format('d M Y') }}</td>
                            <td>{{ $application->days_requested }}</td>
                            <td><span class="badge {{ $application->status === 'Approved' ? 'bg-success' : ($application->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $application->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">No leave requests submitted yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0"><i class="bi bi-shield-exclamation me-2 text-primary"></i>My Excuses</h5>
                @can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-sm btn-navy"><i class="bi bi-plus-lg me-1"></i>Submit Excuse</a>@endcan
            </div>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Date</th><th>Type</th><th>Reason</th><th>Status</th></tr></thead><tbody>
                @forelse($excuseApplications as $excuse)
                    <tr><td>{{ $excuse->excuse_date?->format('d M Y') }}</td><td>{{ $excuse->excuse_type }}</td><td>{{ \Illuminate\Support\Str::limit($excuse->reason, 70) }}</td><td><span class="badge {{ $excuse->status === 'Approved' ? 'bg-success' : ($excuse->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $excuse->status }}</span></td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No excuses submitted yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>

    @can('view internal vacancies')
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold text-navy mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>My Recruitment Applications</h5><a href="{{ route('employee.recruitment') }}" class="btn btn-sm btn-navy">Explore Opportunities</a></div>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Position</th><th>Submitted</th><th>Status</th></tr></thead><tbody>
                @forelse($recruitmentApplications as $application)
                    <tr><td>{{ $application->vacancy?->title ?? 'Vacancy removed' }}</td><td>{{ $application->created_at?->format('d M Y') }}</td><td><span class="badge bg-primary">{{ $application->status }}</span></td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center py-3">No internal recruitment applications yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
    }
</script>
@endpush
