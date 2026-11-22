@extends('layouts.app')
@section('title', 'Leave Approvals')
@section('page-title', 'Leave Request Approvals')
@section('breadcrumb', 'HRMS › Leave Management › Approvals')

@section('content')
<div class="animate-in">
    @php $authUser = auth()->user(); @endphp

    <div class="page-header">
        <div>
            <h1>Leave Request Approvals (3-Tier Workflow)</h1>
            <p>HR Officer (Stage 1) &rarr; Assistant HR Director (Stage 2) &rarr; HR Director Final Approval (Stage 3).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('approvals.index') }}" class="btn btn-outline-navy"><i class="bi bi-diagram-3 me-1"></i>Approval Center</a>
            @can('apply leave')
            <a href="{{ route('leave.apply') }}" class="btn btn-accent"><i class="bi bi-calendar-plus me-1"></i>Apply for Leave</a>
            @endcan
        </div>
    </div>

    <!-- Stage Breakdown Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave.approvals') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ empty($stageFilter) ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">All Pending</div>
                        <div class="fs-2 fw-bold text-navy">{{ $summary['pending'] }}</div>
                        <div class="small text-muted">{{ $summary['on_leave_today'] }} staff away today</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave.approvals', ['stage' => 1]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ (int)$stageFilter === 1 ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Stage 1: HR Officer</div>
                        <div class="fs-2 fw-bold text-primary">{{ $summary['stage1'] }}</div>
                        <div class="small text-muted">Initial verification</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave.approvals', ['stage' => 2]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ (int)$stageFilter === 2 ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Stage 2: Asst. HR Director</div>
                        <div class="fs-2 fw-bold text-warning">{{ $summary['stage2'] }}</div>
                        <div class="small text-muted">Endorsement pending</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('leave.approvals', ['stage' => 3]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ (int)$stageFilter === 3 ? 'border-start border-4 border-success' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Stage 3: HR Director</div>
                        <div class="fs-2 fw-bold text-success">{{ $summary['stage3'] }}</div>
                        <div class="small text-muted">{{ $summary['approved_this_month'] }} finalized this month</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Pending Leave Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <div class="card-header-title"><i class="bi bi-card-checklist"></i>Pending Leave Requests</div>
            @if($stageFilter)
                <a href="{{ route('leave.approvals') }}" class="btn btn-sm btn-outline-secondary">Clear Stage Filter</a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Leave Details</th>
                            <th>3-Tier Approval Trail</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-navy">{{ $app->employee->full_name }}</div>
                                <div class="small text-muted">{{ $app->employee->employee_no }} &bull; {{ $app->employee->department?->name ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $app->leaveType->name }} ({{ $app->days_requested }}d)</span>
                                <div class="small fw-semibold text-navy mt-1">{{ $app->start_date->format('d M Y') }} &rarr; {{ $app->end_date->format('d M Y') }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($app->reason, 60) }}</div>
                            </td>
                            <td>
                                <x-ui.approval-stepper :model="$app" :uid="'leave-trail-' . $app->id" />
                            </td>
                            <td class="pe-4 py-3 text-end">
                                @if($app->canUserApprove($authUser))
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveLeaveModal-{{ $app->id }}">
                                            <i class="bi bi-pen-fill me-1"></i>{{ $app->next_action_label }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectLeaveModal-{{ $app->id }}">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveLeaveModal-{{ $app->id }}" tabindex="-1" style="text-align:left;">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('leave.approve', $app->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title"><i class="bi bi-pen-fill me-2"></i>{{ $app->next_action_label }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small mb-2"><strong>{{ $app->employee->full_name }}</strong> — {{ $app->leaveType->name }} ({{ $app->days_requested }} days)</p>
                                                        <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                                                        <textarea name="remarks" class="form-control" rows="3" placeholder="Optional review/endorsement comments..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Confirm Digital Sign-Off</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectLeaveModal-{{ $app->id }}" tabindex="-1" style="text-align:left;">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('leave.reject', $app->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Leave Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Provide a reason for rejection..."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Reject Application</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border px-2.5 py-2">
                                        <i class="bi bi-lock me-1"></i>Awaiting {{ $app->current_stage_info['role'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                                No pending leave requests matching this view.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($recentDecided->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
            <div class="card-header-title"><i class="bi bi-clock-history"></i>Recently Finalized &amp; Rejected Leave Decisions</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">Employee</th><th>Leave</th><th>Outcome</th><th class="pe-4">Digital Trail</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentDecided as $dec)
                        <tr>
                            <td class="ps-4 py-2"><strong>{{ $dec->employee?->full_name }}</strong></td>
                            <td class="small">{{ $dec->leaveType?->name }} ({{ $dec->days_requested }}d)</td>
                            <td>
                                <span class="badge {{ $dec->status === 'Approved' ? 'bg-success' : 'bg-danger' }}">{{ $dec->status }}</span>
                                @if($dec->rejection_reason)<small class="d-block text-danger">{{ \Illuminate\Support\Str::limit($dec->rejection_reason, 45) }}</small>@endif
                            </td>
                            <td class="pe-4 py-2"><x-ui.approval-stepper :model="$dec" :uid="'dec-leave-' . $dec->id" /></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
