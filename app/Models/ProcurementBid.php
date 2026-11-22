<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementBid extends Model
{
    protected $fillable = [
        'procurement_request_id', 'supplier_id', 'bid_amount_usd', 'submitted_date', 'delivery_days',
        'is_responsive', 'technical_score', 'financial_score', 'total_score', 'status', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'bid_amount_usd'  => 'decimal:2',
        'financial_score' => 'decimal:2',
        'total_score'     => 'decimal:2',
        'submitted_date'  => 'date',
        'is_responsive'   => 'boolean',
        'technical_score' => 'integer',
    ];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
