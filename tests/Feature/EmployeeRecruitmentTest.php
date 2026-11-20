<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeRecruitmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_apply_for_open_vacancy_and_track_application(): void
    {
        $department = Department::create(['name' => 'Human Resources', 'code' => 'REC', 'is_active' => true]);
        $employee = Employee::create([
            'employee_no' => 'MYS-REC-0001', 'first_name' => 'Alex', 'last_name' => 'Doe',
            'gender' => 'Male', 'designation' => 'Officer', 'employment_type' => 'Permanent',
            'employment_status' => 'Active', 'date_joined' => now()->toDateString(),
            'official_email' => 'alex.doe@example.com', 'department_id' => $department->id,
        ]);
        $vacancy = Vacancy::create([
            'title' => 'Senior Programme Officer', 'department_id' => $department->id,
            'description' => 'Lead programmes.', 'requirements' => 'Relevant experience.',
            'positions' => 1, 'status' => 'Open', 'closing_date' => now()->addDays(14)->toDateString(),
        ]);

        Permission::firstOrCreate(['name' => 'view dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view internal vacancies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'apply vacancies', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $role->givePermissionTo(['view dashboard', 'view internal vacancies', 'apply vacancies']);
        $user = User::create(['name' => 'Alex Doe', 'email' => 'alex.rec@example.com', 'password' => bcrypt('secret123'), 'is_active' => true, 'employee_id' => $employee->id]);
        $user->assignRole($role);

        $this->actingAs($user)->get(route('employee.recruitment'))->assertOk()->assertSee('Senior Programme Officer');
        $this->actingAs($user)->post(route('employee.recruitment.apply', $vacancy), ['cover_letter' => 'I am interested in this opportunity.'])->assertRedirect();

        $this->assertDatabaseHas('job_applications', ['vacancy_id' => $vacancy->id, 'employee_id' => $employee->id, 'status' => 'Applied']);
        $this->actingAs($user)->get(route('employee.recruitment'))->assertSee('My Applications')->assertSee('Applied');
    }
}
