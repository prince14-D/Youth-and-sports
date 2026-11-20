<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Mail\HrBroadcastEmail;

class HrEmailCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_send_email_to_a_department_and_history_is_recorded(): void
    {
        Mail::fake();
        $department = Department::create(['name' => 'Administration', 'code' => 'MAIL', 'is_active' => true]);
        $employee = Employee::create(['employee_no' => 'MYS-MAIL-001', 'first_name' => 'Mail', 'last_name' => 'Test', 'gender' => 'Male', 'designation' => 'Officer', 'employment_type' => 'Permanent', 'employment_status' => 'Active', 'date_joined' => now()->toDateString(), 'official_email' => 'mail.test@example.com', 'department_id' => $department->id]);
        Permission::firstOrCreate(['name' => 'manage email communications', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'HR Officer', 'guard_name' => 'web']);
        $role->givePermissionTo('manage email communications');
        $user = User::create(['name' => 'HR Officer', 'email' => 'hr.mail@example.com', 'password' => bcrypt('secret123'), 'is_active' => true]);
        $user->assignRole($role);

        $this->actingAs($user)->post(route('communications.email.send'), ['recipient_scope' => 'department', 'department_id' => $department->id, 'subject' => 'HR update', 'body' => 'Please review this HR update.'])->assertRedirect();
        $this->assertDatabaseHas('email_communications', ['subject' => 'HR update', 'recipient_count' => 1, 'status' => 'Sent']);
        Mail::assertQueued(HrBroadcastEmail::class);
    }
}
