@extends('layouts.app')
@section('title', 'Contract Renewals')
@section('page-title', 'Contract Renewal Workflow')
@section('breadcrumb', 'HRMS › Compliance › Renewals')

@section('content')
<div class="animate-in">
    @php $authUser = auth()->user(); @endphp

    <div class="page-header">
        <div>
            <h1>Contract Renewal Workflow (3-Tier Approval)</h1>
            <p>HR Officer (Stage 1) &rarr; Assistant HR Director (Stage 2) &rarr; HR Director Final Approval (Stage 3).</p>
        </div>
        <a href="{{ route('approvals.index') }}" class="btn btn-outline-navy"><i class="bi bi-diagram-3 me-1"></i>Approval Center</a>
    </div>

    <!-- Stage Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pending Renewals</div><div class="fs-3 fw-bold text-navy">{{ $summary['pending'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 1: HR Officer</div><div class="fs-3 fw-bold text-primary">{{ $summary['stage1'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 2: Asst. Director</div><div class="fs-3 fw-bold text-warning">{{ $summary['stage2'] }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Stage 3: HR Director</div><div class="fs-3 fw-bold text-success">{{ $summary['stage3'] }}</div></div></div></div>
    </div>

    <!-- Submit New Renewal Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-plus-circle me-2 text-primary"></i>Submit Contract Renewal Request</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('contracts.renewals.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Contractor <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select Contractor</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->employee_no }} - {{ $e->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Proposed End Date <span class="text-danger">*</span></label>
                    <input type="date" name="proposed_end_date" class="form-control" min="{{ now()->addDay()->toDateString() }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Justification / Reason <span class="text-danger">*</span></label>
                    <input name="reason" class="form-control" placeholder="Deliverables & renewal justification" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Attachment</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-navy w-100">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Renewals Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Contract Renewal Requests &amp; Digital Trails</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Contractor</th>
                        <th>Contract Dates</th>
                        <th>3-Tier Approval Trail</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($renewals as $r)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-navy">{{ $r->employee?->full_name ?? '—' }}</div>
                                <small class="text-muted">{{ $r->employee?->employee_no }} &bull; {{ $r->employee?->department?->name ?? '—' }}</small>
                                <div class="small text-secondary mt-1">{{ \Illuminate\Support\Str::limit($r->reason, 65) }}</div>
                            </td>
                            <td class="small">
                                <div><span class="text-muted">Current:</span> {{ $r->current_end_date?->format('d M Y') ?? '—' }}</div>
                                <div class="fw-bold text-success"><span class="text-muted fw-normal">Proposed:</span> {{ $r->proposed_end_date?->format('d M Y') ?? '—' }}</div>
                                @if($r->document_path)
                                    <a href="{{ asset('storage/' . $r->document_path) }}" target="_blank" class="small text-decoration-none"><i class="bi bi-paperclip"></i> Document</a>
                                @endif
                            </td>
                            <td>
                                <x-ui.approval-stepper :model="$r" :uid="'renewal-trail-' . $r->id" />
                            </td>
                            <td>
                                <span class="badge {{ $r->status === 'Approved' ? 'bg-success' : ($r->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $r->status }}</span>
                                @if($r->rejection_reason)
                                    <small class="d-block text-danger" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($r->rejection_reason, 40) }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($r->status === 'Pending')
                                    @if($r->canUserApprove($authUser))
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveRen-{{ $r->id }}">
                                                <i class="bi bi-pen-fill me-1"></i>{{ $r->next_action_label }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectRen-{{ $r->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>

                                        <div class="modal fade" id="approveRen-{{ $r->id }}" tabindex="-1" style="text-align:left;">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('contracts.renewals.approve', $r) }}">
                                                        @csrf
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title"><i class="bi bi-pen-fill me-2"></i>{{ $r->next_action_label }}</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small mb-2"><strong>{{ $r->employee?->full_name }}</strong> — Extend until {{ $r->proposed_end_date?->format('d M Y') }}</p>
                                                            <label class="form-label">Sign-Off Remarks <span class="text-muted">(optional)</span></label>
                                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Add verification or endorsement notes..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Digital Sign-Off</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="rejectRen-{{ $r->id }}" tabindex="-1" style="text-align:left;">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('contracts.renewals.reject', $r) }}">
                                                        @csrf
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Contract Renewal</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Provide reason for rejecting this renewal..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Renewal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1.5"><i class="bi bi-lock me-1"></i>Awaiting {{ $r->current_stage_info['role'] }}</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Finalized</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No contract renewal requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
