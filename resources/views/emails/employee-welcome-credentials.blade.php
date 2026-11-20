<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Employee HRMS Account</title></head>
<body style="margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif;">
<div style="max-width:640px;margin:30px auto;background:#fff;border:1px solid #d9e1ec;border-radius:10px;overflow:hidden;">
    <div style="background:#12355b;color:#fff;padding:24px 28px;"><div style="font-size:12px;opacity:.85;">Republic of Liberia</div><h1 style="font-size:21px;margin:7px 0 0;">Ministry of Youth and Sports HRMS</h1></div>
    <div style="padding:28px;font-size:15px;line-height:1.55;">
        <p>Dear {{ $employeeName }},</p>
        <p>You have been registered in the Ministry HRMS as a <strong>{{ $employmentType }}</strong>.</p>
        <p>Your employee account credentials are:</p>
        <table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #d9e1ec;"><tr><td style="padding:10px;font-weight:bold;width:40%;">Employee Number</td><td style="padding:10px;">{{ $employeeNumber }}</td></tr><tr><td style="padding:10px;font-weight:bold;">Login Email</td><td style="padding:10px;">{{ $email }}</td></tr><tr><td style="padding:10px;font-weight:bold;">Temporary Password</td><td style="padding:10px;font-family:monospace;">{{ $temporaryPassword }}</td></tr></table>
        <p style="margin-top:22px;"><a href="{{ $loginUrl }}" style="display:inline-block;background:#12355b;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">Sign in to HRMS</a></p>
        <p style="color:#92400e;background:#fffbeb;padding:12px;border-radius:6px;font-size:13px;">Please keep these credentials private and change the temporary password after signing in.</p>
        <p style="color:#667085;font-size:12px;margin-top:26px;">This is an automated message from the Ministry of Youth and Sports HRMS.</p>
    </div>
</div>
</body></html>
