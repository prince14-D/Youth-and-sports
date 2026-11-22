<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StadiumFacility extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'National Stadium',
        'Regional Stadium',
        'Football Pitch / Turf',
        'Indoor Arena / Gymnasium',
        'Track & Field Complex',
        'Youth Sports Center',
        'Training Complex',
    ];

    public const SURFACES = [
        'Natural Grass',
        'FIFA Artificial Turf',
        'Hardwood / Indoor',
        'Tartan Athletic Track',
        'Multi-Purpose',
    ];

    public const FLOODLIGHT_STATUSES = ['Operational', 'Partial', 'Non-Operational', 'N/A'];

    public const OPERATIONAL_STATUSES = [
        'Operational',
        'Maintenance Mode',
        'Restricted Access',
        'Closed / Unsafe',
    ];

    protected $fillable = [
        'facility_code',
        'name',
        'facility_type',
        'location',
        'county',
        'capacity',
        'surface_type',
        'floodlights_status',
        'operational_status',
        'safety_certificate_expiry',
        'manager_name',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'capacity'                  => 'integer',
        'safety_certificate_expiry' => 'date',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(StadiumEvent::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(StadiumMaintenanceLog::class);
    }

    public function safetyInspections(): HasMany
    {
        return $this->hasMany(StadiumSafetyInspection::class);
    }

    public function latestSafetyInspection(): HasOne
    {
        return $this->hasOne(StadiumSafetyInspection::class)->latestOfMany('inspection_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateFacilityCode(): string
    {
        $prefix = 'MYS-FAC-';
        $last = self::withTrashed()
            ->where('facility_code', 'like', "{$prefix}%")
            ->orderByDesc('facility_code')
            ->value('facility_code');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getSafetyCertificateExpiredAttribute(): bool
    {
        return $this->safety_certificate_expiry !== null && $this->safety_certificate_expiry->lt(today());
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->operational_status) {
            'Operational'       => 'bg-success',
            'Maintenance Mode'  => 'bg-warning text-dark',
            'Restricted Access' => 'bg-info text-dark',
            default             => 'bg-danger',
        };
    }
}
