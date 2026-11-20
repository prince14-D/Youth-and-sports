<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentCommunication extends Model
{
    protected $fillable = [
        'from_department_id',
        'to_department_id',
        'sent_by',
        'reference_no',
        'subject',
        'message',
        'communication_type',
        'attachment_path',
        'status',
        'response',
        'response_attachment_path',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(DepartmentCommunicationRecipient::class);
    }
}

