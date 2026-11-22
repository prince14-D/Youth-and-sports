@extends('layouts.app')
@section('title', ($isContractor ? 'Contractor Payment Advice' : 'Official Payslip') . ' — ' . ($record->period?->name ?? ''))
@section('page-title', $isContractor ? 'Contractor Payment Advice' : 'Official Salary Payslip')
@section('breadcrumb', 'HRMS › Payroll › Payslip')

@section('content')
<style>
.payslip-sheet {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    position: relative;
}
.payslip-masthead {
    background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 65%, #1d4ed8 100%);
    color: #ffffff;
    padding: 24px 28px;
}
.payslip-strip {
    height: 5px;
    background: linear-gradient(90deg, #bf0a30 0%, #bf0a30 33%, #ffffff 33%, #ffffff 66%, #002868 66%, #002868 100%);
}
.payslip-watermark {
    position: absolute;
    top: 52%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-24deg);
    font-size: 72px;
    font-weight: 800;
    letter-spacing: 8px;
    color: rgba(15, 23, 42, 0.035);
    pointer-events: none;
    user-select: none;
    white-space: nowrap;
}
.payslip-info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
}
.payslip-info-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 13px;
}
.payslip-info-row:last-child { border-bottom: none; }
.payslip-ledger th {
    background: #f1f5f9 !important;
    font-size: 11px;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: #475569;
}
.payslip-net-banner {
    background: linear-gradient(135deg, #0f172a 0%, #123f7a 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: 20px 24px;
}
@media print {
    #sidebar, #topbar, .sidebar, .topbar, .page-header, .btn, .d-print-none, nav, footer, header {
        display: none !important;
    }
    body { background: #ffffff !important; padding: 0 !important; margin: 0 !important; }
    #main-content, .main-content { margin-left: 0 !important; margin-top: 0 !important; padding: 0 !important; width: 100% !important; }
    .payslip-sheet { box-shadow: none !important; border: 1px solid #94a3b8 !important; border-radius: 0 !important; }
    .payslip-masthead, .payslip-net-banner, .payslip-strip { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="animate-in" style="max-width:920px; margin:0 auto;">
    <!-- Action Toolbar -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h1 class="mb-0">{{ $isContractor ? 'Contractor Payment Advice' : 'Official Salary Payslip' }}</h1>
            <p class="text-muted small mb-0">Reference: <strong>{{ $payslipRef }}</strong> &bull; {{ $record->period?->name }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('payroll.payslip.pdf', $record->id) }}" class="btn btn-danger rounded-pill px-3.5 py-2 fw-semibold shadow-xs">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download Official PDF
            </a>
            @if($canViewPayroll)
                <form method="POST" action="{{ route('payroll.payslip.email', $record->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold shadow-xs" onclick="return confirm('Email this official PDF payslip to {{ addslashes($record->employee?->full_name ?? 'staff') }}?')">
                        <i class="bi bi-envelope-fill me-1"></i>Email Payslip
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-outline-navy rounded-pill px-3.5 py-2 fw-semibold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            @if($canViewPayroll)
                <a href="{{ route('payroll.show', $record->payroll_period_id) }}" class="btn btn-navy rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i>Back to Payroll Cycle
                </a>
            @else
                <a href="{{ route('employee.payslips') }}" class="btn btn-navy rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i>My Payslips
                </a>
            @endif
        </div>
    </div>

    <!-- Official Payslip Sheet -->
    <div class="payslip-sheet">
        <div class="payslip-watermark">{{ strtoupper($record->status) }} &bull; MYS LIBERIA</div>

        <!-- Masthead -->
        <div class="payslip-masthead">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if(file_exists(public_path('images/logo.png')))
                        <div class="bg-white rounded-3 p-1.5 shadow-sm" style="width:62px;height:62px;display:flex;align-items:center;justify-content:center;">
                            <img src="{{ asset('images/logo.png') }}" alt="MYS Logo" style="max-width:52px;max-height:52px;object-fit:contain;">
                        </div>
                    @endif
                    <div>
                        <div style="font-size:11px;letter-spacing:1.4px;text-transform:uppercase;opacity:0.85;">Republic of Liberia</div>
                        <h4 class="fw-bold mb-0 text-white">MINISTRY OF YOUTH &amp; SPORTS</h4>
                        <div style="font-size:12.5px;opacity:0.9;">
                            {{ $isContractor ? 'Official Contractor Payment Advice & Stipend Statement' : 'Official Employee Earnings & Deductions Statement' }}
                        </div>
                    </div>
                </div>
                <div class="text-md-end">
                    <span class="badge px-3 py-1.5 rounded-pill fw-bold {{ $record->status === 'Paid' ? 'bg-success' : ($record->status === 'Approved' ? 'bg-info text-dark' : 'bg-warning text-dark') }}" style="font-size:12px;">
                        <i class="bi {{ $record->status === 'Paid' ? 'bi-patch-check-fill' : 'bi-clock-history' }} me-1"></i>{{ strtoupper($record->status) }}
                    </span>
                    <div class="mt-2 small" style="opacity:0.9;">
                        <strong>Ref:</strong> {{ $payslipRef }}<br>
                        <strong>Period:</strong> {{ $record->period?->name ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="payslip-strip"></div>

        <div class="p-4">
            <!-- Staff & Payment Metadata -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="payslip-info-box h-100">
                        <div class="small fw-bold text-uppercase text-primary mb-2">
                            <i class="bi bi-person-vcard me-1"></i>{{ $isContractor ? 'Contractor Details' : 'Employee Details' }}
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Full Name</span>
                            <span class="fw-bold text-navy">{{ $record->employee?->full_name ?? 'Deleted Record' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">{{ $isContractor ? 'Contractor ID' : 'Employee ID' }}</span>
                            <span class="fw-bold text-danger font-monospace">{{ $record->employee?->employee_no ?? '—' }}</span>
                        </div>
                        @if($record->employee?->personnel_number)
                        <div class="payslip-info-row">
                            <span class="text-muted">Personnel No.</span>
                            <span class="fw-semibold font-monospace">{{ $record->employee->personnel_number }}</span>
                        </div>
                        @endif
                        <div class="payslip-info-row">
                            <span class="text-muted">Department</span>
                            <span class="fw-semibold text-navy">{{ $record->employee?->department?->name ?? '—' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Designation</span>
                            <span class="fw-semibold">{{ $record->employee?->designation ?? '—' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Employment Category</span>
                            <span class="badge bg-primary-subtle text-primary">{{ $record->employee?->employment_type ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="payslip-info-box h-100">
                        <div class="small fw-bold text-uppercase text-primary mb-2">
                            <i class="bi bi-bank me-1"></i>Payment &amp; Statutory Info
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Pay Period Dates</span>
                            <span class="fw-semibold">
                                {{ $record->period?->start_date?->format('d M Y') ?? '—' }} &ndash; {{ $record->period?->end_date?->format('d M Y') ?? '—' }}
                            </span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Payment Date</span>
                            <span class="fw-semibold">{{ $record->period?->payment_date?->format('d M Y') ?? 'Pending Disbursement' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Salary Grade</span>
                            <span class="fw-semibold">{{ $record->employee?->salary_grade ?? 'Standard' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Bank / Account</span>
                            <span class="fw-semibold font-monospace">{{ $record->employee?->bank_name ? $record->employee->bank_name . ($maskedAccount ? ' (' . $maskedAccount . ')' : '') : 'Direct Ministry Payroll' }}</span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">TIN / NASSCORP</span>
                            <span class="fw-semibold font-monospace">
                                {{ $record->employee?->tin_number ?: '—' }} / {{ $record->employee?->social_security_no ?: '—' }}
                            </span>
                        </div>
                        <div class="payslip-info-row">
                            <span class="text-muted">Payment Reference</span>
                            <span class="fw-semibold font-monospace">{{ $record->period?->payment_reference ?: ('CYCLE-' . $record->payroll_period_id) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings & Deductions Ledger -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="border rounded-3 overflow-hidden h-100">
                        <div class="bg-success-subtle text-success-emphasis px-3 py-2 fw-bold small d-flex justify-content-between">
                            <span><i class="bi bi-plus-circle-fill me-1"></i>EARNINGS &amp; ALLOWANCES</span>
                            <span>AMOUNT</span>
                        </div>
                        <table class="table table-sm mb-0 payslip-ledger">
                            <tbody>
                                <tr>
                                    <td class="ps-3 py-2">{{ $isContractor ? 'Base Contract Fee (LRD)' : 'Basic Salary (LRD)' }}</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold">LRD {{ number_format((float)$record->basic_salary_lrd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2">Allowances (LRD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold">LRD {{ number_format((float)$record->allowances_lrd, 2) }}</td>
                                </tr>
                                @foreach($record->lineItems->where('type', 'allowance')->where('currency', 'LRD') as $li)
                                <tr class="small text-muted">
                                    <td class="ps-4 py-1">&bull; {{ $li->category }} @if($li->description)<span class="opacity-75">({{ $li->description }})</span>@endif</td>
                                    <td class="pe-3 py-1 text-end font-monospace">LRD {{ number_format((float)$li->amount, 2) }}</td>
                                </tr>
                                @endforeach
                                @if((float)$record->overtime_lrd > 0)
                                <tr>
                                    <td class="ps-3 py-2">Overtime / Bonus (LRD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold">LRD {{ number_format((float)$record->overtime_lrd, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-light">
                                    <td class="ps-3 py-2 fw-bold text-navy">Gross Earnings (LRD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-bold text-success">LRD {{ number_format($grossLrd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2">{{ $isContractor ? 'Base Contract Fee (USD)' : 'Basic Salary (USD)' }}</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold">USD {{ number_format((float)$record->basic_salary_usd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2">Allowances (USD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold">USD {{ number_format((float)$record->allowances_usd, 2) }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td class="ps-3 py-2 fw-bold text-navy">Gross Earnings (USD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-bold text-primary">USD {{ number_format($grossUsd, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-3 overflow-hidden h-100 d-flex flex-column">
                        <div class="bg-danger-subtle text-danger-emphasis px-3 py-2 fw-bold small d-flex justify-content-between">
                            <span><i class="bi bi-dash-circle-fill me-1"></i>STATUTORY &amp; OTHER DEDUCTIONS</span>
                            <span>AMOUNT</span>
                        </div>
                        <table class="table table-sm mb-0 payslip-ledger flex-grow-1">
                            <tbody>
                                <tr>
                                    <td class="ps-3 py-2">LRA Income Tax (10%)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold text-danger">-LRD {{ number_format((float)$record->tax_lrd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2">Civil Service Pension / NASSCORP (4.5%)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold text-danger">-LRD {{ number_format((float)$record->pension_lrd, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2">Other Deductions</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-semibold text-danger">-LRD {{ number_format((float)$record->deductions_lrd, 2) }}</td>
                                </tr>
                                @foreach($record->lineItems->where('type', 'deduction') as $li)
                                <tr class="small text-muted">
                                    <td class="ps-4 py-1">&bull; {{ $li->category }} @if($li->description)<span class="opacity-75">({{ $li->description }})</span>@endif</td>
                                    <td class="pe-3 py-1 text-end font-monospace text-danger">-{{ $li->currency }} {{ number_format((float)$li->amount, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="table-light">
                                    <td class="ps-3 py-2 fw-bold text-navy">Total Deductions (LRD)</td>
                                    <td class="pe-3 py-2 text-end font-monospace fw-bold text-danger">-LRD {{ number_format($totalDeductionsLrd, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Prominent Net Pay Banner + Amount in Words -->
            <div class="payslip-net-banner mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-6 border-end border-white border-opacity-25">
                        <div class="small text-uppercase" style="opacity:0.75;letter-spacing:0.8px;">Net Take-Home Pay (LRD)</div>
                        <div class="fs-2 fw-bold font-monospace">LRD {{ number_format((float)$record->net_pay_lrd, 2) }}</div>
                        <div class="small fst-italic" style="opacity:0.85;">{{ $amountInWordsLrd }}</div>
                    </div>
                    <div class="col-md-6 ps-md-4">
                        <div class="small text-uppercase" style="opacity:0.75;letter-spacing:0.8px;">Net Take-Home Pay (USD)</div>
                        <div class="fs-2 fw-bold font-monospace text-warning">USD {{ number_format((float)$record->net_pay_usd, 2) }}</div>
                        <div class="small fst-italic" style="opacity:0.85;">{{ $amountInWordsUsd }}</div>
                    </div>
                </div>
            </div>

            <!-- Year-to-Date (YTD) Summary Strip -->
            <div class="border rounded-3 p-3 mb-4" style="background:#f8fafc;">
                <div class="small fw-bold text-uppercase text-navy mb-2">
                    <i class="bi bi-calendar-check me-1 text-primary"></i>Year-to-Date (YTD {{ $ytd['year'] }}) Cumulative Summary ({{ $ytd['periods_count'] }} {{ \Illuminate\Support\Str::plural('period', $ytd['periods_count']) }})
                </div>
                <div class="row g-2 text-center">
                    <div class="col-6 col-md-3">
                        <div class="bg-white border rounded-2 p-2">
                            <div class="text-muted" style="font-size:11px;">YTD Gross (LRD)</div>
                            <div class="fw-bold font-monospace text-navy small">LRD {{ number_format((float)$ytd['gross_lrd'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white border rounded-2 p-2">
                            <div class="text-muted" style="font-size:11px;">YTD Deductions (LRD)</div>
                            <div class="fw-bold font-monospace text-danger small">LRD {{ number_format((float)$ytd['deductions_lrd'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white border rounded-2 p-2">
                            <div class="text-muted" style="font-size:11px;">YTD Net Pay (LRD)</div>
                            <div class="fw-bold font-monospace text-success small">LRD {{ number_format((float)$ytd['net_lrd'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white border rounded-2 p-2">
                            <div class="text-muted" style="font-size:11px;">YTD Net Pay (USD)</div>
                            <div class="fw-bold font-monospace text-primary small">USD {{ number_format((float)$ytd['net_usd'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification & Signature Block -->
            <div class="row align-items-end pt-3 border-top g-3" style="font-size:12px; color:#475569;">
                <div class="col-md-5">
                    <div class="fw-bold text-navy">Verification Code: <span class="font-monospace text-primary">{{ $verificationCode }}</span></div>
                    <div>Generated: {{ now()->format('d M Y, h:i A') }}</div>
                    <div class="text-muted" style="font-size:11px;">Computer-generated official document from Ministry of Youth &amp; Sports HRMIS.</div>
                </div>
                <div class="col-md-3 text-md-center">
                    <div style="border-bottom:1px solid #94a3b8; height:28px;"></div>
                    <div class="mt-1 fw-semibold">Payroll / Finance Officer</div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div style="border-bottom:1px solid #94a3b8; width:190px; margin-left:auto; height:28px;"></div>
                    <div class="mt-1 fw-semibold">Director of Human Resources</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
