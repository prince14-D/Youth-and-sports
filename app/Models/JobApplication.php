<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    protected $fillable = ['vacancy_id', 'employee_id', 'applicant_name', 'email', 'phone', 'cover_letter', 'resume_path', 'status'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
