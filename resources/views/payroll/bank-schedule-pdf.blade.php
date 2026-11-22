<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Treasury Bank Payment Schedule — {{ $period->name }}</title>
    <style>
        @page { margin: 20px 24px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 9.5px; color: #0f172a; }
        .hdr { background: #0b1f4b; color: #ffffff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .summary-table { margin-bottom: 12px; border: 1px solid #cbd5e1; }
        .summary-table th { background: #e2e8f0; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .summary-table td { padding: 5px 8px; border-top: 1px solid #e2e8f0; }
        .main-table { border: 1px solid #cbd5e1; }
        .main-table th { background: #0b1f4b; color: #ffffff; padding: 6px 6px; font-size: 8.5px; text-transform: uppercase; text-align: left; }
        .main-table td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; }
        .main-table tr:nth-child(even) td { background: #f8fafc; }
        .text-right { text-align: right; }
        .totals-row td { background: #e2e8f0 !important; font-weight: bold; border-top: 1.5px solid #0b1f4b; }
        .sig-table { margin-top: 24px; }
        .sig-table td { width: 33.3%; text-align: center; padding: 0 14px; vertical-align: bottom; }
        .sig-line { border-bottom: 1px solid #475569; height: 28px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="hdr">
        <table>
            <tr>
                <td style="width:70%; vertical-align:middle;">
                    <table>
                        <tr>
                            @if($logoDataUri)
                                <td style="width:48px;"><img src="{{ $logoDataUri }}" style="width:40px;height:40px;background:#fff;padding:3px;border-radius:4px;"></td>
                            @endif
                            <td style="padding-left:8px;">
                                <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia &bull; Treasury &amp; Payroll Disbursement</div>
                                <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS — BANK PAYMENT SCHEDULE</div>
                                <div style="font-size:10px;color:#e2e8f0;">Cycle: {{ $period->name }} ({{ $period->start_date?->format('d M Y') }} – {{ $period->end_date?->format('d M Y') }})</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:30%; text-align:right; vertical-align:middle; font-size:9.5px;">
                    <div><strong>Status:</strong> {{ strtoupper($period->status) }}</div>
                    <div><strong>Ref:</strong> {{ $period->payment_reference ?: ('CYCLE-' . $period->id) }}</div>
                    <div><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="strip"></div>

    <!-- Bank Subtotals Summary -->
    <table class="summary-table">
        <thead>
            <tr>
                <th>Bank / Disbursement Channel</th>
                <th class="text-right">Beneficiaries</th>
                <th class="text-right">Total Net Payable (LRD)</th>
                <th class="text-right">Total Net Payable (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bankGroups as $bankName => $grp)
                <tr>
                    <td><strong>{{ $bankName }}</strong></td>
                    <td class="text-right">{{ $grp['count'] }}</td>
                    <td class="text-right">LRD {{ number_format((float)$grp['net_lrd'], 2) }}</td>
                    <td class="text-right">USD {{ number_format((float)$grp['net_usd'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td>GRAND TOTAL ({{ $records->count() }} Personnel)</td>
                <td class="text-right">{{ $records->count() }}</td>
                <td class="text-right">LRD {{ number_format((float)$totals['net_lrd'], 2) }}</td>
                <td class="text-right">USD {{ number_format((float)$totals['net_usd'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Detailed Beneficiary Schedule -->
    <table class="main-table">
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Beneficiary Name</th>
                <th>Type</th>
                <th>Bank &amp; Account No.</th>
                <th>TIN / NASSCORP</th>
                <th class="text-right">Gross (LRD)</th>
                <th class="text-right">Deductions (LRD)</th>
                <th class="text-right">Net Pay (LRD)</th>
                <th class="text-right">Net Pay (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $rec)
                @php
                    $emp = $rec->employee;
                    $gross = (float)$rec->basic_salary_lrd + (float)$rec->allowances_lrd + (float)$rec->overtime_lrd;
                    $ded   = (float)$rec->tax_lrd + (float)$rec->pension_lrd + (float)$rec->deductions_lrd;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="font-family:monospace;">{{ $emp?->employee_no ?? '—' }}</td>
                    <td><strong>{{ $emp?->full_name ?? 'Deleted' }}</strong><br><span style="color:#64748b;font-size:8px;">{{ $emp?->department?->name ?? '—' }}</span></td>
                    <td>{{ $emp?->employment_type ?? '—' }}</td>
                    <td><strong>{{ $emp?->bank_name ?: 'Direct Payroll' }}</strong><br><span style="font-family:monospace;color:#475569;">{{ $emp?->bank_account ?: '—' }}</span></td>
                    <td style="font-family:monospace;">{{ $emp?->tin_number ?: '—' }}<br>{{ $emp?->social_security_no ?: '—' }}</td>
                    <td class="text-right">{{ number_format($gross, 2) }}</td>
                    <td class="text-right" style="color:#b91c1c;">-{{ number_format($ded, 2) }}</td>
                    <td class="text-right"><strong>{{ number_format((float)$rec->net_pay_lrd, 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format((float)$rec->net_pay_usd, 2) }}</strong></td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="6">TOTAL DISBURSEMENT SCHEDULE</td>
                <td class="text-right">{{ number_format((float)$totals['gross_lrd'], 2) }}</td>
                <td class="text-right">-{{ number_format((float)$totals['ded_lrd'], 2) }}</td>
                <td class="text-right">LRD {{ number_format((float)$totals['net_lrd'], 2) }}</td>
                <td class="text-right">USD {{ number_format((float)$totals['net_usd'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td><div class="sig-line"></div><strong>Prepared By (Payroll / Finance Officer)</strong></td>
            <td><div class="sig-line"></div><strong>Endorsed By (Assistant HR Director)</strong></td>
            <td><div class="sig-line"></div><strong>Authorized By (Director of Human Resources)</strong></td>
        </tr>
    </table>
</body>
</html>
