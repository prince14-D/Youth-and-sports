@extends('layouts.app')
@section('title', 'Accounting Ledger & Vouchers')
@section('page-title', 'Accounting Ledger & Payment Vouchers')
@section('breadcrumb', 'HRMS › Finance Division › Ledger')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-3">
        <div>
            <h1>General Accounting Ledger &amp; Expenditures</h1>
            <p>Full audit trail of Ministry payment vouchers, receipts, vendor disbursements, and budget charges.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('finance.dashboard') }}" class="btn btn-outline-navy fw-semibold"><i class="bi bi-speedometer2 me-1"></i>Finance Dashboard</a>
            <a href="{{ route('finance.ledger.csv') }}" class="btn btn-outline-success fw-semibold"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV</a>
            <a href="{{ route('finance.report.pdf') }}" class="btn btn-outline-danger fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i>Statement PDF</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('finance.ledger') }}" class="row g-2 align-items-center">
                <div class="col-md-3"><input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Voucher #, payee, check ref..."></div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)<option value="{{ $dept->id }}" {{ (string)$deptFilter === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)<option value="{{ $cat }}" {{ $categoryFilter === $cat ? 'selected' : '' }}>{{ $cat }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($transactionTypes as $tt)<option value="{{ $tt }}" {{ $typeFilter === $tt ? 'selected' : '' }}>{{ $tt }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Status</option>
                        @foreach(['Pending DMA', 'Approved', 'Paid', 'Rejected'] as $st)<option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-navy flex-fill">Filter</button>
                    <a href="{{ route('finance.ledger') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Voucher # &amp; Payee</th>
                        <th>Department / Budget</th>
                        <th>Date / Ref</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge bg-navy font-monospace">{{ $tx->voucher_no }}</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size:10px;">{{ $tx->transaction_type }}</span>
                                <div class="fw-bold text-navy mt-1">{{ $tx->payee_name }}</div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($tx->description, 70) }}</small>
                            </td>
                            <td class="small">
                                <div class="fw-semibold text-navy">{{ $tx->department?->name ?? 'Ministry HQ' }}</div>
                                <div class="text-muted">{{ $tx->category }}</div>
                                @if($tx->budgetAllocation)<span class="badge bg-light text-dark border font-monospace" style="font-size:10px;">{{ $tx->budgetAllocation->budget_code }}</span>@endif
                            </td>
                            <td class="small">
                                <div>{{ $tx->transaction_date?->format('d M Y') }}</div>
                                <div class="text-muted">{{ $tx->payment_method }}</div>
                                @if($tx->bank_reference)<code class="small">{{ $tx->bank_reference }}</code>@endif
                            </td>
                            <td class="text-end font-monospace small">
                                @if((float)$tx->amount_usd > 0)<div class="fw-bold {{ $tx->is_inflow ? 'text-success' : 'text-navy' }}">USD {{ number_format((float)$tx->amount_usd, 2) }}</div>@endif
                                @if((float)$tx->amount_lrd > 0)<div class="text-muted">LRD {{ number_format((float)$tx->amount_lrd, 2) }}</div>@endif
                            </td>
                            <td>
                                <span class="badge {{ $tx->status_badge_class }}">{{ $tx->status }}</span>
                                @if($tx->approver)<small class="d-block text-muted mt-1">{{ $tx->approver->name }}</small>@endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('finance.vouchers.pdf', $tx) }}" class="btn btn-sm btn-outline-danger" title="Payment Voucher PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                @if($tx->status === 'Pending DMA')
                                    <form method="POST" action="{{ route('finance.transactions.status', $tx) }}" class="d-inline">
                                        @csrf <input type="hidden" name="decision" value="Approved">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('finance.transactions.status', $tx) }}" class="d-inline">
                                        @csrf <input type="hidden" name="decision" value="Rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                @elseif($tx->status === 'Approved')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal-{{ $tx->id }}">Mark Paid</button>
                                    <div class="modal fade" id="payModal-{{ $tx->id }}" tabindex="-1" style="text-align:left;">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('finance.transactions.status', $tx) }}">
                                                    @csrf <input type="hidden" name="decision" value="Paid">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">Confirm Disbursement — {{ $tx->voucher_no }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <label class="form-label">CBL / Bank Transfer or Check Reference</label>
                                                        <input name="bank_reference" class="form-control" value="{{ $tx->bank_reference }}" placeholder="e.g. CBL-CHK-004921" required>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Confirm Paid</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No financial ledger entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer bg-white border-top">{{ $transactions->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
