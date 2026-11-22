<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditFinding extends Model
{
    use SoftDeletes;

    public const AUDIT_AREAS = [
        'Financial Transactions',
        'Payroll & Personnel',
        'Asset & Fleet Control',
        'Procurement & Contracts',
        'Document Compliance',
        'Internal Controls',
    ];

    public const SEVERITIES = ['Low', 'Medium', 'High', 'Critical'];

    public const STATUSES = ['Open', 'Under Investigation', 'Remediated', 'Closed'];

    protected $fillable = [
        'finding_no',
        'title',
        'audit_area',
        'department_id',
        'severity',
        'status',
        'financial_impact_usd',
        'description',
        'recommendation',
        'management_response',
        'financial_transaction_id',
        'ministry_document_id',
        'due_date',
        'reported_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'financial_impact_usd' => 'decimal:2',
        'due_date'             => 'date',
        'resolved_at'          => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'financial_transaction_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public static function generateFindingNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-AUD-{$year}-";

        $last = self::withTrashed()
            ->where('finding_no', 'like', "{$prefix}%")
            ->orderByDesc('finding_no')
            ->value('finding_no');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->severity) {
            'Critical' => 'bg-danger',
            'High'     => 'bg-warning text-dark',
            'Medium'   => 'bg-info text-dark',
            default    => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Closed', 'Remediated' => 'bg-success',
            'Under Investigation'  => 'bg-primary',
            default                => 'bg-danger-subtle text-danger border border-danger-subtle',
        };
    }
}
