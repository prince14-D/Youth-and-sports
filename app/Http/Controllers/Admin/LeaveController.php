<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function apply(Request $request): View
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $employees = $request->user()->hasRole('Employee')
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

        if ($request->user()->hasRole('Employee')) {
            abort_unless($request->user()->employee_id == $validated['employee_id'], 403);
        }

        $start = new \DateTime($request->start_date);
        $end = new \DateTime($request->end_date);
        $diff = $start->diff($end)->days + 1; // inclusive

        $overlap = LeaveApplication::where('employee_id', $validated['employee_id'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'This employee already has a pending or approved leave covering part of those dates.');
        }

        $data = [
            'employee_id'    => $validated['employee_id'],
            'leave_type_id'  => $validated['leave_type_id'],
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'days_requested' => $diff,
            'reason'         => $validated['reason'],
            'status'         => 'Pending',
        ];

        $app = LeaveApplication::create($data);
        $employee = Employee::find($request->employee_id);

        AuditService::log('created', LeaveApplication::class, $app->id,
            [], $app->toArray(),
            "Submitted leave application for employee: {$employee->full_name} ({$diff} days)"
        );
        UserNotificationService::send($employee->user, 'leave_submitted', 'Leave request submitted', "Your {$app->leaveType->name} request is awaiting HR review.", ['url' => route('employee.dashboard')]);

        return redirect()->route('leave.apply')
            ->with('success', "Leave application for {$employee->full_name} submitted successfully.");
    }

    public function approvals(): View
    {
        $applications = LeaveApplication::with(['employee', 'leaveType'])
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'pending' => LeaveApplication::where('status', 'Pending')->count(),
            'approved_this_month' => LeaveApplication::where('status', 'Approved')->whereMonth('approved_at', now()->month)->whereYear('approved_at', now()->year)->count(),
            'on_leave_today' => LeaveApplication::where('status', 'Approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
        ];

        return view('leave.approvals', compact('applications', 'summary'));
    }

    public function approve(Request $request, $id): RedirectResponse
    {
        $app = LeaveApplication::findOrFail($id);
        $old = $app->toArray();

        $app->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditService::log('updated', LeaveApplication::class, $app->id,
            $old, $app->toArray(),
            "Approved leave application for: {$app->employee->full_name}"
        );
        UserNotificationService::send($app->employee->user, 'leave_approved', 'Leave request approved', "Your leave request from {$app->start_date->format('d M Y')} to {$app->end_date->format('d M Y')} was approved.", ['url' => route('employee.dashboard')]);

        return back()->with('success', "Leave request approved successfully.");
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $app = LeaveApplication::findOrFail($id);
        $old = $app->toArray();

        $app->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditService::log('updated', LeaveApplication::class, $app->id,
            $old, $app->toArray(),
            "Rejected leave application for: {$app->employee->full_name}. Reason: {$request->rejection_reason}"
        );
        UserNotificationService::send($app->employee->user, 'leave_rejected', 'Leave request rejected', "Your leave request was rejected: {$app->rejection_reason}", ['url' => route('employee.dashboard')]);

        return back()->with('success', "Leave request rejected successfully.");
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

        AuditService::log('created', LeaveType::class, $type->id,
            [], $type->toArray(),
            "Created leave type: {$type->name}"
        );

        return back()->with('success', "Leave type '{$type->name}' created successfully.");
    }
}
