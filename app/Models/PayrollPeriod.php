<?php

namespace App\Models;

use App\Traits\HasMultiLevelApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use HasMultiLevelApproval;

    protected $fillable = [
        'name', 'start_date', 'end_date', 'payment_date', 'payment_reference',
        'status', 'approval_stage', 'created_by', 'reviewed_by',
        'reviewed_at', 'locked_at', 'rejection_reason',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'payment_date'   => 'date',
        'reviewed_at'    => 'datetime',
        'locked_at'      => 'datetime',
        'approval_stage' => 'integer',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
