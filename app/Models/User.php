<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'employee_id',
        'is_active', 'two_factor_secret', 'two_factor_recovery_codes',
        'two_factor_confirmed_at', 'last_login_at', 'last_login_ip',
        'force_password_change',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at'           => 'datetime',
        'is_active'               => 'boolean',
        'force_password_change'   => 'boolean',
        'password'                => 'hashed',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->employee?->photo_url ?? asset('images/avatar-male.png');
    }

    public function getPrimaryRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'No Role';
    }
}
