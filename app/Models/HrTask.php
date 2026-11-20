<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrTask extends Model
{
    use HasFactory;

    protected $table = 'hr_tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDueDateBadgeAttribute()
    {
        if (!$this->due_date) return null;

        if ($this->due_date->isToday()) {
            return 'Today';
        } elseif ($this->due_date->isTomorrow()) {
            return 'Tomorrow';
        } elseif ($this->due_date->isYesterday()) {
            return 'Overdue';
        }

        return $this->due_date->format('d M');
    }
}
