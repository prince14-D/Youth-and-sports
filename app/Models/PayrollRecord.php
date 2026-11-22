<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRecord extends Model
{
    protected $fillable = [
        'payroll_period_id', 'employee_id',
        'basic_salary_lrd', 'basic_salary_usd',
        'allowances_lrd', 'allowances_usd',
        'overtime_lrd', 'deductions_lrd',
        'tax_lrd', 'pension_lrd',
        'net_pay_lrd', 'net_pay_usd',
        'status',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(PayrollLineItem::class)->orderBy('type')->orderBy('id');
    }

    /**
     * Recalculate allowance, overtime, deduction, and net pay columns
     * from base salary + statutory 10% base allowance + custom itemized lines.
     */
    public function recalculateTotals(): self
    {
        $items = $this->lineItems()->get();

        $basicLrd = (float) $this->basic_salary_lrd;
        $basicUsd = (float) $this->basic_salary_usd;

        // Standard civil service 10% base allowance + custom itemized allowances
        $baseAllowLrd = round($basicLrd * 0.10, 2);
        $baseAllowUsd = round($basicUsd * 0.10, 2);

        $customAllowLrd = (float) $items->where('type', 'allowance')->where('currency', 'LRD')->where('category', '!=', 'Overtime Pay')->sum('amount');
        $customAllowUsd = (float) $items->where('type', 'allowance')->where('currency', 'USD')->sum('amount');
        $overtimeLrd    = (float) $items->where('type', 'allowance')->where('currency', 'LRD')->where('category', 'Overtime Pay')->sum('amount');

        $customDedLrd   = (float) $items->where('type', 'deduction')->where('currency', 'LRD')->sum('amount');
        $customDedUsd   = (float) $items->where('type', 'deduction')->where('currency', 'USD')->sum('amount');

        $allowLrd   = round($baseAllowLrd + $customAllowLrd, 2);
        $allowUsd   = round($baseAllowUsd + $customAllowUsd, 2);
        $taxLrd     = (float) $this->tax_lrd;
        $pensionLrd = (float) $this->pension_lrd;

        $netLrd = max(0, round($basicLrd + $allowLrd + $overtimeLrd - $taxLrd - $pensionLrd - $customDedLrd, 2));
        $netUsd = max(0, round($basicUsd + $allowUsd - $customDedUsd, 2));

        $this->update([
            'allowances_lrd' => $allowLrd,
            'allowances_usd' => $allowUsd,
            'overtime_lrd'   => round($overtimeLrd, 2),
            'deductions_lrd' => round($customDedLrd, 2),
            'net_pay_lrd'    => $netLrd,
            'net_pay_usd'    => $netUsd,
        ]);

        return $this->fresh(['lineItems']);
    }
}
