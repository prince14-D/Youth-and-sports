<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        'Government Vehicle / Fleet',
        'Laptop / Computer',
        'Printer / Scanner',
        'Office Furniture',
        'Networking / ICT Equipment',
        'Sports / Youth Equipment',
        'Generator / Power',
        'Building / Real Property',
        'Consumable / General Inventory',
        'Mobile / Communications',
        'Other',
    ];

    public const FUNDING_SOURCES = [
        'Government of Liberia (GoL)',
        'GSA Allocation',
        'Donor / International Partner',
        'Ministry Project Grant',
        'FIFA / Sports Development Grant',
        'Other',
    ];

    protected $fillable = [
        'asset_tag',
        'gsa_code',
        'name',
        'category',
        'department_id',
        'location',
        'serial_number',
        'plate_number',
        'chassis_number',
        'condition',
        'quantity',
        'unit',
        'status',
        'purchase_date',
        'warranty_expiry',
        'last_maintenance_date',
        'next_maintenance_date',
        'purchase_value',
        'supplier',
        'funding_source',
        'notes',
    ];

    protected $casts = [
        'purchase_date'         => 'date',
        'warranty_expiry'       => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_value'        => 'decimal:2',
        'quantity'              => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->latest('assigned_date');
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_date')->latestOfMany();
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(AssetMaintenanceLog::class)->latest('service_date');
    }

    public function getIsVehicleAttribute(): bool
    {
        return stripos((string) $this->category, 'Vehicle') !== false || !empty($this->plate_number);
    }

    public function getTotalValuationAttribute(): float
    {
        return round((float) ($this->purchase_value ?? 0) * max(1, (int) ($this->quantity ?? 1)), 2);
    }
}
