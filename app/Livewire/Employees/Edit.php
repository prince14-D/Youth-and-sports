<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use WithFileUploads;

    public Employee $employee;

    // Personal Information
    public $first_name;
    public $middle_name;
    public $last_name;
    public $gender;
    public $date_of_birth;
    public $nationality;
    public $county_of_birth;
    public $marital_status;
    public $phone;
    public $personal_email;
    public $official_email;
    public $address;
    public $photo;

    // Employment Information
    public $employee_no;
    public $personnel_number;
    public $employment_status;
    public $employment_type;
    public $department_id;
    public $designation;
    public $date_joined;
    public $supervisor_id;
    public $contract_duration = '12';
    public $contract_end_date;

    // Banking & Payroll Information
    public $bank_name;
    public $bank_account;
    public $tin_number;
    public $social_security_no;
    public $salary_amount_lrd;
    public $salary_amount_usd;

    public $canEditEmployeeNo = false;

    public function updated($property)
    {
        if (in_array($property, ['employment_type', 'date_joined', 'contract_duration'], true)) {
            $this->calculateContractEndDate();
        }
    }

    public function calculateContractEndDate()
    {
        if ($this->employment_type !== 'Contract') {
            $this->contract_end_date = null;
            return;
        }

        if ($this->contract_duration !== 'custom' && $this->date_joined && is_numeric($this->contract_duration)) {
            $this->contract_end_date = \Carbon\Carbon::parse($this->date_joined)
                ->addMonths((int) $this->contract_duration)
                ->format('Y-m-d');
        }
    }

    public function mount(Employee $employee)
    {
        $this->employee = $employee;

        // Populate fields
        $this->first_name        = $employee->first_name;
        $this->middle_name       = $employee->middle_name;
        $this->last_name         = $employee->last_name;
        $this->gender            = $employee->gender;
        $this->date_of_birth     = $employee->date_of_birth?->format('Y-m-d');
        $this->nationality       = $employee->nationality;
        $this->county_of_birth   = $employee->county_of_birth;
        $this->marital_status    = $employee->marital_status;
        $this->phone             = $employee->phone;
        $this->personal_email    = $employee->personal_email;
        $this->official_email    = $employee->official_email;
        $this->address           = $employee->address;

        $this->employee_no       = $employee->employee_no;
        $this->personnel_number  = $employee->personnel_number;
        $this->employment_status = $employee->employment_status;
        $this->employment_type   = $employee->employment_type;
        $this->department_id     = $employee->department_id;
        $this->designation       = $employee->designation;
        $this->date_joined       = $employee->date_joined?->format('Y-m-d');
        $this->supervisor_id     = $employee->supervisor_id;
        $this->contract_end_date = $employee->contract_end_date?->format('Y-m-d');
        $this->contract_duration = $employee->contract_end_date ? 'custom' : '12';

        $this->bank_name         = $employee->bank_name;
        $this->bank_account      = $employee->bank_account;
        $this->tin_number        = $employee->tin_number;
        $this->social_security_no= $employee->social_security_no;
        $this->salary_amount_lrd = $employee->salary_amount_lrd;
        $this->salary_amount_usd = $employee->salary_amount_usd;

        // Check if user is System Administrator
        $user = Auth::user();
        $this->canEditEmployeeNo = $user && ($user->hasRole('System Administrator') || $user->hasRole('Super Admin'));
    }

    public function save()
    {
        $rules = [
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'gender'         => 'required|in:Male,Female',
            'official_email' => 'required|email|unique:employees,official_email,' . $this->employee->id,
            'department_id'  => 'required|exists:departments,id',
            'designation'    => 'required|string',
            'employment_status' => 'required|string',
            'photo'            => 'nullable|image|max:5120',
        ];

        if ($this->employment_type === 'Contract') {
            $rules['contract_duration'] = 'required';
            $rules['contract_end_date'] = 'required|date|after:date_joined';
        }

        if ($this->canEditEmployeeNo) {
            $rules['employee_no'] = 'required|string|unique:employees,employee_no,' . $this->employee->id;
        }

        $this->validate($rules);

        // Track changes for Audit Log
        $oldValues = $this->employee->only([
            'employee_no', 'personnel_number', 'first_name', 'last_name', 'official_email',
            'department_id', 'designation', 'employment_status', 'salary_amount_lrd', 'salary_amount_usd'
        ]);

        // Handle optional Photo upload
        if ($this->photo) {
            if ($this->employee->photo_path) {
                Storage::disk('public')->delete($this->employee->photo_path);
            }
            $photoPath = $this->photo->store('employees/photos', 'public');
            $this->employee->photo_path = $photoPath;
        }

        // Update fields
        if ($this->canEditEmployeeNo) {
            $this->employee->employee_no = $this->employee_no;
        }

        $this->employee->fill([
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
            'employment_status'  => $this->employment_status,
            'employment_type'    => $this->employment_type,
            'department_id'      => $this->department_id,
            'designation'        => $this->designation,
            'date_joined'        => $this->date_joined ?: null,
            'contract_end_date'  => $this->employment_type === 'Contract' ? $this->contract_end_date : null,
            'supervisor_id'      => $this->supervisor_id ?: null,
            'bank_name'          => $this->bank_name ?: null,
            'bank_account'       => $this->bank_account ?: null,
            'tin_number'         => $this->tin_number ?: null,
            'social_security_no' => $this->social_security_no ?: null,
            'salary_amount_lrd'  => (is_numeric($this->salary_amount_lrd) && $this->salary_amount_lrd !== '') ? (float)$this->salary_amount_lrd : 0.00,
            'salary_amount_usd'  => (is_numeric($this->salary_amount_usd) && $this->salary_amount_usd !== '') ? (float)$this->salary_amount_usd : 0.00,
        ]);

        $this->employee->save();

        $newValues = $this->employee->only([
            'employee_no', 'personnel_number', 'first_name', 'last_name', 'official_email',
            'department_id', 'designation', 'employment_status', 'salary_amount_lrd', 'salary_amount_usd'
        ]);

        // Audit Logging
        AuditService::log('updated', Employee::class, $this->employee->id, $oldValues, $newValues, "Updated employee profile {$this->employee->full_name}");

        session()->flash('success', 'Employee profile updated successfully!');
        return redirect()->route('employees.show', $this->employee);
    }

    public function render()
    {
        $departments = Department::active()->orderBy('name')->get();
        $supervisors = Employee::active()->where('id', '!=', $this->employee->id)->orderBy('first_name')->get();

        return view('livewire.employees.edit', compact('departments', 'supervisors'));
    }
}
