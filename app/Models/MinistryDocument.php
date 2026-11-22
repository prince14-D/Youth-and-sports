<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinistryDocument extends Model
{
    use SoftDeletes;

    public const DOCUMENT_TYPES = [
        'Salary / Payroll Approval Request',
        'Budget / Payment Requisition',
        'Asset / Equipment Requisition',
        'Procurement Request',
        'HR Staffing / Policy Memo',
        'M&E Assessment / Report',
        'PDU Project Implementation Memo',
        'NYPP Youth Program Proposal',
        'Internal Audit Irregularity / Compliance Escalation',
        'Official Correspondence / Directive',
        'Other',
    ];

    protected $fillable = [
        'tracking_number',
        'subject',
        'document_type',
        'priority',
        'confidentiality',
        'origin_department_id',
        'current_department_id',
        'destination_department_id',
        'destination_user_id',
        'allowed_department_ids',
        'sender_id',
        'current_handler_id',
        'amount_lrd',
        'amount_usd',
        'summary',
        'latest_feedback',
        'latest_feedback_by',
        'latest_feedback_at',
        'attachment_path',
        'attachment_original_name',
        'status',
        'revision_count',
        'received_at_current_office_at',
        'completed_at',
    ];

    protected $casts = [
        'allowed_department_ids'        => 'array',
        'amount_lrd'                    => 'decimal:2',
        'amount_usd'                    => 'decimal:2',
        'revision_count'                => 'integer',
        'latest_feedback_at'            => 'datetime',
        'received_at_current_office_at' => 'datetime',
        'completed_at'                  => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function originDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'origin_department_id');
    }

    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function destinationDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'destination_department_id');
    }

    public function destinationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function currentHandler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_handler_id');
    }

    public function latestFeedbackUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_feedback_by');
    }

    public function routingLogs(): HasMany
    {
        return $this->hasMany(DocumentRoutingLog::class)->orderBy('acted_at')->orderBy('id');
    }

    public function latestRoutingLog(): HasOne
    {
        return $this->hasOne(DocumentRoutingLog::class)->latestOfMany('id');
    }

    // ─── Strict Visibility & Privacy Enforcement ─────────────────────────────

    public function hasExecutiveOversight(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $user->hasAnyRole(['System Administrator', 'Minister', 'Deputy Minister for Administration'])
            || $user->can('track all documents');
    }

    public function canUserView(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->hasExecutiveOversight($user)) {
            return true;
        }

        // Sender or directly destined person always sees the file
        if ((int) $this->sender_id === (int) $user->id || ($this->destination_user_id && (int) $this->destination_user_id === (int) $user->id)) {
            return true;
        }

        $deptId = $user->employee?->department_id;
        if (!$deptId) {
            return false;
        }

        if (in_array((int) $deptId, $this->effectiveAllowedDepartmentIds(), true)) {
            return true;
        }

        return false;
    }

    public function canUserActOnCurrentOffice(?User $user): bool
    {
        if (!$user || in_array($this->status, ['Completed', 'Rejected'], true)) {
            return false;
        }

        if ($user->hasRole('System Administrator')) {
            return true;
        }

        // If reverted back to origin, the sender / origin office uses the Resubmit flow instead
        if ($this->status === 'Returned') {
            return $this->canSenderResubmit($user);
        }

        // Directly destined person can always approve, revert, give feedback, or forward while file is at destination (or assigned to them)
        if ($this->destination_user_id && (int) $this->destination_user_id === (int) $user->id) {
            return true;
        }

        $deptId = $user->employee?->department_id;
        if ($deptId && (int) $deptId === (int) $this->current_department_id) {
            return true;
        }

        if ($user->hasRole('Deputy Minister for Administration')) {
            $code = $this->currentDepartment?->code;
            return in_array($code, ['DMA', 'ADMIN'], true);
        }

        if ($user->hasRole('Minister')) {
            return $this->currentDepartment?->code === 'MINISTER';
        }

        return false;
    }

    public function canSenderResubmit(?User $user): bool
    {
        if (!$user || $this->status !== 'Returned') {
            return false;
        }
        if ($user->hasRole('System Administrator')) {
            return true;
        }
        if ((int) $this->sender_id === (int) $user->id) {
            return true;
        }
        $deptId = $user->employee?->department_id;
        return $deptId && (int) $deptId === (int) $this->origin_department_id;
    }

    public function canUserGiveFeedback(?User $user): bool
    {
        if (!$user || in_array($this->status, ['Completed', 'Rejected'], true)) {
            return false;
        }
        return $this->canUserActOnCurrentOffice($user)
            || $this->hasExecutiveOversight($user)
            || (int) $this->sender_id === (int) $user->id;
    }

    public function effectiveAllowedDepartmentIds(): array
    {
        $ids = array_map('intval', is_array($this->allowed_department_ids) ? $this->allowed_department_ids : []);
        $ids[] = (int) $this->origin_department_id;
        $ids[] = (int) $this->current_department_id;
        $ids[] = (int) $this->destination_department_id;
        return array_values(array_unique(array_filter($ids)));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($this->hasExecutiveOversight($user)) {
            return $query;
        }

        $deptId = $user->employee?->department_id;

        return $query->where(function (Builder $q) use ($user, $deptId) {
            $q->where('sender_id', $user->id)
              ->orWhere('destination_user_id', $user->id);

            if ($deptId) {
                $q->orWhere('origin_department_id', $deptId)
                  ->orWhere('current_department_id', $deptId)
                  ->orWhere('destination_department_id', $deptId)
                  ->orWhereJsonContains('allowed_department_ids', (int) $deptId);
            }
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function generateTrackingNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "MYS-DOC-{$year}-";
        $last = self::withTrashed()
            ->where('tracking_number', 'like', "{$prefix}%")
            ->orderByDesc('tracking_number')
            ->value('tracking_number');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getHoursInCurrentOfficeAttribute(): int
    {
        $since = $this->received_at_current_office_at ?? $this->updated_at ?? $this->created_at;
        return $since ? (int) $since->diffInHours(now()) : 0;
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->status === 'Returned' ? 'Reverted' : $this->status;
    }

    public function getAttachmentFilenameAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }
        return $this->attachment_original_name ?: basename($this->attachment_path);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Approved', 'Completed'    => 'bg-success',
            'Rejected'                 => 'bg-danger',
            'Returned'                 => 'bg-warning text-dark',
            'Received', 'Under Review' => 'bg-primary',
            default                    => 'bg-info text-dark',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Urgent' => 'bg-danger',
            'High'   => 'bg-warning text-dark',
            default  => 'bg-light text-dark border',
        };
    }
}
