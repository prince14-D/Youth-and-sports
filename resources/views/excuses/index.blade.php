@extends('layouts.app')
@section('title', 'Excuse Management')
@section('page-title', 'Excuse Management')
@section('breadcrumb', 'HRMS › Leave Management › Excuses')
@section('content')
<div class="animate-in">
    <div class="page-header"><div><h1>Excuse Management</h1><p>Submit, track, and review attendance excuses.</p></div>@can('submit excuse')<a href="{{ route('excuses.create') }}" class="btn btn-accent"><i class="bi bi-plus-circle me-1"></i>Submit Excuse</a>@endcan</div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pending review</div><div class="display-6 fw-bold text-warning">{{ $summary['pending'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Approved this month</div><div class="display-6 fw-bold text-success">{{ $summary['approved_this_month'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Excuses recorded today</div><div class="display-6 fw-bold text-primary">{{ $summary['today'] }}</div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Employee</th><th>Date</th><th>Type</th><th>Reason</th><th>Status</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
    @forelse($excuses as $excuse)
        <tr><td class="ps-4"><strong>{{ $excuse->employee->full_name }}</strong><small class="d-block text-muted">{{ $excuse->employee->employee_no }}</small></td><td>{{ $excuse->excuse_date->format('d M Y') }}</td><td>{{ $excuse->excuse_type }}</td><td class="text-muted" style="max-width:260px;">{{ Str::limit($excuse->reason, 80) }}</td><td><span class="badge {{ $excuse->status === 'Approved' ? 'bg-success' : ($excuse->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $excuse->status }}</span>@if($excuse->rejection_reason)<small class="d-block text-danger">{{ Str::limit($excuse->rejection_reason, 50) }}</small>@endif</td><td class="text-end pe-4">@can('approve excuses') @if($excuse->status === 'Pending')<form method="POST" action="{{ route('excuses.approve', $excuse) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button></form><button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reject{{ $excuse->id }}" title="Reject"><i class="bi bi-x-lg"></i></button>@endif @endcan @if($excuse->attachment_path)<a href="{{ asset('storage/'.$excuse->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View attachment"><i class="bi bi-paperclip"></i></a>@endif</td></tr>
        @can('approve excuses')<div class="modal fade" id="reject{{ $excuse->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('excuses.reject', $excuse) }}">@csrf<div class="modal-header"><h5>Reject Excuse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><textarea name="rejection_reason" class="form-control" required placeholder="Reason for rejection"></textarea></div><div class="modal-footer"><button class="btn btn-danger">Reject</button></div></form></div></div></div>@endcan
    @empty
        <tr><td colspan="6" class="text-center py-5 text-muted">No excuse records found.</td></tr>
    @endforelse
    </tbody></table></div></div></div>
    <div class="mt-3">{{ $excuses->links() }}</div>
</div>
@endsection
