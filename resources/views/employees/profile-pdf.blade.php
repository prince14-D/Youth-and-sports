<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Profile - {{ $employee->full_name }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.45; }
        h1 { color: #12355b; font-size: 21px; margin: 0 0 4px; }
        h2 { color: #12355b; border-bottom: 1px solid #cbd5e1; font-size: 13px; margin: 22px 0 8px; padding-bottom: 5px; }
        p { margin: 3px 0; }
        .header { border-bottom: 3px solid #dc2626; padding-bottom: 12px; }
        .profile-photo { border: 1px solid #cbd5e1; height: 82px; width: 82px; }
        .muted { color: #64748b; }
        .badge { background: #fee2e2; color: #991b1b; padding: 4px 7px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border-bottom: 1px solid #e2e8f0; padding: 6px 5px; text-align: left; vertical-align: top; }
        th { color: #475569; font-size: 10px; text-transform: uppercase; }
        .label { color: #64748b; width: 24%; }
        .two-col td { width: 50%; border-bottom: 0; padding: 4px 12px 4px 0; }
        .footer { color: #64748b; border-top: 1px solid #cbd5e1; font-size: 9px; margin-top: 24px; padding-top: 7px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="border: 0; width: 88px; padding: 0 14px 0 0;">
                    @if($profilePhoto ?? null)
                        <img src="{{ $profilePhoto }}" class="profile-photo" alt="Employee profile photo">
                    @endif
                </td>
                <td style="border: 0; padding: 0;">
                    <h1>Employee Profile</h1>
                    <p class="muted">Ministry of Youth &amp; Sports HRMS</p>
                    <p><strong>{{ $employee->full_name }}</strong> &nbsp; <span class="badge">{{ $employee->employment_status }}</span></p>
                    <p class="muted">Employee ID: {{ $employee->employee_no }} &bull; {{ $employee->designation ?: 'Employee' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <h2>Personal Information</h2>
    <table class="two-col">
        <tr>
            <td><span class="label">Full Name</span><br><strong>{{ $employee->full_name }}</strong></td>
            <td><span class="label">Gender</span><br>{{ $employee->gender ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Date of Birth</span><br>{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</td>
            <td><span class="label">Nationality</span><br>{{ $employee->nationality ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Phone</span><br>{{ $employee->phone ?: '—' }}</td>
            <td><span class="label">Personal Email</span><br>{{ $employee->personal_email ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Address</span><br>{{ $employee->address ?: '—' }}</td>
        </tr>
    </table>

    <h2>Employment Information</h2>
    <table class="two-col">
        <tr>
            <td><span class="label">Department</span><br>{{ $employee->department?->name ?? 'Unassigned' }}</td>
            <td><span class="label">Designation</span><br>{{ $employee->designation ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Employment Type</span><br>{{ $employee->employment_type ?: '—' }}</td>
            <td><span class="label">Date Joined</span><br>{{ $employee->date_joined?->format('d M Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Official Email</span><br>{{ $employee->official_email ?: '—' }}</td>
            <td><span class="label">Supervisor</span><br>{{ $employee->supervisor?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Personnel Number</span><br>{{ $employee->personnel_number ?: '—' }}</td>
            <td><span class="label">Job Title</span><br>{{ $employee->job_title ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Salary Grade</span><br>{{ $employee->salary_grade ?: '—' }}</td>
            <td><span class="label">Contract End Date</span><br>{{ $employee->contract_end_date?->format('d M Y') ?? '—' }}</td>
        </tr>
    </table>

    <h2>Payroll &amp; Payment Information</h2>
    <table class="two-col">
        <tr>
            <td><span class="label">Basic Salary (LRD)</span><br><strong>LRD ${{ number_format((float) $employee->salary_amount_lrd, 2) }}</strong></td>
            <td><span class="label">Basic Salary (USD)</span><br><strong>USD ${{ number_format((float) $employee->salary_amount_usd, 2) }}</strong></td>
        </tr>
        <tr>
            <td><span class="label">Bank Name</span><br>{{ $employee->bank_name ?: '—' }}</td>
            <td><span class="label">Bank Account Number</span><br>{{ $employee->bank_account ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">TIN Number</span><br>{{ $employee->tin_number ?: '—' }}</td>
            <td><span class="label">Social Security Number</span><br>{{ $employee->social_security_no ?: '—' }}</td>
        </tr>
    </table>

    <h2>Education &amp; Certifications</h2>
    @if($employee->education->isNotEmpty())
        <table>
            <tr><th>Institution</th><th>Qualification</th><th>Year</th></tr>
            @foreach($employee->education as $education)
                <tr><td>{{ $education->institution }}</td><td>{{ $education->qualification }}{{ $education->field_of_study ? ' - ' . $education->field_of_study : '' }}</td><td>{{ $education->year_obtained ?: '—' }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="muted">No education records added.</p>
    @endif
    @if($employee->certifications->isNotEmpty())
        <table style="margin-top: 8px;">
            <tr><th>Certification</th><th>Issuing Organization</th><th>Issue Date</th><th>Expiry Date</th></tr>
            @foreach($employee->certifications as $certification)
                <tr><td>{{ $certification->name }}</td><td>{{ $certification->issuing_body ?: '—' }}</td><td>{{ $certification->issue_date?->format('d M Y') ?? '—' }}</td><td>{{ $certification->expiry_date?->format('d M Y') ?? '—' }}</td></tr>
            @endforeach
        </table>
    @endif

    <h2>Next of Kin</h2>
    @if($employee->nextOfKin->isNotEmpty())
        <table>
            <tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Email</th><th>Address</th></tr>
            @foreach($employee->nextOfKin as $kin)
                <tr><td>{{ $kin->name }}</td><td>{{ $kin->relationship }}</td><td>{{ $kin->phone ?: '—' }}</td><td>{{ $kin->email ?: '—' }}</td><td>{{ $kin->address ?: '—' }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="muted">No next-of-kin records added.</p>
    @endif

    <h2>Emergency Contacts</h2>
    @if($employee->emergencyContacts->isNotEmpty())
        <table>
            <tr><th>Name</th><th>Relationship</th><th>Phone</th><th>Email</th></tr>
            @foreach($employee->emergencyContacts as $contact)
                <tr><td>{{ $contact->name }}</td><td>{{ $contact->relationship }}</td><td>{{ $contact->phone }}</td><td>{{ $contact->email ?: '—' }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="muted">No emergency contacts added.</p>
    @endif

    <h2>Document Attachments</h2>
    @if($employee->documents->isNotEmpty())
        <table>
            <tr><th>Document</th><th>Type</th><th>Expiry Date</th><th>File Size</th></tr>
            @foreach($employee->documents as $document)
                <tr>
                    <td>{{ $document->title }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                    <td>{{ $document->expiry_date?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $document->file_size ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="muted">No document attachments added.</p>
    @endif

    <div class="footer">Generated by HRMS on {{ now()->format('d M Y H:i') }}.</div>
</body>
</html>
