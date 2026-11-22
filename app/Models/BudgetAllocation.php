<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAllocation extends Model
{
    public const CATEGORIES = [
        'Personnel & Salaries',
        'Goods & Services',
        'Capital & Assets',
        'Youth Programs & NYPP',
        'Sports Competitions & Facilities',
        'Fuel & Fleet Operations',
        'Utilities & Office Maintenance',
        'Travel & Per Diem',
        'Monitoring & Evaluation',
        'Project Delivery (PDU)',
        'Other',
    ];

    public const FUNDING_SOURCES = [
        'GoL National Budget',
        'MFDP Allotment Release',
        'Donor / Partner Grant',
        'Special Project Fund',
        'Internal Ministry Revenue',
    ];

    protected $fillable = [
        'fiscal_year',
        'department_id',
        'budget_code',
        'category',
        'allocated_usd',
        'allocated_lrd',
        'funding_source',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'allocated_usd' => 'decimal:2',
        'allocated_lrd' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getSpentUsdAttribute(): float
    {
        return (float) $this->transactions()
            ->where('transaction_type', '!=', 'Budget Release / Receipt')
            ->whereIn('status', ['Approved', 'Paid'])
            ->sum('amount_usd');
    }

    public function getRemainingUsdAttribute(): float
    {
        return round((float) $this->allocated_usd - $this->spent_usd, 2);
    }

    public function getUtilizationPercentAttribute(): int
    {
        if ((float) $this->allocated_usd <= 0) {
            return 0;
        }
        return (int) min(100, round(($this->spent_usd / (float) $this->allocated_usd) * 100));
    }
}
