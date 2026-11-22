<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MYS ID Card — {{ $employee->employee_no }}</title>
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .sheet-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #0b1f4b;
            margin-bottom: 4px;
        }
        .sheet-sub {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 18px;
        }
        table.layout { width: 100%; border-collapse: separate; border-spacing: 24px 0; }
        td.card-col { width: 50%; vertical-align: top; text-align: center; }
        .cut-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; font-weight: bold; }
        .card {
            width: 310px;
            margin: 0 auto;
            border: 1.5px dashed #94a3b8;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            text-align: center;
        }
        .hdr {
            background: {{ $isContractor ? '#064e3b' : '#0b1f4b' }};
            color: #ffffff;
            padding: 14px 12px 18px;
        }
        .flag { height: 5px; background: #bf0a30; }
        .badge-pill {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 10px;
            background: #1e293b;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.8px;
        }
        .photo-wrap {
            margin: 14px auto 8px;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 3px solid {{ $isContractor ? '#064e3b' : '#0b1f4b' }};
            overflow: hidden;
            background: {{ $isContractor ? '#0f5132' : '#123f7a' }};
            color: #ffffff;
            line-height: 96px;
            font-size: 30px;
            font-weight: bold;
        }
        .photo-wrap img { width: 96px; height: 96px; object-fit: cover; }
        .name { font-size: 15px; font-weight: bold; color: #0b1f4b; margin: 4px 12px 2px; }
        .desig { font-size: 11px; font-weight: bold; color: {{ $isContractor ? '#0f5132' : '#bf0a30' }}; margin-bottom: 2px; }
        .dept { font-size: 10px; color: #475569; margin-bottom: 12px; }
        .kv { width: 88%; margin: 0 auto 14px; border-collapse: collapse; font-size: 10px; background: #f8fafc; border: 1px solid #cbd5e1; }
        .kv td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .kv td.l { text-align: left; color: #64748b; }
        .kv td.r { text-align: right; font-weight: bold; color: #0f172a; }
        .ftr {
            background: {{ $isContractor ? '#064e3b' : '#0b1f4b' }};
            color: #ffffff;
            font-size: 8.5px;
            letter-spacing: 0.6px;
            padding: 7px 8px;
        }
        .back-body { padding: 16px 18px; }
        .qr-box { display: inline-block; padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; }
        .em-box {
            margin: 12px 0;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            text-align: left;
            font-size: 9.5px;
        }
        .notice { font-size: 9px; color: #475569; line-height: 1.45; margin-bottom: 16px; }
        .sig-line { border-bottom: 1px solid #475569; width: 150px; height: 22px; margin: 0 auto 4px; }
    </style>
</head>
<body>
    <div class="sheet-title">MINISTRY OF YOUTH &amp; SPORTS — OFFICIAL {{ $isContractor ? 'CONTRACTOR' : 'STAFF' }} ID CARD PRINT SHEET</div>
    <div class="sheet-sub">{{ $employee->full_name }} ({{ $employee->employee_no }}) &bull; Verification Hash: {{ $verifyHash }} &bull; Generated {{ now()->format('d M Y') }}</div>

    <table class="layout">
        <tr>
            <!-- FRONT -->
            <td class="card-col">
                <div class="cut-label">Front Side (Cut Along Dashed Border)</div>
                <div class="card">
                    <div class="hdr">
                        @if($logoDataUri)
                            <div style="margin-bottom:4px;">
                                <img src="{{ $logoDataUri }}" style="width:36px;height:36px;background:#fff;padding:3px;border-radius:6px;">
                            </div>
                        @endif
                        <div style="font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:#cbd5e1;">Republic of Liberia</div>
                        <div style="font-size:12.5px;font-weight:bold;">MINISTRY OF YOUTH &amp; SPORTS</div>
                        <span class="badge-pill">{{ $isContractor ? 'CONTRACTOR ID CARD' : 'OFFICIAL STAFF ID CARD' }}</span>
                    </div>
                    <div class="flag"></div>

                    <div class="photo-wrap">
                        @if($photoDataUri)
                            <img src="{{ $photoDataUri }}" alt="{{ $employee->full_name }}">
                        @else
                            {{ $initials }}
                        @endif
                    </div>

                    <div class="name">{{ $employee->full_name }}</div>
                    <div class="desig">{{ $employee->designation ?? ($isContractor ? 'Contract Personnel' : 'Ministry Staff') }}</div>
                    <div class="dept">{{ $employee->department?->name ?? 'Ministry Headquarters' }}</div>

                    <table class="kv">
                        <tr><td class="l">{{ $isContractor ? 'Contractor ID' : 'Employee ID' }}</td><td class="r">{{ $employee->employee_no }}</td></tr>
                        <tr><td class="l">Category</td><td class="r">{{ $employee->employment_type }}</td></tr>
                        <tr><td class="l">Issued Date</td><td class="r">{{ $issueDate->format('d M Y') }}</td></tr>
                        <tr><td class="l">Valid Until</td><td class="r" style="color: {{ $isContractor ? '#b91c1c' : '#15803d' }};">{{ $validUntil }}</td></tr>
                    </table>

                    <div class="ftr">OFFICIAL PROPERTY OF THE GOVERNMENT OF LIBERIA</div>
                </div>
            </td>

            <!-- BACK -->
            <td class="card-col">
                <div class="cut-label">Back Side (QR Verification)</div>
                <div class="card">
                    <div class="hdr" style="padding: 10px 12px;">
                        <div style="font-size:10.5px;font-weight:bold;letter-spacing:0.8px;">IDENTITY VERIFICATION &amp; SECURITY</div>
                    </div>
                    <div class="flag"></div>

                    <div class="back-body">
                        <div class="qr-box">
                            <img src="{{ $qrDataUri }}" style="width:128px;height:128px;">
                        </div>
                        <div style="font-size:10px;font-weight:bold;color:#0b1f4b;margin-top:6px;">Scan QR to Verify Active Status</div>
                        <div style="font-size:9.5px;font-family:monospace;color:#1d4ed8;font-weight:bold;">CODE: {{ $verifyHash }}</div>

                        <div class="em-box">
                            <div style="font-weight:bold;color:#0b1f4b;margin-bottom:2px;">EMERGENCY CONTACT</div>
                            @if($emergencyContact)
                                <div><strong>{{ $emergencyContact->name }}</strong> ({{ $emergencyContact->relationship }})</div>
                                <div style="color:#475569;">{{ $emergencyContact->phone }}</div>
                            @else
                                <div style="color:#475569;">Human Resources Division, Ministry of Youth &amp; Sports, Monrovia</div>
                            @endif
                        </div>

                        <div class="notice">
                            This card certifies that the bearer is an authorized {{ $isContractor ? 'contractor' : 'employee' }} of the <strong>Ministry of Youth &amp; Sports, Republic of Liberia</strong>. If found, please return to the HR Division, SKD Sports Complex, Paynesville, Monrovia.
                        </div>

                        <div class="sig-line"></div>
                        <div style="font-size:10px;font-weight:bold;color:#0b1f4b;">Director of Human Resources</div>
                        <div style="font-size:8.5px;color:#64748b;">Authorized Issuing Authority</div>
                    </div>

                    <div class="flag"></div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
