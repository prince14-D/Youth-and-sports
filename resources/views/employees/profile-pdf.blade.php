<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Record - {{ $employee->full_name }}</title>
    <style>
        @page { margin: 30px 34px 48px; }
        body { font-family: DejaVu Sans, sans-serif; color: #233044; font-size: 10px; line-height: 1.45; }
        table { border-collapse: collapse; width: 100%; }
        p { margin: 0; }
        .top-rule { height: 5px; background: #102c55; margin-bottom: 15px; }
        .header { border-bottom: 1px solid #dce3ec; padding-bottom: 14px; }
        .brand-cell { width: 62px; vertical-align: middle; padding: 0 12px 0 0; }
        .brand-logo { width: 58px; height: 58px; }
        .brand-fallback { width: 54px; height: 54px; line-height: 54px; background: #102c55; color: #fff; text-align: center; font-size: 15px; font-weight: bold; }
        .heading-cell { vertical-align: middle; padding: 0; }
        .eyebrow { color: #64748b; font-size: 8px; font-weight: bold; letter-spacing: 1.1px; text-transform: uppercase; }
        h1 { color: #102c55; font-size: 19px; line-height: 1.2; margin: 4px 0 3px; }
        .ministry { color: #526174; font-size: 9px; }
        .photo-cell { width: 86px; text-align: right; vertical-align: middle; padding: 0 0 0 10px; }
        .profile-photo { width: 74px; height: 82px; border: 2px solid #e1e7ef; }
        .photo-fallback { width: 74px; height: 82px; line-height: 82px; background: #eef3f8; border: 2px solid #e1e7ef; color: #102c55; font-size: 18px; font-weight: bold; text-align: center; }
        .employee-name { color: #102c55; font-size: 12px; font-weight: bold; margin-top: 6px; }
        .summary { margin: 14px 0 18px; }
        .summary td { width: 25%; background: #f3f6fa; border-right: 3px solid #fff; padding: 8px 9px; vertical-align: top; }
        .summary td:last-child { border-right: 0; }
        .summary-label, .field-label { color: #718096; font-size: 7.5px; font-weight: bold; letter-spacing: .45px; text-transform: uppercase; }
        .summary-value { color: #102c55; font-size: 9px; font-weight: bold; margin-top: 3px; }
        .section { margin-top: 16px; page-break-inside: avoid; }
        .section-title { border-bottom: 1px solid #dce3ec; color: #102c55; font-size: 11px; font-weight: bold; margin: 0 0 7px; padding: 0 0 5px; }
        .section-number { color: #d72e36; padding-right: 5px; }
        .section-note { color: #8793a3; float: right; font-size: 7px; font-weight: normal; letter-spacing: .45px; text-transform: uppercase; }
        .details td { width: 50%; border-bottom: 1px solid #edf0f4; padding: 6px 8px 6px 0; vertical-align: top; }
        .details .wide { width: 100%; }
        .field-value { color: #253449; font-size: 9px; margin-top: 2px; word-wrap: break-word; }
        .data-table { margin-top: 3px; }
        .data-table th { background: #eef2f7; border-bottom: 1px solid #dce3ec; color: #526174; font-size: 7.5px; font-weight: bold; letter-spacing: .35px; padding: 6px 7px; text-align: left; text-transform: uppercase; }
        .data-table td { border-bottom: 1px solid #edf0f4; color: #253449; font-size: 8.5px; padding: 6px 7px; text-align: left; vertical-align: top; word-wrap: break-word; }
        .empty { background: #f8fafc; color: #788596; font-size: 9px; padding: 9px; }
        .status { background: #e8f4ec; color: #247044; font-size: 8px; font-weight: bold; padding: 3px 6px; }
        .footer { position: fixed; bottom: -31px; left: 0; right: 0; border-top: 1px solid #dce3ec; color: #718096; font-size: 7px; padding-top: 6px; }
        .footer-left { float: left; }
        .footer-right { float: right; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $ministryLogo = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    @endphp

    <div class="top-rule"></div>
    <table class="header">
        <tr>
            <td class="brand-cell">
                @if($ministryLogo)
                    <img src="{{ $ministryLogo }}" class="brand-logo" alt="Ministry of Youth and Sports">
                @else
                    <div class="brand-fallback">MYS</div>
                @endif
            </td>
            <td class="heading-cell">
                <div class="eyebrow">Republic of Liberia · Human Resources</div>
                <h1>Employee Record</h1>
                <p class="ministry">Ministry of Youth &amp; Sports</p>
                <p class="employee-name">{{ $employee->full_name }}</p>
            </td>
            <td class="photo-cell">
                @if($profilePhoto ?? null)
                    <img src="{{ $profilePhoto }}" class="profile-photo" alt="Employee profile photo">
                @else
                    <div class="photo-fallback">{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><div class="summary-label">Employee ID</div><div class="summary-value">{{ $employee->employee_no ?: '—' }}</div></td>
            <td><div class="summary-label">Department</div><div class="summary-value">{{ $employee->department?->name ?? 'Unassigned' }}</div></td>
            <td><div class="summary-label">Employment Type</div><div class="summary-value">{{ $employee->employment_type ?: '—' }}</div></td>
            <td><div class="summary-label">Status</div><div class="summary-value"><span class="status">{{ $employee->employment_status ?: 'Unknown' }}</span></div></td>
        </tr>
    </table>

    <div class="section">
        <h2 class="section-title"><span class="section-number">01</span>Personal Information <span class="section-note">Identity &amp; contact</span></h2>
        <table class="details">
            <tr>
                <td><div class="field-label">Full Name</div><div class="field-value">{{ $employee->full_name }}</div></td>
                <td><div class="field-label">Gender</div><div class="field-value">{{ $employee->gender ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Date of Birth</div><div class="field-value">{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</div></td>
                <td><div class="field-label">Nationality</div><div class="field-value">{{ $employee->nationality ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">County of Birth</div><div class="field-value">{{ $employee->county_of_birth ?: '—' }}</div></td>
                <td><div class="field-label">Marital Status</div><div class="field-value">{{ $employee->marital_status ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Phone</div><div class="field-value">{{ $employee->phone ?: '—' }}</div></td>
                <td><div class="field-label">Personal Email</div><div class="field-value">{{ $employee->personal_email ?: '—' }}</div></td>
            </tr>
            <tr>
                <td colspan="2"><div class="field-label">Address</div><div class="field-value">{{ $employee->address ?: '—' }}</div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">02</span>Employment Information <span class="section-note">Appointment details</span></h2>
        <table class="details">
            <tr>
                <td><div class="field-label">Official Email</div><div class="field-value">{{ $employee->official_email ?: '—' }}</div></td>
                <td><div class="field-label">Personnel Number</div><div class="field-value">{{ $employee->personnel_number ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Department</div><div class="field-value">{{ $employee->department?->name ?? 'Unassigned' }}</div></td>
                <td><div class="field-label">Designation / Job Title</div><div class="field-value">{{ $employee->designation ?: ($employee->job_title ?: '—') }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Date Joined</div><div class="field-value">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</div></td>
                <td><div class="field-label">Contract End Date</div><div class="field-value">{{ $employee->contract_end_date?->format('d M Y') ?? '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Supervisor</div><div class="field-value">{{ $employee->supervisor?->full_name ?? '—' }}</div></td>
                <td><div class="field-label">Salary Grade</div><div class="field-value">{{ $employee->salary_grade ?: '—' }}</div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">03</span>Payroll &amp; Banking <span class="section-note">Confidential</span></h2>
        <table class="details">
            <tr>
                <td><div class="field-label">Basic Salary (LRD)</div><div class="field-value">LRD {{ number_format((float) $employee->salary_amount_lrd, 2) }}</div></td>
                <td><div class="field-label">Basic Salary (USD)</div><div class="field-value">USD {{ number_format((float) $employee->salary_amount_usd, 2) }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">Bank Name</div><div class="field-value">{{ $employee->bank_name ?: '—' }}</div></td>
                <td><div class="field-label">Bank Account Number</div><div class="field-value">{{ $employee->bank_account ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="field-label">TIN Number</div><div class="field-value">{{ $employee->tin_number ?: '—' }}</div></td>
                <td><div class="field-label">Social Security Number</div><div class="field-value">{{ $employee->social_security_no ?: '—' }}</div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">04</span>Education &amp; Certifications</h2>
        @if($employee->education->isNotEmpty())
            <table class="data-table">
                <thead><tr><th>Institution</th><th>Qualification</th><th>Field of Study</th><th>Year</th></tr></thead>
                <tbody>
                    @foreach($employee->education as $education)
                        <tr>
                            <td>{{ $education->institution ?: '—' }}</td>
                            <td>{{ $education->qualification ?: '—' }}</td>
                            <td>{{ $education->field_of_study ?: '—' }}</td>
                            <td>{{ $education->year_obtained ?: ($education->graduation_year ?? '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No education records added.</div>
        @endif
        @if($employee->certifications->isNotEmpty())
            <table class="data-table" style="margin-top: 8px;">
                <thead><tr><th>Certification</th><th>Issuing Organization</th><th>Issue Date</th><th>Expiry Date</th></tr></thead>
                <tbody>
                    @foreach($employee->certifications as $certification)
                        <tr>
                            <td>{{ $certification->name ?: '—' }}</td>
                            <td>{{ $certification->issuing_body ?: '—' }}</td>
                            <td>{{ $certification->issue_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $certification->expiry_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">05</span>Next of Kin</h2>
        @if($employee->nextOfKin->isNotEmpty())
            <table class="data-table">
                <thead><tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Email</th><th>Address</th></tr></thead>
                <tbody>
                    @foreach($employee->nextOfKin as $kin)
                        <tr><td>{{ $kin->name }}</td><td>{{ $kin->relationship }}</td><td>{{ $kin->phone ?: '—' }}</td><td>{{ $kin->email ?: '—' }}</td><td>{{ $kin->address ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No next-of-kin records added.</div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">06</span>Emergency Contacts</h2>
        @if($employee->emergencyContacts->isNotEmpty())
            <table class="data-table">
                <thead><tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Email</th></tr></thead>
                <tbody>
                    @foreach($employee->emergencyContacts as $contact)
                        <tr><td>{{ $contact->name }}</td><td>{{ $contact->relationship }}</td><td>{{ $contact->phone }}</td><td>{{ $contact->email ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No emergency contacts added.</div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title"><span class="section-number">07</span>Supporting Documents</h2>
        @if($employee->documents->isNotEmpty())
            <table class="data-table">
                <thead><tr><th>Document</th><th>Type</th><th>Expiry Date</th><th>File Size</th></tr></thead>
                <tbody>
                    @foreach($employee->documents as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                            <td>{{ $document->expiry_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $document->file_size ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No document attachments added.</div>
        @endif
    </div>

    <div class="footer">
        <span class="footer-left">OFFICIAL &amp; CONFIDENTIAL · Generated {{ now()->format('d M Y H:i') }}</span>
        <span class="footer-right">{{ $employee->employee_no }} · Ministry of Youth &amp; Sports HRMS</span>
    </div>
</body>
</html>
