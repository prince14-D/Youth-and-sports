<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLineItem extends Model
{
    public const ALLOWANCE_CATEGORIES = [
        'Housing Allowance',
        'Transport Allowance',
        'Hazard Allowance',
        'Communication Stipend',
        'Acting / Responsibility Allowance',
        'Overtime Pay',
        'Field / Per Diem Allowance',
        'Other Allowance',
    ];

    public const DEDUCTION_CATEGORIES = [
        'Salary Advance Recovery',
        'Staff Loan Repayment',
        'Union / Association Dues',
        'Absenteeism Deduction',
        'Equipment / Asset Recovery',
        'Court / Statutory Garnishment',
        'Other Deduction',
    ];

    protected $fillable = [
        'payroll_record_id',
        'type',
        'category',
        'description',
        'currency',
        'amount',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class, 'payroll_record_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
