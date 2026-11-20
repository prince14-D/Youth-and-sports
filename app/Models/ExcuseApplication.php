<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcuseApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'attendance_id', 'excuse_date', 'excuse_type', 'reason',
        'attachment_path', 'status', 'submitted_by', 'reviewed_by', 'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = ['excuse_date' => 'date', 'reviewed_at' => 'datetime'];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function attendance(): BelongsTo { return $this->belongsTo(Attendance::class); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}