<?php

namespace App\Services;

use App\Models\ApprovalTrail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowService
{
    public const STAGES = [
        1 => [
            'number' => 1,
            'role'   => 'HR Officer',
            'short'  => 'HR Officer',
            'label'  => 'Stage 1: HR Officer Review & Verification',
            'verb'   => 'Verified & Forwarded',
        ],
        2 => [
            'number' => 2,
            'role'   => 'Assistant HR Director',
            'short'  => 'Asst. HR Director',
            'label'  => 'Stage 2: Assistant HR Director Endorsement',
            'verb'   => 'Endorsed & Forwarded',
        ],
        3 => [
            'number' => 3,
            'role'   => 'Human Resource Director',
            'short'  => 'HR Director',
            'label'  => 'Stage 3: HR Director Final Approval',
            'verb'   => 'Final Approval Granted',
        ],
    ];

    public static function stageInfo(int $stage): array
    {
        return self::STAGES[$stage] ?? [
            'number' => $stage,
            'role'   => 'System Administrator',
            'short'  => $stage >= 4 ? 'Completed' : 'Draft',
            'label'  => $stage >= 4 ? 'All 3 Stages Completed' : 'Awaiting Submission',
            'verb'   => 'Updated',
        ];
    }

    public static function canUserActOnStage(User $user, int $stage): bool
    {
        if ($user->hasRole('System Administrator')) {
            return $stage >= 1 && $stage <= 3;
        }

        return match ($stage) {
            1 => $user->hasRole('HR Officer'),
            2 => $user->hasRole('Assistant HR Director'),
            3 => $user->hasRole('Human Resource Director'),
            default => false,
        };
    }

    public static function userPrimaryStage(User $user): ?int
    {
        if ($user->hasRole('Human Resource Director')) {
            return 3;
        }
        if ($user->hasRole('Assistant HR Director')) {
            return 2;
        }
        if ($user->hasRole('HR Officer')) {
            return 1;
        }
        return null;
    }

    public static function generateSignature(Model $model, int $stage, User $actor, string $action, string $timestamp): string
    {
        $payload = implode('|', [
            get_class($model),
            $model->getKey(),
            $stage,
            $actor->id,
            $action,
            $timestamp,
        ]);
        return 'SIG-' . strtoupper(substr(hash('sha256', $payload), 0, 12));
    }

    public static function recordSubmission(Model $model, User $actor, string $remarks = 'Submitted into 3-tier HR approval workflow.', ?string $ip = null): ApprovalTrail
    {
        $now = now();
        return ApprovalTrail::create([
            'approvable_type'   => get_class($model),
            'approvable_id'     => $model->getKey(),
            'stage'             => 0,
            'stage_label'       => 'Submitted to Approval Pipeline',
            'required_role'     => 'HR Officer',
            'action'            => 'submitted',
            'actor_id'          => $actor->id,
            'actor_name'        => $actor->name,
            'actor_role'        => $actor->primary_role,
            'remarks'           => $remarks,
            'digital_signature' => self::generateSignature($model, 0, $actor, 'submitted', $now->toIso8601String()),
            'ip_address'        => $ip ?? (app()->bound('request') ? request()->ip() : null),
            'acted_at'          => $now,
        ]);
    }

    /**
     * Advance a workflow record through Stage 1 -> Stage 2 -> Stage 3 -> Final Approval.
     *
     * @return array{stage:int, is_final:bool, next_stage:int, trail:ApprovalTrail, message:string}
     */
    public static function advance(
        Model $model,
        User $actor,
        ?string $remarks = null,
        string $workflowTitle = 'Request',
        ?string $notifyUrl = null
    ): array {
        $stage = (int) ($model->approval_stage ?? 1);
        abort_unless($stage >= 1 && $stage <= 3, 422, 'This record is not currently awaiting approval.');
        abort_unless(self::canUserActOnStage($actor, $stage), 403, 'You are not authorized to approve Stage ' . $stage . ' (' . self::stageInfo($stage)['role'] . ').');

        $stageMeta = self::stageInfo($stage);
        $now = now();
        $isOverride = $actor->hasRole('System Administrator') && !$actor->hasRole($stageMeta['role']);
        $effectiveRemarks = trim((string) $remarks);
        if ($isOverride) {
            $effectiveRemarks = trim('[Admin Override] ' . ($effectiveRemarks ?: $stageMeta['verb']));
        } elseif ($effectiveRemarks === '') {
            $effectiveRemarks = $stageMeta['verb'];
        }

        $trail = ApprovalTrail::create([
            'approvable_type'   => get_class($model),
            'approvable_id'     => $model->getKey(),
            'stage'             => $stage,
            'stage_label'       => $stageMeta['label'],
            'required_role'     => $stageMeta['role'],
            'action'            => 'approved',
            'actor_id'          => $actor->id,
            'actor_name'        => $actor->name,
            'actor_role'        => $actor->primary_role,
            'remarks'           => $effectiveRemarks,
            'digital_signature' => self::generateSignature($model, $stage, $actor, 'approved', $now->toIso8601String()),
            'ip_address'        => app()->bound('request') ? request()->ip() : null,
            'acted_at'          => $now,
        ]);

        $isFinal = ($stage === 3);
        $nextStage = $isFinal ? 4 : ($stage + 1);

        AuditService::log(
            'approval_stage_' . $stage,
            get_class($model),
            $model->getKey(),
            ['approval_stage' => $stage],
            ['approval_stage' => $nextStage, 'signature' => $trail->digital_signature, 'remarks' => $effectiveRemarks],
            "{$workflowTitle}: {$stageMeta['label']} signed off by {$actor->name} ({$actor->primary_role}) [{$trail->digital_signature}]"
        );

        if (!$isFinal) {
            $nextMeta = self::stageInfo($nextStage);
            self::notifyRole(
                $nextMeta['role'],
                'workflow_stage_pending',
                "{$workflowTitle} Awaiting {$nextMeta['short']}",
                "{$workflowTitle} passed Stage {$stage} ({$actor->name}) and now requires your Stage {$nextStage} sign-off.",
                ['url' => $notifyUrl ?? route('approvals.index')]
            );
            $message = "Stage {$stage} ({$stageMeta['short']}) signed off [{$trail->digital_signature}] and forwarded to {$nextMeta['role']} (Stage {$nextStage}).";
        } else {
            $message = "Final Stage 3 (HR Director) approval completed [{$trail->digital_signature}].";
        }

        return [
            'stage'      => $stage,
            'is_final'   => $isFinal,
            'next_stage' => $nextStage,
            'trail'      => $trail,
            'message'    => $message,
        ];
    }

    /**
     * Reject a workflow record at its current stage and record the digital trail.
     */
    public static function reject(
        Model $model,
        User $actor,
        string $reason,
        string $workflowTitle = 'Request'
    ): ApprovalTrail {
        $stage = (int) ($model->approval_stage ?? 1);
        abort_unless($stage >= 1 && $stage <= 3, 422, 'This record is not currently awaiting approval.');
        abort_unless(self::canUserActOnStage($actor, $stage), 403, 'You are not authorized to reject Stage ' . $stage . ' (' . self::stageInfo($stage)['role'] . ').');

        $stageMeta = self::stageInfo($stage);
        $now = now();

        $trail = ApprovalTrail::create([
            'approvable_type'   => get_class($model),
            'approvable_id'     => $model->getKey(),
            'stage'             => $stage,
            'stage_label'       => $stageMeta['label'],
            'required_role'     => $stageMeta['role'],
            'action'            => 'rejected',
            'actor_id'          => $actor->id,
            'actor_name'        => $actor->name,
            'actor_role'        => $actor->primary_role,
            'remarks'           => $reason,
            'digital_signature' => self::generateSignature($model, $stage, $actor, 'rejected', $now->toIso8601String()),
            'ip_address'        => app()->bound('request') ? request()->ip() : null,
            'acted_at'          => $now,
        ]);

        AuditService::log(
            'approval_rejected_stage_' . $stage,
            get_class($model),
            $model->getKey(),
            ['approval_stage' => $stage],
            ['status' => 'Rejected', 'reason' => $reason, 'signature' => $trail->digital_signature],
            "{$workflowTitle}: Rejected at Stage {$stage} by {$actor->name} ({$actor->primary_role}). Reason: {$reason}"
        );

        return $trail;
    }

    public static function notifyRole(string $roleName, string $type, string $title, string $message, array $data = []): void
    {
        try {
            $recipients = User::role($roleName)->where('is_active', true)->get();
            foreach ($recipients as $recipient) {
                UserNotificationService::send($recipient, $type, $title, $message, $data);
            }
        } catch (\Throwable $e) {
            // Do not break approval transitions if notification fails
        }
    }
}
