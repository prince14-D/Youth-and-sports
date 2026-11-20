<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    protected $fillable = [
        'payroll_period_id', 'employee_id',
        'basic_salary_lrd', 'basic_salary_usd',
        'allowances_lrd', 'allowances_usd',
        'overtime_lrd', 'deductions_lrd',
        'tax_lrd', 'pension_lrd',
        'net_pay_lrd', 'net_pay_usd',
        'status'
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
