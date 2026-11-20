<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttendanceDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'device_type',
        'location',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'department_id',
        'status',
        'token_hash',
        'software_version',
        'last_synced_at',
        'last_ip_address',
        'notes',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'geofence_radius_meters' => 'integer',
    ];

    public static function generateDeviceId(string $prefix = 'ATD'): string
    {
        return $prefix . '-' . Str::upper(Str::random(10));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function matchesToken(string $token): bool
    {
        return hash_equals($this->token_hash, hash_hmac('sha256', $token, config('app.key')));
    }
}
