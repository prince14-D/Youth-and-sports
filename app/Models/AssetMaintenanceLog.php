<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceLog extends Model
{
    public const TYPES = [
        'Routine Service',
        'Repair',
        'GSA Inspection / Audit',
        'Fuel / Fleet Log',
        'Hardware / Part Upgrade',
    ];

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'service_date',
        'cost_usd',
        'vendor',
        'notes',
        'next_service_date',
        'recorded_by',
    ];

    protected $casts = [
        'service_date'      => 'date',
        'next_service_date' => 'date',
        'cost_usd'          => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
