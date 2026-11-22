@extends('layouts.app')

@section('title', 'Employee Dashboard')
@section('page-title', 'Employee Self-Service Portal')
@section('breadcrumb', 'HRMS › Employee Portal')

@section('content')
<div class="animate-in">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $pendingLeave = $leaveApplications->where('status', 'Pending')->count();
    @endphp

    <!-- Personalized Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 55%, #1d4ed8 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                @if($employee->photo_path && file_exists(public_path("storage/{$employee->photo_path}")))
                    <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle border border-3 border-white shadow-sm" style="width:82px;height:82px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-white text-navy d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-sm" style="width:82px;height:82px;font-size:26px;">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge rounded-pill px-2.5 py-1" style="background: rgba(255,255,255,0.18); font-size: 11px;">
                            <i class="bi bi-person-badge me-1"></i>{{ $employee->employment_type }} Staff
                        </span>
                        <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size: 11px;">{{ $employee->employment_status }}</span>
                        <span class="small opacity-75">{{ now()->format('l, d F Y') }}</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $greeting }}, {{ $employee->first_name }}!</h3>
                    <div class="opacity-90 small">
                        {{ $employee->designation ?? 'Ministry Staff' }} &bull; {{ $employee->department?->name ?? 'Unassigned Department' }} &bull; ID: <strong>{{ $employee->employee_no }}</strong>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('id-cards.mine') }}" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-person-badge-fill me-1"></i>My Staff ID Card
                    </a>
                    <a href="{{ route('employee.profile.pdf') }}" class="btn btn-light text-navy fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>My Profile PDF
                    </a>
                    <a href="{{ route('employee.profile') }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-pencil-square me-1"></i>Update Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Leave Status</div>
                            <div class="fs-4 fw-bold {{ $leaveCount > 0 ? 'text-warning' : 'text-success' }} mt-1">
                                {{ $leaveCount > 0 ? 'On Leave' : 'Active / Available' }}
                            </div>
                            <div class="small text-muted mt-1">
                                @if($annualLeaveBalance && !is_null($annualLeaveBalance['remaining']))
                                    {{ $annualLeaveBalance['remaining'] }} / {{ $annualLeaveBalance['allowed'] }} annual days left
                                @else
                                    {{ $pendingLeave }} pending request(s)
                                @endif
                            </div>
                        </div>
                        <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-calendar2-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Excuse Requests</div>
                            <div class="fs-4 fw-bold text-navy mt-1">{{ $excuseStats['pending'] }} <span class="fs-6 fw-normal text-muted">Pending</span></div>
                            <div class="small text-success mt-1">{{ $excuseStats['approved'] }} approved this year</div>
                        </div>
                        <div class="rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-shield-exclamation fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Latest Net Pay</div>
                            @if($latestPayslip)
                                <div class="fs-4 fw-bold text-primary mt-1">LRD {{ number_format((float)$latestPayslip->net_pay_lrd, 2) }}</div>
                                <div class="small text-muted mt-1">
                                    USD {{ number_format((float)$latestPayslip->net_pay_usd, 2) }} &bull; {{ $latestPayslip->period?->name ?? 'Latest' }}
                                </div>
                            @else
                                <div class="fs-5 fw-bold text-muted mt-1">No payslip yet</div>
                                <div class="small text-muted mt-1">Awaiting payroll cycle</div>
                            @endif
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Notifications &amp; Assets</div>
                            <div class="fs-4 fw-bold text-navy mt-1">{{ $unreadNotificationsCount }} <span class="fs-6 fw-normal text-muted">Unread</span></div>
                            <div class="small text-muted mt-1">{{ $assignedAssets->count() }} asset(s) &bull; {{ $employee->years_of_service ?? 'New' }} service</div>
                        </div>
                        <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-bell-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Self-Service Action Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="fw-semibold text-navy small"><i class="bi bi-grid-fill text-primary me-1"></i>Self-Service Actions:</div>
            <div class="d-flex flex-wrap gap-2">
                @can('apply leave')<a href="{{ route('leave.apply') }}" class="btn btn-sm btn-navy"><i class="bi bi-calendar-plus me-1"></i>Apply for Leave</a>@endcan
                @can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold"><i class="bi bi-shield-exclamation me-1"></i>Submit Excuse</a>@endcan
                <a href="{{ route('employee.payslips') }}" class="btn btn-sm btn-outline-success fw-semibold"><i class="bi bi-receipt me-1"></i>My Payslips</a>
                <a href="{{ route('employee.profile') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-person me-1"></i>My Profile</a>
                @can('view internal vacancies')<a href="{{ route('employee.recruitment') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-briefcase me-1"></i>Internal Jobs</a>@endcan
                @can('view directory')<a href="{{ route('directory.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people me-1"></i>Directory</a>@endcan
                <a href="{{ route('employee.notifications') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-bell me-1"></i>Notifications
                    @if($unreadNotificationsCount > 0)<span class="badge bg-danger ms-1">{{ $unreadNotificationsCount }}</span>@endif
                </a>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Workspace -->
    <div class="row g-4">
        <!-- Left Column (Main Tables) -->
        <div class="col-12 col-xl-8">
            <!-- Recent Payslips -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-receipt-cutoff me-2 text-success"></i>Recent Payslips</h5>
                    <a href="{{ route('employee.payslips') }}" class="btn btn-sm btn-outline-navy">All Payslips</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Pay Period</th>
                                    <th class="text-end">Gross (LRD)</th>
                                    <th class="text-end">Deductions (LRD)</th>
                                    <th class="text-end">Net Pay (LRD / USD)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Payslip</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayslips as $slip)
                                    @php
                                        $grossLrd = (float)$slip->basic_salary_lrd + (float)$slip->allowances_lrd + (float)$slip->overtime_lrd;
                                        $dedLrd = (float)$slip->tax_lrd + (float)$slip->pension_lrd + (float)$slip->deductions_lrd;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 fw-semibold text-navy">{{ $slip->period?->name ?? $slip->created_at?->format('M Y') }}</td>
                                        <td class="text-end font-monospace">{{ number_format($grossLrd, 2) }}</td>
                                        <td class="text-end font-monospace text-danger">-{{ number_format($dedLrd, 2) }}</td>
                                        <td class="text-end font-monospace">
                                            <div class="fw-bold text-success">LRD {{ number_format((float)$slip->net_pay_lrd, 2) }}</div>
                                            <small class="text-muted">USD {{ number_format((float)$slip->net_pay_usd, 2) }}</small>
                                        </td>
                                        <td><span class="badge {{ $slip->status === 'Paid' ? 'bg-success' : ($slip->status === 'Approved' ? 'bg-primary' : 'bg-secondary') }}">{{ $slip->status }}</span></td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('payroll.payslip', $slip->id) }}" class="btn btn-sm btn-outline-navy" title="View Payslip"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('payroll.payslip.pdf', $slip->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No payslips generated yet. Once payroll is processed, your payslips will appear here.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leave Requests -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-calendar2-check me-2 text-primary"></i>My Leave Requests</h5>
                    @can('apply leave')
                        <a href="{{ route('leave.apply') }}" class="btn btn-sm btn-navy"><i class="bi bi-plus-lg me-1"></i>Apply for Leave</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-3">Leave Type</th><th>Dates</th><th>Days</th><th>Reason</th><th class="pe-3">Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($leaveApplications as $application)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $application->leaveType?->name ?? 'Leave' }}</td>
                                        <td class="small">{{ $application->start_date?->format('d M Y') }} &ndash; {{ $application->end_date?->format('d M Y') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $application->days_requested }}d</span></td>
                                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($application->reason, 45) }}</td>
                                        <td class="pe-3">
                                            <span class="badge {{ $application->status === 'Approved' ? 'bg-success' : ($application->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $application->status }}
                                            </span>
                                            @if($application->status === 'Pending')
                                                <small class="d-block text-primary fw-semibold" style="font-size:10.5px;">{{ $application->current_stage_label }}</small>
                                            @endif
                                            @if($application->rejection_reason)
                                                <small class="d-block text-danger" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($application->rejection_reason, 40) }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted text-center py-4">No leave requests submitted yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Excuse Requests -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-shield-exclamation me-2 text-warning"></i>My Excuse Requests</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('excuses.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                        @can('submit excuse')
                            <a href="{{ route('excuses.create') }}" class="btn btn-sm btn-navy"><i class="bi bi-plus-lg me-1"></i>Submit Excuse</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-3">Date</th><th>Type</th><th>Reason</th><th>Proof</th><th class="pe-3">Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($excuseApplications as $excuse)
                                    <tr>
                                        <td class="ps-3 small fw-semibold">{{ $excuse->excuse_date?->format('d M Y') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $excuse->excuse_type }}</span></td>
                                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($excuse->reason, 60) }}</td>
                                        <td>
                                            @if($excuse->attachment_path)
                                                <a href="{{ asset('storage/' . $excuse->attachment_path) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 small"><i class="bi bi-paperclip"></i></a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="pe-3">
                                            <span class="badge {{ $excuse->status === 'Approved' ? 'bg-success' : ($excuse->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $excuse->status }}</span>
                                            @if($excuse->rejection_reason)
                                                <small class="d-block text-danger" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($excuse->rejection_reason, 40) }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted text-center py-4">No excuses submitted yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Training & Internal Applications -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>My Trainings &amp; Development</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($trainings as $et)
                                    <div class="list-group-item px-3 py-2.5 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small text-navy">{{ $et->training?->title ?? 'Training Program' }}</div>
                                            <small class="text-muted">{{ $et->completion_date?->format('d M Y') ?? 'In Progress' }}</small>
                                        </div>
                                        <span class="badge {{ $et->status === 'Completed' ? 'bg-success' : 'bg-info text-dark' }}">{{ $et->status }}</span>
                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-4">No training enrollments recorded yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Internal Applications</h6>
                            @can('view internal vacancies')<a href="{{ route('employee.recruitment') }}" class="small text-decoration-none">Explore</a>@endcan
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($recruitmentApplications as $application)
                                    <div class="list-group-item px-3 py-2.5 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small text-navy">{{ $application->vacancy?->title ?? 'Vacancy' }}</div>
                                            <small class="text-muted">{{ $application->created_at?->format('d M Y') }}</small>
                                        </div>
                                        <span class="badge bg-primary">{{ $application->status }}</span>
                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-4">No internal job applications yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column (Sidebar Cards) -->
        <div class="col-12 col-xl-4">
            <!-- Leave Balance Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>{{ now()->year }} Leave Allowances</h6>
                </div>
                <div class="card-body">
                    @forelse($leaveBalances->take(4) as $bal)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold text-navy">{{ $bal['name'] }}</span>
                                <span class="text-muted">
                                    @if($bal['allowed'] > 0)
                                        <strong>{{ $bal['remaining'] }}</strong> / {{ $bal['allowed'] }} days left
                                    @else
                                        {{ $bal['used'] }} days used
                                    @endif
                                </span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $bal['percent'] >= 80 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $bal['percent'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No active leave types configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- Employment Snapshot -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Employment Snapshot</h6>
                    <a href="{{ route('employee.profile') }}" class="small text-decoration-none">Edit</a>
                </div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Employee No</span><span class="fw-bold text-navy">{{ $employee->employee_no }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Department</span><span class="fw-semibold text-navy">{{ $employee->department?->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Supervisor</span><span class="fw-semibold text-navy">{{ $employee->supervisor?->full_name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Salary Grade</span><span class="fw-semibold text-navy">{{ $employee->salary_grade ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Date Joined</span><span class="fw-semibold text-navy">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Official Email</span><span class="fw-semibold text-navy">{{ $employee->official_email ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5"><span class="text-muted">Latest Appraisal</span><span class="fw-semibold text-navy">{{ $latestAppraisal ? ($latestAppraisal->rating . ' (' . $latestAppraisal->score . '%)') : 'Not yet appraised' }}</span></div>
                </div>
            </div>

            <!-- Assigned Assets -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-box-seam me-2 text-info"></i>Assigned Ministry Assets</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($assignedAssets as $assign)
                            <div class="list-group-item px-3 py-2.5 d-flex justify-content-between align-items-center small">
                                <div>
                                    <div class="fw-semibold text-navy">{{ $assign->asset?->name ?? 'Asset' }}</div>
                                    <small class="text-muted">Issued {{ $assign->assigned_date?->format('d M Y') }}</small>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $assign->asset?->asset_tag ?? 'Active' }}</span>
                            </div>
                        @empty
                            <div class="text-center text-muted small py-4">No equipment currently assigned.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-bell me-2 text-warning"></i>Recent Notifications</h6>
                    <a href="{{ route('employee.notifications') }}" class="small text-decoration-none">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentNotifications as $notif)
                            <a href="{{ route('employee.notifications.read', $notif->id) }}" class="list-group-item list-group-item-action px-3 py-2.5 {{ is_null($notif->read_at) ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between small">
                                    <strong class="text-navy">{{ $notif->title }}</strong>
                                    <span class="text-muted" style="font-size:11px;">{{ $notif->created_at?->diffForHumans(null, true) }}</span>
                                </div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($notif->message, 75) }}</div>
                            </a>
                        @empty
                            <div class="text-center text-muted small py-4">You're all caught up!</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
