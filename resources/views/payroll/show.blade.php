@extends('layouts.app')
@section('title', 'Payroll Cycle Details')
@section('page-title', 'Payroll Cycle Details')
@section('breadcrumb', 'HRMS › Finance & Payroll › Cycle Details')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap">
        <div>
            <h1>{{ $period->name }}</h1>
            <p>Status: 
                @if($period->status == 'Draft')
                <span class="badge bg-secondary">Draft</span>
                @elseif($period->status == 'Approved')
                <span class="badge bg-primary">Approved</span>
                @else
                <span class="badge bg-success">Paid</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.index') }}" class="btn btn-white border shadow-xs rounded-pill px-3.5 py-2 text-navy font-weight-600 d-inline-flex align-items-center gap-2 hover-shadow transition-all" style="font-size: 13px; background: #ffffff; border-color: #CBD5E1 !important;">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                    <i class="bi bi-arrow-left text-primary" style="font-size: 13px;"></i>
                </div>
                <span>Back to Cycles</span>
            </a>
            @if($period->status == 'Draft')
            <form action="{{ route('payroll.review', $period->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Send this payroll run for review?')">
                    <i class="bi bi-search me-1"></i>Send for Review
                </button>
            </form>
            @elseif($period->status == 'Processing')
            <form action="{{ route('payroll.approve', $period->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-navy" onclick="return confirm('Approve this payroll run?')"><i class="bi bi-patch-check me-1"></i>Approve Run</button>
            </form>
            @endif
            @if($period->status == 'Approved')
            <form action="{{ route('payroll.pay', $period->id) }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="payment_reference" value="">
                <button type="submit" class="btn btn-success" onclick="return confirm('Confirm payment. This will finalize the payroll cycle.')">
                    <i class="bi bi-wallet2 me-1"></i>Mark Paid
                </button>
            </form>
            @endif
            @if($period->locked_at)<span class="badge bg-dark align-self-center"><i class="bi bi-lock me-1"></i>Locked</span>@endif
        </div>
    </div>

    <!-- Cycle Totals Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card bg-navy text-white">
                <div class="card-body">
                    <div style="font-size:12px; text-transform:uppercase; opacity:0.8;">Total Net LRD</div>
                    <div style="font-size:24px; font-weight:700; margin-top:5px;">LRD {{ number_format($totals['lrd'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card bg-gold text-navy">
                <div class="card-body">
                    <div style="font-size:12px; text-transform:uppercase; opacity:0.8;">Total Net USD</div>
                    <div style="font-size:24px; font-weight:700; margin-top:5px;">USD {{ number_format($totals['usd'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-light border">
                <div class="card-body">
                    <div class="text-muted" style="font-size:12px; text-transform:uppercase;">Staff Enrolled</div>
                    <div style="font-size:24px; font-weight:700; color:var(--navy); margin-top:5px;">{{ $records->count() }} Employees</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salaries breakdown grid -->
    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-wallet"></i>Salaries Registry Breakdown</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13.5px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Employee</th>
                            <th class="py-3 text-end">Basic LRD</th>
                            <th class="py-3 text-end">Pension LRD</th>
                            <th class="py-3 text-end">Tax LRD</th>
                            <th class="py-3 text-end">Allowances LRD</th>
                            <th class="py-3 text-end">Net Pay LRD</th>
                            <th class="py-3 text-end">Net Pay USD</th>
                            <th class="pe-4 py-3 text-end">Payslip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div style="font-weight:600; color:#1e293b;">{{ $rec->employee?->full_name ?? 'Employee Record Deleted' }}</div>
                                <div style="font-size:11px; color: #dc2626 !important;" class="fw-bold">{{ $rec->employee?->employee_no ?? '—' }}</div>
                            </td>
                            <td class="text-end font-monospace">{{ number_format($rec->basic_salary_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format($rec->pension_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format($rec->tax_lrd, 2) }}</td>
                            <td class="text-end font-monospace text-success">+{{ number_format($rec->allowances_lrd, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-navy">{{ number_format($rec->net_pay_lrd, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-accent">{{ number_format($rec->net_pay_usd, 2) }}</td>
                            <td class="pe-4 py-3 text-end">
                                <a href="{{ route('payroll.payslip', $rec->id) }}" class="btn btn-sm btn-outline-navy">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Payslip
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No records processed for this cycle.
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
