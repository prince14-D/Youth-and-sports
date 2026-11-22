<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Payslip</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px; color:#1e293b;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #cbd5e1;">
        <div style="background:#0b1f4b; color:#ffffff; padding:20px 24px;">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:1.2px; opacity:0.8;">Republic of Liberia</div>
            <h2 style="margin:4px 0 2px; font-size:18px;">MINISTRY OF YOUTH &amp; SPORTS</h2>
            <div style="font-size:13px; opacity:0.9;">
                {{ !empty($isContractor) ? 'Official Contractor Payment Advice' : 'Official Employee Salary Payslip' }}
            </div>
        </div>
        <div style="height:4px; background:#bf0a30;"></div>

        <div style="padding:24px;">
            <p style="margin-top:0;">Dear <strong>{{ $record->employee?->full_name ?? 'Staff Member' }}</strong>,</p>
            <p style="font-size:14px; line-height:1.6;">
                Your official {{ !empty($isContractor) ? 'contractor payment advice' : 'salary payslip' }} for
                <strong>{{ $record->period?->name ?? 'the latest payroll cycle' }}</strong> is ready and attached to this email as a PDF document.
            </p>

            <table style="width:100%; border-collapse:collapse; margin:18px 0; font-size:13px; background:#f8fafc; border:1px solid #e2e8f0;">
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; color:#64748b;">Payslip Reference</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:bold;">{{ $payslipRef }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; color:#64748b;">Gross Earnings (LRD)</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:bold;">LRD {{ number_format((float)$grossLrd, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; color:#64748b;">Total Deductions (LRD)</td>
                    <td style="padding:10px 14px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:bold; color:#b91c1c;">-LRD {{ number_format((float)$totalDeductionsLrd, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 14px; border-bottom:1px solid #e2e8f0; font-weight:bold; color:#0b1f4b;">Net Take-Home Pay (LRD)</td>
                    <td style="padding:12px 14px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight:bold; font-size:15px; color:#15803d;">LRD {{ number_format((float)$record->net_pay_lrd, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 14px; font-weight:bold; color:#0b1f4b;">Net Take-Home Pay (USD)</td>
                    <td style="padding:12px 14px; text-align:right; font-weight:bold; font-size:15px; color:#1d4ed8;">USD {{ number_format((float)$record->net_pay_usd, 2) }}</td>
                </tr>
            </table>

            <p style="font-size:12px; color:#64748b;">
                Verification Code: <strong style="color:#0b1f4b;">{{ $verificationCode }}</strong><br>
                You can also view or download all your payslips anytime inside the Ministry HRMIS portal.
            </p>
            <p style="margin-bottom:0; font-size:13px;">
                Regards,<br>
                <strong>Human Resources &amp; Payroll Division</strong><br>
                Ministry of Youth and Sports, Republic of Liberia
            </p>
        </div>
    </div>
</body>
</html>
