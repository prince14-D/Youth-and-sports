<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StadiumEvent extends Model
{
    use SoftDeletes;

    public const EVENT_TYPES = [
        'International Match (CAF / FIFA)',
        'National County Sports Meet',
        'League / Federation Match',
        'Youth Festival / Civic Event',
        'Training / Friendly',
        'Private / Commercial Booking',
    ];

    public const STATUSES = [
        'Scheduled',
        'Preparation In Progress',
        'Safety Cleared',
        'Completed',
        'Postponed',
        'Cancelled',
    ];

    public const CHECKLIST_FIELDS = [
        'checklist_pitch_ready'      => 'Playing Surface / Pitch Marked & Inspected',
        'checklist_lighting_power'   => 'Floodlights & Standby Generators Tested',
        'checklist_medical_ems'      => 'Ambulance, First-Aid & Medical Team Confirmed',
        'checklist_security_crowd'   => 'LNP Security, Stewards & Crowd Entry Plan Briefed',
        'checklist_sanitation_water' => 'Restrooms, Dressing Rooms & Water Supply Ready',
        'checklist_sound_scoreboard' => 'PA System, Emergency Announcements & Scoreboard Tested',
    ];

    protected $fillable = [
        'event_code',
        'stadium_facility_id',
        'title',
        'event_type',
        'organizer',
        'organizer_contact',
        'event_date',
        'start_time',
        'end_time',
        'expected_attendance',
        'revenue_fee_usd',
        'status',
        'checklist_pitch_ready',
        'checklist_lighting_power',
        'checklist_medical_ems',
        'checklist_security_crowd',
        'checklist_sanitation_water',
        'checklist_sound_scoreboard',
        'safety_cleared_by',
        'safety_cleared_at',
        'ministry_document_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'event_date'                 => 'date',
        'expected_attendance'        => 'integer',
        'revenue_fee_usd'            => 'decimal:2',
        'checklist_pitch_ready'      => 'boolean',
        'checklist_lighting_power'   => 'boolean',
        'checklist_medical_ems'      => 'boolean',
        'checklist_security_crowd'   => 'boolean',
        'checklist_sanitation_water' => 'boolean',
        'checklist_sound_scoreboard' => 'boolean',
        'safety_cleared_at'          => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(StadiumFacility::class, 'stadium_facility_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function safetyClearer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'safety_cleared_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function safetyInspections(): HasMany
    {
        return $this->hasMany(StadiumSafetyInspection::class);
    }

    public static function generateEventCode(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-EVT-{$year}-";
        $last = self::withTrashed()
            ->where('event_code', 'like', "{$prefix}%")
            ->orderByDesc('event_code')
            ->value('event_code');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getChecklistCompletedCountAttribute(): int
    {
        $done = 0;
        foreach (array_keys(self::CHECKLIST_FIELDS) as $field) {
            if ($this->{$field}) {
                $done++;
            }
        }
        return $done;
    }

    public function getChecklistPercentAttribute(): int
    {
        $total = count(self::CHECKLIST_FIELDS);
        return $total > 0 ? (int) round(($this->checklist_completed_count / $total) * 100) : 0;
    }

    public function getIsChecklistCompleteAttribute(): bool
    {
        return $this->checklist_completed_count === count(self::CHECKLIST_FIELDS);
    }

    public function getExceedsCapacityAttribute(): bool
    {
        return $this->facility && $this->facility->capacity > 0 && $this->expected_attendance > $this->facility->capacity;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Safety Cleared'          => 'bg-success',
            'Preparation In Progress' => 'bg-info text-dark',
            'Completed'               => 'bg-dark',
            'Postponed'               => 'bg-warning text-dark',
            'Cancelled'               => 'bg-danger',
            default                   => 'bg-primary',
        };
    }
}
