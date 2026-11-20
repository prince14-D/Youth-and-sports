<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Administration',      'code' => 'ADMIN',    'description' => 'General Administration and Office Management'],
            ['name' => 'Human Resources',     'code' => 'HR',       'description' => 'Human Resource Management and Personnel Affairs'],
            ['name' => 'Finance',             'code' => 'FIN',      'description' => 'Financial Management, Budgeting and Accounting'],
            ['name' => 'Procurement',         'code' => 'PROC',     'description' => 'Procurement and Supply Chain Management'],
            ['name' => 'Youth Development',   'code' => 'YOUTH',    'description' => 'Youth Programs, Empowerment and Development'],
            ['name' => 'Sports Development',  'code' => 'SPORTS',   'description' => 'Sports Programs, Competitions and Facilities'],
            ['name' => 'Planning',            'code' => 'PLAN',     'description' => 'Strategic Planning, Monitoring and Evaluation'],
            ['name' => 'ICT',                 'code' => 'ICT',      'description' => 'Information and Communications Technology'],
            ['name' => 'Internal Audit',      'code' => 'AUDIT',    'description' => 'Internal Audit and Compliance'],
            ['name' => 'County Offices',      'code' => 'COUNTY',   'description' => 'County-Level Field Operations and Outreach'],
            ['name' => 'Legal',               'code' => 'LEGAL',    'description' => 'Legal Affairs and Compliance'],
            ['name' => 'Communications',      'code' => 'COMM',     'description' => 'Public Relations and Communications'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                array_merge($dept, ['is_active' => true])
            );
        }

        $this->command->info('✅ Departments seeded successfully.');
    }
}
