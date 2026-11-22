<?php

namespace App\Models;

use App\Traits\HasMultiLevelApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use SoftDeletes, HasMultiLevelApproval;

    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date',
        'days_requested', 'reason', 'status', 'approval_stage',
        'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'approved_at'    => 'datetime',
        'approval_stage' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
