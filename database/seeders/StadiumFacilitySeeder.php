<?php

namespace Database\Seeders;

use App\Models\StadiumFacility;
use Illuminate\Database\Seeder;

class StadiumFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'facility_code'             => 'MYS-FAC-0001',
                'name'                      => 'Samuel Kanyon Doe (SKD) Sports Complex',
                'facility_type'             => 'National Stadium',
                'location'                  => 'Paynesville, Monrovia',
                'county'                    => 'Montserrado',
                'capacity'                  => 22000,
                'surface_type'              => 'FIFA Artificial Turf',
                'floodlights_status'        => 'Operational',
                'operational_status'        => 'Operational',
                'safety_certificate_expiry' => now()->addMonths(8)->toDateString(),
                'manager_name'              => 'Stadium Operations Directorate',
                'notes'                     => 'National flagship multi-purpose stadium hosting FIFA/CAF international fixtures and National County Sports Meet finals.',
            ],
            [
                'facility_code'             => 'MYS-FAC-0002',
                'name'                      => 'Antoinette Tubman Stadium (ATS)',
                'facility_type'             => 'Regional Stadium',
                'location'                  => 'UN Drive, Central Monrovia',
                'county'                    => 'Montserrado',
                'capacity'                  => 12000,
                'surface_type'              => 'FIFA Artificial Turf',
                'floodlights_status'        => 'Partial',
                'operational_status'        => 'Operational',
                'safety_certificate_expiry' => now()->addMonths(5)->toDateString(),
                'manager_name'              => 'ATS Facility Supervisor',
                'notes'                     => 'Premier league football venue, LFA matches, and civic youth events.',
            ],
            [
                'facility_code'             => 'MYS-FAC-0003',
                'name'                      => 'Doris Williams Sports Stadium',
                'facility_type'             => 'Regional Stadium',
                'location'                  => 'Buchanan City',
                'county'                    => 'Grand Bassa',
                'capacity'                  => 6500,
                'surface_type'              => 'Natural Grass',
                'floodlights_status'        => 'Non-Operational',
                'operational_status'        => 'Operational',
                'safety_certificate_expiry' => now()->addMonths(4)->toDateString(),
                'manager_name'              => 'Grand Bassa Stadium Coordinator',
                'notes'                     => 'Regional sports complex for County Sports Meet qualifiers and youth athletics.',
            ],
        ];

        foreach ($facilities as $item) {
            StadiumFacility::firstOrCreate(
                ['facility_code' => $item['facility_code']],
                $item
            );
        }

        $this->command->info('✅ National & regional stadium facilities seeded successfully.');
    }
}
