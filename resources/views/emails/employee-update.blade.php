<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>{{ $title }}</title></head>
<body style="margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif;">
    <div style="max-width:620px;margin:32px auto;background:#ffffff;border:1px solid #d9e1ec;border-radius:10px;overflow:hidden;">
        <div style="background:#12355b;color:#ffffff;padding:22px 28px;">
            <div style="font-size:12px;opacity:.8;">Ministry of Youth and Sports HRMS</div>
            <h1 style="font-size:21px;margin:8px 0 0;">{{ $title }}</h1>
        </div>
        <div style="padding:28px;font-size:15px;line-height:1.6;">
            <p>{{ $body }}</p>
            @if($actionUrl)
                <p><a href="{{ $actionUrl }}" style="display:inline-block;background:#12355b;color:#ffffff;text-decoration:none;padding:11px 17px;border-radius:6px;">Open HRMS</a></p>
            @endif
            <p style="color:#667085;font-size:12px;margin-top:28px;">This is an automated HRMS notification. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
