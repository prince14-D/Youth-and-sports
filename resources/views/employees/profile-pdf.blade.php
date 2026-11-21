<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Employee Personnel Record - {{ $employee->full_name }}</title>
    <style>
        @page {
            margin: 18px 26px 38px 26px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 8.6px;
            line-height: 1.32;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        p {
            margin: 0;
        }

        /* ── Logo-Color Top Accent Bands (Deep Navy #07144A + Crimson #D41920) ── */
        .brand-bar-navy {
            height: 4.5px;
            background: #07144A;
        }

        .brand-bar-red {
            height: 2px;
            background: #D41920;
            margin-bottom: 7px;
        }

        /* ── Official Form Masthead ── */
        .masthead {
            border: 1.25px solid #07144A;
            margin-bottom: 6px;
        }

        .masthead td {
            vertical-align: middle;
            padding: 6px 8px;
        }

        .masthead-logo-cell {
            width: 68px;
            text-align: center;
            border-right: 1px solid #C7D0E0;
            background: #FFFFFF;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
        }

        .brand-fallback {
            width: 54px;
            height: 54px;
            line-height: 54px;
            background: #07144A;
            color: #FFFFFF;
            border: 2px solid #D41920;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 0 auto;
        }

        .masthead-title-cell {
            text-align: center;
            background: #FFFFFF;
            padding: 5px 8px;
        }

        .republic-label {
            color: #D41920;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }

        .ministry-name {
            color: #07144A;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin: 1.5px 0 1px;
        }

        .dept-subtitle {
            color: #0E2380;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.85px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .form-banner {
            display: inline-block;
            background: #07144A;
            color: #FFFFFF;
            font-size: 8.4px;
            font-weight: bold;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            padding: 3px 11px;
            border-bottom: 2px solid #D41920;
        }

        .masthead-photo-cell {
            width: 82px;
            text-align: center;
            border-left: 1px solid #C7D0E0;
            background: #F7F8FC;
            padding: 5px;
        }

        .photo-frame {
            width: 64px;
            height: 70px;
            border: 1px solid #07144A;
            background: #FFFFFF;
            margin: 0 auto;
        }

        .photo-frame td {
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            height: 66px;
        }

        .profile-photo {
            max-width: 60px;
            max-height: 66px;
        }

        .photo-initials {
            color: #07144A;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .photo-caption {
            color: #4B5A73;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.55px;
            text-transform: uppercase;
            margin-top: 2.5px;
        }

        /* ── Official Control Reference Strip ── */
        .control-strip {
            margin-bottom: 7px;
            border: 1px solid #07144A;
        }

        .control-strip td {
            width: 25%;
            background: #F2F4FA;
            border-right: 1px solid #C7D0E0;
            padding: 4px 7px;
            vertical-align: top;
        }

        .control-strip td:last-child {
            border-right: 0;
        }

        .ctrl-label {
            color: #0E2380;
            font-size: 6.3px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 1.5px;
        }

        .ctrl-value {
            color: #07144A;
            font-size: 8.5px;
            font-weight: bold;
        }

        /* ── Status Badges ── */
        .status-badge {
            display: inline-block;
            font-size: 7.2px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            padding: 1px 5.5px;
            border: 1px solid transparent;
        }

        .status-active {
            background: #E8F5EE;
            color: #146338;
            border-color: #9ED3B5;
        }

        .status-suspended {
            background: #FEF5E6;
            color: #9A5B00;
            border-color: #F2C97D;
        }

        .status-terminated {
            background: #FDECEC;
            color: #B4141A;
            border-color: #EFA8AA;
        }

        .status-neutral {
            background: #EEF1F6;
            color: #374151;
            border-color: #C7D0E0;
        }

        /* ── Form Section Headers ── */
        .section-block {
            margin-top: 6.5px;
        }

        .section-header {
            width: 100%;
            background: #07144A;
            color: #FFFFFF;
            border: 1px solid #07144A;
            page-break-after: avoid;
        }

        .section-header td {
            padding: 3.5px 7px;
            vertical-align: middle;
        }

        .section-code-cell {
            width: 62px;
            background: #D41920;
            color: #FFFFFF;
            font-size: 7.2px;
            font-weight: bold;
            letter-spacing: 0.65px;
            text-transform: uppercase;
            text-align: center;
        }

        .section-title-cell {
            color: #FFFFFF;
            font-size: 8.1px;
            font-weight: bold;
            letter-spacing: 0.55px;
            text-transform: uppercase;
        }

        .section-note-cell {
            width: 130px;
            text-align: right;
            color: #D5DDF0;
            font-size: 6.5px;
            letter-spacing: 0.45px;
            text-transform: uppercase;
        }

        /* ── Bordered Form Field Grid ── */
        .form-grid {
            border-left: 1px solid #07144A;
            border-right: 1px solid #07144A;
            border-bottom: 1px solid #07144A;
        }

        .form-grid td {
            border-top: 1px solid #CFD7E6;
            border-right: 1px solid #CFD7E6;
            padding: 3.5px 6.5px 4px;
            vertical-align: top;
            background: #FFFFFF;
        }

        .form-grid tr:first-child td {
            border-top: 0;
        }

        .form-grid td:last-child {
            border-right: 0;
        }

        .w-50 { width: 50%; }
        .w-33 { width: 33.33%; }
        .w-25 { width: 25%; }
        .w-100 { width: 100%; }

        .field-label {
            color: #4B5A73;
            font-size: 6.3px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 1.5px;
        }

        .field-value {
            color: #111827;
            font-size: 8.4px;
            font-weight: normal;
            word-wrap: break-word;
            min-height: 10px;
        }

        .field-value-strong {
            color: #07144A;
            font-weight: bold;
        }

        .muted-dash {
            color: #9CA3AF;
        }

        /* ── Form Sub-Bar (Certifications under Section D) ── */
        .sub-header-bar {
            background: #EEF1F8;
            border-left: 1px solid #07144A;
            border-right: 1px solid #07144A;
            border-bottom: 1px solid #C7D0E0;
            color: #07144A;
            font-size: 7.1px;
            font-weight: bold;
            letter-spacing: 0.45px;
            text-transform: uppercase;
            padding: 3.5px 7px;
            page-break-after: avoid;
        }

        /* ── Structured Data Tables inside Form Sections ── */
        .form-table {
            border-left: 1px solid #07144A;
            border-right: 1px solid #07144A;
            border-bottom: 1px solid #07144A;
        }

        .form-table th {
            background: #F2F4FA;
            border-bottom: 1px solid #B8C4DA;
            border-right: 1px solid #CFD7E6;
            color: #0E2380;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.35px;
            text-transform: uppercase;
            padding: 3.5px 5.5px;
            text-align: left;
        }

        .form-table th:last-child,
        .form-table td:last-child {
            border-right: 0;
        }

        .form-table td {
            border-top: 1px solid #DCE3EF;
            border-right: 1px solid #DCE3EF;
            color: #111827;
            font-size: 8px;
            padding: 3.5px 5.5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .form-table tbody tr:nth-child(even) td {
            background: #FAFBFD;
        }

        .col-num {
            width: 20px;
            text-align: center !important;
            color: #4B5A73;
            font-weight: bold;
        }

        .primary-tag {
            display: inline-block;
            background: #07144A;
            color: #FFFFFF;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.35px;
            text-transform: uppercase;
            padding: 1px 4px;
            margin-left: 4px;
        }

        .empty-row {
            color: #6B7280;
            font-style: italic;
            font-size: 7.8px;
            text-align: center;
            padding: 5.5px !important;
            background: #FAFBFD;
        }

        /* ── Official HR Verification & Sign-Off Block ── */
        .verification-wrap {
            margin-top: 7.5px;
            page-break-inside: avoid;
        }

        .declaration-box {
            border-left: 1px solid #07144A;
            border-right: 1px solid #07144A;
            border-bottom: 1px solid #CFD7E6;
            background: #FAFBFD;
            color: #374151;
            font-size: 7.4px;
            line-height: 1.38;
            padding: 4.5px 7px;
        }

        .signature-grid {
            border-left: 1px solid #07144A;
            border-right: 1px solid #07144A;
            border-bottom: 1px solid #07144A;
        }

        .signature-grid > tbody > tr > td,
        .signature-grid > tr > td {
            width: 33.33%;
            border-right: 1px solid #CFD7E6;
            padding: 6px 8px 6px;
            vertical-align: bottom;
            background: #FFFFFF;
        }

        .signature-grid > tbody > tr > td:last-child,
        .signature-grid > tr > td:last-child {
            border-right: 0;
            vertical-align: middle;
        }

        .sig-space {
            height: 20px;
            border-bottom: 1px solid #6B7280;
            margin-bottom: 3px;
        }

        .sig-role {
            color: #07144A;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .sig-date {
            color: #6B7280;
            font-size: 6.8px;
            margin-top: 3px;
        }

        .stamp-table {
            width: 100%;
            border: 1px dashed #8B9BB8;
            background: #F7F8FC;
        }

        .stamp-table td {
            height: 40px;
            text-align: center;
            vertical-align: middle;
            color: #6B7B99;
            font-size: 6.6px;
            font-weight: bold;
            letter-spacing: 0.65px;
            text-transform: uppercase;
            padding: 4px;
        }

        /* ── Fixed Page Footer ── */
        .footer {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            border-top: 1.5px solid #07144A;
            padding-top: 4px;
        }

        .footer td {
            font-size: 6.6px;
            color: #4B5A73;
            vertical-align: middle;
        }

        .footer-left {
            text-align: left;
            font-weight: bold;
            color: #07144A;
            letter-spacing: 0.35px;
        }

        .footer-center {
            text-align: center;
        }

        .footer-right {
            text-align: right;
            font-weight: bold;
            color: #D41920;
        }

        .page-num::after {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $ministryLogo = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $firstName = trim((string) ($employee->first_name ?? ''));
        $middleName = trim((string) ($employee->middle_name ?? ''));
        $lastName = trim((string) ($employee->last_name ?? ''));

        $statusRaw = trim((string) ($employee->employment_status ?: 'Unknown'));
        $statusClass = match (strtolower($statusRaw)) {
            'active' => 'status-active',
            'suspended', 'on leave' => 'status-suspended',
            'terminated', 'dismissed' => 'status-terminated',
            default => 'status-neutral',
        };

        $initials = strtoupper(
            mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1)
        ) ?: 'MYS';
    @endphp

    {{-- Top Official Brand Accent Bars --}}
    <div class="brand-bar-navy"></div>
    <div class="brand-bar-red"></div>

    {{-- Official Form Masthead --}}
    <table class="masthead">
        <tr>
            <td class="masthead-logo-cell">
                @if($ministryLogo)
                    <img src="{{ $ministryLogo }}" class="brand-logo" alt="Ministry of Youth and Sports">
                @else
                    <div class="brand-fallback">MYS</div>
                @endif
            </td>
            <td class="masthead-title-cell">
                <div class="republic-label">Republic of Liberia</div>
                <div class="ministry-name">Ministry of Youth &amp; Sports</div>
                <div class="dept-subtitle">Department of Human Resources Management</div>
                <div class="form-banner">Official Employee Personnel Record Form</div>
            </td>
            <td class="masthead-photo-cell">
                <table class="photo-frame">
                    <tr>
                        <td>
                            @if($profilePhoto ?? null)
                                <img src="{{ $profilePhoto }}" class="profile-photo" alt="Employee photo">
                            @else
                                <div class="photo-initials">{{ $initials }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="photo-caption">Passport Photo</div>
            </td>
        </tr>
    </table>

    {{-- Official Control & Reference Strip --}}
    <table class="control-strip">
        <tr>
            <td>
                <div class="ctrl-label">Employee ID No.</div>
                <div class="ctrl-value">{{ $employee->employee_no ?: '—' }}</div>
            </td>
            <td>
                <div class="ctrl-label">Personnel / CSA No.</div>
                <div class="ctrl-value">{{ $employee->personnel_number ?: '—' }}</div>
            </td>
            <td>
                <div class="ctrl-label">Employment Status</div>
                <div class="ctrl-value"><span class="status-badge {{ $statusClass }}">{{ $statusRaw }}</span></div>
            </td>
            <td>
                <div class="ctrl-label">Form Ref. / Date Issued</div>
                <div class="ctrl-value">MYS-HR-01 · {{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- SECTION A: PERSONAL & DEMOGRAPHIC DETAILS --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section A</td>
                <td class="section-title-cell">Personal &amp; Demographic Information</td>
                <td class="section-note-cell">Identity &amp; Contact</td>
            </tr>
        </table>
        <table class="form-grid">
            <tr>
                <td class="w-33">
                    <div class="field-label">First Name</div>
                    <div class="field-value field-value-strong">{{ $firstName ?: '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Middle Name</div>
                    <div class="field-value">{!! $middleName !== '' ? e($middleName) : '<span class="muted-dash">—</span>' !!}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Last Name / Surname</div>
                    <div class="field-value field-value-strong">{{ $lastName ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td class="w-33">
                    <div class="field-label">Gender</div>
                    <div class="field-value">{{ $employee->gender ?: '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Date of Birth</div>
                    <div class="field-value">
                        @if($employee->date_of_birth)
                            {{ $employee->date_of_birth->format('d M Y') }}@if($employee->age > 0) ({{ $employee->age }} yrs)@endif
                        @else
                            <span class="muted-dash">—</span>
                        @endif
                    </div>
                </td>
                <td class="w-33">
                    <div class="field-label">Marital Status</div>
                    <div class="field-value">{{ $employee->marital_status ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td class="w-33">
                    <div class="field-label">Nationality</div>
                    <div class="field-value">{{ $employee->nationality ?: '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">County of Birth</div>
                    <div class="field-value">{{ $employee->county_of_birth ?: '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Phone Number</div>
                    <div class="field-value">{{ $employee->phone ?: '—' }}</div>
                </td>
            </tr>
        </table>
        <table class="form-grid" style="border-top: 0;">
            <tr>
                <td class="w-50" style="border-top: 1px solid #CFD7E6;">
                    <div class="field-label">Personal Email Address</div>
                    <div class="field-value">{{ $employee->personal_email ?: '—' }}</div>
                </td>
                <td class="w-50" style="border-top: 1px solid #CFD7E6;">
                    <div class="field-label">Official Ministry Email</div>
                    <div class="field-value">{{ $employee->official_email ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="field-label">Residential Address</div>
                    <div class="field-value">{{ $employee->address ?: '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- SECTION B: APPOINTMENT & EMPLOYMENT DETAILS --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section B</td>
                <td class="section-title-cell">Appointment &amp; Employment Details</td>
                <td class="section-note-cell">Official Posting</td>
            </tr>
        </table>
        <table class="form-grid">
            <tr>
                <td class="w-50">
                    <div class="field-label">Department / Division</div>
                    <div class="field-value field-value-strong">{{ $employee->department?->name ?? 'Unassigned' }}</div>
                </td>
                <td class="w-50">
                    <div class="field-label">Designation / Job Title</div>
                    <div class="field-value field-value-strong">{{ $employee->designation ?: ($employee->job_title ?: '—') }}</div>
                </td>
            </tr>
        </table>
        <table class="form-grid" style="border-top: 0;">
            <tr>
                <td class="w-33" style="border-top: 1px solid #CFD7E6;">
                    <div class="field-label">Employment Type</div>
                    <div class="field-value">{{ $employee->employment_type ?: '—' }}</div>
                </td>
                <td class="w-33" style="border-top: 1px solid #CFD7E6;">
                    <div class="field-label">Salary Grade</div>
                    <div class="field-value">{{ $employee->salary_grade ?: '—' }}</div>
                </td>
                <td class="w-33" style="border-top: 1px solid #CFD7E6;">
                    <div class="field-label">Direct Supervisor</div>
                    <div class="field-value">{{ $employee->supervisor?->full_name ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td class="w-33">
                    <div class="field-label">Date Joined / Appointed</div>
                    <div class="field-value">{{ $employee->date_joined?->format('d M Y') ?? '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Length of Service</div>
                    <div class="field-value">{{ $employee->years_of_service ?: '—' }}</div>
                </td>
                <td class="w-33">
                    <div class="field-label">Contract End Date</div>
                    <div class="field-value">{{ $employee->contract_end_date?->format('d M Y') ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- SECTION C: PAYROLL, BANKING & STATUTORY INFORMATION --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section C</td>
                <td class="section-title-cell">Payroll, Banking &amp; Statutory Details</td>
                <td class="section-note-cell">Strictly Confidential</td>
            </tr>
        </table>
        <table class="form-grid">
            <tr>
                <td class="w-50">
                    <div class="field-label">Basic Salary (LRD)</div>
                    <div class="field-value field-value-strong">LRD {{ number_format((float) $employee->salary_amount_lrd, 2) }}</div>
                </td>
                <td class="w-50">
                    <div class="field-label">Basic Salary (USD)</div>
                    <div class="field-value field-value-strong">USD {{ number_format((float) $employee->salary_amount_usd, 2) }}</div>
                </td>
            </tr>
            <tr>
                <td class="w-50">
                    <div class="field-label">Bank Name</div>
                    <div class="field-value">{{ $employee->bank_name ?: '—' }}</div>
                </td>
                <td class="w-50">
                    <div class="field-label">Bank Account Number</div>
                    <div class="field-value">{{ $employee->bank_account ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td class="w-50">
                    <div class="field-label">Tax Identification Number (TIN)</div>
                    <div class="field-value">{{ $employee->tin_number ?: '—' }}</div>
                </td>
                <td class="w-50">
                    <div class="field-label">Social Security Number (NASSCORP)</div>
                    <div class="field-value">{{ $employee->social_security_no ?: '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- SECTION D: ACADEMIC QUALIFICATIONS & CERTIFICATIONS --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section D</td>
                <td class="section-title-cell">Academic Qualifications &amp; Certifications</td>
                <td class="section-note-cell">Education Record</td>
            </tr>
        </table>
        <table class="form-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th style="width: 34%;">Institution</th>
                    <th style="width: 24%;">Qualification / Degree</th>
                    <th style="width: 27%;">Field of Study</th>
                    <th style="width: 10%;">Year</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->education as $index => $education)
                    <tr>
                        <td class="col-num">{{ $index + 1 }}</td>
                        <td>{{ $education->institution ?: '—' }}</td>
                        <td>{{ $education->qualification ?: '—' }}</td>
                        <td>{{ $education->field_of_study ?: '—' }}</td>
                        <td>{{ $education->year_obtained ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-row">No academic qualification records on file.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($employee->certifications->isNotEmpty())
            <div class="sub-header-bar">Professional Certifications &amp; Licenses</div>
            <table class="form-table">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th style="width: 34%;">Certification / License</th>
                        <th style="width: 30%;">Issuing Body</th>
                        <th style="width: 16%;">Issue Date</th>
                        <th style="width: 15%;">Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->certifications as $index => $certification)
                        <tr>
                            <td class="col-num">{{ $index + 1 }}</td>
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

    {{-- SECTION E: NEXT OF KIN --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section E</td>
                <td class="section-title-cell">Next of Kin</td>
                <td class="section-note-cell">Beneficiary Contact</td>
            </tr>
        </table>
        <table class="form-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th style="width: 24%;">Full Name</th>
                    <th style="width: 14%;">Relationship</th>
                    <th style="width: 15%;">Phone</th>
                    <th style="width: 21%;">Email</th>
                    <th style="width: 21%;">Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->nextOfKin as $index => $kin)
                    <tr>
                        <td class="col-num">{{ $index + 1 }}</td>
                        <td>
                            {{ $kin->name ?: '—' }}
                            @if($kin->is_primary)
                                <span class="primary-tag">Primary</span>
                            @endif
                        </td>
                        <td>{{ $kin->relationship ?: '—' }}</td>
                        <td>{{ $kin->phone ?: '—' }}</td>
                        <td>{{ $kin->email ?: '—' }}</td>
                        <td>{{ $kin->address ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-row">No next-of-kin records on file.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SECTION F: EMERGENCY CONTACTS --}}
    <div class="section-block">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section F</td>
                <td class="section-title-cell">Emergency Contacts</td>
                <td class="section-note-cell">Urgent Notification</td>
            </tr>
        </table>
        <table class="form-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th style="width: 30%;">Full Name</th>
                    <th style="width: 18%;">Relationship</th>
                    <th style="width: 20%;">Phone Number</th>
                    <th style="width: 27%;">Email Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->emergencyContacts as $index => $contact)
                    <tr>
                        <td class="col-num">{{ $index + 1 }}</td>
                        <td>{{ $contact->name ?: '—' }}</td>
                        <td>{{ $contact->relationship ?: '—' }}</td>
                        <td>{{ $contact->phone ?: '—' }}</td>
                        <td>{{ $contact->email ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-row">No emergency contacts on file.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SECTION G: OFFICIAL CERTIFICATION & HR VERIFICATION --}}
    <div class="verification-wrap">
        <table class="section-header">
            <tr>
                <td class="section-code-cell">Section G</td>
                <td class="section-title-cell">Official Certification &amp; HR Verification</td>
                <td class="section-note-cell">For Official Use</td>
            </tr>
        </table>
        <div class="declaration-box">
            I hereby certify that the particulars recorded on this Official Employee Personnel Record Form accurately reflect the official human resources records maintained by the Ministry of Youth &amp; Sports, Republic of Liberia.
        </div>
        <table class="signature-grid">
            <tr>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-role">Employee Signature</div>
                    <div class="sig-date">Date: ______ / ______ / __________</div>
                </td>
                <td>
                    <div class="sig-space"></div>
                    <div class="sig-role">Director / HR Officer Signature</div>
                    <div class="sig-date">Date: ______ / ______ / __________</div>
                </td>
                <td>
                    <table class="stamp-table">
                        <tr>
                            <td>Official Ministry Stamp / Seal</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Fixed Page Footer --}}
    <table class="footer">
        <tr>
            <td class="footer-left" style="width: 34%;">OFFICIAL &amp; CONFIDENTIAL · FORM MYS-HR-01</td>
            <td class="footer-center" style="width: 48%;">Ministry of Youth &amp; Sports HRMS · {{ $employee->employee_no ?: 'N/A' }} · Generated {{ now()->format('d M Y H:i') }}</td>
            <td class="footer-right" style="width: 18%;"><span class="page-num"></span></td>
        </tr>
    </table>
</body>
</html>
