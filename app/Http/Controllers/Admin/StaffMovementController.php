<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\StaffMovement;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffMovementController extends Controller
{
    public function index(): View
    {
        $movements = StaffMovement::with(['employee', 'fromDepartment', 'toDepartment', 'requester', 'approvalTrails'])
            ->latest()
            ->get();
        $employees = Employee::active()->with('department')->orderBy('first_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $summary = [
            'pending'  => $movements->where('status', 'Pending')->count(),
            'stage1'   => $movements->where('status', 'Pending')->where('approval_stage', 1)->count(),
            'stage2'   => $movements->where('status', 'Pending')->where('approval_stage', 2)->count(),
            'stage3'   => $movements->where('status', 'Pending')->where('approval_stage', 3)->count(),
            'approved' => $movements->where('status', 'Approved')->count(),
        ];

        return view('staff-movements.index', compact('movements', 'employees', 'departments', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'movement_type'    => 'required|in:Transfer,Promotion,Acting Appointment,Department Change',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_designation'   => 'nullable|string|max:150',
            'effective_date'   => 'required|date',
            'reason'           => 'required|string|max:1000',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $movement = StaffMovement::create($data + [
            'from_department_id' => $employee->department_id,
            'from_designation'   => $employee->designation,
            'status'             => 'Pending',
            'approval_stage'     => 1,
            'requested_by'       => $request->user()->id,
        ]);

        ApprovalWorkflowService::recordSubmission(
            $movement,
            $request->user(),
            "{$data['movement_type']} for {$employee->full_name} submitted for Stage 1 HR Officer verification."
        );
        ApprovalWorkflowService::notifyRole(
            'HR Officer',
            'workflow_stage_pending',
            'Staff Movement Awaiting Stage 1 Review',
            "{$data['movement_type']} request for {$employee->full_name} awaits HR Officer review.",
            ['url' => route('staff-movements.index')]
        );
        AuditService::log('created', StaffMovement::class, $movement->id, [], $movement->toArray(), "Submitted {$data['movement_type']} for {$employee->full_name}");

        return back()->with('success', "Staff movement for {$employee->full_name} submitted into the 3-tier HR approval workflow.");
    }

    public function approve(Request $request, StaffMovement $movement): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $movement->load('employee.user');
        abort_if($movement->status !== 'Pending', 422, 'Only pending staff movements can be approved.');

        $result = ApprovalWorkflowService::advance(
            $movement,
            $request->user(),
            $request->input('remarks'),
            "{$movement->movement_type} ({$movement->employee->full_name})",
            route('staff-movements.index')
        );

        if ($result['is_final']) {
            $movement->update([
                'status'         => 'Approved',
                'approval_stage' => 4,
                'reviewed_by'    => $request->user()->id,
                'reviewed_at'    => now(),
            ]);
            $movement->employee->update([
                'department_id' => $movement->to_department_id ?: $movement->employee->department_id,
                'designation'   => $movement->to_designation ?: $movement->employee->designation,
            ]);
            UserNotificationService::send(
                $movement->employee->user,
                'staff_movement_approved',
                "{$movement->movement_type} Approved",
                "Your {$movement->movement_type} effective {$movement->effective_date->format('d M Y')} received final HR Director approval.",
                ['url' => route('dashboard')]
            );
        } else {
            $movement->update([
                'approval_stage' => $result['next_stage'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function reject(Request $request, StaffMovement $movement): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $movement->load('employee.user');
        abort_if($movement->status !== 'Pending', 422, 'Only pending staff movements can be rejected.');

        $trail = ApprovalWorkflowService::reject(
            $movement,
            $request->user(),
            $data['rejection_reason'],
            "{$movement->movement_type} ({$movement->employee->full_name})"
        );

        $movement->update([
            'status'           => 'Rejected',
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        UserNotificationService::send(
            $movement->employee->user,
            'staff_movement_rejected',
            "{$movement->movement_type} Rejected",
            "Your {$movement->movement_type} was rejected at Stage {$trail->stage}: {$data['rejection_reason']}",
            ['url' => route('dashboard')]
        );

        return back()->with('success', "Staff movement rejected at Stage {$trail->stage} [{$trail->digital_signature}].");
    }
}
