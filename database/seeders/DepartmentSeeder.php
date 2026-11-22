<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Office of the Minister',                     'code' => 'MINISTER', 'description' => 'Executive Oversight of All Ministry Departments and Daily Operations'],
            ['name' => 'Deputy Minister for Administration (DMA)',   'code' => 'DMA',      'description' => 'Head of Administration — Approvals for HR, Finance, Assets & Operations'],
            ['name' => 'Administration',                             'code' => 'ADMIN',    'description' => 'General Administration and Office Management'],
            ['name' => 'Asset Management',                           'code' => 'ASSETS',   'description' => 'Manages government property, vehicles, equipment, inventory and other Ministry assets'],
            ['name' => 'Human Resources',                            'code' => 'HR',       'description' => 'Human Resource Management and Personnel Affairs'],
            ['name' => 'Finance',                                    'code' => 'FIN',      'description' => 'Financial Management, Budgeting and Accounting'],
            ['name' => 'Monitoring & Evaluation (M&E)',              'code' => 'ME',       'description' => 'Monitoring, Evaluation, Research and Impact Tracking'],
            ['name' => 'Project Delivery Unit (PDU)',                'code' => 'PDU',      'description' => 'Ministry Project Delivery, Coordination and Implementation'],
            ['name' => 'National Youth Policy & Programs (NYPP)',    'code' => 'NYPP',     'description' => 'National Youth Policy Coordination and Empowerment Programs'],
            ['name' => 'Procurement',                                'code' => 'PROC',     'description' => 'Procurement and Supply Chain Management'],
            ['name' => 'Youth Development',                          'code' => 'YOUTH',    'description' => 'Youth Programs, Empowerment and Development'],
            ['name' => 'Sports Development',                         'code' => 'SPORTS',   'description' => 'Sports Programs, Competitions and Facilities'],
            ['name' => 'Stadium Management',                         'code' => 'STADIUM',  'description' => 'Oversees stadium operations, maintenance, facility management, event preparation and safety'],
            ['name' => 'Planning',                                   'code' => 'PLAN',     'description' => 'Strategic Planning and Policy Analysis'],
            ['name' => 'ICT',                                        'code' => 'ICT',      'description' => 'Information and Communications Technology'],
            ['name' => 'Internal Audit',                             'code' => 'AUDIT',    'description' => 'Internal Audit and Compliance'],
            ['name' => 'County Offices',                             'code' => 'COUNTY',   'description' => 'County-Level Field Operations and Outreach'],
            ['name' => 'Legal',                                      'code' => 'LEGAL',    'description' => 'Legal Affairs and Compliance'],
            ['name' => 'Communications',                             'code' => 'COMM',     'description' => 'Public Relations and Communications'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                array_merge($dept, ['is_active' => true])
            );
        }

        $this->command->info('✅ Ministry departments & executive offices seeded successfully.');
    }
}
