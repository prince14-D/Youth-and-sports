@extends('layouts.app')
@section('title', 'Leave Types')
@section('page-title', 'Leave Types')
@section('breadcrumb', 'HRMS › Leave Management › Types')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Manage Leave Types</h1>
            <p>Configure leave rules, allowed quotas, and paid/unpaid statuses.</p>
        </div>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#createTypeModal">
            <i class="bi bi-plus-circle me-1"></i>Add Leave Type
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-sliders"></i>Configured Leave Types</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Type Name</th>
                            <th class="py-3">Code</th>
                            <th class="py-3 text-center">Allowed Days</th>
                            <th class="py-3 text-center">Paid Status</th>
                            <th class="py-3 text-center">Requires Approval</th>
                            <th class="pe-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3" style="font-weight:600; color:#1e293b;">{{ $type->name }}</td>
                            <td><code style="font-size:12px; background:#f1f5f9; padding:2px 6px; border-radius:4px;">{{ $type->code }}</code></td>
                            <td class="text-center font-monospace">{{ $type->days_allowed ?: 'Flexible' }}</td>
                            <td class="text-center">
                                @if($type->is_paid)
                                <span class="badge bg-success">Paid</span>
                                @else
                                <span class="badge bg-secondary">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($type->requires_approval)
                                <span class="badge bg-primary">Yes</span>
                                @else
                                <span class="badge bg-light text-dark border">No</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-center">
                                @if($type->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-sliders fs-2 d-block mb-2"></i>
                                No leave types configured.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Type Modal -->
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leave.store-type') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createTypeModalLabel"><i class="bi bi-plus-circle me-2 text-navy"></i>Create Leave Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Study Leave, Casual Leave" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. STL" maxlength="10" style="text-transform:uppercase;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Days Allowed <span class="text-danger">*</span></label>
                        <input type="number" name="days_allowed" class="form-control" placeholder="e.g. 14 (0 for flexible limit)" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Is Paid? <span class="text-danger">*</span></label>
                        <select name="is_paid" class="form-select" required>
                            <option value="1">Paid Leave</option>
                            <option value="0">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Requires Approval? <span class="text-danger">*</span></label>
                        <select name="requires_approval" class="form-select" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
