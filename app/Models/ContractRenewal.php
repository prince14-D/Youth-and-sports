<?php

namespace App\Models;

use App\Traits\HasMultiLevelApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRenewal extends Model
{
    use HasMultiLevelApproval;

    protected $fillable = [
        'employee_id', 'current_end_date', 'proposed_end_date', 'reason',
        'document_path', 'status', 'approval_stage', 'requested_by',
        'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'current_end_date'  => 'date',
        'proposed_end_date' => 'date',
        'reviewed_at'       => 'datetime',
        'approval_stage'    => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
