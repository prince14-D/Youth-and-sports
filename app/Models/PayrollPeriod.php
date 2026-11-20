<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'payment_date', 'payment_reference', 'status', 'created_by', 'reviewed_by', 'reviewed_at', 'locked_at'];
    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'payment_date' => 'date',
        'reviewed_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
