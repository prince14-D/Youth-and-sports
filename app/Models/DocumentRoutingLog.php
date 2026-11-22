<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRoutingLog extends Model
{
    protected $fillable = [
        'ministry_document_id',
        'from_department_id',
        'to_department_id',
        'target_user_id',
        'action',
        'actor_id',
        'actor_name',
        'actor_role',
        'minutes_note',
        'attachment_path',
        'attachment_original_name',
        'digital_signature',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match (strtolower((string) $this->action)) {
            'returned', 'reverted' => 'Reverted',
            'feedback'             => 'Feedback',
            'resubmitted'          => 'Resubmitted',
            'approved'             => 'Approved',
            'rejected'             => 'Rejected',
            'completed'            => 'Completed',
            'forwarded'            => 'Forwarded',
            'received'             => 'Received',
            'dispatched'           => 'Dispatched',
            default                => ucfirst((string) $this->action),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match (strtolower((string) $this->action)) {
            'approved', 'completed' => 'bg-success',
            'returned', 'reverted'  => 'bg-warning text-dark',
            'rejected'              => 'bg-danger',
            'feedback'              => 'bg-info text-dark',
            'resubmitted'           => 'bg-primary',
            default                 => 'bg-navy',
        };
    }
}
