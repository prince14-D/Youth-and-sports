<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalTrail extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'stage',
        'stage_label',
        'required_role',
        'action',
        'actor_id',
        'actor_name',
        'actor_role',
        'remarks',
        'digital_signature',
        'ip_address',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
        'stage'    => 'integer',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
