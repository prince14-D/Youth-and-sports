<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceAppraisal extends Model
{
    protected $fillable = [
        'employee_id', 'appraiser_id', 'appraisal_year', 'appraisal_period',
        'score', 'rating', 'comments', 'recommendations', 'status'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appraiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appraiser_id');
    }
}
