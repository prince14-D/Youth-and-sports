<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $fillable = ['title', 'type', 'description', 'provider', 'start_date', 'end_date', 'location', 'is_active'];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }
}
