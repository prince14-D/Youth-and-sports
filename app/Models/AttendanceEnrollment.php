<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'allowed_methods',
        'face_photo_path',
        'face_descriptor',
        'qr_token',
        'pin_hash',
        'status',
        'enrolled_at',
        'notes',
    ];

    protected $casts = [
        'allowed_methods' => 'array',
        'face_descriptor' => 'array',
        'enrolled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
