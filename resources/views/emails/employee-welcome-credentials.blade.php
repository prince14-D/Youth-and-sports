<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Ministry of Youth &amp; Sports HRMS</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f8;color:#111827;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#eef2f8;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;width:100%;background-color:#ffffff;border:1px solid #cbd5e1;border-radius:8px;overflow:hidden;">
                    <!-- Top Logo-Color Accent Band -->
                    <tr>
                        <td style="height:5px;background-color:#D41920;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Official Ministry Header -->
                    <tr>
                        <td style="background-color:#07144A;padding:26px 32px;text-align:center;">
                            <div style="color:#fca5a5;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;">
                                Republic of Liberia
                            </div>
                            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;letter-spacing:0.4px;line-height:1.25;">
                                MINISTRY OF YOUTH &amp; SPORTS
                            </h1>
                            <div style="color:#c7d2fe;font-size:12px;letter-spacing:0.8px;text-transform:uppercase;margin-top:6px;">
                                Department of Human Resources Management · HRMS Portal
                            </div>
                        </td>
                    </tr>

                    <!-- Crimson Divider -->
                    <tr>
                        <td style="height:3px;background-color:#D41920;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Main Body -->
                    <tr>
                        <td style="padding:30px 32px 24px;">
                            <div style="display:inline-block;background-color:#F2F4FA;color:#0E2380;border:1px solid #C7D0E0;font-size:11px;font-weight:bold;letter-spacing:0.8px;text-transform:uppercase;padding:4px 10px;border-radius:4px;margin-bottom:16px;">
                                Official Personnel Account Activation
                            </div>

                            <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#111827;">
                                Dear <strong>{{ $employeeName }}</strong>,
                            </p>

                            <p style="margin:0 0 20px;font-size:14px;line-height:1.65;color:#374151;">
                                Welcome to the <strong>Ministry of Youth &amp; Sports Human Resource Management System (HRMS)</strong>. Your official personnel record has been registered under the category of <strong style="color:#07144A;">{{ $employmentType }}</strong>, and your self-service portal account is now active.
                            </p>

                            <!-- Structured Credentials Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #07144A;border-radius:6px;overflow:hidden;margin-bottom:22px;">
                                <tr>
                                    <td colspan="2" style="background-color:#07144A;color:#ffffff;padding:9px 14px;font-size:11px;font-weight:bold;letter-spacing:0.9px;text-transform:uppercase;">
                                        Your Official HRMS Login Credentials
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;padding:11px 14px;background-color:#F2F4FA;border-bottom:1px solid #CFD7E6;color:#0E2380;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:0.4px;">
                                        Employee ID No.
                                    </td>
                                    <td style="padding:11px 14px;background-color:#ffffff;border-bottom:1px solid #CFD7E6;color:#07144A;font-size:14px;font-weight:bold;font-family:'Courier New',Courier,monospace;">
                                        {{ $employeeNumber }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;padding:11px 14px;background-color:#F2F4FA;border-bottom:1px solid #CFD7E6;color:#0E2380;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:0.4px;">
                                        Portal Login Email
                                    </td>
                                    <td style="padding:11px 14px;background-color:#ffffff;border-bottom:1px solid #CFD7E6;color:#111827;font-size:14px;">
                                        {{ $email }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;padding:11px 14px;background-color:#F2F4FA;color:#0E2380;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:0.4px;">
                                        Temporary Password
                                    </td>
                                    <td style="padding:11px 14px;background-color:#ffffff;font-size:14px;">
                                        <span style="display:inline-block;background-color:#FEF2F2;color:#B4141A;border:1px solid #FCA5A5;padding:3px 9px;border-radius:4px;font-family:'Courier New',Courier,monospace;font-weight:bold;letter-spacing:0.5px;">
                                            {{ $temporaryPassword }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Sign-in CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $loginUrl }}" style="display:inline-block;background-color:#07144A;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;letter-spacing:0.4px;padding:13px 28px;border-radius:6px;border-bottom:3px solid #D41920;">
                                            Sign In to Ministry HRMS Portal &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Getting Started Steps -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F8FAFC;border:1px solid #DCE3EF;border-radius:6px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="color:#07144A;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;">
                                            First-Time Sign-In Instructions
                                        </div>
                                        <ol style="margin:0;padding-left:18px;font-size:13px;line-height:1.65;color:#374151;">
                                            <li>Click the button above or visit <a href="{{ $loginUrl }}" style="color:#0E2380;text-decoration:underline;">{{ $loginUrl }}</a>.</li>
                                            <li>Sign in using your portal login email and the temporary password provided above.</li>
                                            <li>Change your temporary password immediately and review your personnel profile, attendance, and payslips.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Advisory -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FEF2F2;border-left:4px solid #D41920;border-radius:4px;">
                                <tr>
                                    <td style="padding:11px 14px;font-size:12.5px;line-height:1.55;color:#7F1D1D;">
                                        <strong>Confidentiality Notice:</strong> Keep these login credentials strictly private. Never share your password with anyone. If you experience trouble signing in, please contact the Department of Human Resources Management.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Official Ministry Footer -->
                    <tr>
                        <td style="background-color:#F2F4FA;border-top:1px solid #CFD7E6;padding:18px 32px;text-align:center;">
                            <div style="color:#07144A;font-size:12px;font-weight:bold;letter-spacing:0.4px;">
                                Ministry of Youth &amp; Sports · Republic of Liberia
                            </div>
                            <div style="color:#64748B;font-size:11px;margin-top:4px;line-height:1.5;">
                                Automated Official Notification from the Ministry HRMS · Please do not reply directly to this message.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
