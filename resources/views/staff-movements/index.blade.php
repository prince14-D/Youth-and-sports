@extends('layouts.app')
@section('title', 'Staff Movement')
@section('page-title', 'Staff Movement & Approvals')
@section('breadcrumb', 'HRMS › Staff Movement')

@section('content')
<div class="animate-in">
    @php $authUser = auth()->user(); @endphp

    <div class="page-header">
        <div>
            <h1>Staff Movement (3-Tier Approval)</h1>
            <p>Manage transfers, promotions, acting appointments &amp; department changes through HR Officer &rarr; Asst. HR Director &rarr; HR Director sign-off.</p>
        </div>
        <a href="{{ route('approvals.index') }}" class="btn btn-outline-navy"><i class="bi bi-diagram-3 me-1"></i>Approval Center</a>
    </div>

    <!-- Stage Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pending Movements</div><div class="fs-3 fw-bold text-navy">{{ $summary['pending'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 1: HR Officer</div><div class="fs-3 fw-bold text-primary">{{ $summary['stage1'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 2: Asst. Director</div><div class="fs-3 fw-bold text-warning">{{ $summary['stage2'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 3: HR Director</div><div class="fs-3 fw-bold text-success">{{ $summary['stage3'] }}</div></div></div></div>
    </div>

    <!-- Request Movement Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-plus-circle me-2 text-primary"></i>Initiate Staff Movement</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('staff-movements.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->employee_no }} - {{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Movement Type <span class="text-danger">*</span></label>
                    <select name="movement_type" class="form-select" required>
                        <option>Transfer</option>
                        <option>Promotion</option>
                        <option>Acting Appointment</option>
                        <option>Department Change</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Target Department</label>
                    <select name="to_department_id" class="form-select">
                        <option value="">Keep current</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">New Designation</label>
                    <input name="to_designation" class="form-control" placeholder="Optional new job title">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" name="effective_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-10">
                    <input name="reason" class="form-control" placeholder="Official justification / memo reference..." required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-navy w-100"><i class="bi bi-send me-1"></i>Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Movement Requests &amp; Digital Approval Trails</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Employee &amp; Change</th>
                        <th>Effective</th>
                        <th>3-Tier Approval Trail</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-navy">{{ $movement->employee?->full_name }} <span class="badge bg-info-subtle text-info-emphasis ms-1">{{ $movement->movement_type }}</span></div>
                                <div class="small text-muted">
                                    {{ $movement->fromDepartment?->name ?? '—' }} &rarr; <strong>{{ $movement->toDepartment?->name ?? $movement->fromDepartment?->name ?? '—' }}</strong>
                                    @if($movement->to_designation)
                                        &bull; {{ $movement->from_designation }} &rarr; <strong>{{ $movement->to_designation }}</strong>
                                    @endif
                                </div>
                                <div class="small text-secondary mt-1">{{ \Illuminate\Support\Str::limit($movement->reason, 65) }}</div>
                            </td>
                            <td class="small fw-semibold text-nowrap">{{ $movement->effective_date?->format('d M Y') }}</td>
                            <td>
                                <x-ui.approval-stepper :model="$movement" :uid="'mov-trail-' . $movement->id" />
                            </td>
                            <td>
                                <span class="badge {{ $movement->status === 'Approved' ? 'bg-success' : ($movement->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $movement->status }}</span>
                                @if($movement->rejection_reason)
                                    <small class="d-block text-danger" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($movement->rejection_reason, 40) }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($movement->status === 'Pending')
                                    @if($movement->canUserApprove($authUser))
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveMov-{{ $movement->id }}">
                                                <i class="bi bi-pen-fill me-1"></i>{{ $movement->next_action_label }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectMov-{{ $movement->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>

                                        <div class="modal fade" id="approveMov-{{ $movement->id }}" tabindex="-1" style="text-align:left;">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('staff-movements.approve', $movement) }}">
                                                        @csrf
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title"><i class="bi bi-pen-fill me-2"></i>{{ $movement->next_action_label }}</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small mb-2"><strong>{{ $movement->employee?->full_name }}</strong> — {{ $movement->movement_type }}</p>
                                                            <label class="form-label">Sign-Off Remarks <span class="text-muted">(optional)</span></label>
                                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Verification / endorsement notes..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Digital Sign-Off</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="rejectMov-{{ $movement->id }}" tabindex="-1" style="text-align:left;">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('staff-movements.reject', $movement) }}">
                                                        @csrf
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Staff Movement</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="State reason for rejecting this staff movement..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Movement</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1.5"><i class="bi bi-lock me-1"></i>Awaiting {{ $movement->current_stage_info['role'] }}</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Finalized</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No staff movement requests recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
