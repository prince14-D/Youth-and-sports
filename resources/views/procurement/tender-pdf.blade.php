<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bid Evaluation Report — {{ $tender->tender_no }}</title>
    <style>
        @page { margin: 26px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #0f172a; }
        .hdr { background: #064e3b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .kv td { padding: 6px 9px; border: 1px solid #cbd5e1; }
        .kv td.l { background: #f8fafc; font-weight: bold; width: 28%; color: #475569; }
        h3 { font-size: 11.5px; color: #064e3b; margin: 16px 0 6px; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 3px; text-transform: uppercase; }
        .data th { background: #e2e8f0; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; }
        .data td { padding: 5px 6px; border: 1px solid #e2e8f0; font-size: 9.5px; }
        .win { background: #dcfce7; }
        .sig td { width: 33%; text-align: center; padding: 0 8px; font-size: 9.5px; }
        .line { border-bottom: 1px solid #475569; height: 28px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="hdr">
        <div style="font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:#a7f3d0;">Republic of Liberia &bull; Procurement Unit</div>
        <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
        <div style="font-size:11px;">BID EVALUATION &amp; AWARD RECOMMENDATION REPORT &mdash; {{ $tender->tender_no }}</div>
    </div>
    <div class="strip"></div>

    <table class="kv">
        <tr><td class="l">Procurement Title</td><td><strong>{{ $tender->title }}</strong></td></tr>
        <tr><td class="l">Requesting Department</td><td>{{ $tender->requestingDepartment?->name ?? 'Ministry-Wide' }}</td></tr>
        <tr><td class="l">Category / Method</td><td>{{ $tender->category }} &mdash; {{ $tender->method }}</td></tr>
        <tr><td class="l">Estimated Cost</td><td>USD {{ number_format((float)$tender->estimated_cost_usd, 2) }}</td></tr>
        <tr><td class="l">Budget Line</td><td>{{ $tender->budgetAllocation?->budget_code ?? 'Not linked' }}</td></tr>
        <tr><td class="l">Advertised / Deadline</td><td>{{ $tender->advertised_date?->format('d M Y') ?? '—' }} / {{ $tender->bid_deadline?->format('d M Y') ?? '—' }}</td></tr>
        <tr><td class="l">Status</td><td>{{ $tender->status }}</td></tr>
        @if($tender->justification)<tr><td class="l">Justification</td><td>{{ $tender->justification }}</td></tr>@endif
    </table>

    <h3>Bids Received &amp; Evaluation Scores (Technical 70% / Financial 30%)</h3>
    <table class="data">
        <thead><tr><th>Rank</th><th>Supplier</th><th style="text-align:right;">Bid (USD)</th><th>Technical</th><th>Financial</th><th>Total</th><th>Outcome</th></tr></thead>
        <tbody>
        @forelse($bids as $i => $bid)
            <tr class="{{ $bid->status === 'Awarded' ? 'win' : '' }}">
                <td>{{ $bid->total_score !== null ? '#' . ($i + 1) : '—' }}</td>
                <td>{{ $bid->supplier->name }} ({{ $bid->supplier->supplier_no }})</td>
                <td style="text-align:right;">{{ number_format((float)$bid->bid_amount_usd, 2) }}</td>
                <td>{{ $bid->technical_score ?? '—' }}</td>
                <td>{{ $bid->financial_score !== null ? number_format((float)$bid->financial_score, 1) : '—' }}</td>
                <td><strong>{{ $bid->total_score !== null ? number_format((float)$bid->total_score, 1) : '—' }}</strong></td>
                <td>{{ !$bid->is_responsive ? 'Non-responsive' : $bid->status }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;color:#64748b;">No bids recorded.</td></tr>
        @endforelse
        </tbody>
    </table>

    @if($tender->awardedSupplier)
    <h3>Award Recommendation</h3>
    <table class="kv">
        <tr><td class="l">Recommended Supplier</td><td><strong>{{ $tender->awardedSupplier->name }}</strong> ({{ $tender->awardedSupplier->supplier_no }})</td></tr>
        <tr><td class="l">Contract Value</td><td>USD {{ number_format((float)$tender->awarded_amount_usd, 2) }}
            @php $diff = (float)$tender->estimated_cost_usd - (float)$tender->awarded_amount_usd; @endphp
            ({{ $diff >= 0 ? 'saving' : 'over estimate' }}: USD {{ number_format(abs($diff), 2) }})</td></tr>
        <tr><td class="l">Awarded</td><td>{{ $tender->awarded_at?->format('d M Y') }} by {{ $tender->awarder?->name }}</td></tr>
        @if($tender->award_note)<tr><td class="l">Notes</td><td>{!! nl2br(e($tender->award_note)) !!}</td></tr>@endif
    </table>
    @endif

    <table class="sig" style="margin-top:34px;">
        <tr>
            <td><div class="line"></div><strong>Prepared By (Procurement Unit)</strong><br>{{ $tender->creator?->name }}</td>
            <td><div class="line"></div><strong>Evaluation Committee Chair</strong><br>&nbsp;</td>
            <td><div class="line"></div><strong>Approved By (DMA)</strong><br>&nbsp;</td>
        </tr>
    </table>
    <div style="margin-top:18px;font-size:9px;color:#64748b;">Generated {{ now()->format('d M Y, h:i A') }} &bull; Ministry of Youth &amp; Sports Procurement Unit.</div>
</body>
</html>
