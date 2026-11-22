@extends('layouts.app')
@section('title', $employee->isContractor() ? 'My Payment Slips' : 'My Payslips')
@section('page-title', $employee->isContractor() ? 'My Payment Slips' : 'My Payslips')
@section('breadcrumb', 'HRMS › Finance › My Payslips')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>{{ $employee->isContractor() ? 'My Payment Slips' : 'My Salary Payslips' }}</h1>
            <p>View earnings breakdowns, statutory deductions, and download official PDF statements.</p>
        </div>
        @if($availableYears->isNotEmpty())
        <form method="GET" action="{{ route('employee.payslips') }}" class="d-flex align-items-center gap-2">
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:130px;">
                <option value="">All Years</option>
                @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    <!-- Summary KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Payslips Available</div>
                    <div class="fs-3 fw-bold text-navy mt-1">{{ $summary['count'] }}</div>
                    <div class="small text-muted">{{ $selectedYear ? 'In ' . $selectedYear : 'All time' }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Total Net Pay (LRD)</div>
                    <div class="fs-4 fw-bold text-success mt-1">LRD {{ number_format((float)$summary['total_net_lrd'], 2) }}</div>
                    <div class="small text-muted">Cumulative net earnings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Total Net Pay (USD)</div>
                    <div class="fs-4 fw-bold text-primary mt-1">USD {{ number_format((float)$summary['total_net_usd'], 2) }}</div>
                    <div class="small text-muted">Cumulative USD earnings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Total Deductions (LRD)</div>
                    <div class="fs-4 fw-bold text-danger mt-1">LRD {{ number_format((float)$summary['total_ded_lrd'], 2) }}</div>
                    <div class="small text-muted">Tax, pension &amp; other</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payslips Table -->
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Pay Period</th>
                            <th class="text-end">Basic (LRD)</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Deductions (Tax + Pension)</th>
                            <th class="text-end">Net Pay (LRD)</th>
                            <th class="text-end">Net Pay (USD)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $payslip)
                            @php
                                $totalDed = (float)$payslip->tax_lrd + (float)$payslip->pension_lrd + (float)$payslip->deductions_lrd;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-navy">{{ $payslip->period?->name ?? $payslip->created_at?->format('M Y') }}</div>
                                    <small class="text-muted">
                                        {{ $payslip->period?->start_date?->format('d M') }} &ndash; {{ $payslip->period?->end_date?->format('d M Y') }}
                                    </small>
                                </td>
                                <td class="text-end font-monospace">LRD {{ number_format((float)$payslip->basic_salary_lrd, 2) }}</td>
                                <td class="text-end font-monospace text-success">+LRD {{ number_format((float)$payslip->allowances_lrd, 2) }}</td>
                                <td class="text-end font-monospace text-danger">-LRD {{ number_format($totalDed, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-success">LRD {{ number_format((float)$payslip->net_pay_lrd, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-primary">USD {{ number_format((float)$payslip->net_pay_usd, 2) }}</td>
                                <td>
                                    <span class="badge {{ $payslip->status === 'Paid' ? 'bg-success' : ($payslip->status === 'Approved' ? 'bg-primary' : 'bg-secondary') }}">
                                        {{ $payslip->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('payroll.payslip', $payslip->id) }}" class="btn btn-sm btn-outline-navy" title="View Official Payslip">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                    <a href="{{ route('payroll.payslip.pdf', $payslip->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-receipt fs-2 d-block mb-2 opacity-50"></i>
                                    No payslips are available yet.
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
