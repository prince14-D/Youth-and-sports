<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movement Docket — {{ $document->tracking_number }}</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10.5px; color: #0f172a; }
        .hdr { background: #0b1f4b; color: #fff; padding: 14px 18px; border-radius: 6px 6px 0 0; }
        .strip { height: 4px; background: #bf0a30; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        .kv td { padding: 6px 8px; border: 1px solid #cbd5e1; }
        .kv td.l { background: #f8fafc; font-weight: bold; width: 28%; color: #475569; }
        .trail { margin-top: 16px; border: 1px solid #cbd5e1; }
        .trail th { background: #e2e8f0; padding: 7px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; }
        .trail td { padding: 6px 8px; border-top: 1px solid #e2e8f0; vertical-align: top; }
    </style>
</head>
<body>
    <div class="hdr">
        <table>
            <tr>
                <td style="width:72%;">
                    <div style="font-size:9px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia &bull; Paperless Registry</div>
                    <div style="font-size:15px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS — FILE MOVEMENT DOCKET</div>
                </td>
                <td style="width:28%; text-align:right; font-family:monospace; font-size:12px; font-weight:bold;">
                    {{ $document->tracking_number }}
                </td>
            </tr>
        </table>
    </div>
    <div class="strip"></div>

    <table class="kv">
        <tr><td class="l">Subject</td><td><strong>{{ $document->subject }}</strong></td></tr>
        <tr><td class="l">Document Type / Priority</td><td>{{ $document->document_type }} &bull; <strong>{{ $document->priority }}</strong> ({{ $document->confidentiality }})</td></tr>
        <tr><td class="l">Originating Office</td><td>{{ $document->originDepartment?->name }} (Dispatched by {{ $document->sender?->name }})</td></tr>
        <tr><td class="l">Destination Office / Person</td><td>{{ $document->destinationDepartment?->name }}{{ $document->destinationUser ? ' — Attn: ' . $document->destinationUser->name : '' }}</td></tr>
        <tr><td class="l">Current Office / Status</td><td><strong>{{ $document->currentDepartment?->name }}</strong> — {{ strtoupper($document->display_status) }}{{ $document->revision_count > 0 ? ' (Rev #' . $document->revision_count . ')' : '' }}</td></tr>
        @if($document->attachment_filename)
        <tr><td class="l">Uploaded Document</td><td>{{ $document->attachment_filename }}</td></tr>
        @endif
        @if($document->amount_lrd || $document->amount_usd)
        <tr><td class="l">Financial Value</td><td>{{ $document->amount_lrd ? 'LRD ' . number_format((float)$document->amount_lrd, 2) : '' }} {{ ($document->amount_lrd && $document->amount_usd) ? '|' : '' }} {{ $document->amount_usd ? 'USD ' . number_format((float)$document->amount_usd, 2) : '' }}</td></tr>
        @endif
        <tr><td class="l">Summary</td><td>{{ $document->summary }}</td></tr>
    </table>

    <h4 style="margin:16px 0 6px; color:#0b1f4b;">Chain of Custody, Approvals, Reversions &amp; Recorded Feedback</h4>
    <table class="trail">
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>Action</th>
                <th>Route</th>
                <th>Officer &amp; Role</th>
                <th>Feedback / Minute Remarks</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->routingLogs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->acted_at?->format('d M Y H:i') }}</td>
                    <td style="text-transform:uppercase; font-weight:bold;">{{ $log->action_label }}</td>
                    <td>{{ $log->fromDepartment?->code ?? '—' }} &rarr; {{ $log->toDepartment?->code ?? '—' }}{{ $log->targetUser ? ' (' . $log->targetUser->name . ')' : '' }}</td>
                    <td><strong>{{ $log->actor_name }}</strong><br><span style="color:#64748b;font-size:9px;">{{ $log->actor_role }}</span></td>
                    <td>{{ $log->minutes_note }}{{ $log->attachment_original_name ? ' [File: ' . $log->attachment_original_name . ']' : '' }}</td>
                    <td style="font-family:monospace; font-size:9px;">{{ $log->digital_signature }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:18px; font-size:9px; color:#64748b;">
        Generated {{ now()->format('d M Y, h:i A') }} from the Ministry of Youth &amp; Sports Paperless HRMIS &amp; Document Tracking System.
    </div>
</body>
</html>
