<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeEducation;
use App\Models\EmployeeCertification;
use App\Models\EmployeeNextOfKin;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\EmployeeWelcomeCredentials;

class Create extends Component
{
    use WithFileUploads;

    public $step = 1;
    public $totalSteps = 6;

    // STEP 1: Personal Information
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $gender = '';
    public $date_of_birth = '';
    public $nationality = 'Liberian';
    public $county_of_birth = '';
    public $marital_status = '';
    public $national_id = '';
    public $passport_no = '';
    public $phone = '';
    public $personal_email = '';
    public $official_email = '';
    public $address = '';
    public $photo;
    public $photo_base64 = '';
    public $duplicateWarning = null;

    public function updatedFirstName() { $this->checkDuplicate(); }
    public function updatedLastName() { $this->checkDuplicate(); }
    public function updatedDateOfBirth() { $this->checkDuplicate(); }
    public function updatedPhone() { $this->checkDuplicate(); }
    public function updatedOfficialEmail() { $this->checkDuplicate(); }

    public function checkDuplicate()
    {
        $this->duplicateWarning = null;

        if (!empty($this->first_name) && !empty($this->last_name) && !empty($this->date_of_birth)) {
            $existing = Employee::where('first_name', trim($this->first_name))
                ->where('last_name', trim($this->last_name))
                ->whereDate('date_of_birth', $this->date_of_birth)
                ->first();

            if ($existing) {
                $dob = $existing->date_of_birth ? $existing->date_of_birth->format('Y-m-d') : '';
                $dept = $existing->department?->name ?? 'Unassigned';
                $this->duplicateWarning = "CRITICAL DUPLICATE ALERT: An employee named '{$existing->full_name}' born on {$dob} is ALREADY registered in the system (Employee ID: {$existing->employee_no}, Department: {$dept}). Re-registering the same person is forbidden.";
                return;
            }
        }

        if (!empty($this->official_email)) {
            $existingEmail = Employee::where('official_email', trim($this->official_email))->first();
            if ($existingEmail) {
                $this->duplicateWarning = "DUPLICATE EMAIL ALERT: Official email '{$this->official_email}' already belongs to existing staff member '{$existingEmail->full_name}' (#{$existingEmail->employee_no}).";
                return;
            }
        }

        if (!empty($this->phone)) {
            $existingPhone = Employee::where('phone', trim($this->phone))->first();
            if ($existingPhone) {
                $this->duplicateWarning = "DUPLICATE PHONE ALERT: Phone number '{$this->phone}' already belongs to existing staff member '{$existingPhone->full_name}' (#{$existingPhone->employee_no}).";
                return;
            }
        }
    }

    // STEP 2: Employment Information
    public $is_existing_employee = true;
    public $employee_no = '';
    public $personnel_number = '';
    public $employment_status = 'Active';
    public $employment_type = 'Permanent';
    public $department_id = '';
    public $designation = '';
    public $salary_grade = '';
    public $date_joined = '';
    public $supervisor_id = '';
    public $contract_duration = '12';
    public $contract_end_date = '';

    public function updated($property)
    {
        if (in_array($property, ['employment_type', 'date_joined', 'contract_duration'], true)) {
            $this->calculateContractEndDate();
        }
    }

    public function calculateContractEndDate()
    {
        if ($this->employment_type !== 'Contract') {
            $this->contract_end_date = '';
            return;
        }

        if ($this->contract_duration !== 'custom' && $this->date_joined && is_numeric($this->contract_duration)) {
            $this->contract_end_date = \Carbon\Carbon::parse($this->date_joined)
                ->addMonths((int) $this->contract_duration)
                ->format('Y-m-d');
        }
    }

    // STEP 3: Banking & Payroll Information
    public $bank_name = '';
    public $bank_account = '';
    public $tin_number = '';
    public $social_security_no = '';
    public $salary_amount_lrd = 0.0;
    public $salary_amount_usd = 0.0;

    // STEP 4: Academic Qualifications & Professional Certifications
    public $educations = [];
    public $new_institution = '';
    public $new_qualification = '';
    public $new_field_of_study = '';
    public $new_year_obtained = '';

    public $certifications = [];
    public $new_certification_name = '';
    public $new_issuing_body = '';
    public $new_issue_date = '';
    public $new_expiry_date = '';

    // STEP 5: Next of Kin & Emergency Contacts
    public $next_of_kin = [];
    public $kin_name = '';
    public $kin_relationship = '';
    public $kin_phone = '';
    public $kin_email = '';
    public $kin_address = '';
    public $kin_is_primary = true;

    public $emergency_contacts = [];
    public $ec_name = '';
    public $ec_relationship = '';
    public $ec_phone = '';
    public $ec_email = '';

    // STEP 6: Document Uploads
    public $contract_file;
    public $id_file;
    public $academic_attachment;
    public $supporting_documents = [];

    public function mount()
    {
        $this->educations = [];
        $this->certifications = [];
        $this->next_of_kin = [];
        $this->emergency_contacts = [];
    }

    public function nextStep()
    {
        if ($this->step === 4) {
            $this->flushPendingAcademicEntries();
        } elseif ($this->step === 5) {
            $this->flushPendingContactEntries();
        }

        $this->validateStep();
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = ['Bytes', 'KB', 'MB', 'GB'];
        $unitIndex = (int) floor(log($bytes, 1024));
        $unitIndex = min($unitIndex, count($units) - 1);

        return round($bytes / (1024 ** $unitIndex), 2) . ' ' . $units[$unitIndex];
    }

    // ── Academic Qualifications ──────────────────────────────────────────
    public function addEducation()
    {
        $this->validate([
            'new_institution'    => 'required|string|max:255',
            'new_qualification'  => 'required|string|max:255',
            'new_field_of_study' => 'nullable|string|max:255',
            'new_year_obtained'  => 'nullable|numeric|digits:4|min:1950|max:' . (now()->year + 1),
        ]);

        $this->educations[] = [
            'institution'    => trim($this->new_institution),
            'qualification'  => trim($this->new_qualification),
            'field_of_study' => trim($this->new_field_of_study) ?: null,
            'year_obtained'  => trim((string) $this->new_year_obtained) ?: null,
        ];

        $this->new_institution = '';
        $this->new_qualification = '';
        $this->new_field_of_study = '';
        $this->new_year_obtained = '';
    }

    public function removeEducation($index)
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    // ── Professional Certifications ──────────────────────────────────────
    public function addCertification()
    {
        $this->validate([
            'new_certification_name' => 'required|string|max:255',
            'new_issuing_body'       => 'nullable|string|max:255',
            'new_issue_date'         => 'nullable|date',
            'new_expiry_date'        => 'nullable|date|after_or_equal:new_issue_date',
        ]);

        $this->certifications[] = [
            'name'         => trim($this->new_certification_name),
            'issuing_body' => trim($this->new_issuing_body) ?: null,
            'issue_date'   => $this->new_issue_date ?: null,
            'expiry_date'  => $this->new_expiry_date ?: null,
        ];

        $this->new_certification_name = '';
        $this->new_issuing_body = '';
        $this->new_issue_date = '';
        $this->new_expiry_date = '';
    }

    public function removeCertification($index)
    {
        unset($this->certifications[$index]);
        $this->certifications = array_values($this->certifications);
    }

    // ── Next of Kin ──────────────────────────────────────────────────────
    public function addNextOfKin()
    {
        $this->validate([
            'kin_name'         => 'required|string|max:150',
            'kin_relationship' => 'required|string|max:100',
            'kin_phone'        => 'nullable|string|max:20',
            'kin_email'        => 'nullable|email|max:150',
            'kin_address'      => 'nullable|string|max:500',
            'kin_is_primary'   => 'boolean',
        ]);

        if ($this->kin_is_primary) {
            foreach ($this->next_of_kin as &$existingKin) {
                $existingKin['is_primary'] = false;
            }
            unset($existingKin);
        }

        $this->next_of_kin[] = [
            'name'         => trim($this->kin_name),
            'relationship' => trim($this->kin_relationship),
            'phone'        => trim($this->kin_phone) ?: null,
            'email'        => trim($this->kin_email) ?: null,
            'address'      => trim($this->kin_address) ?: null,
            'is_primary'   => (bool) $this->kin_is_primary,
        ];

        $this->kin_name = '';
        $this->kin_relationship = '';
        $this->kin_phone = '';
        $this->kin_email = '';
        $this->kin_address = '';
        $this->kin_is_primary = false;
    }

    public function removeNextOfKin($index)
    {
        unset($this->next_of_kin[$index]);
        $this->next_of_kin = array_values($this->next_of_kin);
    }

    // ── Emergency Contacts ───────────────────────────────────────────────
    public function addEmergencyContact()
    {
        $this->validate([
            'ec_name'         => 'required|string|max:150',
            'ec_relationship' => 'required|string|max:100',
            'ec_phone'        => 'required|string|max:20',
            'ec_email'        => 'nullable|email|max:150',
        ]);

        $this->emergency_contacts[] = [
            'name'         => trim($this->ec_name),
            'relationship' => trim($this->ec_relationship),
            'phone'        => trim($this->ec_phone),
            'email'        => trim($this->ec_email) ?: null,
        ];

        $this->ec_name = '';
        $this->ec_relationship = '';
        $this->ec_phone = '';
        $this->ec_email = '';
    }

    public function removeEmergencyContact($index)
    {
        unset($this->emergency_contacts[$index]);
        $this->emergency_contacts = array_values($this->emergency_contacts);
    }

    private function flushPendingAcademicEntries(): void
    {
        if (trim($this->new_institution) !== '' || trim($this->new_qualification) !== '') {
            $this->addEducation();
        }
        if (trim($this->new_certification_name) !== '') {
            $this->addCertification();
        }
    }

    private function flushPendingContactEntries(): void
    {
        if (trim($this->kin_name) !== '' || trim($this->kin_relationship) !== '') {
            $this->addNextOfKin();
        }
        if (trim($this->ec_name) !== '' || trim($this->ec_relationship) !== '' || trim($this->ec_phone) !== '') {
            $this->addEmergencyContact();
        }
    }

    private function validateStep()
    {
        if ($this->step == 1) {
            $this->validate([
                'first_name'     => 'required|string|max:100',
                'last_name'      => 'required|string|max:100',
                'gender'         => 'required|in:Male,Female',
                'date_of_birth'  => 'required|date',
                'nationality'    => 'required|string',
                'official_email' => 'required|email|unique:employees,official_email',
                'phone'          => 'required|string|unique:employees,phone',
                'photo'          => 'nullable|image|max:5120',
            ]);

            // Strict Anti-Duplicate Check
            $existing = Employee::where('first_name', trim($this->first_name))
                ->where('last_name', trim($this->last_name))
                ->whereDate('date_of_birth', $this->date_of_birth)
                ->first();

            if ($existing) {
                $dob = $existing->date_of_birth ? $existing->date_of_birth->format('Y-m-d') : '';
                throw ValidationException::withMessages([
                    'first_name' => "DUPLICATE ENROLLMENT REJECTED: '{$existing->full_name}' (DOB: {$dob}) is already enrolled as Employee #{$existing->employee_no}. Admin cannot register one person twice.",
                ]);
            }
        } elseif ($this->step == 2) {
            $rules = [
                'department_id' => 'required|exists:departments,id',
                'designation'   => 'required|string',
                'date_joined'   => 'required|date',
            ];
            if ($this->employment_type === 'Contract') {
                $rules['contract_duration'] = 'required';
                $rules['contract_end_date'] = 'required|date|after:date_joined';
            }
            if ($this->is_existing_employee) {
                $rules['employee_no'] = 'required|string|unique:employees,employee_no';
            }
            $this->validate($rules);
        } elseif ($this->step == 6) {
            $this->validate([
                'contract_file'          => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
                'id_file'                => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
                'academic_attachment'    => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
                'supporting_documents.*' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
            ]);
        }
    }

    public function save()
    {
        $this->flushPendingAcademicEntries();
        $this->flushPendingContactEntries();
        $this->validateStep();

        $temporaryPassword = 'MYSWelcome@2026!';
        $welcomeUser = null;
        $welcomeEmployee = null;

        DB::transaction(function () use ($temporaryPassword, &$welcomeUser, &$welcomeEmployee) {
            // 1. Resolve Employee Number
            $employeeNo = trim((string) $this->employee_no);
            if (!$this->is_existing_employee || $employeeNo === '') {
                $employeeNo = app(EmployeeService::class)->generateEmployeeNumber();
            }

            // 2. Handle Photo Upload or Live Camera Snapshot
            $photoPath = null;
            if ($this->photo) {
                $photoPath = $this->photo->store('employees/photos', 'public');
            } elseif (!empty($this->photo_base64)) {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $this->photo_base64);
                $fileName = 'employees/photos/' . uniqid() . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, base64_decode($imageData));
                $photoPath = $fileName;
            }

            // 3. Create Employee
            $employee = Employee::create([
                'employee_no'        => $employeeNo,
                'personnel_number'   => trim((string) $this->personnel_number) ?: null,
                'first_name'         => trim($this->first_name),
                'middle_name'        => trim((string) $this->middle_name) ?: null,
                'last_name'          => trim($this->last_name),
                'gender'             => $this->gender,
                'date_of_birth'      => $this->date_of_birth ?: null,
                'nationality'        => trim((string) $this->nationality) ?: null,
                'county_of_birth'    => trim((string) $this->county_of_birth) ?: null,
                'marital_status'     => $this->marital_status ?: null,
                'phone'              => trim((string) $this->phone) ?: null,
                'personal_email'     => trim((string) $this->personal_email) ?: null,
                'official_email'     => trim($this->official_email),
                'address'            => trim((string) $this->address) ?: null,
                'photo_path'         => $photoPath,
                'employment_status'  => $this->employment_status,
                'employment_type'    => $this->employment_type,
                'department_id'      => $this->department_id,
                'designation'        => trim($this->designation),
                'salary_grade'       => trim((string) $this->salary_grade) ?: null,
                'date_joined'        => $this->date_joined ?: null,
                'contract_end_date'  => $this->employment_type === 'Contract' ? $this->contract_end_date : null,
                'supervisor_id'      => $this->supervisor_id ?: null,
                'bank_name'          => trim((string) $this->bank_name) ?: null,
                'bank_account'       => trim((string) $this->bank_account) ?: null,
                'tin_number'         => trim((string) $this->tin_number) ?: null,
                'social_security_no' => trim((string) $this->social_security_no) ?: null,
                'salary_amount_lrd'  => (is_numeric($this->salary_amount_lrd) && $this->salary_amount_lrd !== '') ? (float) $this->salary_amount_lrd : 0.00,
                'salary_amount_usd'  => (is_numeric($this->salary_amount_usd) && $this->salary_amount_usd !== '') ? (float) $this->salary_amount_usd : 0.00,
                'created_by'         => auth()->id(),
            ]);

            // 4. Create User linked account
            $user = User::create([
                'name'                  => $employee->full_name,
                'email'                 => $employee->official_email,
                'password'              => Hash::make($temporaryPassword),
                'employee_id'           => $employee->id,
                'is_active'             => true,
                'force_password_change' => true,
            ]);
            $user->assignRole('Employee');
            $welcomeUser = $user;
            $welcomeEmployee = $employee;

            // 5. Save Academic Records
            foreach ($this->educations as $edu) {
                EmployeeEducation::create([
                    'employee_id'    => $employee->id,
                    'institution'    => $edu['institution'],
                    'qualification'  => $edu['qualification'],
                    'field_of_study' => $edu['field_of_study'] ?? null,
                    'year_obtained'  => $edu['year_obtained'] ?? null,
                ]);
            }

            // 6. Save Certifications
            foreach ($this->certifications as $cert) {
                EmployeeCertification::create([
                    'employee_id'  => $employee->id,
                    'name'         => $cert['name'],
                    'issuing_body' => $cert['issuing_body'] ?? null,
                    'issue_date'   => $cert['issue_date'] ?? null,
                    'expiry_date'  => $cert['expiry_date'] ?? null,
                ]);
            }

            // 7. Save Next of Kin
            foreach ($this->next_of_kin as $kin) {
                EmployeeNextOfKin::create([
                    'employee_id'  => $employee->id,
                    'name'         => $kin['name'],
                    'relationship' => $kin['relationship'],
                    'phone'        => $kin['phone'] ?? null,
                    'email'        => $kin['email'] ?? null,
                    'address'      => $kin['address'] ?? null,
                    'is_primary'   => (bool) ($kin['is_primary'] ?? false),
                ]);
            }

            // 8. Save Emergency Contacts
            foreach ($this->emergency_contacts as $contact) {
                EmployeeEmergencyContact::create([
                    'employee_id'  => $employee->id,
                    'name'         => $contact['name'],
                    'relationship' => $contact['relationship'],
                    'phone'        => $contact['phone'],
                    'email'        => $contact['email'] ?? null,
                ]);
            }

            // 9. Save Contract/ID Document Uploads
            if ($this->contract_file) {
                $cPath = $this->contract_file->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'contract',
                    'title'         => 'Employment Contract',
                    'file_path'     => $cPath,
                    'file_size'     => $this->formatBytes($this->contract_file->getSize()),
                    'mime_type'     => $this->contract_file->getMimeType(),
                    'uploaded_by'   => auth()->id(),
                ]);
            }
            if ($this->id_file) {
                $idPath = $this->id_file->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'id_card',
                    'title'         => 'National ID Card',
                    'file_path'     => $idPath,
                    'file_size'     => $this->formatBytes($this->id_file->getSize()),
                    'mime_type'     => $this->id_file->getMimeType(),
                    'uploaded_by'   => auth()->id(),
                ]);
            }
            if ($this->academic_attachment) {
                $path = $this->academic_attachment->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'academic',
                    'title'         => 'Academic Certificate / Attachment',
                    'file_path'     => $path,
                    'file_size'     => $this->formatBytes($this->academic_attachment->getSize()),
                    'mime_type'     => $this->academic_attachment->getMimeType(),
                    'uploaded_by'   => auth()->id(),
                ]);
            }
            foreach ($this->supporting_documents as $document) {
                if (!$document) {
                    continue;
                }
                $path = $document->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'supporting',
                    'title'         => $document->getClientOriginalName(),
                    'file_path'     => $path,
                    'file_size'     => $this->formatBytes($document->getSize()),
                    'mime_type'     => $document->getMimeType(),
                    'uploaded_by'   => auth()->id(),
                ]);
            }

            AuditService::log(
                'created',
                Employee::class,
                $employee->id,
                [],
                $employee->toArray(),
                "Registered employee record {$employee->full_name} ({$employee->employee_no})"
            );
        });

        $emailFailed = false;
        if ($welcomeUser?->email && $welcomeEmployee) {
            try {
                Mail::to($welcomeUser->email)->queue(new EmployeeWelcomeCredentials(
                    $welcomeEmployee->full_name,
                    $welcomeEmployee->employee_no,
                    $welcomeEmployee->employment_type === 'Contract' ? 'Contractor' : $welcomeEmployee->employment_type,
                    $welcomeUser->email,
                    $temporaryPassword,
                    route('login'),
                ));
            } catch (\Throwable $e) {
                $emailFailed = true;
                Log::warning('Welcome credentials email failed during employee registration.', [
                    'employee_id' => $welcomeEmployee->id,
                    'email'       => $welcomeUser->email,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        if ($emailFailed) {
            session()->flash(
                'warning',
                "Employee {$welcomeEmployee->full_name} ({$welcomeEmployee->employee_no}) was registered successfully, but the welcome email could not be sent. Please check the Ministry mail configuration."
            );
        } else {
            session()->flash('success', "Employee {$welcomeEmployee->full_name} ({$welcomeEmployee->employee_no}) registered successfully!");
        }

        return redirect()->route('employees.show', $welcomeEmployee);
    }

    public function render()
    {
        $departments = Department::active()->orderBy('name')->get();
        $supervisors = Employee::active()->orderBy('first_name')->get();

        return view('livewire.employees.create', compact('departments', 'supervisors'));
    }
}
