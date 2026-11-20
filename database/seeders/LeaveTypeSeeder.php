<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'days_allowed' => 21,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'days_allowed' => 14,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'days_allowed' => 90,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STL',
                'days_allowed' => 0, // 0 means custom/flexible limit
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Compassionate Leave',
                'code' => 'CL',
                'days_allowed' => 7,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
