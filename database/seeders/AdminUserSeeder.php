<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $deptByCode = Department::pluck('id', 'code');
        $hrDepartmentId = $deptByCode['HR']
            ?? Department::where('name', 'like', '%Human Resource%')->value('id')
            ?? Department::value('id');

        $accounts = [
            [
                'employee_no'       => 'MYS-2026-MIN1',
                'first_name'        => 'Office of',
                'last_name'         => 'The Minister',
                'gender'            => 'Male',
                'date_of_birth'     => '1975-03-10',
                'designation'       => 'Minister of Youth & Sports',
                'employment_type'   => 'Permanent',
                'email'             => 'minister@mys.gov.lr',
                'password'          => 'Minister@MYS2026!',
                'role'              => 'Minister',
                'department_id'     => $deptByCode['MINISTER'] ?? $hrDepartmentId,
                'salary_lrd'        => 250000,
                'salary_usd'        => 1250,
            ],
            [
                'employee_no'       => 'MYS-2026-DMA1',
                'first_name'        => 'Deputy Minister',
                'last_name'         => 'Administration (DMA)',
                'gender'            => 'Male',
                'date_of_birth'     => '1977-09-18',
                'designation'       => 'Deputy Minister for Administration',
                'employment_type'   => 'Permanent',
                'email'             => 'dma@mys.gov.lr',
                'password'          => 'DMA@MYS2026!',
                'role'              => 'Deputy Minister for Administration',
                'department_id'     => $deptByCode['DMA'] ?? $hrDepartmentId,
                'salary_lrd'        => 210000,
                'salary_usd'        => 1050,
            ],
            [
                'employee_no'       => 'MYS-2026-FIN1',
                'first_name'        => 'Finance',
                'last_name'         => 'Director',
                'gender'            => 'Female',
                'date_of_birth'     => '1982-04-20',
                'designation'       => 'Director of Finance',
                'employment_type'   => 'Permanent',
                'email'             => 'finance@mys.gov.lr',
                'password'          => 'Finance@MYS2026!',
                'role'              => 'Finance Officer',
                'department_id'     => $deptByCode['FIN'] ?? $hrDepartmentId,
                'salary_lrd'        => 140000,
                'salary_usd'        => 700,
            ],
            [
                'employee_no'       => 'MYS-2026-PRC1',
                'first_name'        => 'Procurement',
                'last_name'         => 'Director',
                'gender'            => 'Female',
                'date_of_birth'     => '1983-12-02',
                'designation'       => 'Head of Procurement Unit',
                'employment_type'   => 'Permanent',
                'email'             => 'procurement@mys.gov.lr',
                'password'          => 'Procurement@MYS2026!',
                'role'              => 'Procurement Officer',
                'department_id'     => $deptByCode['PROC'] ?? $hrDepartmentId,
                'salary_lrd'        => 130000,
                'salary_usd'        => 650,
            ],
            [
                'employee_no'       => 'MYS-2026-STD1',
                'first_name'        => 'Stadium',
                'last_name'         => 'Director',
                'gender'            => 'Male',
                'date_of_birth'     => '1980-05-22',
                'designation'       => 'Director of Stadium Management',
                'employment_type'   => 'Permanent',
                'email'             => 'stadium@mys.gov.lr',
                'password'          => 'Stadium@MYS2026!',
                'role'              => 'Stadium Manager',
                'department_id'     => $deptByCode['STADIUM'] ?? $hrDepartmentId,
                'salary_lrd'        => 125000,
                'salary_usd'        => 625,
            ],
            [
                'employee_no'       => 'MYS-2026-AUD1',
                'first_name'        => 'Internal Audit',
                'last_name'         => 'Director',
                'gender'            => 'Male',
                'date_of_birth'     => '1981-08-14',
                'designation'       => 'Director of Internal Audit',
                'employment_type'   => 'Permanent',
                'email'             => 'audit@mys.gov.lr',
                'password'          => 'Audit@MYS2026!',
                'role'              => 'Auditor',
                'department_id'     => $deptByCode['AUDIT'] ?? $hrDepartmentId,
                'salary_lrd'        => 145000,
                'salary_usd'        => 725,
            ],
            [
                'employee_no'       => 'MYS-2026-ME01',
                'first_name'        => 'M&E',
                'last_name'         => 'Director',
                'gender'            => 'Female',
                'date_of_birth'     => '1985-06-12',
                'designation'       => 'Head of Monitoring & Evaluation',
                'employment_type'   => 'Permanent',
                'email'             => 'me.head@mys.gov.lr',
                'password'          => 'DeptHead@MYS2026!',
                'role'              => 'Department Head',
                'department_id'     => $deptByCode['ME'] ?? $hrDepartmentId,
                'salary_lrd'        => 120000,
                'salary_usd'        => 600,
            ],
            [
                'employee_no'       => 'MYS-2026-PDU1',
                'first_name'        => 'PDU',
                'last_name'         => 'Coordinator',
                'gender'            => 'Male',
                'date_of_birth'     => '1984-02-28',
                'designation'       => 'Head of Project Delivery Unit',
                'employment_type'   => 'Permanent',
                'email'             => 'pdu.head@mys.gov.lr',
                'password'          => 'DeptHead@MYS2026!',
                'role'              => 'Department Head',
                'department_id'     => $deptByCode['PDU'] ?? $hrDepartmentId,
                'salary_lrd'        => 120000,
                'salary_usd'        => 600,
            ],
            [
                'employee_no'       => 'MYS-2026-NYPP',
                'first_name'        => 'NYPP',
                'last_name'         => 'Director',
                'gender'            => 'Female',
                'date_of_birth'     => '1986-10-05',
                'designation'       => 'Director of NYPP',
                'employment_type'   => 'Permanent',
                'email'             => 'nypp.head@mys.gov.lr',
                'password'          => 'DeptHead@MYS2026!',
                'role'              => 'Department Head',
                'department_id'     => $deptByCode['NYPP'] ?? $hrDepartmentId,
                'salary_lrd'        => 120000,
                'salary_usd'        => 600,
            ],
            [
                'employee_no'       => 'MYS-2026-AST1',
                'first_name'        => 'Asset',
                'last_name'         => 'Manager',
                'gender'            => 'Male',
                'date_of_birth'     => '1984-07-12',
                'designation'       => 'Director of Asset & Property Management',
                'employment_type'   => 'Permanent',
                'email'             => 'assets@mys.gov.lr',
                'password'          => 'Assets@MYS2026!',
                'role'              => 'Asset Manager',
                'department_id'     => $deptByCode['ASSETS'] ?? $hrDepartmentId,
                'salary_lrd'        => 110000,
                'salary_usd'        => 550,
            ],
            [
                'employee_no'       => 'MYS-2024-0001',
                'first_name'        => 'System',
                'last_name'         => 'Administrator',
                'gender'            => 'Male',
                'date_of_birth'     => '1980-01-01',
                'designation'       => 'System Administrator',
                'employment_type'   => 'Permanent',
                'email'             => 'admin@mys.gov.lr',
                'password'          => 'Admin@MYS2024!',
                'role'              => 'System Administrator',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 120000,
                'salary_usd'        => 600,
            ],
            [
                'employee_no'       => 'MYS-2026-HR01',
                'first_name'        => 'HR',
                'last_name'         => 'Director',
                'gender'            => 'Female',
                'date_of_birth'     => '1978-05-14',
                'designation'       => 'Director of Human Resources',
                'employment_type'   => 'Permanent',
                'email'             => 'hrdirector@mys.gov.lr',
                'password'          => 'Director@MYS2026!',
                'role'              => 'Human Resource Director',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 150000,
                'salary_usd'        => 750,
            ],
            [
                'employee_no'       => 'MYS-2026-HR02',
                'first_name'        => 'Assistant HR',
                'last_name'         => 'Director',
                'gender'            => 'Male',
                'date_of_birth'     => '1983-08-22',
                'designation'       => 'Assistant Director of Human Resources',
                'employment_type'   => 'Permanent',
                'email'             => 'asst.hrdirector@mys.gov.lr',
                'password'          => 'AsstDirector@MYS2026!',
                'role'              => 'Assistant HR Director',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 125000,
                'salary_usd'        => 625,
            ],
            [
                'employee_no'       => 'MYS-2026-HR03',
                'first_name'        => 'HR Officer',
                'last_name'         => 'One',
                'gender'            => 'Female',
                'date_of_birth'     => '1989-03-11',
                'designation'       => 'Human Resource Officer',
                'employment_type'   => 'Permanent',
                'email'             => 'hrofficer1@mys.gov.lr',
                'password'          => 'HROfficer@MYS2026!',
                'role'              => 'HR Officer',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 85000,
                'salary_usd'        => 425,
            ],
            [
                'employee_no'       => 'MYS-2026-HR04',
                'first_name'        => 'HR Officer',
                'last_name'         => 'Two',
                'gender'            => 'Male',
                'date_of_birth'     => '1991-07-19',
                'designation'       => 'Human Resource Officer',
                'employment_type'   => 'Permanent',
                'email'             => 'hrofficer2@mys.gov.lr',
                'password'          => 'HROfficer@MYS2026!',
                'role'              => 'HR Officer',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 85000,
                'salary_usd'        => 425,
            ],
            [
                'employee_no'       => 'MYS-2026-DA01',
                'first_name'        => 'HR Data',
                'last_name'         => 'Analyst',
                'gender'            => 'Female',
                'date_of_birth'     => '1993-11-04',
                'designation'       => 'HR Data & Records Analyst',
                'employment_type'   => 'Permanent',
                'email'             => 'data.analyst@mys.gov.lr',
                'password'          => 'DataAnalyst@MYS2026!',
                'role'              => 'HR Data Analyst',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 75000,
                'salary_usd'        => 375,
            ],
            [
                'employee_no'       => 'MYS-2024-0002',
                'first_name'        => 'Default',
                'last_name'         => 'Employee',
                'gender'            => 'Male',
                'date_of_birth'     => '1995-01-01',
                'designation'       => 'Staff Officer',
                'employment_type'   => 'Permanent',
                'email'             => 'employee@mys.gov.lr',
                'password'          => 'Employee@MYS2024!',
                'role'              => 'Employee',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 60000,
                'salary_usd'        => 300,
            ],
            [
                'employee_no'       => 'MYS-2026-CTR1',
                'first_name'        => 'Default',
                'last_name'         => 'Contractor',
                'gender'            => 'Male',
                'date_of_birth'     => '1992-06-15',
                'designation'       => 'Technical Consultant',
                'employment_type'   => 'Contract',
                'contract_end_date' => now()->addDays(45)->toDateString(),
                'email'             => 'contractor@mys.gov.lr',
                'password'          => 'Contractor@MYS2026!',
                'role'              => 'Contractor',
                'department_id'     => $hrDepartmentId,
                'salary_lrd'        => 70000,
                'salary_usd'        => 350,
            ],
        ];

        $rows = [];
        foreach ($accounts as $acc) {
            $employee = Employee::firstOrCreate(
                ['employee_no' => $acc['employee_no']],
                [
                    'first_name'        => $acc['first_name'],
                    'last_name'         => $acc['last_name'],
                    'gender'            => $acc['gender'],
                    'date_of_birth'     => $acc['date_of_birth'],
                    'nationality'       => 'Liberian',
                    'department_id'     => $acc['department_id'],
                    'designation'       => $acc['designation'],
                    'employment_type'   => $acc['employment_type'],
                    'employment_status' => 'Active',
                    'date_joined'       => now()->subMonths(6)->toDateString(),
                    'contract_end_date' => $acc['contract_end_date'] ?? null,
                    'official_email'    => $acc['email'],
                    'salary_amount_lrd' => $acc['salary_lrd'],
                    'salary_amount_usd' => $acc['salary_usd'],
                ]
            );

            $user = User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name'        => trim($acc['first_name'] . ' ' . $acc['last_name']),
                    'password'    => Hash::make($acc['password']),
                    'employee_id' => $employee->id,
                    'is_active'   => true,
                ]
            );

            if (!$user->hasRole($acc['role'])) {
                $user->syncRoles([$acc['role']]);
            }

            $rows[] = [$acc['role'], $acc['email'], $acc['password']];
        }

        // Also ensure any existing Contract-type employee with only the Employee role gets Contractor role
        User::with(['roles', 'employee'])->get()->each(function (User $u) {
            if ($u->employee && $u->employee->employment_type === 'Contract' && $u->hasRole('Employee') && $u->roles->count() === 1) {
                $u->syncRoles(['Contractor']);
            }
        });

        $this->command->info('✅ Default HR, Admin, Employee & Contractor accounts ensured:');
        $this->command->table(['Role', 'Email', 'Password'], $rows);
    }
}
