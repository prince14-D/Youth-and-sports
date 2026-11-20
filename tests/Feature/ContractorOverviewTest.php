<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractorOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractors_index_and_profile_are_accessible(): void
    {
        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
            'is_active' => true,
        ]);

        $contractor = Employee::create([
            'employee_no' => 'MYS-2026-0002',
            'first_name' => 'Nora',
            'last_name' => 'Kollie',
            'gender' => 'Female',
            'designation' => 'Contract Technician',
            'employment_type' => 'Contract',
            'employment_status' => 'Active',
            'date_joined' => now()->toDateString(),
            'department_id' => $department->id,
        ]);

        Permission::firstOrCreate(['name' => 'view employees', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'System Administrator', 'guard_name' => 'web']);
        $role->givePermissionTo('view employees');

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'is_active' => true,
            'employee_id' => $contractor->id,
        ]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/contractors')
            ->assertOk()
            ->assertSee('Total Contractors');

        $this->actingAs($user)
            ->get('/contractors/' . $contractor->id)
            ->assertOk()
            ->assertSee('Nora Kollie');
    }
}
