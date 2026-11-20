@extends('layouts.app')
@section('title', 'Employee Payslip')
@section('page-title', 'Employee Payslip')
@section('breadcrumb', 'HRMS › Finance & Payroll › Payslip')

@section('content')
<style>
@media print {
    #sidebar, #topbar, .sidebar, .topbar, .page-header, .btn, .d-print-none, nav, footer, header {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #main-content, .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>

<div class="animate-in" style="max-width:800px; margin:0 auto;">
    <div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h1>Salary Payslip</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-navy shadow-xs rounded-pill px-3 py-2 fw-semibold" onclick="window.print()"><i class="bi bi-printer me-1.5"></i>Print Payslip</button>
            <a href="{{ route('payroll.show', $record->payroll_period_id) }}" class="btn btn-navy shadow-xs rounded-pill px-3 py-2 fw-semibold"><i class="bi bi-arrow-left me-1.5"></i>Back to Cycle</a>
        </div>
    </div>

    <!-- Official Payslip Layout -->
    <div class="card p-4 shadow-sm border border-secondary-subtle">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h4 class="fw-bold text-navy mb-0">MINISTRY OF YOUTH & SPORTS</h4>
                <div style="font-size:12px; color:var(--text-muted);">Republic of Liberia</div>
                <div class="mt-2" style="font-size:13px; font-weight:600; color:#334155;">{{ $record->period->name }}</div>
            </div>
            <div style="text-align:right;">
                <div class="badge bg-success" style="font-size:12px;">{{ $record->status }}</div>
                <div class="mt-2" style="font-size:11px; color:var(--text-muted);">Run ID: #PR-{{ $record->id }}</div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="row mb-4">
            <div class="col-6">
                <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted);">Employee Name</div>
                <div class="fw-bold text-navy">{{ $record->employee?->full_name ?? 'Employee Record Deleted' }}</div>
                <div style="font-size:12px; color: #dc2626 !important;" class="fw-bold mt-1">ID: {{ $record->employee?->employee_no ?? '—' }}</div>
            </div>
            <div class="col-6" style="text-align:right;">
                <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted);">Department / Grade</div>
                <div class="fw-bold text-navy">{{ $record->employee?->department?->name ?? '—' }}</div>
                <div style="font-size:12px; color:#475569;" class="mt-1">Grade: {{ $record->employee?->salary_grade ?? '—' }}</div>
            </div>
        </div>

        <!-- Calculations Table -->
        <div class="row g-4 border-top pt-4">
            <!-- Earnings -->
            <div class="col-6">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="bi bi-plus-circle me-1"></i>Earnings & Allowances</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Basic Salary (LRD):</span>
                    <span class="font-monospace fw-semibold">{{ number_format($record->basic_salary_lrd, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Allowances (LRD):</span>
                    <span class="font-monospace fw-semibold">{{ number_format($record->allowances_lrd, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Basic Salary (USD):</span>
                    <span class="font-monospace fw-semibold">{{ number_format($record->basic_salary_usd, 2) }}</span>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-6 border-start">
                <h6 class="fw-bold text-danger border-bottom pb-2 mb-2"><i class="bi bi-dash-circle me-1"></i>Deductions</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Income Tax (10%):</span>
                    <span class="font-monospace fw-semibold text-danger">-{{ number_format($record->tax_lrd, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Pension (4.5%):</span>
                    <span class="font-monospace fw-semibold text-danger">-{{ number_format($record->pension_lrd, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Totals Summary Box -->
        <div class="mt-4 p-3 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
            <div class="row text-center">
                <div class="col-6 border-end">
                    <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted);">Net Take-Home LRD</div>
                    <div class="font-monospace fw-bold text-navy" style="font-size:20px; margin-top:4px;">LRD {{ number_format($record->net_pay_lrd, 2) }}</div>
                </div>
                <div class="col-6">
                    <div style="font-size:11px; text-transform:uppercase; color:var(--text-muted);">Net Take-Home USD</div>
                    <div class="font-monospace fw-bold text-accent" style="font-size:20px; margin-top:4px;">USD {{ number_format($record->net_pay_usd, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Signature/Footer -->
        <div class="d-flex justify-content-between align-items-end mt-5 pt-4 border-top" style="font-size:12px; color:var(--text-muted);">
            <div>
                <div>Generated on {{ now()->format('d M Y h:i A') }}</div>
                <div>HRMS Ministry of Youth & Sports</div>
            </div>
            <div style="text-align:right;">
                <div style="border-bottom:1px solid #94a3b8; width:150px; height:30px;"></div>
                <div class="mt-1">Authorized HR Director Signature</div>
            </div>
        </div>
    </div>
</div>
@endsection
