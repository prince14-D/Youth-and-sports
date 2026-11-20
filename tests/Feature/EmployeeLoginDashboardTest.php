<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeLoginDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_user_is_redirected_to_employee_dashboard_after_login(): void
    {
        Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_no' => 'MYS-2026-0123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'Male',
            'designation' => 'Junior Officer',
            'employment_type' => 'Permanent',
            'employment_status' => 'Active',
            'date_joined' => now()->toDateString(),
            'department_id' => 1,
        ]);

        Permission::firstOrCreate(['name' => 'view dashboard', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $role->givePermissionTo('view dashboard');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
            'is_active' => true,
            'employee_id' => $employee->id,
        ]);
        $user->assignRole($role);

        $this->assertTrue($user->fresh()->can('view dashboard'));

        $response = $this->from('/login')->post('/login', [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Employee Dashboard');
    }
}
