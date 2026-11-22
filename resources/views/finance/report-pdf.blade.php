<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ministry Financial Statement — FY {{ $fiscalYear }}</title>
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 9.5px; color: #0f172a; }
        .hdr { background: #0b1f4b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .kpi td { border: 1px solid #cbd5e1; background: #f8fafc; padding: 8px 10px; width: 25%; }
        .tbl { border: 1px solid #cbd5e1; }
        .tbl th { background: #0b1f4b; color: #fff; padding: 6px 8px; text-align: left; font-size: 8.5px; text-transform: uppercase; }
        .tbl td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .sig td { width: 33.3%; text-align: center; padding: 18px 12px 0; }
        .sig-line { border-bottom: 1px solid #475569; height: 26px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="hdr">
        <table>
            <tr>
                <td style="width:70%;">
                    <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia &bull; Ministry of Youth &amp; Sports</div>
                    <div style="font-size:15px;font-weight:bold;">FINANCIAL STATEMENT &amp; BUDGET PERFORMANCE REPORT — FY {{ $fiscalYear }}</div>
                </td>
                <td style="width:30%; text-align:right; font-size:9px;">
                    Generated: {{ now()->format('d M Y, h:i A') }}<br>Finance &amp; Accounting Division
                </td>
            </tr>
        </table>
    </div>
    <div class="strip"></div>

    <table class="kpi">
        <tr>
            <td><div style="color:#64748b;font-size:8.5px;">TOTAL FY BUDGET (USD)</div><strong style="font-size:13px;">USD {{ number_format($summary['budget_usd'], 2) }}</strong></td>
            <td><div style="color:#64748b;font-size:8.5px;">TOTAL EXPENDITURES (USD)</div><strong style="font-size:13px;color:#b91c1c;">USD {{ number_format($summary['spent_usd'], 2) }}</strong></td>
            <td><div style="color:#64748b;font-size:8.5px;">TOTAL EXPENDITURES (LRD)</div><strong style="font-size:13px;">LRD {{ number_format($summary['spent_lrd'], 2) }}</strong></td>
            <td><div style="color:#64748b;font-size:8.5px;">PAYROLL DISBURSED (LRD / USD)</div><strong style="font-size:11px;color:#15803d;">LRD {{ number_format($summary['payroll_lrd'], 0) }} | USD {{ number_format($summary['payroll_usd'], 0) }}</strong></td>
        </tr>
    </table>

    <h4 style="margin:6px 0; color:#0b1f4b;">1. Departmental Budget vs. Actual Utilization</h4>
    <table class="tbl">
        <thead>
            <tr><th>Budget Code</th><th>Department</th><th>Category</th><th>Funding Source</th><th class="text-right">Allocated (USD)</th><th class="text-right">Spent (USD)</th><th class="text-right">Balance (USD)</th></tr>
        </thead>
        <tbody>
            @forelse($budgets as $b)
                <tr>
                    <td style="font-family:monospace;">{{ $b->budget_code }}</td>
                    <td>{{ $b->department?->name }}</td>
                    <td>{{ $b->category }}</td>
                    <td>{{ $b->funding_source }}</td>
                    <td class="text-right">${{ number_format((float)$b->allocated_usd, 2) }}</td>
                    <td class="text-right" style="color:#b91c1c;">${{ number_format($b->spent_usd, 2) }}</td>
                    <td class="text-right" style="color:#15803d;"><strong>${{ number_format($b->remaining_usd, 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#64748b;">No budget lines recorded for FY {{ $fiscalYear }}.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4 style="margin:10px 0 6px; color:#0b1f4b;">2. Recent Ledger Transactions &amp; Payment Vouchers</h4>
    <table class="tbl">
        <thead>
            <tr><th>Voucher #</th><th>Date</th><th>Payee</th><th>Department</th><th>Category</th><th>Status</th><th class="text-right">USD</th><th class="text-right">LRD</th></tr>
        </thead>
        <tbody>
            @forelse($transactions->take(20) as $t)
                <tr>
                    <td style="font-family:monospace;">{{ $t->voucher_no }}</td>
                    <td>{{ $t->transaction_date?->format('d M Y') }}</td>
                    <td>{{ $t->payee_name }}</td>
                    <td>{{ $t->department?->code ?? 'HQ' }}</td>
                    <td>{{ $t->category }}</td>
                    <td>{{ $t->status }}</td>
                    <td class="text-right">${{ number_format((float)$t->amount_usd, 2) }}</td>
                    <td class="text-right">{{ number_format((float)$t->amount_lrd, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#64748b;">No transactions recorded for FY {{ $fiscalYear }}.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sig">
        <tr>
            <td><div class="sig-line"></div><strong>Director of Finance</strong></td>
            <td><div class="sig-line"></div><strong>Deputy Minister for Administration (DMA)</strong></td>
            <td><div class="sig-line"></div><strong>Office of the Minister / Internal Audit</strong></td>
        </tr>
    </table>
</body>
</html>
