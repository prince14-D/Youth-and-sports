<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StadiumSafetyInspection extends Model
{
    public const INSPECTION_TYPES = [
        'Pre-Event Safety Clearance',
        'Routine Facility Safety Audit',
        'Fire & Emergency Exit Check',
        'Structural / Stands Inspection',
        'Medical & Crowd Control Review',
        'Post-Incident Investigation',
    ];

    public const RATINGS = [
        'Passed',
        'Passed with Conditions',
        'Failed / Hold Event',
    ];

    protected $fillable = [
        'inspection_no',
        'stadium_facility_id',
        'stadium_event_id',
        'inspection_type',
        'inspection_date',
        'overall_rating',
        'safety_score',
        'fire_exits_clear',
        'structural_ok',
        'medical_station_ready',
        'security_briefed',
        'hazards_identified',
        'corrective_actions',
        'inspector_name',
        'recorded_by',
    ];

    protected $casts = [
        'inspection_date'       => 'date',
        'safety_score'          => 'integer',
        'fire_exits_clear'      => 'boolean',
        'structural_ok'         => 'boolean',
        'medical_station_ready' => 'boolean',
        'security_briefed'      => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(StadiumFacility::class, 'stadium_facility_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(StadiumEvent::class, 'stadium_event_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generateInspectionNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-SAF-{$year}-";
        $last = self::where('inspection_no', 'like', "{$prefix}%")
            ->orderByDesc('inspection_no')
            ->value('inspection_no');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getRatingBadgeClassAttribute(): string
    {
        return match ($this->overall_rating) {
            'Passed'                 => 'bg-success',
            'Passed with Conditions' => 'bg-warning text-dark',
            default                  => 'bg-danger',
        };
    }
}
