<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function apply(Request $request): View
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $isSelfOnly = $request->user()->hasAnyRole(['Employee', 'Contractor']) && !$request->user()->can('view leave');
        $employees = $isSelfOnly
            ? Employee::whereKey($request->user()->employee_id)->get()
            : Employee::active()->orderBy('first_name')->get();

        return view('leave.apply', compact('leaveTypes', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:500',
        ]);

        if ($request->user()->hasAnyRole(['Employee', 'Contractor']) && !$request->user()->can('view leave')) {
            abort_unless((int) $request->user()->employee_id === (int) $validated['employee_id'], 403);
        }

        $start = new \DateTime($request->start_date);
        $end = new \DateTime($request->end_date);
        $diff = $start->diff($end)->days + 1;

        $overlap = LeaveApplication::where('employee_id', $validated['employee_id'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'This employee already has a pending or approved leave covering part of those dates.');
        }

        $app = LeaveApplication::create([
            'employee_id'    => $validated['employee_id'],
            'leave_type_id'  => $validated['leave_type_id'],
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'days_requested' => $diff,
            'reason'         => $validated['reason'],
            'status'         => 'Pending',
            'approval_stage' => 1,
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        ApprovalWorkflowService::recordSubmission(
            $app,
            $request->user(),
            "Leave request ({$app->leaveType->name}, {$diff} days) submitted for Stage 1 HR Officer review."
        );
        ApprovalWorkflowService::notifyRole(
            'HR Officer',
            'workflow_stage_pending',
            'New Leave Request (Stage 1)',
            "{$employee->full_name} submitted a {$diff}-day {$app->leaveType->name} request awaiting HR Officer verification.",
            ['url' => route('leave.approvals')]
        );

        AuditService::log(
            'created',
            LeaveApplication::class,
            $app->id,
            [],
            $app->toArray(),
            "Submitted leave application for employee: {$employee->full_name} ({$diff} days)"
        );
        UserNotificationService::send(
            $employee->user,
            'leave_submitted',
            'Leave request submitted',
            "Your {$app->leaveType->name} request entered Stage 1 (HR Officer Review).",
            ['url' => route('dashboard')]
        );

        return redirect()->route('leave.apply')
            ->with('success', "Leave application for {$employee->full_name} submitted into the 3-tier HR approval workflow.");
    }

    public function approvals(Request $request): View
    {
        $stageFilter = $request->query('stage');
        $query = LeaveApplication::with(['employee.department', 'leaveType', 'approvalTrails'])
            ->where('status', 'Pending')
            ->orderBy('approval_stage')
            ->orderByDesc('created_at');

        if ($stageFilter && in_array((int) $stageFilter, [1, 2, 3], true)) {
            $query->where('approval_stage', (int) $stageFilter);
        }

        $applications = $query->get();

        $recentDecided = LeaveApplication::with(['employee.department', 'leaveType', 'approvalTrails'])
            ->whereIn('status', ['Approved', 'Rejected'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $summary = [
            'pending'             => LeaveApplication::where('status', 'Pending')->count(),
            'stage1'              => LeaveApplication::where('status', 'Pending')->where('approval_stage', 1)->count(),
            'stage2'              => LeaveApplication::where('status', 'Pending')->where('approval_stage', 2)->count(),
            'stage3'              => LeaveApplication::where('status', 'Pending')->where('approval_stage', 3)->count(),
            'approved_this_month' => LeaveApplication::where('status', 'Approved')->whereMonth('approved_at', now()->month)->whereYear('approved_at', now()->year)->count(),
            'on_leave_today'      => LeaveApplication::where('status', 'Approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
        ];

        return view('leave.approvals', compact('applications', 'recentDecided', 'summary', 'stageFilter'));
    }

    public function approve(Request $request, $id): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $app = LeaveApplication::with(['employee.user', 'leaveType'])->findOrFail($id);
        abort_if($app->status !== 'Pending', 422, 'Only pending leave requests can be approved.');

        $result = ApprovalWorkflowService::advance(
            $app,
            $request->user(),
            $request->input('remarks'),
            "Leave Request ({$app->employee->full_name})",
            route('leave.approvals')
        );

        if ($result['is_final']) {
            $app->update([
                'status'         => 'Approved',
                'approval_stage' => 4,
                'approved_by'    => $request->user()->id,
                'approved_at'    => now(),
            ]);
            UserNotificationService::send(
                $app->employee->user,
                'leave_approved',
                'Leave Request Fully Approved',
                "Your {$app->leaveType->name} ({$app->start_date->format('d M Y')} - {$app->end_date->format('d M Y')}) received final HR Director approval.",
                ['url' => route('dashboard')]
            );
        } else {
            $app->update([
                'approval_stage' => $result['next_stage'],
            ]);
            $nextRole = ApprovalWorkflowService::stageInfo($result['next_stage'])['short'];
            UserNotificationService::send(
                $app->employee->user,
                'leave_stage_updated',
                'Leave Request Progressed',
                "Your leave request passed Stage {$result['stage']} and is now awaiting {$nextRole} (Stage {$result['next_stage']}/3).",
                ['url' => route('dashboard')]
            );
        }

        return back()->with('success', $result['message']);
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $app = LeaveApplication::with(['employee.user', 'leaveType'])->findOrFail($id);
        abort_if($app->status !== 'Pending', 422, 'Only pending leave requests can be rejected.');

        $trail = ApprovalWorkflowService::reject(
            $app,
            $request->user(),
            $request->rejection_reason,
            "Leave Request ({$app->employee->full_name})"
        );

        $app->update([
            'status'           => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
        ]);

        UserNotificationService::send(
            $app->employee->user,
            'leave_rejected',
            'Leave request rejected',
            "Your leave request was rejected at Stage {$trail->stage} ({$trail->actor_role}): {$app->rejection_reason}",
            ['url' => route('dashboard')]
        );

        return back()->with('success', "Leave request rejected at Stage {$trail->stage} [{$trail->digital_signature}].");
    }

    public function types(): View
    {
        $types = LeaveType::orderBy('name')->get();
        return view('leave.types', compact('types'));
    }

    public function storeType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:100',
            'code'              => 'required|string|max:10|unique:leave_types,code',
            'days_allowed'      => 'required|integer|min:0',
            'is_paid'           => 'required|boolean',
            'requires_approval' => 'required|boolean',
        ]);

        $type = LeaveType::create($data);

        AuditService::log(
            'created',
            LeaveType::class,
            $type->id,
            [],
            $type->toArray(),
            "Created leave type: {$type->name}"
        );

        return back()->with('success', "Leave type '{$type->name}' created successfully.");
    }
}
