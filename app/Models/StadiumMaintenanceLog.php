<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StadiumMaintenanceLog extends Model
{
    use SoftDeletes;

    public const ZONES = [
        'Playing Pitch / Turf',
        'Floodlights & Electrical',
        'Seating / Stands / Roof',
        'Dressing Rooms & VIP Box',
        'Perimeter Fence & Gates',
        'Plumbing & Restrooms',
        'Scoreboard & PA System',
        'Generators & Backup Power',
        'Track & Field',
    ];

    public const TYPES = [
        'Preventive',
        'Corrective / Repair',
        'Emergency',
        'Pitch Care / Grounds',
        'Upgrade / Renovation',
    ];

    public const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const STATUSES = [
        'Open',
        'In Progress',
        'Awaiting Parts / Funding',
        'Completed',
        'Cancelled',
    ];

    protected $fillable = [
        'work_order_no',
        'stadium_facility_id',
        'zone_area',
        'title',
        'maintenance_type',
        'priority',
        'status',
        'scheduled_date',
        'completed_date',
        'estimated_cost_usd',
        'actual_cost_usd',
        'assigned_contractor',
        'description',
        'completion_notes',
        'ministry_document_id',
        'reported_by',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_date'     => 'date',
        'completed_date'     => 'date',
        'estimated_cost_usd' => 'decimal:2',
        'actual_cost_usd'    => 'decimal:2',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(StadiumFacility::class, 'stadium_facility_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public static function generateWorkOrderNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-MNT-{$year}-";
        $last = self::withTrashed()
            ->where('work_order_no', 'like', "{$prefix}%")
            ->orderByDesc('work_order_no')
            ->value('work_order_no');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Critical' => 'bg-danger',
            'High'     => 'bg-warning text-dark',
            'Medium'   => 'bg-info text-dark',
            default    => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Completed'                => 'bg-success',
            'In Progress'              => 'bg-primary',
            'Awaiting Parts / Funding' => 'bg-warning text-dark',
            'Cancelled'                => 'bg-secondary',
            default                    => 'bg-danger-subtle text-danger border border-danger-subtle',
        };
    }
}
