@extends('layouts.app')
@section('title', 'Leave Approvals')
@section('page-title', 'Leave Approvals')
@section('breadcrumb', 'HRMS › Leave Management › Approvals')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Leave Request Approvals</h1>
            <p>Approve or reject pending leave requests submitted by staff.</p>
        </div>
        <a href="{{ route('leave.apply') }}" class="btn btn-accent">
            <i class="bi bi-calendar-plus me-1"></i>Apply for Leave
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pending review</div><div class="display-6 fw-bold text-warning">{{ $summary['pending'] }}</div><div class="small text-muted">Requests waiting for HR action</div></div></div></div>
        <div class="col-12 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Approved this month</div><div class="display-6 fw-bold text-success">{{ $summary['approved_this_month'] }}</div><div class="small text-muted">Leave decisions completed</div></div></div></div>
        <div class="col-12 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Away today</div><div class="display-6 fw-bold text-primary">{{ $summary['on_leave_today'] }}</div><div class="small text-muted">Approved leave currently active</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-card-checklist"></i>Pending Requests</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Employee</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Dates</th>
                            <th class="py-3 text-center">Days</th>
                            <th class="py-3">Reason</th>
                            <th class="pe-4 py-3 text-end" style="width: 25%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="emp-avatar-placeholder" style="width:32px; height:32px; font-size:12px;">
                                        {{ strtoupper(substr($app->employee->first_name, 0, 1) . substr($app->employee->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; font-size: 13.5px; color:#1e293b;">{{ $app->employee->full_name }}</div>
                                        <div style="font-size: 11px; color:#64748b;">{{ $app->employee->employee_no }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color:rgba(78,96,255,.08); color:var(--accent); font-size:12px;">
                                    {{ $app->leaveType->name }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight:600; color:#334155;">
                                    {{ $app->start_date->format('d M Y') }} &rarr; {{ $app->end_date->format('d M Y') }}
                                </div>
                                <div style="font-size:11px; color:#64748b;">Requested on {{ $app->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" style="font-size:12px;">{{ $app->days_requested }} Days</span>
                            </td>
                            <td style="font-size: 13px; color:#475569; max-width: 200px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $app->reason }}">
                                {{ $app->reason ?? '—' }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="{{ route('leave.approve', $app->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this request?')">
                                            <i class="bi bi-check-circle me-1"></i>Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $app->id }}">
                                        <i class="bi bi-x-circle me-1"></i>Reject
                                    </button>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal-{{ $app->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('leave.reject', $app->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-navy"><i class="bi bi-x-circle me-2 text-danger"></i>Reject Leave Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Provide a reason why this leave is rejected..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger">Reject Application</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                                No pending leave requests to show.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
