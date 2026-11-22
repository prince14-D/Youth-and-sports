<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Voucher — {{ $transaction->voucher_no }}</title>
    <style>
        @page { margin: 26px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #0f172a; }
        .hdr { background: #0b1f4b; color: #fff; padding: 16px 20px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        .kv td { padding: 7px 10px; border: 1px solid #cbd5e1; }
        .kv td.l { background: #f8fafc; font-weight: bold; width: 30%; color: #475569; }
        .amt-box { margin-top: 16px; background: #0f172a; color: #fff; padding: 14px 18px; border-radius: 6px; }
        .sig-table { margin-top: 36px; }
        .sig-table td { width: 33.3%; text-align: center; padding: 0 10px; vertical-align: bottom; font-size: 10px; }
        .sig-line { border-bottom: 1px solid #475569; height: 30px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="hdr">
        <table>
            <tr>
                <td style="width:68%;">
                    <div style="font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia &bull; Finance Division</div>
                    <div style="font-size:16px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
                    <div style="font-size:11px;color:#e2e8f0;">OFFICIAL PAYMENT / ACCOUNTING VOUCHER</div>
                </td>
                <td style="width:32%; text-align:right; font-family:monospace;">
                    <div style="font-size:13px;font-weight:bold;">{{ $transaction->voucher_no }}</div>
                    <div style="font-size:10px;margin-top:4px;">STATUS: {{ strtoupper($transaction->status) }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="strip"></div>

    <table class="kv">
        <tr><td class="l">Payee / Beneficiary</td><td><strong>{{ $transaction->payee_name }}</strong></td></tr>
        <tr><td class="l">Transaction Type</td><td>{{ $transaction->transaction_type }}</td></tr>
        <tr><td class="l">Department / Office</td><td>{{ $transaction->department?->name ?? 'Ministry Headquarters' }}</td></tr>
        <tr><td class="l">Account Category</td><td>{{ $transaction->category }}</td></tr>
        <tr><td class="l">Budget Line Code</td><td style="font-family:monospace;">{{ $transaction->budgetAllocation?->budget_code ?? 'Unlinked / General' }}</td></tr>
        <tr><td class="l">Transaction Date</td><td>{{ $transaction->transaction_date?->format('d M Y') }}</td></tr>
        <tr><td class="l">Payment Method &amp; Ref</td><td>{{ $transaction->payment_method }} {{ $transaction->bank_reference ? ' — Ref: ' . $transaction->bank_reference : '' }}</td></tr>
        @if($transaction->document)
        <tr><td class="l">Linked File Tracking #</td><td style="font-family:monospace;">{{ $transaction->document->tracking_number }}</td></tr>
        @endif
        <tr><td class="l">Description / Narration</td><td>{{ $transaction->description }}</td></tr>
    </table>

    <div class="amt-box">
        <table>
            <tr>
                <td style="width:50%; border-right:1px solid #334155;">
                    <div style="font-size:9px;text-transform:uppercase;color:#94a3b8;">Voucher Amount (USD)</div>
                    <div style="font-size:20px;font-weight:bold;color:#fbbf24;">USD {{ number_format((float)$transaction->amount_usd, 2) }}</div>
                </td>
                <td style="width:50%; padding-left:16px;">
                    <div style="font-size:9px;text-transform:uppercase;color:#94a3b8;">Voucher Amount (LRD)</div>
                    <div style="font-size:20px;font-weight:bold;">LRD {{ number_format((float)$transaction->amount_lrd, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="sig-table">
        <tr>
            <td><div class="sig-line"></div><strong>Prepared By (Finance Division)</strong><br>{{ $transaction->recorder?->name ?? '—' }}</td>
            <td><div class="sig-line"></div><strong>Verified By (Internal Audit)</strong><br>{{ $transaction->auditor?->name ? $transaction->auditor->name . ' (' . ($transaction->audit_status ?? 'Verified') . ')' : ($transaction->audit_status ?? 'Pending Review') }}</td>
            <td><div class="sig-line"></div><strong>Approved By (DMA / Finance Director)</strong><br>{{ $transaction->approver?->name ?? 'Pending Signature' }}</td>
        </tr>
    </table>

    <div style="margin-top:24px; font-size:9px; color:#64748b;">
        Generated {{ now()->format('d M Y, h:i A') }} &bull; Ministry of Youth &amp; Sports Finance &amp; Accounting Division.
    </div>
</body>
</html>
