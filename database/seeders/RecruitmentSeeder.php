<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Vacancy;
use App\Models\JobApplication;
use Illuminate\Database\Seeder;

class RecruitmentSeeder extends Seeder
{
    public function run(): void
    {
        $ict = Department::where('code', 'ICT')->first();
        $ictId = $ict ? $ict->id : 1;

        // 1. Vacancies
        $v1 = Vacancy::create([
            'title' => 'Middle UI/UX Designer',
            'department_id' => $ictId,
            'description' => 'Responsible for product user interface and experience design.',
            'status' => 'Open',
            'closing_date' => '2026-06-15',
        ]);

        $v2 = Vacancy::create([
            'title' => 'Middle Front-end developer',
            'department_id' => $ictId,
            'description' => 'Responsible for building front-end web client apps.',
            'status' => 'Open',
            'closing_date' => '2026-06-15',
        ]);

        $v3 = Vacancy::create([
            'title' => 'WordPress Developer',
            'department_id' => $ictId,
            'description' => 'Responsible for managing ministry portals and CMS platforms.',
            'status' => 'Open',
            'closing_date' => '2026-06-15',
        ]);

        $v4 = Vacancy::create([
            'title' => 'Senior Front-end developer',
            'department_id' => $ictId,
            'description' => 'Senior role overseeing front-end architecture and standards.',
            'status' => 'Closed',
            'closing_date' => '2026-06-02',
        ]);

        // 2. Job Applications / Recent Applicants
        $applicants = [
            [
                'name' => 'Jane Cooper',
                'title' => 'Middle UI/UX Designer',
                'vacancy_id' => $v1->id,
                'email' => 'jane.cooper@example.com',
            ],
            [
                'name' => 'Cody Fisher',
                'title' => 'Middle Front-end developer',
                'vacancy_id' => $v2->id,
                'email' => 'cody.fisher@example.com',
            ],
            [
                'name' => 'Darrell Steward',
                'title' => 'Senior Front-end developer',
                'vacancy_id' => $v4->id,
                'email' => 'darrell.steward@example.com',
            ],
            [
                'name' => 'Dianne Russell',
                'title' => 'Junior UI/UX Designer',
                'vacancy_id' => $v1->id,
                'email' => 'dianne.russell@example.com',
            ],
        ];

        foreach ($applicants as $app) {
            JobApplication::create([
                'vacancy_id' => $app['vacancy_id'],
                'applicant_name' => $app['name'],
                'email' => $app['email'],
                'phone' => '+231-886-000-000',
                'status' => 'Applied',
            ]);
        }

        // Seed additional mock applications to make counts matching screenshot
        for ($i = 0; $i < 10; $i++) {
            JobApplication::create(['vacancy_id' => $v1->id, 'applicant_name' => "Mock UI $i", 'email' => "mockui$i@example.com", 'status' => 'Applied']);
        }
        for ($i = 0; $i < 13; $i++) {
            JobApplication::create(['vacancy_id' => $v2->id, 'applicant_name' => "Mock FE $i", 'email' => "mockfe$i@example.com", 'status' => 'Applied']);
        }
        for ($i = 0; $i < 9; $i++) {
            JobApplication::create(['vacancy_id' => $v3->id, 'applicant_name' => "Mock WP $i", 'email' => "mockwp$i@example.com", 'status' => 'Applied']);
        }
        for ($i = 0; $i < 13; $i++) {
            JobApplication::create(['vacancy_id' => $v4->id, 'applicant_name' => "Mock SFE $i", 'email' => "mocksfe$i@example.com", 'status' => 'Applied']);
        }
    }
}
