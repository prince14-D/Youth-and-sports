<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequest extends Model
{
    use SoftDeletes;

    public const CATEGORIES = ['Goods', 'Works', 'Consulting Services', 'Non-Consulting Services'];

    public const METHODS = [
        'Request for Quotation (RFQ)',
        'National Competitive Bidding (NCB)',
        'International Competitive Bidding (ICB)',
        'Sole Source / Direct Procurement',
    ];

    public const STATUSES = [
        'Draft', 'Open for Bids', 'Bid Evaluation', 'Awarded', 'Contract Signed', 'Completed', 'Cancelled',
    ];

    /** Ministry-configurable compliance thresholds (USD). */
    public const RFQ_CEILING_USD = 25000;
    public const SOLE_SOURCE_REVIEW_USD = 5000;
    public const MIN_COMPETITIVE_BIDS = 3;
    public const DMA_APPROVAL_THRESHOLD_USD = 5000;
    public const TECH_WEIGHT = 0.70;
    public const FIN_WEIGHT = 0.30;

    protected $fillable = [
        'tender_no', 'title', 'description', 'requesting_department_id', 'category', 'method',
        'estimated_cost_usd', 'budget_allocation_id', 'status', 'advertised_date', 'bid_deadline',
        'justification', 'awarded_supplier_id', 'awarded_bid_id', 'awarded_amount_usd', 'awarded_at',
        'award_note', 'contract_signed_date', 'delivery_due_date', 'completed_at', 'cancellation_reason',
        'ministry_document_id', 'created_by', 'awarded_by',
    ];

    protected $casts = [
        'estimated_cost_usd'   => 'decimal:2',
        'awarded_amount_usd'   => 'decimal:2',
        'advertised_date'      => 'date',
        'bid_deadline'         => 'date',
        'contract_signed_date' => 'date',
        'delivery_due_date'    => 'date',
        'awarded_at'           => 'datetime',
        'completed_at'         => 'datetime',
    ];

    public function requestingDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requesting_department_id');
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(ProcurementBid::class);
    }

    public function awardedSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'awarded_supplier_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function awarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SupplierEvaluation::class);
    }

    public function getIsSoleSourceAttribute(): bool
    {
        return str_starts_with($this->method, 'Sole Source');
    }

    public static function generateTenderNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-PROC-{$year}-";
        $last = self::withTrashed()->where('tender_no', 'like', "{$prefix}%")->orderByDesc('tender_no')->value('tender_no');
        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /** Recompute technical/financial/total scores for all responsive bids on this tender. */
    public function recomputeScores(): void
    {
        $bids = $this->bids()->get();
        $responsive = $bids->where('is_responsive', true);
        $lowest = $responsive->min('bid_amount_usd');

        foreach ($bids as $bid) {
            if (!$bid->is_responsive || $bid->technical_score === null || !$lowest || (float) $bid->bid_amount_usd <= 0) {
                $bid->update(['financial_score' => null, 'total_score' => null]);
                continue;
            }
            $fin = round(((float) $lowest / (float) $bid->bid_amount_usd) * 100, 2);
            $total = round($bid->technical_score * self::TECH_WEIGHT + $fin * self::FIN_WEIGHT, 2);
            $bid->update([
                'financial_score' => $fin,
                'total_score'     => $total,
                'status'          => in_array($bid->status, ['Awarded', 'Rejected'], true) ? $bid->status : 'Evaluated',
            ]);
        }
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Open for Bids'   => 'bg-primary',
            'Bid Evaluation'  => 'bg-info text-dark',
            'Awarded'         => 'bg-success',
            'Contract Signed' => 'bg-success',
            'Completed'       => 'bg-dark',
            'Cancelled'       => 'bg-danger',
            default           => 'bg-secondary',
        };
    }
}
