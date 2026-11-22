@extends('layouts.app')
@section('title', 'Internal Audit Division')
@section('page-title', 'Internal Audit Division')
@section('breadcrumb', 'HRMS › Internal Audit Division')

@section('content')
<div class="animate-in">
    {{-- Executive Header Banner --}}
    <div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 55%, #7f1d1d 100%);">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="text-uppercase small fw-semibold opacity-75 mb-1" style="letter-spacing:1.1px;">
                    <i class="bi bi-shield-check me-1"></i> Ministry of Youth &amp; Sports &bull; Republic of Liberia
                </div>
                <h4 class="fw-bold mb-1">Internal Audit &amp; Compliance Command Center</h4>
                <p class="mb-0 small opacity-75">
                    Review financial records, verify payment vouchers, assess departmental internal controls, scan anomalies &amp; escalate irregularities.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('manage internal audit')
                <button type="button" class="btn btn-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#logFindingModal">
                    <i class="bi bi-exclamation-octagon me-1"></i>Log Irregularity / Finding
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#controlAssessmentModal">
                    <i class="bi bi-clipboard2-check me-1"></i>Record Control Assessment
                </button>
                @endcan
                <a href="{{ route('internal-audit.report.pdf') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Audit Report PDF
                </a>
                <a href="{{ route('internal-audit.findings.csv') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download me-1"></i>Findings CSV
                </a>
                <a href="{{ route('audit.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-journal-text me-1"></i>System Trail
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Summary Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Pending Voucher Review</div>
                    <div class="fs-3 fw-bold text-warning">{{ $kpis['pending_vouchers'] }}</div>
                    <div class="small text-muted">{{ $kpis['verified_vouchers'] }} verified</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Queried / Flagged</div>
                    <div class="fs-3 fw-bold text-danger">{{ $kpis['flagged_vouchers'] }}</div>
                    <div class="small text-muted">Payment vouchers</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Open Irregularities</div>
                    <div class="fs-3 fw-bold text-danger">{{ $kpis['open_findings'] }}</div>
                    <div class="small text-danger fw-semibold">{{ $kpis['critical_high_findings'] }} Critical / High</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Financial Exposure</div>
                    <div class="fs-4 fw-bold text-navy">${{ number_format($kpis['financial_exposure_usd'], 2) }}</div>
                    <div class="small text-muted">Open audit findings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Avg Control Score</div>
                    <div class="fs-3 fw-bold {{ ($kpis['avg_control_score'] ?? 100) >= 75 ? 'text-success' : 'text-warning' }}">
                        {{ $kpis['avg_control_score'] !== null ? $kpis['avg_control_score'] . '%' : '—' }}
                    </div>
                    <div class="small text-muted">Internal controls</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Live Risk Flags</div>
                    <div class="fs-3 fw-bold {{ $kpis['active_anomalies'] > 0 ? 'text-danger' : 'text-success' }}">{{ $kpis['active_anomalies'] }}</div>
                    <div class="small text-muted">{{ $kpis['system_logs_7d'] }} logs (7d)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Automated Anomaly & Irregularity Scanner --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
            <div>
                <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-radar me-2 text-danger"></i>Automated Irregularity &amp; Compliance Risk Scanner</h6>
                <small class="text-muted">Live cross-ministry control checks across Finance, Payroll, Contracts, Paperless Workflow, and Assets</small>
            </div>
            <span class="badge {{ $kpis['active_anomalies'] > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-2">
                {{ $kpis['active_anomalies'] > 0 ? $kpis['active_anomalies'] . ' Active Risk Flag(s)' : 'All Automated Checks Clear' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($anomalies as $item)
                @php
                    $isFlagged = $item['count'] > 0;
                    $borderClass = match ($item['severity']) {
                        'Critical' => 'border-danger',
                        'High'     => 'border-warning',
                        'Medium'   => 'border-info',
                        default    => 'border-success',
                    };
                    $badgeClass = match ($item['severity']) {
                        'Critical' => 'bg-danger',
                        'High'     => 'bg-warning text-dark',
                        'Medium'   => 'bg-info text-dark',
                        default    => 'bg-success',
                    };
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="p-3 rounded-3 border border-start border-4 {{ $borderClass }} bg-light-subtle h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-uppercase fw-semibold text-muted" style="font-size:10px;letter-spacing:.6px;">{{ $item['category'] }}</span>
                                <span class="badge {{ $badgeClass }}">{{ $item['severity'] }} &bull; {{ $item['count'] }}</span>
                            </div>
                            <div class="fw-semibold small text-dark mb-1">{{ $item['title'] }}</div>
                            <div class="text-muted" style="font-size:12px;">{{ $item['detail'] }}</div>
                        </div>
                        <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                            <a href="{{ $item['action_url'] }}" class="small fw-semibold text-decoration-none">
                                Inspect Records <i class="bi bi-arrow-right"></i>
                            </a>
                            @if($isFlagged)
                            @can('manage internal audit')
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none"
                                onclick="prefillFindingFromAnomaly(@js($item['title']), @js($item['detail'] . ' Detected count: ' . $item['count'] . '.'), @js($item['severity'] === 'Clear' ? 'Medium' : $item['severity']))"
                                data-bs-toggle="modal" data-bs-target="#logFindingModal">
                                <i class="bi bi-flag-fill me-1"></i>Log Finding
                            </button>
                            @endcan
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Workspace Navigation Tabs --}}
    <ul class="nav nav-pills mb-3 gap-2" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'vouchers' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('internal-audit.dashboard', ['tab' => 'vouchers']) }}">
                <i class="bi bi-receipt-cutoff me-1"></i>Financial Voucher Verification
                @if($kpis['pending_vouchers'] > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $kpis['pending_vouchers'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'findings' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('internal-audit.dashboard', ['tab' => 'findings']) }}">
                <i class="bi bi-exclamation-triangle me-1"></i>Irregularities &amp; Findings
                @if($kpis['open_findings'] > 0)
                    <span class="badge bg-danger ms-1">{{ $kpis['open_findings'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'controls' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('internal-audit.dashboard', ['tab' => 'controls']) }}">
                <i class="bi bi-clipboard2-check me-1"></i>Internal Control Assessments ({{ $assessments->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'oversight' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('internal-audit.dashboard', ['tab' => 'oversight']) }}">
                <i class="bi bi-activity me-1"></i>Audit Inbox &amp; System Activity
            </a>
        </li>
    </ul>

    {{-- TAB 1: Financial Voucher Verification --}}
    @if($activeTab === 'vouchers')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy">Financial Transactions &amp; Payment Voucher Audit Queue</h6>
                <small class="text-muted">Verify compliance, query missing documentation, or flag irregularities directly on Ministry vouchers</small>
            </div>
            <form method="GET" action="{{ route('internal-audit.dashboard') }}" class="d-flex gap-2">
                <input type="hidden" name="tab" value="vouchers">
                <select name="audit_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Audit Statuses</option>
                    @foreach(['Pending Review', 'Verified', 'Queried', 'Irregularity Flagged'] as $st)
                    <option value="{{ $st }}" {{ $auditStatusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                <a href="{{ route('finance.ledger') }}" class="btn btn-sm btn-outline-primary text-nowrap">Full Finance Ledger</a>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Voucher #</th>
                        <th>Date &amp; Dept</th>
                        <th>Payee / Narration</th>
                        <th class="text-end">Amount</th>
                        <th>Finance Status</th>
                        <th>Audit Verification</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td class="font-monospace fw-semibold small">
                            {{ $tx->voucher_no }}
                            <div class="text-muted fw-normal" style="font-size:11px;">{{ $tx->transaction_type }}</div>
                        </td>
                        <td class="small">
                            <div>{{ $tx->transaction_date?->format('d M Y') }}</div>
                            <div class="text-muted">{{ $tx->department?->name ?? 'Ministry HQ' }}</div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $tx->payee_name }}</div>
                            <div class="text-muted text-truncate" style="max-width:260px;" title="{{ $tx->description }}">{{ $tx->description }}</div>
                            @if($tx->bank_reference)
                                <span class="badge bg-light text-dark border mt-1">Ref: {{ $tx->bank_reference }}</span>
                            @elseif($tx->status === 'Paid')
                                <span class="badge bg-danger-subtle text-danger mt-1">Missing Bank Ref!</span>
                            @endif
                        </td>
                        <td class="text-end small">
                            <div class="fw-bold text-navy">${{ number_format((float)$tx->amount_usd, 2) }}</div>
                            <div class="text-muted">LRD {{ number_format((float)$tx->amount_lrd, 0) }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $tx->status_badge_class }}">{{ $tx->status }}</span>
                        </td>
                        <td class="small">
                            <span class="badge {{ $tx->audit_badge_class }}">{{ $tx->audit_status ?? 'Pending Review' }}</span>
                            @if($tx->auditor)
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    By {{ $tx->auditor->name }} &bull; {{ $tx->audited_at?->format('d M') }}
                                </div>
                            @endif
                            @if($tx->audit_notes)
                                <div class="text-muted fst-italic text-truncate" style="max-width:190px;font-size:11px;" title="{{ $tx->audit_notes }}">"{{ $tx->audit_notes }}"</div>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('finance.vouchers.pdf', $tx) }}" class="btn btn-sm btn-outline-secondary" title="Download Voucher PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @can('manage internal audit')
                            <button type="button" class="btn btn-sm btn-navy"
                                onclick="openVerifyVoucherModal({{ $tx->id }}, @js($tx->voucher_no), @js($tx->payee_name), @js(number_format((float)$tx->amount_usd, 2)), @js($tx->audit_status ?? 'Pending Review'), @js($tx->audit_notes ?? ''))"
                                data-bs-toggle="modal" data-bs-target="#verifyVoucherModal">
                                <i class="bi bi-patch-check me-1"></i>Audit
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-circle fs-2 d-block mb-2 text-success"></i>
                            No financial vouchers match the selected audit filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 2: Audit Findings & Irregularities Registry --}}
    @if($activeTab === 'findings')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy">Audit Findings &amp; Irregularities Registry</h6>
                <small class="text-muted">Track compliance exceptions, financial exposure, executive escalations, and departmental remediation</small>
            </div>
            <form method="GET" action="{{ route('internal-audit.dashboard') }}" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="tab" value="findings">
                <select name="severity" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Severities</option>
                    @foreach($severities as $sev)
                    <option value="{{ $sev }}" {{ $severityFilter === $sev ? 'selected' : '' }}>{{ $sev }}</option>
                    @endforeach
                </select>
                <select name="finding_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($findingStatuses as $fst)
                    <option value="{{ $fst }}" {{ $findingStatusFilter === $fst ? 'selected' : '' }}>{{ $fst }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Finding #</th>
                        <th>Title &amp; Area</th>
                        <th>Department</th>
                        <th>Severity</th>
                        <th class="text-end">Impact (USD)</th>
                        <th>Status &amp; Escalation</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $f)
                    <tr>
                        <td class="font-monospace fw-semibold small">
                            {{ $f->finding_no }}
                            <div class="text-muted fw-normal" style="font-size:11px;">{{ $f->created_at?->format('d M Y') }}</div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold text-dark">{{ $f->title }}</div>
                            <div class="text-muted">{{ $f->audit_area }}
                                @if($f->transaction)
                                    &bull; Voucher <span class="font-monospace">{{ $f->transaction->voucher_no }}</span>
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:12px;">{{ \Illuminate\Support\Str::limit($f->description, 120) }}</div>
                            @if($f->management_response)
                                <div class="text-success mt-1" style="font-size:11px;"><strong>Response:</strong> {{ \Illuminate\Support\Str::limit($f->management_response, 100) }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $f->department?->name ?? 'Ministry-Wide' }}</td>
                        <td><span class="badge {{ $f->severity_badge_class }}">{{ $f->severity }}</span></td>
                        <td class="text-end small fw-semibold">
                            {{ $f->financial_impact_usd !== null ? '$' . number_format((float)$f->financial_impact_usd, 2) : '—' }}
                        </td>
                        <td class="small">
                            <span class="badge {{ $f->status_badge_class }}">{{ $f->status }}</span>
                            @if($f->document)
                                <div class="mt-1">
                                    <a href="{{ route('documents.show', $f->document) }}" class="badge bg-navy text-white text-decoration-none">
                                        <i class="bi bi-send-check me-1"></i>{{ $f->document->tracking_number }}
                                    </a>
                                </div>
                            @endif
                            @if($f->due_date)
                                <div class="text-muted mt-1" style="font-size:11px;">Due: {{ $f->due_date->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('manage internal audit')
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="openFindingStatusModal({{ $f->id }}, @js($f->finding_no), @js($f->title), @js($f->status), @js($f->management_response ?? ''))"
                                data-bs-toggle="modal" data-bs-target="#findingStatusModal">
                                Update
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-2 d-block mb-2 text-success"></i>
                            No audit findings recorded yet. Use <strong>Log Irregularity / Finding</strong> or flag a voucher to begin tracking.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 3: Internal Control Assessments --}}
    @if($activeTab === 'controls')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-navy">Departmental Internal Control Assessments</h6>
                        <small class="text-muted">Compliance effectiveness ratings &amp; corrective action plans</small>
                    </div>
                    @can('manage internal audit')
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#controlAssessmentModal">
                        <i class="bi bi-plus-circle me-1"></i>New Assessment
                    </button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Ref #</th>
                                <th>Department &amp; Area</th>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Rating</th>
                                <th>Observations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assessments as $a)
                            <tr>
                                <td class="font-monospace small fw-semibold">{{ $a->assessment_no }}</td>
                                <td class="small">
                                    <div class="fw-semibold">{{ $a->department?->name }}</div>
                                    <div class="text-muted">{{ $a->control_area }}</div>
                                </td>
                                <td class="small">{{ $a->assessment_date?->format('d M Y') }}</td>
                                <td class="small fw-bold">{{ $a->score }}%</td>
                                <td><span class="badge {{ $a->rating_badge_class }}">{{ $a->rating }}</span></td>
                                <td class="small">
                                    <div>{{ \Illuminate\Support\Str::limit($a->observations, 95) }}</div>
                                    @if($a->action_plan)
                                        <div class="text-primary" style="font-size:11px;"><strong>Action:</strong> {{ \Illuminate\Support\Str::limit($a->action_plan, 85) }}</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No internal control assessments recorded yet. Click <strong>New Assessment</strong> to evaluate a division's controls.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-building-check me-1"></i>Department Risk Scorecard</h6>
                </div>
                <div class="card-body">
                    @forelse($departmentCompliance as $row)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">{{ $row['department']->name }}</span>
                            @if($row['open_findings'] > 0)
                                <span class="badge bg-danger">{{ $row['open_findings'] }} Open</span>
                            @else
                                <span class="badge bg-success">0 Open</span>
                            @endif
                        </div>
                        @if($row['avg_score'] !== null)
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar {{ $row['avg_score'] >= 75 ? 'bg-success' : ($row['avg_score'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width: {{ $row['avg_score'] }}%"></div>
                            </div>
                            <span class="small fw-bold">{{ $row['avg_score'] }}%</span>
                        </div>
                        @else
                        <div class="text-muted small">No control score assessed yet</div>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted small mb-0">Department scorecards will appear automatically as internal control assessments and audit findings are logged.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 4: Audit Inbox & System Activity --}}
    @if($activeTab === 'oversight')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-inbox me-1"></i>Files Routed to Internal Audit</h6>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">Open Workflow</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($incomingAuditDocs as $doc)
                    <a href="{{ route('documents.show', $doc) }}" class="list-group-item list-group-item-action py-3">
                        <div class="d-flex justify-content-between">
                            <span class="font-monospace small fw-bold text-primary">{{ $doc->tracking_number }}</span>
                            <span class="badge {{ $doc->priority_badge_class }}">{{ $doc->priority }}</span>
                        </div>
                        <div class="fw-semibold small mt-1">{{ $doc->subject }}</div>
                        <div class="text-muted" style="font-size:11px;">From {{ $doc->originDepartment?->name }} &bull; {{ $doc->hours_in_current_office }}h in office</div>
                    </a>
                    @empty
                    <div class="p-4 text-center text-muted small">No active paperless files currently sitting in the Internal Audit office.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-journal-text me-1"></i>Latest System Audit Trail Events</h6>
                    <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">View All Logs</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Time</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentSystemLogs as $log)
                            <tr>
                                <td class="text-nowrap text-muted">{{ $log->created_at?->format('d M H:i') }}</td>
                                <td class="fw-semibold">{{ $log->user?->name ?? 'System' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($log->description, 80) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No recent audit logs.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@can('manage internal audit')
{{-- Modal: Verify / Audit Voucher --}}
<div class="modal fade" id="verifyVoucherModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="verifyVoucherForm" class="modal-content">
            @csrf
            <div class="modal-header bg-navy text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-patch-check me-2"></i>Verify Financial Voucher</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 rounded bg-light border mb-3 small">
                    <div class="fw-bold font-monospace" id="vvVoucherNo">—</div>
                    <div id="vvPayee" class="text-muted">—</div>
                    <div class="fw-bold text-navy mt-1">Amount: USD <span id="vvAmount">0.00</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Audit Determination <span class="text-danger">*</span></label>
                    <select name="audit_status" id="vvStatus" class="form-select" required>
                        <option value="Verified">Verified — Compliant &amp; Cleared</option>
                        <option value="Queried">Queried — Missing Documentation / Clarification Needed</option>
                        <option value="Irregularity Flagged">Irregularity Flagged — Auto-Log Audit Finding</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Audit Verification Notes / Exception Details</label>
                    <textarea name="audit_notes" id="vvNotes" rows="3" class="form-control" placeholder="Record supporting invoice check, bank ref validation, or irregularity details..."></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" name="auto_log_finding" id="vvAutoLog">
                    <label class="form-check-label small" for="vvAutoLog">Also create formal Audit Finding when marked <em>Queried</em></label>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold small">Executive Escalation (if Irregularity / Finding logged)</label>
                    <select name="escalate_to" class="form-select form-select-sm">
                        <option value="none">Do not escalate yet (Internal Audit tracking only)</option>
                        <option value="DMA">Escalate via Paperless Workflow to DMA (Admin Head)</option>
                        <option value="MINISTER">Escalate via Paperless Workflow to Office of the Minister</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-navy">Save Audit Determination</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Log Irregularity / Audit Finding --}}
<div class="modal fade" id="logFindingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('internal-audit.findings.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-octagon me-2"></i>Log Audit Finding / Irregularity</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Finding Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="lfTitle" class="form-control" required maxlength="200" placeholder="e.g. Unverified Vendor Payment Exceeding Allotment Ceiling">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Severity <span class="text-danger">*</span></label>
                        <select name="severity" id="lfSeverity" class="form-select" required>
                            @foreach($severities as $sev)
                            <option value="{{ $sev }}" {{ $sev === 'High' ? 'selected' : '' }}>{{ $sev }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Audit Area <span class="text-danger">*</span></label>
                        <select name="audit_area" class="form-select" required>
                            @foreach($auditAreas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Affected Department / Office</label>
                        <select name="department_id" class="form-select">
                            <option value="">Ministry-Wide / Cross-Departmental</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Financial Exposure (USD)</label>
                        <input type="number" step="0.01" min="0" name="financial_impact_usd" class="form-control" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Linked Payment Voucher</label>
                        <select name="financial_transaction_id" class="form-select">
                            <option value="">None</option>
                            @foreach($vouchersForSelect as $v)
                            <option value="{{ $v->id }}">{{ $v->voucher_no }} (${{ number_format((float)$v->amount_usd, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Remediation Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(14)->toDateString() }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Detailed Audit Observation / Irregularity <span class="text-danger">*</span></label>
                        <textarea name="description" id="lfDescription" rows="3" class="form-control" required placeholder="Describe the evidence, control failure, or financial discrepancy identified..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Audit Recommendation &amp; Corrective Action <span class="text-danger">*</span></label>
                        <textarea name="recommendation" rows="2" class="form-control" required placeholder="Specify required remediation steps and documentation..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Executive Escalation via Paperless Document Workflow</label>
                        <select name="escalate_to" class="form-select">
                            <option value="none">Keep in Internal Audit Registry (No immediate escalation)</option>
                            <option value="DMA">Escalate Confidentially to Deputy Minister for Administration (DMA)</option>
                            <option value="MINISTER">Escalate Confidentially to Office of the Minister</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Record Audit Finding</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Update Finding Status --}}
<div class="modal fade" id="findingStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="findingStatusForm" class="modal-content">
            @csrf
            <div class="modal-header bg-navy text-white">
                <h6 class="modal-title fw-bold">Update Audit Finding Resolution</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="small mb-3 p-2 rounded bg-light border">
                    <div class="font-monospace fw-bold" id="fsFindingNo">—</div>
                    <div class="text-muted" id="fsTitle">—</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Resolution Status</label>
                    <select name="status" id="fsStatus" class="form-select" required>
                        @foreach($findingStatuses as $fst)
                        <option value="{{ $fst }}">{{ $fst }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-semibold">Management Response / Closure Notes</label>
                    <textarea name="management_response" id="fsResponse" rows="3" class="form-control" placeholder="Summarize departmental remediation proof or closure verification..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-navy">Update Finding</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Record Internal Control Assessment --}}
<div class="modal fade" id="controlAssessmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('internal-audit.assessments.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="bi bi-clipboard2-check me-2"></i>Record Departmental Internal Control Assessment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Department / Division <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Control Area Assessed <span class="text-danger">*</span></label>
                        <select name="control_area" class="form-select" required>
                            @foreach($controlAreas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Assessment Date <span class="text-danger">*</span></label>
                        <input type="date" name="assessment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Compliance Rating <span class="text-danger">*</span></label>
                        <select name="rating" class="form-select" required>
                            @foreach($controlRatings as $rating)
                            <option value="{{ $rating }}">{{ $rating }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Control Score (0–100%) <span class="text-danger">*</span></label>
                        <input type="number" name="score" class="form-control" min="0" max="100" value="85" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Control Testing Observations <span class="text-danger">*</span></label>
                        <textarea name="observations" rows="3" class="form-control" required placeholder="Document segregation of duties, voucher approvals, record completeness, and control effectiveness..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Corrective Action Plan</label>
                        <textarea name="action_plan" rows="2" class="form-control" placeholder="Recommended strengthening steps and target timeline..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning text-dark fw-semibold">Save Control Assessment</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function openVerifyVoucherModal(id, voucherNo, payee, amount, status, notes) {
    document.getElementById('verifyVoucherForm').action = "{{ url('/internal-audit/transactions') }}/" + id + "/verify";
    document.getElementById('vvVoucherNo').textContent = voucherNo;
    document.getElementById('vvPayee').textContent = payee;
    document.getElementById('vvAmount').textContent = amount;
    document.getElementById('vvStatus').value = (status === 'Pending Review' || !status) ? 'Verified' : status;
    document.getElementById('vvNotes').value = notes || '';
}

function openFindingStatusModal(id, findingNo, title, status, response) {
    document.getElementById('findingStatusForm').action = "{{ url('/internal-audit/findings') }}/" + id + "/status";
    document.getElementById('fsFindingNo').textContent = findingNo;
    document.getElementById('fsTitle').textContent = title;
    document.getElementById('fsStatus').value = status;
    document.getElementById('fsResponse').value = response || '';
}

function prefillFindingFromAnomaly(title, detail, severity) {
    document.getElementById('lfTitle').value = title;
    document.getElementById('lfDescription').value = detail;
    if (['Low', 'Medium', 'High', 'Critical'].includes(severity)) {
        document.getElementById('lfSeverity').value = severity;
    }
}
</script>
@endpush
