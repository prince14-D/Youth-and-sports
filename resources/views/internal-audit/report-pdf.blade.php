<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Internal Audit &amp; Compliance Report — Ministry of Youth &amp; Sports</title>
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10px; color: #0f172a; }
        .hdr { background: #1e1b4b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .kpi td { width: 25%; border: 1px solid #cbd5e1; padding: 8px 10px; background: #f8fafc; }
        .kpi .lbl { font-size: 8.5px; text-transform: uppercase; color: #475569; font-weight: bold; }
        .kpi .val { font-size: 15px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        h3 { font-size: 11.5px; color: #1e1b4b; margin: 14px 0 6px; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 3px; text-transform: uppercase; }
        .data th { background: #e2e8f0; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9px; }
        .data td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; font-size: 9px; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-weight: bold; font-size: 8px; }
        .b-red { background: #fee2e2; color: #991b1b; }
        .b-amber { background: #fef3c7; color: #92400e; }
        .b-green { background: #dcfce7; color: #166534; }
        .b-gray { background: #f1f5f9; color: #334155; }
    </style>
</head>
<body>
    <div class="hdr">
        <table>
            <tr>
                <td style="width:72%;">
                    <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia &bull; Internal Audit Division</div>
                    <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
                    <div style="font-size:10.5px;color:#e2e8f0;">INTERNAL AUDIT, FINANCIAL VERIFICATION &amp; CONTROL COMPLIANCE REPORT</div>
                </td>
                <td style="width:28%; text-align:right; font-family:monospace; font-size:9.5px;">
                    <div>REPORT DATE: {{ now()->format('d M Y') }}</div>
                    <div>GENERATED: {{ now()->format('h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="strip"></div>

    <table class="kpi">
        <tr>
            <td>
                <div class="lbl">Voucher Verification</div>
                <div class="val">{{ $summary['verified_vouchers'] }} Verified / {{ $summary['pending_vouchers'] }} Pending</div>
                <div style="font-size:8.5px;color:#b91c1c;">{{ $summary['flagged_vouchers'] }} Queried or Flagged</div>
            </td>
            <td>
                <div class="lbl">Open Irregularities</div>
                <div class="val">{{ $summary['open_findings'] }} Findings</div>
                <div style="font-size:8.5px;color:#b91c1c;">{{ $summary['critical_high_findings'] }} Critical / High Severity</div>
            </td>
            <td>
                <div class="lbl">Identified Financial Exposure</div>
                <div class="val">USD {{ number_format($summary['exposure_usd'], 2) }}</div>
                <div style="font-size:8.5px;color:#475569;">Across open audit findings</div>
            </td>
            <td>
                <div class="lbl">Internal Control Score</div>
                <div class="val">{{ $summary['avg_control_score'] !== null ? $summary['avg_control_score'] . '%' : 'N/A' }}</div>
                <div style="font-size:8.5px;color:#475569;">{{ $summary['active_anomalies'] }} automated risk flag(s)</div>
            </td>
        </tr>
    </table>

    <h3>1. Automated Risk &amp; Irregularity Scan Summary</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:18%;">Control Category</th>
                <th style="width:34%;">Automated Check</th>
                <th style="width:32%;">Risk Description</th>
                <th style="width:8%; text-align:center;">Count</th>
                <th style="width:8%; text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anomalies as $a)
            <tr>
                <td><strong>{{ $a['category'] }}</strong></td>
                <td>{{ $a['title'] }}</td>
                <td>{{ $a['detail'] }}</td>
                <td style="text-align:center;font-weight:bold;">{{ $a['count'] }}</td>
                <td style="text-align:center;">
                    <span class="badge {{ $a['severity'] === 'Clear' ? 'b-green' : ($a['severity'] === 'Critical' ? 'b-red' : 'b-amber') }}">{{ $a['severity'] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>2. Audit Findings &amp; Irregularities Registry</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:13%;">Finding #</th>
                <th style="width:24%;">Title &amp; Area</th>
                <th style="width:14%;">Department</th>
                <th style="width:9%;">Severity</th>
                <th style="width:11%; text-align:right;">Exposure (USD)</th>
                <th style="width:11%;">Status</th>
                <th style="width:18%;">Recommendation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($findings as $f)
            <tr>
                <td style="font-family:monospace;"><strong>{{ $f->finding_no }}</strong></td>
                <td><strong>{{ $f->title }}</strong><br><span style="color:#475569;">{{ $f->audit_area }}</span></td>
                <td>{{ $f->department?->name ?? 'Ministry-Wide' }}</td>
                <td><span class="badge {{ in_array($f->severity, ['Critical','High']) ? 'b-red' : 'b-amber' }}">{{ $f->severity }}</span></td>
                <td style="text-align:right;">{{ $f->financial_impact_usd !== null ? number_format((float)$f->financial_impact_usd, 2) : '0.00' }}</td>
                <td><span class="badge {{ in_array($f->status, ['Closed','Remediated']) ? 'b-green' : 'b-gray' }}">{{ $f->status }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($f->recommendation, 110) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#64748b;">No audit findings recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>3. Departmental Internal Control Assessments</h3>
    <table class="data">
        <thead>
            <tr>
                <th style="width:13%;">Ref #</th>
                <th style="width:20%;">Department</th>
                <th style="width:22%;">Control Area</th>
                <th style="width:10%;">Date</th>
                <th style="width:7%; text-align:center;">Score</th>
                <th style="width:12%;">Rating</th>
                <th style="width:16%;">Assessed By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $a)
            <tr>
                <td style="font-family:monospace;">{{ $a->assessment_no }}</td>
                <td>{{ $a->department?->name }}</td>
                <td>{{ $a->control_area }}</td>
                <td>{{ $a->assessment_date?->format('d M Y') }}</td>
                <td style="text-align:center;font-weight:bold;">{{ $a->score }}%</td>
                <td><span class="badge {{ $a->rating === 'Effective' ? 'b-green' : ($a->rating === 'Needs Improvement' ? 'b-amber' : 'b-red') }}">{{ $a->rating }}</span></td>
                <td>{{ $a->assessor?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#64748b;">No internal control assessments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:18px; font-size:8.5px; color:#64748b;">
        Confidential &bull; Prepared by the Internal Audit Division, Ministry of Youth &amp; Sports, Republic of Liberia.
    </div>
</body>
</html>
