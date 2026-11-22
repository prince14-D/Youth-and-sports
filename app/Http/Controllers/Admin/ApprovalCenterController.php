<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalTrail;
use App\Models\ContractRenewal;
use App\Models\LeaveApplication;
use App\Models\PayrollPeriod;
use App\Models\StaffMovement;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalCenterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $userStage = ApprovalWorkflowService::userPrimaryStage($user);
        $isAdmin = $user->hasRole('System Administrator');

        $pendingLeaves = LeaveApplication::with(['employee.department', 'leaveType', 'approvalTrails'])
            ->where('status', 'Pending')
            ->orderBy('approval_stage')
            ->latest()
            ->get();

        $pendingRenewals = ContractRenewal::with(['employee.department', 'requester', 'approvalTrails'])
            ->where('status', 'Pending')
            ->orderBy('approval_stage')
            ->latest()
            ->get();

        $pendingMovements = StaffMovement::with(['employee', 'fromDepartment', 'toDepartment', 'requester', 'approvalTrails'])
            ->where('status', 'Pending')
            ->orderBy('approval_stage')
            ->latest()
            ->get();

        $pendingPayrolls = PayrollPeriod::with(['creator', 'approvalTrails'])
            ->withCount('records')
            ->where('status', 'Processing')
            ->orderBy('approval_stage')
            ->latest()
            ->get();

        // Build unified item list for easy filtering & rendering
        $allPending = collect();

        foreach ($pendingLeaves as $item) {
            $allPending->push([
                'key'           => 'leave-' . $item->id,
                'module'        => 'Leave Request',
                'badge_class'   => 'bg-primary-subtle text-primary',
                'icon'          => 'bi-calendar2-check',
                'title'         => ($item->employee?->full_name ?? 'Employee') . ' — ' . ($item->leaveType?->name ?? 'Leave'),
                'subtitle'      => $item->start_date?->format('d M Y') . ' to ' . $item->end_date?->format('d M Y') . ' (' . $item->days_requested . ' days)',
                'detail'        => $item->reason,
                'stage'         => (int) $item->approval_stage,
                'model'         => $item,
                'can_act'       => $item->canUserApprove($user),
                'approve_route' => route('leave.approve', $item->id),
                'reject_route'  => route('leave.reject', $item->id),
                'module_route'  => route('leave.approvals'),
                'created_at'    => $item->created_at,
            ]);
        }

        foreach ($pendingRenewals as $item) {
            $allPending->push([
                'key'           => 'renewal-' . $item->id,
                'module'        => 'Contract Renewal',
                'badge_class'   => 'bg-success-subtle text-success',
                'icon'          => 'bi-arrow-repeat',
                'title'         => ($item->employee?->full_name ?? 'Contractor') . ' — Contract Extension',
                'subtitle'      => 'Current End: ' . ($item->current_end_date?->format('d M Y') ?? '—') . ' → Proposed: ' . ($item->proposed_end_date?->format('d M Y') ?? '—'),
                'detail'        => $item->reason,
                'stage'         => (int) $item->approval_stage,
                'model'         => $item,
                'can_act'       => $item->canUserApprove($user),
                'approve_route' => route('contracts.renewals.approve', $item->id),
                'reject_route'  => route('contracts.renewals.reject', $item->id),
                'module_route'  => route('contracts.renewals'),
                'created_at'    => $item->created_at,
            ]);
        }

        foreach ($pendingMovements as $item) {
            $allPending->push([
                'key'           => 'movement-' . $item->id,
                'module'        => 'Staff Movement',
                'badge_class'   => 'bg-info-subtle text-info-emphasis',
                'icon'          => 'bi-arrow-left-right',
                'title'         => ($item->employee?->full_name ?? 'Employee') . ' — ' . $item->movement_type,
                'subtitle'      => ($item->fromDepartment?->name ?? '—') . ' → ' . ($item->toDepartment?->name ?? $item->fromDepartment?->name ?? '—') . ' (Effective ' . $item->effective_date?->format('d M Y') . ')',
                'detail'        => $item->reason,
                'stage'         => (int) $item->approval_stage,
                'model'         => $item,
                'can_act'       => $item->canUserApprove($user),
                'approve_route' => route('staff-movements.approve', $item->id),
                'reject_route'  => route('staff-movements.reject', $item->id),
                'module_route'  => route('staff-movements.index'),
                'created_at'    => $item->created_at,
            ]);
        }

        foreach ($pendingPayrolls as $item) {
            $allPending->push([
                'key'           => 'payroll-' . $item->id,
                'module'        => 'Payroll Cycle',
                'badge_class'   => 'bg-warning-subtle text-warning-emphasis',
                'icon'          => 'bi-cash-stack',
                'title'         => $item->name . ' (' . $item->records_count . ' Staff)',
                'subtitle'      => $item->start_date?->format('d M Y') . ' to ' . $item->end_date?->format('d M Y'),
                'detail'        => 'Monthly payroll disbursement cycle awaiting tiered sign-off.',
                'stage'         => (int) $item->approval_stage,
                'model'         => $item,
                'can_act'       => $item->canUserApprove($user),
                'approve_route' => route('payroll.approve', $item->id),
                'reject_route'  => route('payroll.reject', $item->id),
                'module_route'  => route('payroll.show', $item->id),
                'created_at'    => $item->created_at,
            ]);
        }

        $allPending = $allPending->sortByDesc('created_at')->values();
        $myQueue = $allPending->filter(fn ($i) => $i['can_act'])->values();

        $stageCounts = [
            'stage1'   => $allPending->where('stage', 1)->count(),
            'stage2'   => $allPending->where('stage', 2)->count(),
            'stage3'   => $allPending->where('stage', 3)->count(),
            'my_queue' => $myQueue->count(),
            'total'    => $allPending->count(),
        ];

        $recentTrails = ApprovalTrail::with('actor')
            ->latest('acted_at')
            ->take(25)
            ->get();

        return view('approvals.index', compact(
            'allPending',
            'myQueue',
            'stageCounts',
            'recentTrails',
            'userStage',
            'isAdmin'
        ));
    }
}
