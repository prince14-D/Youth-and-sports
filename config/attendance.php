<?php

return [
    'official_start' => env('ATTENDANCE_OFFICIAL_START', '08:30:00'),
    'official_end' => env('ATTENDANCE_OFFICIAL_END', '17:00:00'),
    'late_threshold_minutes' => env('ATTENDANCE_LATE_THRESHOLD_MINUTES', 0),
    'early_departure_threshold_minutes' => env('ATTENDANCE_EARLY_DEPARTURE_THRESHOLD_MINUTES', 0),
    'overtime_threshold_minutes' => env('ATTENDANCE_OVERTIME_THRESHOLD_MINUTES', 0),
    'half_day_hours' => env('ATTENDANCE_HALF_DAY_HOURS', 4),
    'weekend_days' => array_map('intval', explode(',', env('ATTENDANCE_WEEKEND_DAYS', '0,6'))),
];
