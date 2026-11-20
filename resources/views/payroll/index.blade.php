@extends('layouts.app')
@section('title', 'Payroll Management')
@section('page-title', 'Payroll Management')
@section('breadcrumb', 'HRMS › Finance & Payroll')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Payroll Administration</h1>
            <p>Generate, process, and track monthly salary distributions for staff.</p>
        </div>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
            <i class="bi bi-gear-fill me-1"></i>Run New Payroll
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-cash-stack"></i>Payroll Run Logs</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Payroll Cycle</th>
                            <th class="py-3">Start Date</th>
                            <th class="py-3">End Date</th>
                            <th class="py-3 text-center">Staff Count</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Payment Date</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $per)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3" style="font-weight:600; color:#1e293b;">
                                <a href="{{ route('payroll.show', $per->id) }}" style="text-decoration:none; color:var(--navy);">
                                    {{ $per->name }}
                                </a>
                            </td>
                            <td class="font-monospace" style="font-size:13px;">{{ $per->start_date->format('d M Y') }}</td>
                            <td class="font-monospace" style="font-size:13px;">{{ $per->end_date->format('d M Y') }}</td>
                            <td class="text-center font-monospace">{{ $per->records_count }}</td>
                            <td class="text-center">
                                @if($per->status == 'Draft')
                                <span class="badge bg-secondary">Draft</span>
                                @elseif($per->status == 'Processing')
                                <span class="badge bg-warning text-dark">In Review</span>
                                @elseif($per->status == 'Approved')
                                <span class="badge bg-primary">Approved</span>
                                @else
                                <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace" style="font-size:12.5px;">
                                {{ $per->payment_date ? $per->payment_date->format('d M Y') : '—' }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <a href="{{ route('payroll.show', $per->id) }}" class="btn btn-sm btn-outline-navy">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack fs-2 d-block mb-2"></i>
                                No payroll runs found. Click "Run New Payroll" to begin processing.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Run New Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-labelledby="generatePayrollModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payroll.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="generatePayrollModalLabel"><i class="bi bi-gear-fill me-2 text-navy"></i>Run New Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payroll Period Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. July 2026 Salary Cycle" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded text-muted" style="font-size:12px;">
                        <strong>Note:</strong> Generating payroll runs automatically imports all active employees, calculates basic salaries, applies tax (10%), pension deduction (4.5%), and logs a draft record for review.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Process Run</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
