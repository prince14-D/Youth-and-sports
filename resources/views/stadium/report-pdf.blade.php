<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stadium Operations, Maintenance &amp; Safety Report</title>
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; color: #0f172a; }
        .hdr { background: #064e3b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        .kpi td { width: 25%; border: 1px solid #cbd5e1; padding: 8px 10px; background: #f8fafc; }
        .lbl { font-size: 8.5px; text-transform: uppercase; color: #475569; font-weight: bold; }
        .val { font-size: 14px; font-weight: bold; margin-top: 2px; }
        h3 { font-size: 11px; color: #064e3b; margin: 14px 0 6px; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 3px; text-transform: uppercase; }
        .data th { background: #e2e8f0; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.8px; }
        .data td { padding: 5px 6px; border: 1px solid #e2e8f0; font-size: 8.8px; vertical-align: top; }
    </style>
</head>
<body>
    <div class="hdr">
        <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#a7f3d0;">Republic of Liberia &bull; Stadium Management Directorate</div>
        <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
        <div style="font-size:10.5px;">STADIUM OPERATIONS, MAINTENANCE, EVENT READINESS &amp; SAFETY REPORT &mdash; {{ now()->format('d M Y') }}</div>
    </div>
    <div class="strip"></div>

    <table class="kpi"><tr>
        <td><div class="lbl">Operational Facilities</div><div class="val">{{ $summary['operational'] }} / {{ $summary['facilities'] }}</div></td>
        <td><div class="lbl">Upcoming Events</div><div class="val">{{ $summary['upcoming_events'] }}</div></td>
        <td><div class="lbl">Open Work Orders</div><div class="val">{{ $summary['open_maintenance'] }}</div></td>
        <td><div class="lbl">Safety Score / Alerts</div><div class="val">{{ $summary['avg_safety_score'] !== null ? $summary['avg_safety_score'] . '%' : 'N/A' }} &bull; {{ $summary['active_alerts'] }} flag(s)</div></td>
    </tr></table>

    <h3>1. Managed Stadiums &amp; Facilities</h3>
    <table class="data">
        <thead><tr><th>Code</th><th>Facility</th><th>County / Location</th><th>Capacity</th><th>Surface</th><th>Floodlights</th><th>Safety Cert</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($facilities as $f)
            <tr>
                <td style="font-family:monospace;">{{ $f->facility_code }}</td>
                <td><strong>{{ $f->name }}</strong></td>
                <td>{{ $f->location }} ({{ $f->county }})</td>
                <td>{{ number_format($f->capacity) }}</td>
                <td>{{ $f->surface_type }}</td>
                <td>{{ $f->floodlights_status }}</td>
                <td>{{ $f->safety_certificate_expiry?->format('d M Y') ?? 'Missing' }}</td>
                <td>{{ $f->operational_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>2. Scheduled Events &amp; Readiness</h3>
    <table class="data">
        <thead><tr><th>Event #</th><th>Title</th><th>Venue</th><th>Date</th><th>Crowd</th><th>Checklist</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($events as $ev)
            <tr>
                <td style="font-family:monospace;">{{ $ev->event_code }}</td>
                <td>{{ $ev->title }}</td>
                <td>{{ $ev->facility?->name }}</td>
                <td>{{ $ev->event_date?->format('d M Y') }}</td>
                <td>{{ number_format($ev->expected_attendance) }}</td>
                <td>{{ $ev->checklist_completed_count }}/6 ({{ $ev->checklist_percent }}%)</td>
                <td>{{ $ev->status }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#64748b;">No events scheduled.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>3. Maintenance Work Orders &amp; Safety Inspections</h3>
    <table class="data">
        <thead><tr><th>Work Order #</th><th>Facility &amp; Zone</th><th>Title</th><th>Priority</th><th>Est. (USD)</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($maintenanceLogs as $m)
            <tr>
                <td style="font-family:monospace;">{{ $m->work_order_no }}</td>
                <td>{{ $m->facility?->name }} — {{ $m->zone_area }}</td>
                <td>{{ $m->title }}</td>
                <td>{{ $m->priority }}</td>
                <td>{{ number_format((float)$m->estimated_cost_usd, 2) }}</td>
                <td>{{ $m->status }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#64748b;">No work orders logged.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;font-size:8.5px;color:#64748b;">Prepared by Stadium Management, Ministry of Youth &amp; Sports, Republic of Liberia.</div>
</body>
</html>
