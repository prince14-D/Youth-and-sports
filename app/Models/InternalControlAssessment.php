<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalControlAssessment extends Model
{
    public const CONTROL_AREAS = [
        'Financial & Voucher Controls',
        'Payroll Integrity & Authorization',
        'Segregation of Duties',
        'Asset & Fleet Custody Controls',
        'Procurement & Contract Compliance',
        'Paperless Document & Approval Trail',
        'Budget Execution & Allotment Discipline',
    ];

    public const RATINGS = [
        'Effective',
        'Needs Improvement',
        'Deficient',
        'Critical Weakness',
    ];

    protected $fillable = [
        'assessment_no',
        'department_id',
        'control_area',
        'assessment_date',
        'rating',
        'score',
        'observations',
        'action_plan',
        'assessed_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'score'           => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public static function generateAssessmentNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-ICA-{$year}-";

        $last = self::where('assessment_no', 'like', "{$prefix}%")
            ->orderByDesc('assessment_no')
            ->value('assessment_no');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getRatingBadgeClassAttribute(): string
    {
        return match ($this->rating) {
            'Effective'         => 'bg-success',
            'Needs Improvement' => 'bg-warning text-dark',
            'Deficient'         => 'bg-danger-subtle text-danger border border-danger-subtle',
            default             => 'bg-danger',
        };
    }
}
