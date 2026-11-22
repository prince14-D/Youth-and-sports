<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\ContractRenewal;
use App\Models\EmployeeTraining;
use App\Models\ExcuseApplication;
use App\Models\Grievance;
use App\Models\JobApplication;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PayrollRecord;
use App\Models\PerformanceAppraisal;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403, 'Employee record not found for this account.');
        }

        // If user is a Contractor (by role or employment_type), render the dedicated Contractor Portal
        if ($user->hasRole('Contractor') || ($user->hasRole('Employee') && $employee->isContractor())) {
            return $this->contractorDashboard($request);
        }

        $employee->load(['department', 'supervisor', 'documents']);
        $today = now()->toDateString();
        $year = now()->year;

        $leaveCount = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $leaveApplications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(6)
            ->get();

        // Compute leave balances per active leave type for the current year
        $approvedDaysByType = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereYear('start_date', $year)
            ->selectRaw('leave_type_id, SUM(days_requested) as used_days')
            ->groupBy('leave_type_id')
            ->pluck('used_days', 'leave_type_id');

        $leaveBalances = LeaveType::where('is_active', true)->orderBy('name')->get()->map(function ($type) use ($approvedDaysByType) {
            $used = (int) ($approvedDaysByType[$type->id] ?? 0);
            $allowed = (int) ($type->days_allowed ?? 0);
            $remaining = $allowed > 0 ? max(0, $allowed - $used) : null;
            return [
                'name'      => $type->name,
                'code'      => $type->code,
                'allowed'   => $allowed,
                'used'      => $used,
                'remaining' => $remaining,
                'percent'   => $allowed > 0 ? min(100, (int) round(($used / $allowed) * 100)) : 0,
            ];
        });

        $annualLeaveBalance = $leaveBalances->first(fn ($b) => stripos($b['name'], 'Annual') !== false)
            ?? $leaveBalances->first();

        $excuseApplications = ExcuseApplication::where('employee_id', $employee->id)
            ->latest()
            ->take(6)
            ->get();

        $excuseStats = [
            'pending'  => ExcuseApplication::where('employee_id', $employee->id)->where('status', 'Pending')->count(),
            'approved' => ExcuseApplication::where('employee_id', $employee->id)->where('status', 'Approved')->whereYear('excuse_date', $year)->count(),
            'total'    => ExcuseApplication::where('employee_id', $employee->id)->count(),
        ];

        $recentPayslips = PayrollRecord::with('period')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(4)
            ->get();

        $latestPayslip = $recentPayslips->first();

        $assignedAssets = AssetAssignment::with('asset')
            ->where('employee_id', $employee->id)
            ->whereNull('returned_date')
            ->latest('assigned_date')
            ->get();

        $trainings = EmployeeTraining::with('training')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        $latestAppraisal = PerformanceAppraisal::with('appraiser')
            ->where('employee_id', $employee->id)
            ->latest()
            ->first();

        $recruitmentApplications = JobApplication::with('vacancy')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        $recentNotifications = $user->notifications()->latest()->take(5)->get();
        $unreadNotificationsCount = $user->unreadNotifications()->count();

        $grievanceCount = Grievance::where('employee_id', $employee->id)->count();

        return view('employee.dashboard', compact(
            'employee',
            'leaveCount',
            'leaveApplications',
            'leaveBalances',
            'annualLeaveBalance',
            'excuseApplications',
            'excuseStats',
            'recentPayslips',
            'latestPayslip',
            'assignedAssets',
            'trainings',
            'latestAppraisal',
            'recruitmentApplications',
            'recentNotifications',
            'unreadNotificationsCount',
            'grievanceCount'
        ));
    }

    public function contractorDashboard(Request $request): View
    {
        $user = $request->user();
        $contractor = $user?->employee;

        if (!$contractor) {
            abort(403, 'Contractor record not found for this account.');
        }

        $contractor->load(['department', 'supervisor', 'documents']);
        $year = now()->year;

        $daysRemaining = $contractor->contract_days_remaining;
        $contractProgress = $contractor->contract_progress_percent;
        $contractHealth = match (true) {
            is_null($daysRemaining) => ['label' => 'End Date Pending', 'badge' => 'bg-secondary', 'tone' => 'secondary'],
            $daysRemaining < 0      => ['label' => 'Expired', 'badge' => 'bg-danger', 'tone' => 'danger'],
            $daysRemaining <= 60    => ['label' => 'Expiring Soon', 'badge' => 'bg-warning text-dark', 'tone' => 'warning'],
            default                 => ['label' => 'Active Contract', 'badge' => 'bg-success', 'tone' => 'success'],
        };

        $renewalHistory = ContractRenewal::where('employee_id', $contractor->id)
            ->latest()
            ->get();

        $excuseApplications = ExcuseApplication::where('employee_id', $contractor->id)
            ->latest()
            ->take(6)
            ->get();

        $excuseStats = [
            'pending'  => ExcuseApplication::where('employee_id', $contractor->id)->where('status', 'Pending')->count(),
            'approved' => ExcuseApplication::where('employee_id', $contractor->id)->where('status', 'Approved')->whereYear('excuse_date', $year)->count(),
            'total'    => ExcuseApplication::where('employee_id', $contractor->id)->count(),
        ];

        $recentPayslips = PayrollRecord::with('period')
            ->where('employee_id', $contractor->id)
            ->latest()
            ->take(5)
            ->get();

        $latestPayslip = $recentPayslips->first();

        $assignedAssets = AssetAssignment::with('asset')
            ->where('employee_id', $contractor->id)
            ->whereNull('returned_date')
            ->latest('assigned_date')
            ->get();

        $recruitmentApplications = JobApplication::with('vacancy')
            ->where('employee_id', $contractor->id)
            ->latest()
            ->take(5)
            ->get();

        $recentNotifications = $user->notifications()->latest()->take(5)->get();
        $unreadNotificationsCount = $user->unreadNotifications()->count();

        return view('contractor.dashboard', compact(
            'contractor',
            'daysRemaining',
            'contractProgress',
            'contractHealth',
            'renewalHistory',
            'excuseApplications',
            'excuseStats',
            'recentPayslips',
            'latestPayslip',
            'assignedAssets',
            'recruitmentApplications',
            'recentNotifications',
            'unreadNotificationsCount'
        ));
    }

    public function submitRenewalRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $contractor = $user?->employee;
        abort_unless($contractor && $contractor->isContractor(), 403, 'Only contractors can submit self-service renewal requests.');

        $data = $request->validate([
            'proposed_end_date' => 'required|date|after:today',
            'reason'            => 'required|string|max:1000',
            'document'          => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $hasPending = ContractRenewal::where('employee_id', $contractor->id)
            ->where('status', 'Pending')
            ->exists();

        if ($hasPending) {
            return back()->with('warning', 'You already have a pending contract renewal request awaiting HR review.');
        }

        $path = $request->hasFile('document')
            ? $request->file('document')->store('contracts/renewals', 'public')
            : null;

        $renewal = ContractRenewal::create([
            'employee_id'       => $contractor->id,
            'current_end_date'  => $contractor->contract_end_date ?? today(),
            'proposed_end_date' => $data['proposed_end_date'],
            'reason'            => $data['reason'],
            'document_path'     => $path,
            'status'            => 'Pending',
            'approval_stage'    => 1,
            'requested_by'      => $user->id,
        ]);

        ApprovalWorkflowService::recordSubmission(
            $renewal,
            $user,
            "Contractor {$contractor->full_name} requested renewal until {$data['proposed_end_date']} (Stage 1: HR Officer Review)."
        );
        ApprovalWorkflowService::notifyRole(
            'HR Officer',
            'workflow_stage_pending',
            'Contractor Renewal Request (Stage 1)',
            "{$contractor->full_name} requested contract renewal until {$data['proposed_end_date']}.",
            ['url' => route('contracts.renewals')]
        );

        AuditService::log(
            'created',
            ContractRenewal::class,
            $renewal->id,
            [],
            $renewal->toArray(),
            "Contractor {$contractor->full_name} requested contract renewal until {$data['proposed_end_date']}"
        );

        UserNotificationService::send(
            $user,
            'contract_renewal_requested',
            'Contract Renewal Requested',
            'Your contract renewal request has been submitted to HR for review.',
            ['url' => route('contractor.dashboard')]
        );

        return back()->with('success', 'Your contract renewal request has been submitted to HR for review.');
    }
}
