<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCommunication extends Model
{
    protected $fillable = [
        'sent_by', 'recipient_scope', 'department_id', 'subject', 'body',
        'recipient_count', 'status', 'error_message', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sent_by'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
}
