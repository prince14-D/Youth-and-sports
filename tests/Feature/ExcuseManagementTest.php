<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\ExcuseApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeUpdate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExcuseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_excuse_and_hr_can_approve_it(): void
    {
        Mail::fake();
        $department = Department::create(['name' => 'Operations', 'code' => 'EXC', 'is_active' => true]);
        $employee = Employee::create([
            'employee_no' => 'MYS-EXC-0001', 'first_name' => 'Jane', 'last_name' => 'Doe',
            'gender' => 'Female', 'designation' => 'Officer', 'employment_type' => 'Permanent',
            'employment_status' => 'Active', 'date_joined' => now()->toDateString(),
            'department_id' => $department->id,
        ]);

        Permission::firstOrCreate(['name' => 'submit excuse', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view excuses', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'approve excuses', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employeeRole->givePermissionTo('submit excuse');
        $hrRole = Role::firstOrCreate(['name' => 'HR Officer', 'guard_name' => 'web']);
        $hrRole->givePermissionTo(['view excuses', 'approve excuses']);

        $employeeUser = User::create([
            'name' => 'Jane Doe', 'email' => 'jane.excuse@example.com',
            'password' => bcrypt('secret123'), 'is_active' => true, 'employee_id' => $employee->id,
        ]);
        $employeeUser->assignRole($employeeRole);

        $this->actingAs($employeeUser)
            ->get(route('excuses.index'))
            ->assertOk()
            ->assertSee('Excuse Management');

        $response = $this->actingAs($employeeUser)->post(route('excuses.store'), [
            'employee_id' => $employee->id,
            'excuse_date' => now()->subDay()->toDateString(),
            'excuse_type' => 'Medical',
            'reason' => 'Medical appointment with supporting documentation.',
        ]);

        $response->assertRedirect(route('excuses.index'));
        $excuse = ExcuseApplication::firstOrFail();
        $this->assertSame('Pending', $excuse->status);
        Mail::assertQueued(EmployeeUpdate::class, fn ($mail) => $mail->title === 'Excuse submitted');

        $hrUser = User::create([
            'name' => 'HR Officer', 'email' => 'hr.excuse@example.com',
            'password' => bcrypt('secret123'), 'is_active' => true,
        ]);
        $hrUser->assignRole($hrRole);

        $this->actingAs($hrUser)
            ->post(route('excuses.approve', $excuse))
            ->assertRedirect();

        $this->assertSame('Approved', $excuse->fresh()->status);
        Mail::assertQueued(EmployeeUpdate::class, fn ($mail) => $mail->title === 'Excuse approved');
    }
}
