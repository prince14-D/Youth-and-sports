<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── System Administrator Employee Record ───────────────────────────
        $adminEmployee = Employee::firstOrCreate(
            ['employee_no' => 'MYS-2024-0001'],
            [
                'first_name'        => 'System',
                'last_name'         => 'Administrator',
                'gender'            => 'Male',
                'date_of_birth'     => '1980-01-01',
                'nationality'       => 'Liberian',
                'department_id'     => null,
                'designation'       => 'System Administrator',
                'employment_type'   => 'Permanent',
                'employment_status' => 'Active',
                'date_joined'       => now(),
                'official_email'    => 'admin@mys.gov.lr',
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mys.gov.lr'],
            [
                'name'        => 'System Administrator',
                'password'    => Hash::make('Admin@MYS2024!'),
                'employee_id' => $adminEmployee->id,
                'is_active'   => true,
            ]
        );
        $adminUser->assignRole('System Administrator');

        $emp = Employee::firstOrCreate(
            ['employee_no' => 'MYS-2024-0002'],
            [
                'first_name'        => 'Default',
                'last_name'         => 'Employee',
                'gender'            => 'Male',
                'date_of_birth'     => '1995-01-01',
                'nationality'       => 'Liberian',
                'designation'       => 'Staff Officer',
                'employment_type'   => 'Permanent',
                'employment_status' => 'Active',
                'date_joined'       => now(),
                'official_email'    => 'employee@mys.gov.lr',
            ]
        );
        $empUser = User::firstOrCreate(
            ['email' => 'employee@mys.gov.lr'],
            [
                'name'        => 'Default Employee',
                'password'    => Hash::make('Employee@MYS2024!'),
                'employee_id' => $emp->id,
                'is_active'   => true,
            ]
        );
        $empUser->assignRole('Employee');

        $this->command->info('✅ Default users created:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['System Administrator', 'admin@mys.gov.lr', 'Admin@MYS2024!'],
            ]
        );
    }
}
