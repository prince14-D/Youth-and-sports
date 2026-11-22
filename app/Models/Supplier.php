<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        'General Goods & Supplies',
        'ICT & Electronics',
        'Vehicles & Fleet',
        'Sports Equipment & Facilities',
        'Construction & Civil Works',
        'Consulting & Professional Services',
        'Printing & Branding',
        'Catering & Events',
        'Fuel & Lubricants',
        'Other',
    ];

    public const STATUSES = ['Active', 'Under Review', 'Suspended', 'Blacklisted'];

    protected $fillable = [
        'supplier_no', 'name', 'category', 'business_reg_no', 'tin_number',
        'contact_person', 'phone', 'email', 'address', 'status',
        'tax_clearance_expiry', 'business_license_expiry', 'performance_rating',
        'status_reason', 'registered_by',
    ];

    protected $casts = [
        'tax_clearance_expiry'    => 'date',
        'business_license_expiry' => 'date',
        'performance_rating'      => 'decimal:2',
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(ProcurementBid::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SupplierEvaluation::class);
    }

    public function awardedTenders(): HasMany
    {
        return $this->hasMany(ProcurementRequest::class, 'awarded_supplier_id');
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public static function generateSupplierNo(): string
    {
        $prefix = 'MYS-SUP-';
        $last = self::withTrashed()->where('supplier_no', 'like', "{$prefix}%")->orderByDesc('supplier_no')->value('supplier_no');
        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getTaxClearanceExpiredAttribute(): bool
    {
        return $this->tax_clearance_expiry !== null && $this->tax_clearance_expiry->lt(today());
    }

    public function getLicenseExpiredAttribute(): bool
    {
        return $this->business_license_expiry !== null && $this->business_license_expiry->lt(today());
    }

    /** Reasons this supplier is NOT eligible for a contract award (empty = eligible). */
    public function ineligibilityReasons(): array
    {
        $reasons = [];
        if ($this->status !== 'Active') {
            $reasons[] = "supplier status is {$this->status}";
        }
        if ($this->tax_clearance_expired) {
            $reasons[] = 'tax clearance certificate has expired';
        }
        if ($this->license_expired) {
            $reasons[] = 'business license has expired';
        }
        return $reasons;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Active'       => 'bg-success',
            'Under Review' => 'bg-warning text-dark',
            'Suspended'    => 'bg-secondary',
            default        => 'bg-danger',
        };
    }
}
