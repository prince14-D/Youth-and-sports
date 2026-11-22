@extends('layouts.app')

@section('title', 'Contractor Portal')
@section('page-title', 'Contractor Self-Service Portal')
@section('breadcrumb', 'HRMS › Contractor Portal')

@section('content')
<div class="animate-in">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $hasPendingRenewal = $renewalHistory->where('status', 'Pending')->isNotEmpty();
    @endphp

    <!-- Contractor Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #064e3b 0%, #0f5132 45%, #123f7a 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                @if($contractor->photo_path && file_exists(public_path("storage/{$contractor->photo_path}")))
                    <img src="{{ $contractor->photo_url }}" alt="{{ $contractor->full_name }}" class="rounded-circle border border-3 border-white shadow-sm" style="width:82px;height:82px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-sm" style="width:82px;height:82px;font-size:26px;">
                        {{ strtoupper(substr($contractor->first_name, 0, 1) . substr($contractor->last_name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge rounded-pill px-2.5 py-1" style="background: rgba(255,255,255,0.2); font-size: 11px;">
                            <i class="bi bi-file-earmark-person me-1"></i>MINISTRY CONTRACTOR
                        </span>
                        <span class="badge {{ $contractHealth['badge'] }} rounded-pill px-2.5 py-1" style="font-size: 11px;">
                            {{ $contractHealth['label'] }}
                        </span>
                        <span class="small opacity-75">{{ now()->format('l, d F Y') }}</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $greeting }}, {{ $contractor->first_name }}!</h3>
                    <div class="opacity-90 small">
                        {{ $contractor->designation ?? 'Contract Personnel' }} &bull; {{ $contractor->department?->name ?? 'Unassigned Department' }} &bull; Contractor ID: <strong>{{ $contractor->employee_no }}</strong>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('id-cards.mine') }}" class="btn btn-light text-dark fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-person-badge-fill text-success me-1"></i>My Contractor ID
                    </a>
                    <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#renewalRequestModal" {{ $hasPendingRenewal ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-repeat me-1"></i>{{ $hasPendingRenewal ? 'Renewal Pending' : 'Request Renewal' }}
                    </button>
                    <a href="{{ route('employee.profile.pdf') }}" class="btn btn-light text-navy fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Profile PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Expiration Alert Banner (if expiring soon or expired) -->
    @if(!is_null($daysRemaining) && $daysRemaining <= 60)
    <div class="alert {{ $daysRemaining < 0 ? 'alert-danger' : 'alert-warning' }} border-0 shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi {{ $daysRemaining < 0 ? 'bi-exclamation-octagon-fill' : 'bi-clock-history' }} fs-4"></i>
            <div>
                @if($daysRemaining < 0)
                    <strong>Your contract expired {{ abs($daysRemaining) }} day(s) ago</strong> (on {{ $contractor->contract_end_date->format('d M Y') }}). Please submit a renewal request or contact HR.
                @else
                    <strong>Contract Expiring Soon:</strong> Your current agreement ends in <strong>{{ $daysRemaining }} day(s)</strong> on {{ $contractor->contract_end_date->format('d M Y') }}.
                @endif
            </div>
        </div>
        @if(!$hasPendingRenewal)
        <button type="button" class="btn btn-sm btn-navy flex-shrink-0" data-bs-toggle="modal" data-bs-target="#renewalRequestModal">
            <i class="bi bi-arrow-repeat me-1"></i>Request Renewal Now
        </button>
        @endif
    </div>
    @endif

    <!-- Contractor KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Contract Timeline</div>
                            @if(is_null($daysRemaining))
                                <div class="fs-5 fw-bold text-secondary mt-1">Not Set</div>
                                <div class="small text-muted mt-1">Contact HR Data Analyst</div>
                            @elseif($daysRemaining < 0)
                                <div class="fs-4 fw-bold text-danger mt-1">Expired</div>
                                <div class="small text-danger mt-1">Ended {{ $contractor->contract_end_date->format('d M Y') }}</div>
                            @else
                                <div class="fs-4 fw-bold {{ $daysRemaining <= 60 ? 'text-warning' : 'text-success' }} mt-1">{{ $daysRemaining }} Days</div>
                                <div class="small text-muted mt-1">Ends {{ $contractor->contract_end_date->format('d M Y') }}</div>
                            @endif
                        </div>
                        <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-calendar-range fs-5"></i>
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
                            <div class="text-muted small">Latest Payment Slip</div>
                            @if($latestPayslip)
                                <div class="fs-4 fw-bold text-primary mt-1">LRD {{ number_format((float)$latestPayslip->net_pay_lrd, 2) }}</div>
                                <div class="small text-muted mt-1">USD {{ number_format((float)$latestPayslip->net_pay_usd, 2) }} &bull; {{ $latestPayslip->period?->name ?? 'Latest' }}</div>
                            @else
                                <div class="fs-5 fw-bold text-muted mt-1">No slip yet</div>
                                <div class="small text-muted mt-1">Monthly stipend: LRD {{ number_format((float)$contractor->salary_amount_lrd, 2) }}</div>
                            @endif
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-cash-coin fs-5"></i>
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
                            <div class="text-muted small">Files &amp; Equipment</div>
                            <div class="fs-4 fw-bold text-navy mt-1">{{ $contractor->documents->count() }} <span class="fs-6 fw-normal text-muted">Docs</span></div>
                            <div class="small text-muted mt-1">{{ $assignedAssets->count() }} asset(s) &bull; {{ $unreadNotificationsCount }} unread alerts</div>
                        </div>
                        <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-folder2-open fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="fw-semibold text-navy small"><i class="bi bi-lightning-charge-fill text-success me-1"></i>Contractor Actions:</div>
            <div class="d-flex flex-wrap gap-2">
                @can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-sm btn-navy"><i class="bi bi-shield-exclamation me-1"></i>Submit Excuse</a>@endcan
                <a href="{{ route('employee.payslips') }}" class="btn btn-sm btn-outline-success fw-semibold"><i class="bi bi-receipt me-1"></i>My Payment Slips</a>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#renewalRequestModal" {{ $hasPendingRenewal ? 'disabled' : '' }}>
                    <i class="bi bi-arrow-repeat me-1"></i>Request Contract Renewal
                </button>
                <a href="{{ route('employee.profile') }}" class="btn btn-sm btn-outline-navy"><i class="bi bi-person me-1"></i>My Profile</a>
                @can('view internal vacancies')<a href="{{ route('employee.recruitment') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-briefcase me-1"></i>Opportunities</a>@endcan
                @can('view directory')<a href="{{ route('directory.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people me-1"></i>Directory</a>@endcan
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Left Column -->
        <div class="col-12 col-xl-8">
            <!-- Contract Progress & Agreement Summary -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-file-earmark-text me-2 text-success"></i>My Contract Agreement &amp; Timeline</h5>
                    <span class="badge {{ $contractHealth['badge'] }}">{{ $contractHealth['label'] }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Start Date</div>
                            <div class="fw-bold text-navy">{{ $contractor->date_joined?->format('d M Y') ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">End Date</div>
                            <div class="fw-bold text-navy">{{ $contractor->contract_end_date?->format('d M Y') ?? 'Not Set' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Monthly Stipend (LRD)</div>
                            <div class="fw-bold text-success">LRD {{ number_format((float)$contractor->salary_amount_lrd, 2) }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Monthly Stipend (USD)</div>
                            <div class="fw-bold text-primary">USD {{ number_format((float)$contractor->salary_amount_usd, 2) }}</div>
                        </div>
                    </div>

                    @if(!is_null($contractProgress))
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Contract Term Elapsed</span>
                                <span class="fw-semibold text-navy">{{ $contractProgress }}%</span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 99px;">
                                <div class="progress-bar {{ $contractProgress >= 85 ? 'bg-warning' : 'bg-success' }}" style="width: {{ $contractProgress }}%"></div>
                            </div>
                        </div>
                    @endif

                    @if($renewalHistory->isNotEmpty())
                        <hr class="my-4">
                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-arrow-repeat me-1 text-primary"></i>Renewal Request History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Submitted</th><th>Proposed End Date</th><th>Justification</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($renewalHistory as $ren)
                                        <tr>
                                            <td class="small">{{ $ren->created_at?->format('d M Y') }}</td>
                                            <td class="small fw-semibold">{{ $ren->proposed_end_date?->format('d M Y') }}</td>
                                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ren->reason, 60) }}</td>
                                            <td>
                                                <span class="badge {{ $ren->status === 'Approved' ? 'bg-success' : ($ren->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $ren->status }}</span>
                                                @if($ren->status === 'Pending')
                                                    <small class="d-block text-primary fw-semibold" style="font-size:10.5px;">{{ $ren->current_stage_label }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Slips / Payslips -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>My Payment Slips</h5>
                    <a href="{{ route('employee.payslips') }}" class="btn btn-sm btn-outline-navy">All Payment Slips</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Period</th>
                                    <th class="text-end">Gross (LRD)</th>
                                    <th class="text-end">Deductions (LRD)</th>
                                    <th class="text-end">Net Paid (LRD / USD)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Advice</th>
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
                                            <a href="{{ route('payroll.payslip', $slip->id) }}" class="btn btn-sm btn-outline-navy" title="View Slip"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('payroll.payslip.pdf', $slip->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No contractor payment slips available yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Excuse Requests -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-navy mb-0"><i class="bi bi-shield-exclamation me-2 text-warning"></i>My Excuse Requests</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('excuses.index') }}" class="btn btn-sm btn-outline-secondary">All Excuses</a>
                        @can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-sm btn-navy"><i class="bi bi-plus-lg me-1"></i>Submit Excuse</a>@endcan
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
                                    <tr><td colspan="5" class="text-muted text-center py-4">No excuse requests submitted yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-12 col-xl-4">
            <!-- Assignment Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-person-lines-fill me-2 text-success"></i>Contractor Assignment</h6>
                    <a href="{{ route('employee.profile') }}" class="small text-decoration-none">Edit Contact</a>
                </div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Contractor ID</span><span class="fw-bold text-navy">{{ $contractor->employee_no }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Department</span><span class="fw-semibold text-navy">{{ $contractor->department?->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Supervisor</span><span class="fw-semibold text-navy">{{ $contractor->supervisor?->full_name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Official Email</span><span class="fw-semibold text-navy">{{ $contractor->official_email ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom"><span class="text-muted">Phone</span><span class="fw-semibold text-navy">{{ $contractor->phone ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between py-1.5"><span class="text-muted">Bank</span><span class="fw-semibold text-navy">{{ $contractor->bank_name ?? '—' }}</span></div>
                </div>
            </div>

            <!-- Contract Documents -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-folder-fill me-2 text-primary"></i>My Contract Documents</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($contractor->documents as $doc)
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="list-group-item list-group-item-action px-3 py-2.5 d-flex justify-content-between align-items-center small">
                                <div>
                                    <div class="fw-semibold text-navy"><i class="bi bi-file-earmark-text me-1 text-danger"></i>{{ $doc->title }}</div>
                                    <small class="text-muted">{{ ucfirst($doc->document_type) }} {{ $doc->file_size ? '• ' . $doc->file_size : '' }}</small>
                                </div>
                                <i class="bi bi-download text-primary"></i>
                            </a>
                        @empty
                            <div class="text-center text-muted small py-4">No contract files uploaded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Assigned Assets -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-laptop me-2 text-info"></i>Assigned Equipment</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($assignedAssets as $assign)
                            <div class="list-group-item px-3 py-2.5 d-flex justify-content-between align-items-center small">
                                <div>
                                    <div class="fw-semibold text-navy">{{ $assign->asset?->name ?? 'Equipment' }}</div>
                                    <small class="text-muted">Issued {{ $assign->assigned_date?->format('d M Y') }}</small>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $assign->asset?->asset_tag ?? 'Issued' }}</span>
                            </div>
                        @empty
                            <div class="text-center text-muted small py-4">No ministry equipment assigned.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-bell me-2 text-warning"></i>Notifications</h6>
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
                            <div class="text-center text-muted small py-4">No new notifications.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contract Renewal Request Modal -->
<div class="modal fade" id="renewalRequestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('contractor.renewal-request') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Request Contract Renewal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Current Contract End Date</label>
                        <input type="text" class="form-control bg-light" value="{{ $contractor->contract_end_date?->format('d M Y') ?? 'Not set' }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proposed New End Date <span class="text-danger">*</span></label>
                        <input type="date" name="proposed_end_date" class="form-control" min="{{ now()->addDay()->toDateString() }}" value="{{ ($contractor->contract_end_date && $contractor->contract_end_date->isFuture() ? $contractor->contract_end_date->copy()->addMonths(12) : now()->addMonths(12))->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Justification / Deliverables Summary <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Briefly summarize completed deliverables and reason for contract extension..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Supporting Document <span class="text-muted">(PDF / DOC, optional)</span></label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-send me-1"></i>Submit Renewal Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
