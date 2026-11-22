<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Readiness &amp; Safety Clearance — {{ $event->event_code }}</title>
    <style>
        @page { margin: 26px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5px; color: #0f172a; }
        .hdr { background: #064e3b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        .kv td { padding: 6px 9px; border: 1px solid #cbd5e1; }
        .kv td.l { background: #f8fafc; font-weight: bold; width: 30%; color: #475569; }
        h3 { font-size: 11.5px; color: #064e3b; margin: 16px 0 6px; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 3px; text-transform: uppercase; }
        .chk th { background: #e2e8f0; text-align: left; padding: 6px; border: 1px solid #cbd5e1; }
        .chk td { padding: 6px; border: 1px solid #e2e8f0; }
        .ok { color: #166534; font-weight: bold; }
        .no { color: #991b1b; font-weight: bold; }
        .sig td { width: 33.3%; text-align: center; padding: 0 8px; font-size: 9.5px; }
        .line { border-bottom: 1px solid #475569; height: 28px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="hdr">
        <div style="font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:#a7f3d0;">Republic of Liberia &bull; Stadium Management Directorate</div>
        <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
        <div style="font-size:11px;">EVENT READINESS &amp; SAFETY CLEARANCE CERTIFICATE &mdash; {{ $event->event_code }}</div>
    </div>
    <div class="strip"></div>

    <table class="kv">
        <tr><td class="l">Event Title</td><td><strong>{{ $event->title }}</strong> ({{ $event->event_type }})</td></tr>
        <tr><td class="l">Venue / Stadium</td><td><strong>{{ $event->facility?->name }}</strong> &mdash; {{ $event->facility?->location }} ({{ $event->facility?->county }})</td></tr>
        <tr><td class="l">Organizer</td><td>{{ $event->organizer }} {{ $event->organizer_contact ? ' — ' . $event->organizer_contact : '' }}</td></tr>
        <tr><td class="l">Event Date &amp; Time</td><td>{{ $event->event_date?->format('d M Y') }} {{ $event->start_time ? '(' . $event->start_time . ($event->end_time ? ' – ' . $event->end_time : '') . ')' : '' }}</td></tr>
        <tr><td class="l">Expected Crowd / Capacity</td><td>{{ number_format($event->expected_attendance) }} / {{ number_format($event->facility?->capacity ?? 0) }} seats</td></tr>
        <tr><td class="l">Clearance Status</td><td><strong>{{ strtoupper($event->status) }}</strong> ({{ $event->checklist_completed_count }}/6 checklist verified)</td></tr>
    </table>

    <h3>Pre-Event Operational &amp; Safety Checklist</h3>
    <table class="chk">
        <thead><tr><th style="width:75%;">Readiness Verification Requirement</th><th style="width:25%; text-align:center;">Status</th></tr></thead>
        <tbody>
            @foreach($checklistFields as $field => $label)
            <tr>
                <td>{{ $label }}</td>
                <td style="text-align:center;" class="{{ $event->{$field} ? 'ok' : 'no' }}">{{ $event->{$field} ? 'VERIFIED' : 'PENDING' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($event->notes)
    <h3>Operational Notes</h3>
    <p>{{ $event->notes }}</p>
    @endif

    <table class="sig" style="margin-top:34px;">
        <tr>
            <td><div class="line"></div><strong>Stadium Operations Director</strong><br>{{ $event->safetyClearer?->name ?? $event->creator?->name ?? 'Pending' }}</td>
            <td><div class="line"></div><strong>Safety &amp; Crowd Control Officer</strong><br>{{ $event->safety_cleared_at?->format('d M Y H:i') ?? 'Pending Clearance' }}</td>
            <td><div class="line"></div><strong>Deputy Minister for Administration</strong><br>Ministry Oversight</td>
        </tr>
    </table>

    <div style="margin-top:20px;font-size:9px;color:#64748b;">Generated {{ now()->format('d M Y, h:i A') }} &bull; Ministry of Youth &amp; Sports Stadium Management.</div>
</body>
</html>
