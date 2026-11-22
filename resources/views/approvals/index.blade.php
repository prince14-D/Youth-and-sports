@extends('layouts.app')
@section('title', 'Multi-Level Approval Center')
@section('page-title', 'Multi-Level Approval Center')
@section('breadcrumb', 'HRMS › Approval Center')

@section('content')
<div class="animate-in">
    @php
        $authUser = auth()->user();
        $tierBanner = match ($userStage) {
            1 => 'Stage 1 — HR Officer Review & Verification',
            2 => 'Stage 2 — Assistant HR Director Endorsement',
            3 => 'Stage 3 — HR Director Final Approval Authority',
            default => $isAdmin ? 'All Tiers — System Administrator Oversight' : 'Read-Only Workflow View',
        };
    @endphp

    <!-- Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 55%, #1d4ed8 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.18); font-size: 11px;">
                        <i class="bi bi-diagram-3-fill me-1"></i>TIERED SIGN-OFF PIPELINE
                    </span>
                    <h3 class="fw-bold mb-1">Multi-Level HR Approval Center</h3>
                    <div class="small opacity-90">
                        Your Active Tier: <strong>{{ $tierBanner }}</strong>
                    </div>
                </div>
                <!-- Visual Pipeline Diagram -->
                <div class="d-flex align-items-center flex-wrap gap-2 bg-white bg-opacity-10 rounded-3 px-3 py-2.5 small">
                    <span class="badge {{ $userStage === 1 ? 'bg-warning text-dark' : 'bg-light text-navy' }} px-2.5 py-1.5">1. HR Officer</span>
                    <i class="bi bi-arrow-right opacity-75"></i>
                    <span class="badge {{ $userStage === 2 ? 'bg-warning text-dark' : 'bg-light text-navy' }} px-2.5 py-1.5">2. Asst. HR Director</span>
                    <i class="bi bi-arrow-right opacity-75"></i>
                    <span class="badge {{ $userStage === 3 ? 'bg-warning text-dark' : 'bg-light text-navy' }} px-2.5 py-1.5">3. HR Director</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">My Action Queue</div>
                    <div class="fs-2 fw-bold text-danger mt-1">{{ $stageCounts['my_queue'] }}</div>
                    <div class="small text-muted">Awaiting your direct sign-off</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Stage 1: HR Officer</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ $stageCounts['stage1'] }}</div>
                    <div class="small text-muted">Initial verification queue</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Stage 2: Asst. Director</div>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ $stageCounts['stage2'] }}</div>
                    <div class="small text-muted">Awaiting endorsement</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Stage 3: HR Director</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $stageCounts['stage3'] }}</div>
                    <div class="small text-muted">Awaiting final approval</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-3 gap-2" id="approvalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" data-bs-toggle="pill" data-bs-target="#tabMyQueue" type="button">
                <i class="bi bi-inbox-fill me-1"></i>My Action Queue
                <span class="badge bg-danger ms-1">{{ $stageCounts['my_queue'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tabAllPending" type="button">
                <i class="bi bi-layers me-1"></i>All In-Progress Workflows
                <span class="badge bg-secondary ms-1">{{ $stageCounts['total'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tabTrailLog" type="button">
                <i class="bi bi-fingerprint me-1"></i>Digital Sign-Off Audit Trail
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: My Action Queue -->
        <div class="tab-pane fade show active" id="tabMyQueue">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-check2-square me-2 text-primary"></i>Items Requiring Your Sign-Off</h5>
                    <span class="small text-muted">Leave, Contract Renewals, Staff Movements &amp; Payroll</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Module &amp; Request</th>
                                    <th>Approval Pipeline</th>
                                    <th class="text-end pe-4">Sign-Off Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myQueue as $item)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="badge {{ $item['badge_class'] }} mb-1"><i class="bi {{ $item['icon'] }} me-1"></i>{{ $item['module'] }}</span>
                                            <div class="fw-bold text-navy">{{ $item['title'] }}</div>
                                            <div class="small text-muted">{{ $item['subtitle'] }}</div>
                                            @if($item['detail'])
                                                <div class="small text-secondary mt-1">{{ \Illuminate\Support\Str::limit($item['detail'], 90) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <x-ui.approval-stepper :model="$item['model']" :uid="'myq-trail-' . $item['key']" />
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal-{{ $item['key'] }}">
                                                    <i class="bi bi-pen-fill me-1"></i>{{ $item['model']->next_action_label }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $item['key'] }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                <a href="{{ $item['module_route'] }}" class="btn btn-sm btn-outline-secondary" title="Open Module"><i class="bi bi-box-arrow-up-right"></i></a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal-{{ $item['key'] }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form method="POST" action="{{ $item['approve_route'] }}">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title"><i class="bi bi-pen-fill me-2"></i>{{ $item['model']->next_action_label }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="p-3 rounded bg-light border mb-3 small">
                                                            <div class="fw-bold text-navy">{{ $item['title'] }}</div>
                                                            <div class="text-muted">{{ $item['subtitle'] }}</div>
                                                            <div class="mt-1"><span class="badge bg-primary">Stage {{ $item['stage'] }} of 3</span></div>
                                                        </div>
                                                        <label class="form-label">Digital Sign-Off Remarks <span class="text-muted">(optional)</span></label>
                                                        <textarea name="remarks" class="form-control" rows="3" placeholder="Add verification notes or endorsement remarks..."></textarea>
                                                        <div class="form-text"><i class="bi bi-fingerprint me-1"></i>A timestamped cryptographic signature will be recorded under <strong>{{ $authUser->name }}</strong> ({{ $authUser->primary_role }}).</div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Sign &amp; Confirm</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal-{{ $item['key'] }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form method="POST" action="{{ $item['reject_route'] }}">
                                                    @csrf
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-x-octagon me-2"></i>Reject at Stage {{ $item['stage'] }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted mb-2">Rejecting <strong>{{ $item['title'] }}</strong>:</p>
                                                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="State clearly why this request is rejected or returned..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-patch-check-fill text-success fs-1 d-block mb-2"></i>
                                            <div class="fw-semibold text-navy">Your approval queue is clear!</div>
                                            <div class="small">No pending items are currently awaiting your tier's sign-off.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: All Active Pipelines -->
        <div class="tab-pane fade" id="tabAllPending">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-layers me-2 text-primary"></i>All In-Progress Multi-Level Workflows</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Module &amp; Request</th>
                                    <th>3-Tier Progress &amp; Digital Trail</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allPending as $item)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="badge {{ $item['badge_class'] }} mb-1">{{ $item['module'] }}</span>
                                            <div class="fw-bold text-navy">{{ $item['title'] }}</div>
                                            <div class="small text-muted">{{ $item['subtitle'] }}</div>
                                        </td>
                                        <td>
                                            <x-ui.approval-stepper :model="$item['model']" :uid="'all-trail-' . $item['key']" />
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ $item['module_route'] }}" class="btn btn-sm btn-outline-navy">
                                                Open <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted">No workflows are currently in progress.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Digital Sign-Off Audit Log -->
        <div class="tab-pane fade" id="tabTrailLog">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-fingerprint me-2 text-primary"></i>Recent Digital Sign-Off Trail</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Timestamp</th>
                                    <th>Workflow &amp; Stage</th>
                                    <th>Action</th>
                                    <th>Signed By</th>
                                    <th>Remarks</th>
                                    <th class="pe-4">Digital Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTrails as $t)
                                    <tr>
                                        <td class="ps-4 text-nowrap">{{ $t->acted_at?->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <span class="fw-bold text-navy">{{ class_basename($t->approvable_type) }} #{{ $t->approvable_id }}</span>
                                            <div class="text-muted">{{ $t->stage_label }}</div>
                                        </td>
                                        <td>
                                            <span class="badge text-uppercase {{ $t->action === 'approved' ? 'bg-success' : ($t->action === 'rejected' ? 'bg-danger' : 'bg-secondary') }}">
                                                {{ $t->action }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-navy">{{ $t->actor_name }}</div>
                                            <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $t->actor_role }}</span>
                                        </td>
                                        <td class="text-muted" style="max-width:240px;">{{ \Illuminate\Support\Str::limit($t->remarks, 70) }}</td>
                                        <td class="pe-4 font-monospace fw-bold text-primary">{{ $t->digital_signature }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No digital sign-offs recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
