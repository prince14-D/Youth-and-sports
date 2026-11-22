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
            'view minister dashboard',
            'view dma dashboard',

            // Paperless Document Workflow & Tracking
            'view document workflow', 'create document workflow', 'approve document workflow', 'track all documents',

            // Asset Requisitions
            'manage asset requests', 'approve asset requests',

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

            // Audit & Internal Audit Division
            'view audit logs', 'view internal audit', 'manage internal audit',

            // Procurement Unit
            'view procurement', 'manage procurement',

            // Stadium Management
            'view stadium management', 'manage stadium management',

            // System
            'manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Define roles and assign permissions ─────────────────────────────
        $rolePermissions = [
            'System Administrator' => Permission::all()->pluck('name')->toArray(),

            'Minister' => [
                'view dashboard', 'view minister dashboard',
                'view employees', 'view employee salary', 'export employees',
                'view departments',
                'view document workflow', 'create document workflow', 'approve document workflow', 'track all documents',
                'view assets', 'manage asset requests', 'approve asset requests',
                'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'view leave', 'approve leave', 'view excuses', 'approve excuses',
                'view payroll', 'approve payroll', 'view payslips',
                'view performance', 'view training',
                'view reports', 'export reports', 'view audit logs', 'view internal audit', 'view procurement', 'view stadium management',
                'view grievances',
            ],

            'Deputy Minister for Administration' => [
                'view dashboard', 'view dma dashboard',
                'view employees', 'view employee salary', 'export employees',
                'view departments', 'edit departments',
                'view document workflow', 'create document workflow', 'approve document workflow', 'track all documents',
                'view assets', 'manage assets', 'manage asset requests', 'approve asset requests',
                'view staff movements', 'approve staff movements', 'view contracts', 'manage contract renewals', 'view directory',
                'view leave', 'approve leave', 'view excuses', 'approve excuses',
                'view payroll', 'approve payroll', 'export payroll', 'view payslips',
                'view performance', 'approve appraisals', 'view training',
                'view reports', 'export reports', 'view audit logs', 'view internal audit', 'view procurement', 'view stadium management',
                'view grievances', 'manage department communications',
            ],

            'Human Resource Director' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees', 'view employee salary',
                'view departments', 'create departments', 'edit departments',
                'view users', 'create users', 'edit users',
                'view vacancies', 'create vacancies', 'edit vacancies', 'view applications', 'manage applications',
                'schedule interviews', 'notify applicants', 'view recruitment reports', 'export recruitment reports',
                'view leave', 'approve leave', 'manage leave types',
                'submit excuse', 'view excuses', 'approve excuses',
                'view payroll', 'create payroll', 'approve payroll', 'export payroll', 'view payslips', 'manage salary grades',
                'view performance', 'create appraisals', 'approve appraisals',
                'view training', 'create training', 'manage training',
                'view reports', 'export reports',
                'view audit logs',
                'manage email communications',
                'view assets', 'manage assets', 'manage asset requests', 'approve asset requests',
                'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'manage contract renewals', 'manage grievances', 'view grievances',
                'manage department communications',
                'view document workflow', 'create document workflow', 'approve document workflow',
            ],

            'Assistant HR Director' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees', 'view employee salary',
                'view departments', 'create departments', 'edit departments',
                'view users', 'create users', 'edit users',
                'view vacancies', 'create vacancies', 'edit vacancies', 'view applications', 'manage applications',
                'schedule interviews', 'notify applicants', 'view recruitment reports', 'export recruitment reports',
                'view leave', 'approve leave', 'manage leave types',
                'submit excuse', 'view excuses', 'approve excuses',
                'view payroll', 'approve payroll', 'export payroll', 'view payslips',
                'view performance', 'create appraisals', 'approve appraisals',
                'view training', 'create training', 'manage training',
                'view reports', 'export reports',
                'view audit logs',
                'manage email communications',
                'view assets', 'manage assets', 'manage asset requests', 'approve asset requests',
                'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'manage contract renewals', 'manage grievances', 'view grievances',
                'manage department communications',
                'view document workflow', 'create document workflow', 'approve document workflow',
            ],

            'HR Officer' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees',
                'view departments',
                'view vacancies', 'create vacancies', 'view applications', 'manage applications',
                'schedule interviews', 'notify applicants', 'view recruitment reports',
                'view leave', 'approve leave', 'manage leave types',
                'submit excuse', 'view excuses', 'approve excuses',
                'view payroll', 'approve payroll', 'view payslips',
                'view performance', 'create appraisals',
                'view training', 'create training',
                'view reports', 'export reports',
                'manage email communications',
                'view assets', 'manage assets', 'manage asset requests',
                'view staff movements', 'approve staff movements', 'view contracts', 'view directory',
                'manage contract renewals', 'manage grievances', 'view grievances',
                'manage department communications',
                'view document workflow', 'create document workflow',
            ],

            'HR Data Analyst' => [
                'view dashboard',
                'view employees', 'create employees', 'edit employees', 'export employees',
                'view departments',
                'view contracts', 'manage contract renewals',
                'view directory',
                'view reports', 'export reports',
                'view assets', 'manage asset requests',
                'view document workflow', 'create document workflow',
            ],

            'Asset Manager' => [
                'view dashboard',
                'view assets', 'manage assets', 'manage asset requests',
                'view departments',
                'view directory',
                'view document workflow', 'create document workflow',
                'view reports', 'export reports',
            ],

            'Procurement Officer' => [
                'view dashboard',
                'view procurement', 'manage procurement',
                'view departments',
                'view assets', 'manage asset requests',
                'view directory',
                'view document workflow', 'create document workflow',
                'view reports', 'export reports',
            ],

            'Stadium Manager' => [
                'view dashboard',
                'view stadium management', 'manage stadium management',
                'view departments',
                'view assets', 'manage asset requests',
                'view directory',
                'view document workflow', 'create document workflow',
                'view reports', 'export reports',
            ],

            'Payroll Officer' => [
                'view dashboard',
                'view employees', 'view employee salary',
                'view leave',
                'view payroll', 'create payroll', 'export payroll', 'view payslips', 'manage salary grades',
                'view reports', 'export reports',
                'view assets', 'manage asset requests',
                'view document workflow', 'create document workflow',
            ],

            'Finance Officer' => [
                'view dashboard',
                'view employees',
                'view departments',
                'view payroll', 'approve payroll', 'export payroll', 'view payslips',
                'view reports', 'export reports',
                'view assets', 'manage asset requests',
                'view document workflow', 'create document workflow', 'approve document workflow',
            ],

            'Director/Manager' => [
                'view dashboard',
                'view employees',
                'view departments',
                'view leave', 'approve leave',
                'view excuses', 'approve excuses',
                'view performance', 'create appraisals',
                'view reports',
                'view assets', 'manage asset requests',
                'view document workflow', 'create document workflow', 'approve document workflow',
            ],

            'Department Head' => [
                'view dashboard',
                'view employees',
                'view departments',
                'view leave', 'approve leave',
                'view excuses', 'approve excuses',
                'view performance', 'create appraisals',
                'view training',
                'view reports',
                'view assets', 'manage asset requests',
                'view document workflow', 'create document workflow', 'approve document workflow',
            ],

            'Supervisor' => [
                'view dashboard',
                'view employees',
                'view leave', 'approve leave',
                'view excuses', 'approve excuses',
                'view performance', 'create appraisals',
            ],

            'Employee' => [
                'view dashboard',
                'apply leave',
                'submit excuse',
                'view internal vacancies', 'apply vacancies',
                'view payslips',
                'view directory',
                'manage grievances',
            ],

            'Contractor' => [
                'view dashboard',
                'submit excuse',
                'view internal vacancies', 'apply vacancies',
                'view payslips',
                'view directory',
                'manage grievances',
            ],

            'Auditor' => [
                'view dashboard',
                'view internal audit', 'manage internal audit',
                'view procurement',
                'view stadium management',
                'view employees', 'view employee salary',
                'view departments',
                'view assets',
                'view contracts',
                'view leave',
                'view excuses',
                'view payroll', 'view payslips',
                'view performance',
                'view reports', 'export reports',
                'view audit logs',
                'view document workflow', 'create document workflow', 'track all documents',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);
        }

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }
}
