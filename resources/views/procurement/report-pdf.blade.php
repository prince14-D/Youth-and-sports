<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Procurement Unit Report</title>
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #0f172a; }
        .hdr { background: #064e3b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .kpi td { width: 25%; border: 1px solid #cbd5e1; padding: 8px 10px; background: #f8fafc; }
        .lbl { font-size: 8.5px; text-transform: uppercase; color: #475569; font-weight: bold; }
        .val { font-size: 14px; font-weight: bold; margin-top: 2px; }
        h3 { font-size: 11.5px; color: #064e3b; margin: 14px 0 6px; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 3px; text-transform: uppercase; }
        .data th { background: #e2e8f0; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9px; }
        .data td { padding: 5px 6px; border: 1px solid #e2e8f0; font-size: 9px; vertical-align: top; }
    </style>
</head>
<body>
    <div class="hdr">
        <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#a7f3d0;">Republic of Liberia &bull; Procurement Unit</div>
        <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
        <div style="font-size:10.5px;">PROCUREMENT ACTIVITY, TENDER REGISTER &amp; COMPLIANCE REPORT &mdash; {{ now()->format('d M Y') }}</div>
    </div>
    <div class="strip"></div>

    <table class="kpi"><tr>
        <td><div class="lbl">Tenders (Active / Total)</div><div class="val">{{ $summary['active'] }} / {{ $summary['total_tenders'] }}</div></td>
        <td><div class="lbl">Contracts Awarded</div><div class="val">{{ $summary['awarded_count'] }} &mdash; USD {{ number_format($summary['awarded_usd'], 2) }}</div></td>
        <td><div class="lbl">Savings vs Estimate</div><div class="val">USD {{ number_format($summary['estimated_usd'] - $summary['awarded_usd'], 2) }}</div></td>
        <td><div class="lbl">Suppliers / Compliance Flags</div><div class="val">{{ $summary['suppliers'] }} / {{ $summary['flagged'] }}</div></td>
    </tr></table>

    <h3>1. Procurement Compliance Checks</h3>
    <table class="data">
        <thead><tr><th>Category</th><th>Check</th><th>Description</th><th style="text-align:center;">Count</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($anomalies as $a)
            <tr><td><strong>{{ $a['category'] }}</strong></td><td>{{ $a['title'] }}</td><td>{{ $a['detail'] }}</td><td style="text-align:center;font-weight:bold;">{{ $a['count'] }}</td><td>{{ $a['severity'] }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <h3>2. Tender Register</h3>
    <table class="data">
        <thead><tr><th>Tender #</th><th>Title</th><th>Department</th><th>Method</th><th style="text-align:right;">Estimate</th><th>Status</th><th>Bids</th><th>Awarded To</th><th style="text-align:right;">Awarded</th></tr></thead>
        <tbody>
        @forelse($tenders as $t)
            <tr>
                <td style="font-family:monospace;">{{ $t->tender_no }}</td><td>{{ $t->title }}</td><td>{{ $t->requestingDepartment?->name ?? 'Ministry-Wide' }}</td>
                <td>{{ $t->method }}</td><td style="text-align:right;">{{ number_format((float)$t->estimated_cost_usd, 2) }}</td><td>{{ $t->status }}</td>
                <td>{{ $t->bids_count }}</td><td>{{ $t->awardedSupplier?->name ?? '—' }}</td>
                <td style="text-align:right;">{{ $t->awarded_amount_usd !== null ? number_format((float)$t->awarded_amount_usd, 2) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" style="text-align:center;color:#64748b;">No tenders recorded.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h3>3. Supplier Registry</h3>
    <table class="data">
        <thead><tr><th>Supplier #</th><th>Name</th><th>Category</th><th>TIN</th><th>Tax Clearance</th><th>License</th><th>Rating</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($suppliers as $s)
            <tr>
                <td style="font-family:monospace;">{{ $s->supplier_no }}</td><td>{{ $s->name }}</td><td>{{ $s->category }}</td><td>{{ $s->tin_number ?: '—' }}</td>
                <td>{{ $s->tax_clearance_expiry?->format('d M Y') ?? '—' }}{{ $s->tax_clearance_expired ? ' (EXPIRED)' : '' }}</td>
                <td>{{ $s->business_license_expiry?->format('d M Y') ?? '—' }}{{ $s->license_expired ? ' (EXPIRED)' : '' }}</td>
                <td>{{ $s->performance_rating ? number_format((float)$s->performance_rating, 1) . '/5' : '—' }}</td><td>{{ $s->status }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#64748b;">No suppliers registered.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px;font-size:8.5px;color:#64748b;">Prepared by the Procurement Unit, Ministry of Youth &amp; Sports, Republic of Liberia.</div>
</body>
</html>
