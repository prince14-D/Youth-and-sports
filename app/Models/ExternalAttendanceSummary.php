<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalAttendanceSummary extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'period_month',
        'working_days',
        'days_present',
        'days_absent',
        'days_late',
        'overtime_hours',
        'source_system',
        'notes',
        'imported_by',
    ];

    protected $casts = [
        'working_days'   => 'integer',
        'days_present'   => 'integer',
        'days_absent'    => 'integer',
        'days_late'      => 'integer',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function getAttendanceRateAttribute(): float
    {
        if ($this->working_days <= 0) {
            return 0.0;
        }
        return round(min(100, ($this->days_present / $this->working_days) * 100), 1);
    }
}
