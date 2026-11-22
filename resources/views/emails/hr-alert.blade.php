<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $alertSubject }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px; color:#1e293b;">
    <div style="max-width:620px; margin:0 auto; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #cbd5e1;">
        <div style="background:#0b1f4b; color:#ffffff; padding:18px 24px;">
            <div style="font-size:10.5px; text-transform:uppercase; letter-spacing:1.2px; opacity:0.8;">Republic of Liberia</div>
            <h2 style="margin:4px 0 0; font-size:17px;">MINISTRY OF YOUTH &amp; SPORTS — HR ALERTS</h2>
        </div>
        <div style="height:4px; background:{{ $badgeColor }};"></div>
        <div style="padding:24px;">
            <span style="display:inline-block; padding:4px 10px; border-radius:99px; font-size:11px; font-weight:bold; color:#ffffff; background:{{ $badgeColor }}; margin-bottom:12px;">
                {{ strtoupper($badgeLabel) }}
            </span>
            <h3 style="margin:0 0 10px; color:#0b1f4b;">{{ $headline }}</h3>
            <p style="font-size:14px; line-height:1.6;">Dear <strong>{{ $recipientName }}</strong>,</p>
            <p style="font-size:14px; line-height:1.6;">{{ $bodyMessage }}</p>

            @if(!empty($details))
                <table style="width:100%; border-collapse:collapse; margin:16px 0; font-size:13px; background:#f8fafc; border:1px solid #e2e8f0;">
                    @foreach($details as $label => $val)
                        <tr>
                            <td style="padding:8px 12px; border-bottom:1px solid #e2e8f0; color:#64748b;">{{ $label }}</td>
                            <td style="padding:8px 12px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:bold; color:#0f172a;">{{ $val }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if($actionUrl)
                <div style="margin:20px 0;">
                    <a href="{{ $actionUrl }}" style="display:inline-block; padding:10px 20px; background:#0b1f4b; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:bold; font-size:13px;">
                        {{ $actionText }}
                    </a>
                </div>
            @endif

            <p style="margin-bottom:0; font-size:12px; color:#64748b;">
                Automated compliance notification from the Ministry of Youth &amp; Sports HRMIS.
            </p>
        </div>
    </div>
</body>
</html>
