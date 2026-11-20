<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTraining extends Model
{
    protected $table = 'employee_trainings';
    protected $fillable = ['employee_id', 'training_id', 'status', 'certificate_path', 'completion_date'];
    protected $casts = [
        'completion_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
