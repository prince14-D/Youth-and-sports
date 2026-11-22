<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRequest extends Model
{
    protected $fillable = [
        'request_no',
        'department_id',
        'requested_by',
        'item_name',
        'category',
        'quantity',
        'estimated_cost_usd',
        'priority',
        'justification',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'ministry_document_id',
    ];

    protected $casts = [
        'estimated_cost_usd' => 'decimal:2',
        'reviewed_at'        => 'datetime',
        'quantity'           => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MinistryDocument::class, 'ministry_document_id');
    }

    public static function generateRequestNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-AST-{$year}-";
        $last = self::where('request_no', 'like', "{$prefix}%")->orderByDesc('request_no')->value('request_no');
        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
