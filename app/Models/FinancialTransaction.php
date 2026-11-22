<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'Expenditure / Payment',
        'Vendor Payment',
        'Payroll Disbursement',
        'Imprest / Petty Cash',
        'Budget Release / Receipt',
        'Refund / Adjustment',
    ];

    public const PAYMENT_METHODS = [
        'Bank Transfer / CBL',
        'Ministry Check',
        'Direct Treasury (MFDP)',
        'Mobile Money',
        'Cash / Petty Cash',
    ];

    protected $fillable = [
        'voucher_no',
        'transaction_type',
        'category',
        'department_id',
        'budget_allocation_id',
        'payee_name',
        'description',
        'transaction_date',
        'amount_lrd',
        'amount_usd',
        'payment_method',
        'bank_reference',
        'status',
        'attachment_path',
        'ministry_document_id',
        'recorded_by',
        'approved_by',
        'approved_at',
        'paid_at',
        'rejection_reason',
        'audit_status',
        'audited_by',
        'audited_at',
        'audit_notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'approved_at'      => 'datetime',
        'paid_at'          => 'datetime',
        'audited_at'       => 'datetime',
        'amount_lrd'       => 'decimal:2',
        'amount_usd'       => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function getAuditBadgeClassAttribute(): string
    {
        return match ($this->audit_status) {
            'Verified'             => 'bg-success',
            'Queried'              => 'bg-warning text-dark',
            'Irregularity Flagged' => 'bg-danger',
            default                => 'bg-secondary-subtle text-secondary border',
        };
    }

    public static function generateVoucherNo(string $transactionType): string
    {
        $year = now()->format('Y');
        $prefix = $transactionType === 'Budget Release / Receipt'
            ? "MYS-RV-{$year}-"
            : "MYS-PV-{$year}-";

        $last = self::withTrashed()
            ->where('voucher_no', 'like', "{$prefix}%")
            ->orderByDesc('voucher_no')
            ->value('voucher_no');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getIsInflowAttribute(): bool
    {
        return $this->transaction_type === 'Budget Release / Receipt';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Paid'     => 'bg-success',
            'Approved' => 'bg-primary',
            'Rejected' => 'bg-danger',
            default    => 'bg-warning text-dark',
        };
    }
}
