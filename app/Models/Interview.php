<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'job_application_id', 'scheduled_at', 'duration_minutes', 'mode', 'location',
        'meeting_url', 'interviewer', 'status', 'notes',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}