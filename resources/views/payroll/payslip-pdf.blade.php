<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $payslipRef }} — Official Payslip</title>
    <style>
        @page { margin: 22px 26px; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.45;
        }
        .header-box {
            background: #0b1f4b;
            color: #ffffff;
            padding: 16px 20px;
            border-radius: 6px 6px 0 0;
        }
        .flag-strip {
            height: 4px;
            background: #bf0a30;
            margin-bottom: 14px;
        }
        table { width: 100%; border-collapse: collapse; }
        .meta-table td { vertical-align: top; width: 50%; }
        .info-card {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #123f7a;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .kv-row td { padding: 2.5px 0; border-bottom: 1px dotted #e2e8f0; }
        .kv-label { color: #64748b; width: 45%; }
        .kv-val { text-align: right; font-weight: bold; color: #0f172a; }
        .ledger-table { border: 1px solid #cbd5e1; margin-top: 12px; }
        .ledger-table th {
            background: #e2e8f0;
            color: #0f172a;
            font-size: 10px;
            text-transform: uppercase;
            padding: 7px 10px;
            text-align: left;
            border-bottom: 1px solid #94a3b8;
        }
        .ledger-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right { text-align: right; }
        .text-danger { color: #b91c1c; }
        .text-success { color: #15803d; }
        .total-row td {
            background: #f1f5f9;
            font-weight: bold;
            border-top: 1px solid #94a3b8;
        }
        .net-box {
            margin-top: 14px;
            background: #0f172a;
            color: #ffffff;
            padding: 14px 18px;
            border-radius: 6px;
        }
        .net-amount { font-size: 18px; font-weight: bold; margin-top: 2px; }
        .net-words { font-size: 9.5px; font-style: italic; color: #cbd5e1; margin-top: 2px; }
        .ytd-box {
            margin-top: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .ytd-table td {
            width: 25%;
            text-align: center;
            border-right: 1px solid #e2e8f0;
            padding: 4px;
        }
        .ytd-table td:last-child { border-right: none; }
        .footer-box {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #cbd5e1;
            font-size: 10px;
            color: #475569;
        }
        .sig-line {
            border-bottom: 1px solid #64748b;
            height: 28px;
            margin-bottom: 4px;
        }
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: bold;
            background: #16a34a;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="header-box">
        <table>
            <tr>
                <td style="width: 68%; vertical-align: middle;">
                    <table>
                        <tr>
                            @if($logoDataUri)
                            <td style="width: 56px; vertical-align: middle;">
                                <div style="background:#ffffff; padding:4px; border-radius:6px; width:46px; text-align:center;">
                                    <img src="{{ $logoDataUri }}" style="width:42px; height:42px;">
                                </div>
                            </td>
                            @endif
                            <td style="vertical-align: middle; padding-left: 8px;">
                                <div style="font-size: 9px; letter-spacing: 1.2px; text-transform: uppercase; color: #cbd5e1;">Republic of Liberia</div>
                                <div style="font-size: 16px; font-weight: bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
                                <div style="font-size: 10.5px; color: #e2e8f0;">
                                    {{ $isContractor ? 'Official Contractor Payment Advice & Stipend Statement' : 'Official Employee Earnings & Deductions Statement' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 32%; text-align: right; vertical-align: middle;">
                    <span class="badge">{{ strtoupper($record->status) }}</span>
                    <div style="margin-top: 6px; font-size: 10px;">
                        <strong>Ref:</strong> {{ $payslipRef }}<br>
                        <strong>Period:</strong> {{ $record->period?->name ?? '—' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="flag-strip"></div>

    <!-- Staff & Payment Metadata -->
    <table class="meta-table">
        <tr>
            <td style="padding-right: 7px;">
                <div class="info-card">
                    <div class="section-title">{{ $isContractor ? 'Contractor Details' : 'Employee Details' }}</div>
                    <table class="kv-row">
                        <tr><td class="kv-label">Full Name</td><td class="kv-val">{{ $record->employee?->full_name ?? '—' }}</td></tr>
                        <tr><td class="kv-label">{{ $isContractor ? 'Contractor ID' : 'Employee ID' }}</td><td class="kv-val text-danger">{{ $record->employee?->employee_no ?? '—' }}</td></tr>
                        <tr><td class="kv-label">Department</td><td class="kv-val">{{ $record->employee?->department?->name ?? '—' }}</td></tr>
                        <tr><td class="kv-label">Designation</td><td class="kv-val">{{ $record->employee?->designation ?? '—' }}</td></tr>
                        <tr><td class="kv-label">Category</td><td class="kv-val">{{ $record->employee?->employment_type ?? '—' }}</td></tr>
                    </table>
                </div>
            </td>
            <td style="padding-left: 7px;">
                <div class="info-card">
                    <div class="section-title">Payment &amp; Statutory Info</div>
                    <table class="kv-row">
                        <tr><td class="kv-label">Pay Period</td><td class="kv-val">{{ $record->period?->start_date?->format('d M Y') ?? '—' }} - {{ $record->period?->end_date?->format('d M Y') ?? '—' }}</td></tr>
                        <tr><td class="kv-label">Payment Date</td><td class="kv-val">{{ $record->period?->payment_date?->format('d M Y') ?? 'Pending' }}</td></tr>
                        <tr><td class="kv-label">Salary Grade</td><td class="kv-val">{{ $record->employee?->salary_grade ?? 'Standard' }}</td></tr>
                        <tr><td class="kv-label">Bank / Account</td><td class="kv-val">{{ $record->employee?->bank_name ? $record->employee->bank_name . ($maskedAccount ? ' (' . $maskedAccount . ')' : '') : 'Ministry Payroll' }}</td></tr>
                        <tr><td class="kv-label">TIN / NASSCORP</td><td class="kv-val">{{ $record->employee?->tin_number ?: '—' }} / {{ $record->employee?->social_security_no ?: '—' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Earnings & Deductions Ledger -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 34%;">Earnings &amp; Allowances</th>
                <th class="text-right" style="width: 16%;">Amount</th>
                <th style="width: 34%; border-left: 1px solid #94a3b8;">Deductions</th>
                <th class="text-right" style="width: 16%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $isContractor ? 'Base Contract Fee (LRD)' : 'Basic Salary (LRD)' }}</td>
                <td class="text-right">LRD {{ number_format((float)$record->basic_salary_lrd, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">LRA Income Tax (10%)</td>
                <td class="text-right text-danger">-LRD {{ number_format((float)$record->tax_lrd, 2) }}</td>
            </tr>
            <tr>
                <td>Allowances (LRD)</td>
                <td class="text-right">LRD {{ number_format((float)$record->allowances_lrd, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Pension / NASSCORP (4.5%)</td>
                <td class="text-right text-danger">-LRD {{ number_format((float)$record->pension_lrd, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime / Bonus (LRD)</td>
                <td class="text-right">LRD {{ number_format((float)$record->overtime_lrd, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Other Deductions</td>
                <td class="text-right text-danger">-LRD {{ number_format((float)$record->deductions_lrd, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Gross Earnings (LRD)</td>
                <td class="text-right text-success">LRD {{ number_format($grossLrd, 2) }}</td>
                <td style="border-left: 1px solid #94a3b8;">Total Deductions (LRD)</td>
                <td class="text-right text-danger">-LRD {{ number_format($totalDeductionsLrd, 2) }}</td>
            </tr>
            <tr>
                <td>{{ $isContractor ? 'Base Contract Fee (USD)' : 'Basic Salary (USD)' }}</td>
                <td class="text-right">USD {{ number_format((float)$record->basic_salary_usd, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Allowances (USD)</td>
                <td class="text-right">USD {{ number_format((float)$record->allowances_usd, 2) }}</td>
            </tr>
            @if($record->lineItems->isNotEmpty())
            <tr>
                <td colspan="4" style="background:#f8fafc; font-size:9.5px; color:#334155;">
                    <strong>Itemized Adjustments:</strong>
                    @foreach($record->lineItems as $li)
                        <span style="display:inline-block; margin-right:10px;">
                            {{ $li->category }}: {{ $li->type === 'allowance' ? '+' : '-' }}{{ $li->currency }} {{ number_format((float)$li->amount, 2) }}
                        </span>
                    @endforeach
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Net Pay Box -->
    <div class="net-box">
        <table>
            <tr>
                <td style="width: 52%; border-right: 1px solid #334155; padding-right: 14px;">
                    <div style="font-size: 9px; text-transform: uppercase; color: #94a3b8;">Net Take-Home Pay (LRD)</div>
                    <div class="net-amount">LRD {{ number_format((float)$record->net_pay_lrd, 2) }}</div>
                    <div class="net-words">{{ $amountInWordsLrd }}</div>
                </td>
                <td style="width: 48%; padding-left: 14px;">
                    <div style="font-size: 9px; text-transform: uppercase; color: #94a3b8;">Net Take-Home Pay (USD)</div>
                    <div class="net-amount" style="color: #fbbf24;">USD {{ number_format((float)$record->net_pay_usd, 2) }}</div>
                    <div class="net-words">{{ $amountInWordsUsd }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- YTD Summary -->
    <div class="ytd-box">
        <div class="section-title">Year-to-Date ({{ $ytd['year'] }}) Summary — {{ $ytd['periods_count'] }} Pay Period(s)</div>
        <table class="ytd-table">
            <tr>
                <td><div style="font-size:9px; color:#64748b;">YTD Gross (LRD)</div><strong>LRD {{ number_format((float)$ytd['gross_lrd'], 2) }}</strong></td>
                <td><div style="font-size:9px; color:#64748b;">YTD Deductions (LRD)</div><strong class="text-danger">LRD {{ number_format((float)$ytd['deductions_lrd'], 2) }}</strong></td>
                <td><div style="font-size:9px; color:#64748b;">YTD Net (LRD)</div><strong class="text-success">LRD {{ number_format((float)$ytd['net_lrd'], 2) }}</strong></td>
                <td><div style="font-size:9px; color:#64748b;">YTD Net (USD)</div><strong>USD {{ number_format((float)$ytd['net_usd'], 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Signatures & Verification -->
    <div class="footer-box">
        <table>
            <tr>
                <td style="width: 42%; vertical-align: bottom;">
                    <div><strong>Verification Code:</strong> {{ $verificationCode }}</div>
                    <div><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                    <div style="font-size: 9px; color: #64748b;">Official Ministry of Youth &amp; Sports HRMIS document.</div>
                </td>
                <td style="width: 28%; padding: 0 12px; text-align: center; vertical-align: bottom;">
                    <div class="sig-line"></div>
                    <strong>Payroll / Finance Officer</strong>
                </td>
                <td style="width: 30%; padding-left: 12px; text-align: center; vertical-align: bottom;">
                    <div class="sig-line"></div>
                    <strong>Director of Human Resources</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
