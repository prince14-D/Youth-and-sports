<?php

namespace App\Traits;

use App\Models\ApprovalTrail;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMultiLevelApproval
{
    public function approvalTrails(): MorphMany
    {
        return $this->morphMany(ApprovalTrail::class, 'approvable')->orderBy('acted_at')->orderBy('id');
    }

    public function getCurrentStageInfoAttribute(): array
    {
        return ApprovalWorkflowService::stageInfo((int) ($this->approval_stage ?? 1));
    }

    public function getCurrentStageLabelAttribute(): string
    {
        $stage = (int) ($this->approval_stage ?? 1);
        if ($this->status === 'Rejected') {
            return 'Rejected';
        }
        if ($this->status === 'Approved' || $this->status === 'Paid' || $stage >= 4) {
            return 'Fully Approved (3/3)';
        }
        if ($stage === 0) {
            return 'Draft (Not Submitted)';
        }
        $info = ApprovalWorkflowService::stageInfo($stage);
        return "Stage {$stage}/3: {$info['short']}";
    }

    public function isAwaitingWorkflow(): bool
    {
        $stage = (int) ($this->approval_stage ?? 0);
        return in_array($this->status, ['Pending', 'Processing'], true) && $stage >= 1 && $stage <= 3;
    }

    public function canUserApprove(?User $user): bool
    {
        if (!$user || !$this->isAwaitingWorkflow()) {
            return false;
        }
        return ApprovalWorkflowService::canUserActOnStage($user, (int) $this->approval_stage);
    }

    public function getNextActionLabelAttribute(): string
    {
        return match ((int) ($this->approval_stage ?? 1)) {
            1 => 'Verify & Forward to Asst. Director',
            2 => 'Endorse & Forward to HR Director',
            3 => 'Grant Final HR Director Approval',
            default => 'Approve',
        };
    }

    public function trailForStage(int $stage): ?ApprovalTrail
    {
        return $this->approvalTrails
            ->where('stage', $stage)
            ->whereIn('action', ['approved', 'rejected'])
            ->last();
    }
}
