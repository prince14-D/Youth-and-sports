@extends('layouts.app')

@section('title', 'Contractors')
@section('page-title', 'Contractors')
@section('breadcrumb', 'HRMS › Contractors')

@section('content')
<div class="animate-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold text-navy mb-1">Contractor Directory</h1>
            <p class="text-muted mb-0 small">Track active and inactive contractors across the ministry.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-white border rounded-pill px-3.5 py-2 text-navy fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Back to Employees
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Contractors</div>
                            <div class="display-6 fw-bold text-navy">{{ number_format($totalContractors) }}</div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Active Contractors</div>
                            <div class="display-6 fw-bold text-success">{{ number_format($activeContractors) }}</div>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Inactive Contractors</div>
                            <div class="display-6 fw-bold text-warning">{{ number_format($inactiveContractors) }}</div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-navy">Contractor Records</h5>
                </div>
                <form method="GET" action="{{ route('contractors.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search contractor" style="min-width:220px;">
                    <select name="status" class="form-select form-select-sm" style="min-width:150px;">
                        <option value="">All Status</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-navy">Apply</button>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contractor</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contractors as $contractor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($contractor->photo_url)
                                            <img src="{{ $contractor->photo_url }}" alt="{{ $contractor->full_name }}" class="rounded-circle border" style="width:42px;height:42px;object-fit:cover;">
                                        @else
                                            <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center fw-bold border" style="width:42px;height:42px;font-size:12px;">
                                                {{ strtoupper(substr($contractor->first_name, 0, 1) . substr($contractor->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-navy">{{ $contractor->full_name }}</div>
                                            <div class="small text-muted">{{ $contractor->employee_no }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $contractor->department?->name ?? '—' }}</td>
                                <td>{{ $contractor->designation ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $contractor->employment_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $contractor->employment_status }}
                                    </span>
                                </td>
                                <td>{{ $contractor->date_joined?->format('d M Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-sm btn-outline-navy rounded-pill px-3">
                                        View Profile
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No contractor records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($contractors->hasPages())
            <div class="card-footer bg-white border-top d-flex justify-content-end">
                {{ $contractors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
