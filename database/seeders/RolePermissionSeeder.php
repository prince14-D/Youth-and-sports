<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions ──────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view dashboard',

            // Employees
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view employee salary', 'export employees',

            // Departments
            'view departments', 'create departments', 'edit departments', 'delete departments',

            // Users & Roles
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'assign roles',

            // Recruitment
            'view vacancies', 'create vacancies', 'edit vacancies', 'delete vacancies',
            'view applications', 'manage applications', 'schedule interviews', 'notify applicants',
            'view recruitment reports', 'export recruitment reports',
            'view internal vacancies', 'apply vacancies',
            'manage email communications',
            'view assets', 'manage assets', 'view staff movements', 'approve staff movements',
            'view contracts', 'view directory',
            'manage contract renewals', 'manage grievances', 'view grievances',
            'manage department communications',

            // Attendance
            'view attendance', 'manage attendance', 'export attendance',

            // Leave
            'view leave', 'apply leave', 'approve leave', 'manage leave types',
            'submit excuse', 'view excuses', 'approve excuses',

            // Payroll
            'view payroll', 'create payroll', 'approve payroll', 'export payroll',
            'view payslips', 'manage salary grades',

            // Performance
            'view performance', 'create appraisals', 'approve appraisals',

            // Training
            'view training', 'create training', 'manage training',

            // Reports
            'view reports', 'export reports',

            // Audit
            'view audit logs',

            // System
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Define roles and assign permissions ─────────────────────────────
        $rolePermissions = [
            'System Administrator' => Permission::all()->pluck('name')->toArray(),

            'Human Resource Director' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees', 'view employee salary',
                'view departments', 'create departments', 'edit departments',
                'view users', 'create users', 'edit users',
                'view vacancies', 'create vacancies', 'edit vacancies', 'view applications', 'manage applications',
                'view attendance', 'manage attendance', 'export attendance',
                'view leave', 'approve leave', 'manage leave types',
                'submit excuse', 'view excuses', 'approve excuses',
                'view payroll', 'create payroll', 'approve payroll', 'export payroll', 'view payslips', 'manage salary grades',
                'view performance', 'create appraisals', 'approve appraisals',
                'view training', 'create training', 'manage training',
                'view reports', 'export reports',
                'view audit logs',
                'manage email communications',
                'view assets', 'manage assets', 'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'manage contract renewals', 'manage grievances', 'view grievances',
                'manage department communications',
            ],

            'HR Officer' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees',
                'view departments',
                'view vacancies', 'create vacancies', 'view applications', 'manage applications',
                'view attendance', 'manage attendance',
                'view leave', 'approve leave', 'manage leave types',
                'submit excuse', 'view excuses', 'approve excuses',
                'view performance', 'create appraisals',
                'view training', 'create training',
                'view reports', 'export reports',
                'manage email communications',
                'view assets', 'manage assets', 'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'manage contract renewals', 'manage grievances', 'view grievances',
                'manage department communications',
            ],

            'Payroll Officer' => [
                'view dashboard',
                'view employees', 'view employee salary',
                'view attendance',
                'view leave',
                'view payroll', 'create payroll', 'export payroll', 'view payslips', 'manage salary grades',
                'view reports', 'export reports',
            ],

            'Finance Officer' => [
                'view dashboard',
                'view employees',
                'view payroll', 'approve payroll', 'export payroll', 'view payslips',
                'view reports', 'export reports',
            ],

            'Director/Manager' => [
                'view dashboard',
                'view employees',
                'view attendance',
                'view leave', 'approve leave',
                'view performance', 'create appraisals',
                'view reports',
            ],

            'Department Head' => [
                'view dashboard',
                'view employees',
                'view attendance',
                'view leave', 'approve leave',
                'view performance', 'create appraisals',
                'view training',
                'view reports',
            ],

            'Supervisor' => [
                'view dashboard',
                'view employees',
                'view attendance',
                'view leave', 'approve leave',
                'view performance', 'create appraisals',
            ],

            'Employee' => [
                'view dashboard',
                'apply leave',
                'submit excuse',
                'view internal vacancies', 'apply vacancies',
                'view payslips',
            ],

            'Auditor' => [
                'view dashboard',
                'view employees',
                'view attendance',
                'view leave',
                'view payroll',
                'view performance',
                'view reports', 'export reports',
                'view audit logs',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);
        }

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }
}
