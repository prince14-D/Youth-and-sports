<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeEducation;
use App\Models\EmployeeCertification;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    public $date_joined = '';
    public $supervisor_id = '';

    // STEP 3: Banking & Payroll Information
    public $bank_name = '';
    public $bank_account = '';
    public $tin_number = '';
    public $social_security_no = '';
    public $salary_amount_lrd = 0.0;
    public $salary_amount_usd = 0.0;

    // STEP 4: Academic Information (Allows Multiple)
    public $educations = [];
    public $new_institution = '';
    public $new_qualification = '';
    public $new_graduation_year = '';

    // STEP 5: Professional Certifications (Allows Multiple)
    public $certifications = [];
    public $new_certification = '';
    public $new_organization = '';
    public $new_date_awarded = '';

    // STEP 6: Document Uploads
    public $contract_file;
    public $id_file;

    public function mount()
    {
        $this->educations = [];
        $this->certifications = [];
    }

    public function nextStep()
    {
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

    public function addEducation()
    {
        $this->validate([
            'new_institution'     => 'required|string',
            'new_qualification'   => 'required|string',
            'new_graduation_year' => 'required|numeric|digits:4',
        ]);

        $this->educations[] = [
            'institution'     => $this->new_institution,
            'qualification'   => $this->new_qualification,
            'graduation_year' => $this->new_graduation_year,
        ];

        $this->new_institution = '';
        $this->new_qualification = '';
        $this->new_graduation_year = '';
    }

    public function removeEducation($index)
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    public function addCertification()
    {
        $this->validate([
            'new_certification' => 'required|string',
            'new_organization'  => 'required|string',
            'new_date_awarded'  => 'required|date',
        ]);

        $this->certifications[] = [
            'certification' => $this->new_certification,
            'organization'  => $this->new_organization,
            'date_awarded'  => $this->new_date_awarded,
        ];

        $this->new_certification = '';
        $this->new_organization = '';
        $this->new_date_awarded = '';
    }

    public function removeCertification($index)
    {
        unset($this->certifications[$index]);
        $this->certifications = array_values($this->certifications);
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
                $this->addError('first_name', "DUPLICATE ENROLLMENT REJECTED: '{$existing->full_name}' (DOB: {$dob}) is already enrolled as Employee #{$existing->employee_no}. Admin cannot register one person twice.");
                return;
            }
        } elseif ($this->step == 2) {
            $rules = [
                'department_id' => 'required|exists:departments,id',
                'designation'   => 'required|string',
                'date_joined'   => 'required|date',
            ];
            if ($this->is_existing_employee) {
                $rules['employee_no'] = 'required|string|unique:employees,employee_no';
            }
            $this->validate($rules);
        }
    }

    public function save()
    {
        $this->validateStep();

        $temporaryPassword = 'MYSWelcome@2026!';
        $welcomeUser = null;
        $welcomeEmployee = null;

        DB::transaction(function () use ($temporaryPassword, &$welcomeUser, &$welcomeEmployee) {
            // 1. Resolve Employee Number
            $employeeNo = $this->employee_no;
            if (!$this->is_existing_employee) {
                $year = now()->format('Y');
                $count = Employee::whereYear('created_at', $year)->count() + 1;
                $employeeNo = 'MYS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
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
                'personnel_number'   => $this->personnel_number ?: null,
                'first_name'         => $this->first_name,
                'middle_name'        => $this->middle_name ?: null,
                'last_name'          => $this->last_name,
                'gender'             => $this->gender,
                'date_of_birth'      => $this->date_of_birth ?: null,
                'nationality'        => $this->nationality ?: null,
                'county_of_birth'    => $this->county_of_birth ?: null,
                'marital_status'     => $this->marital_status ?: null,
                'phone'              => $this->phone ?: null,
                'personal_email'     => $this->personal_email ?: null,
                'official_email'     => $this->official_email,
                'address'            => $this->address ?: null,
                'photo_path'         => $photoPath,
                'employment_status'  => $this->employment_status,
                'employment_type'    => $this->employment_type,
                'department_id'      => $this->department_id,
                'designation'        => $this->designation,
                'date_joined'        => $this->date_joined ?: null,
                'supervisor_id'      => $this->supervisor_id ?: null,
                'bank_name'          => $this->bank_name ?: null,
                'bank_account'       => $this->bank_account ?: null,
                'tin_number'         => $this->tin_number ?: null,
                'social_security_no' => $this->social_security_no ?: null,
                'salary_amount_lrd'  => (is_numeric($this->salary_amount_lrd) && $this->salary_amount_lrd !== '') ? (float)$this->salary_amount_lrd : 0.00,
                'salary_amount_usd'  => (is_numeric($this->salary_amount_usd) && $this->salary_amount_usd !== '') ? (float)$this->salary_amount_usd : 0.00,
            ]);

            // 4. Create User linked account
            $user = User::create([
                'name'        => $employee->full_name,
                'email'       => $employee->official_email,
                'password'    => Hash::make($temporaryPassword),
                'employee_id' => $employee->id,
                'is_active'   => true,
                'force_password_change' => true,
            ]);
            $user->assignRole('Employee');
            $welcomeUser = $user;
            $welcomeEmployee = $employee;

            // 5. Save Academic Records
            foreach ($this->educations as $edu) {
                EmployeeEducation::create([
                    'employee_id'     => $employee->id,
                    'institution'     => $edu['institution'],
                    'qualification'   => $edu['qualification'],
                    'graduation_year' => $edu['graduation_year'],
                ]);
            }

            // 6. Save Certifications
            foreach ($this->certifications as $cert) {
                EmployeeCertification::create([
                    'employee_id'   => $employee->id,
                    'certification' => $cert['certification'],
                    'organization'  => $cert['organization'],
                    'date_awarded'  => $cert['date_awarded'],
                ]);
            }

            // 7. Save Contract/ID Document Uploads
            if ($this->contract_file) {
                $cPath = $this->contract_file->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'contract',
                    'title'         => 'Employment Contract',
                    'file_path'     => $cPath,
                ]);
            }
            if ($this->id_file) {
                $idPath = $this->id_file->store('employees/documents', 'public');
                EmployeeDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => 'id_card',
                    'title'         => 'National ID Card',
                    'file_path'     => $idPath,
                ]);
            }

            AuditService::log('created', Employee::class, $employee->id,
                [], $employee->toArray(),
                "Registered employee record {$employee->full_name} and auto-generated code {$employee->employee_no}"
            );
        });

        if ($welcomeUser?->email && $welcomeEmployee) {
            Mail::to($welcomeUser->email)->queue(new EmployeeWelcomeCredentials(
                $welcomeEmployee->full_name,
                $welcomeEmployee->employee_no,
                $welcomeEmployee->employment_type === 'Contract' ? 'Contractor' : $welcomeEmployee->employment_type,
                $welcomeUser->email,
                $temporaryPassword,
                route('login'),
            ));
        }

        session()->flash('success', 'Employee registered successfully!');
        return redirect()->route('employees.index');
    }

    public function render()
    {
        $departments = Department::active()->orderBy('name')->get();
        $supervisors = Employee::active()->orderBy('first_name')->get();

        return view('livewire.employees.create', compact('departments', 'supervisors'));
    }
}
