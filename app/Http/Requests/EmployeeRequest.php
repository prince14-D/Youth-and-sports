<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            // Personal
            'first_name'        => 'required|string|max:60',
            'middle_name'       => 'nullable|string|max:60',
            'last_name'         => 'required|string|max:60',
            'gender'            => 'required|in:Male,Female,Other',
            'date_of_birth'     => 'nullable|date|before:today',
            'nationality'       => 'nullable|string|max:50',
            'county_of_birth'   => 'nullable|string|max:50',
            'marital_status'    => 'nullable|in:Single,Married,Divorced,Widowed',
            'address'           => 'nullable|string|max:500',
            'phone'             => 'nullable|string|max:20',
            'personal_email'    => 'nullable|email|max:100',
            'official_email'    => [
                'nullable', 'email', 'max:100',
                Rule::unique('employees', 'official_email')->ignore($employeeId),
            ],
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Employment
            'department_id'     => 'nullable|exists:departments,id',
            'designation'       => 'required|string|max:100',
            'job_title'         => 'nullable|string|max:100',
            'employment_type'   => 'required|in:Permanent,Contract,Casual,Intern,Probationary,Temporary',
            'employment_status' => 'required|in:Active,Inactive,On Leave,Suspended,Retired,Resigned,Terminated,Deceased,Probationary',
            'date_joined'       => 'required|date',
            'contract_end_date' => 'nullable|date|after:date_joined',
            'supervisor_id'     => ['nullable', 'exists:employees,id', Rule::notIn([$employeeId])],

            // Salary
            'salary_grade'      => 'nullable|string|max:20',
            'salary_amount_lrd' => 'nullable|numeric|min:0',
            'salary_amount_usd' => 'nullable|numeric|min:0',

            // Bank & Identity
            'bank_name'         => 'nullable|string|max:100',
            'bank_account'      => 'nullable|string|max:30',
            'tin_number'        => 'nullable|string|max:30',
            'social_security_no'=> 'nullable|string|max:30',

            // Education (array)
            'education'                 => 'nullable|array',
            'education.*.institution'   => 'nullable|string|max:200',
            'education.*.qualification' => 'nullable|string|max:100',
            'education.*.field_of_study'=> 'nullable|string|max:100',
            'education.*.year_obtained' => 'nullable|integer|min:1950|max:' . now()->year,

            // Next of Kin (array)
            'next_of_kin'               => 'nullable|array',
            'next_of_kin.*.name'        => 'nullable|string|max:100',
            'next_of_kin.*.relationship'=> 'nullable|string|max:50',
            'next_of_kin.*.phone'       => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'    => 'First name is required.',
            'last_name.required'     => 'Last name is required.',
            'gender.required'        => 'Gender is required.',
            'designation.required'   => 'Designation/Job Title is required.',
            'employment_type.required' => 'Employment type is required.',
            'employment_status.required' => 'Employment status is required.',
            'date_joined.required'   => 'Date of joining is required.',
        ];
    }
}
