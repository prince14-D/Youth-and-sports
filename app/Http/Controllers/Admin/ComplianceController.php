<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractRenewal;
use App\Models\Employee;
use App\Models\Grievance;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    public function renewals(): View
    {
        $renewals = ContractRenewal::with(['employee.department', 'requester', 'approvalTrails'])
            ->latest()
            ->get();
        $employees = Employee::active()->where('employment_type', 'Contract')->orderBy('first_name')->get();

        $summary = [
            'pending'  => $renewals->where('status', 'Pending')->count(),
            'stage1'   => $renewals->where('status', 'Pending')->where('approval_stage', 1)->count(),
            'stage2'   => $renewals->where('status', 'Pending')->where('approval_stage', 2)->count(),
            'stage3'   => $renewals->where('status', 'Pending')->where('approval_stage', 3)->count(),
            'approved' => $renewals->where('status', 'Approved')->count(),
        ];

        return view('compliance.renewals', compact('renewals', 'employees', 'summary'));
    }

    public function requestRenewal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'proposed_end_date' => 'required|date|after:today',
            'reason'            => 'required|string|max:1000',
            'document'          => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $path = $request->hasFile('document')
            ? $request->file('document')->store('contracts/renewals', 'public')
            : null;

        $renewal = ContractRenewal::create([
            'employee_id'       => $employee->id,
            'current_end_date'  => $employee->contract_end_date ?? today(),
            'proposed_end_date' => $data['proposed_end_date'],
            'reason'            => $data['reason'],
            'document_path'     => $path,
            'status'            => 'Pending',
            'approval_stage'    => 1,
            'requested_by'      => $request->user()->id,
        ]);

        ApprovalWorkflowService::recordSubmission(
            $renewal,
            $request->user(),
            "Contract renewal for {$employee->full_name} (until {$data['proposed_end_date']}) submitted for Stage 1 HR Officer verification."
        );
        ApprovalWorkflowService::notifyRole(
            'HR Officer',
            'workflow_stage_pending',
            'Contract Renewal Awaiting Stage 1 Review',
            "Contract renewal for {$employee->full_name} requires HR Officer verification.",
            ['url' => route('contracts.renewals')]
        );
        AuditService::log('created', ContractRenewal::class, $renewal->id, [], $renewal->toArray(), "Submitted contract renewal for {$employee->full_name}");

        return back()->with('success', "Contract renewal for {$employee->full_name} submitted into the 3-tier HR approval workflow.");
    }

    public function renewApprove(Request $request, ContractRenewal $renewal): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $renewal->load('employee.user');
        abort_if($renewal->status !== 'Pending', 422, 'Only pending contract renewals can be approved.');

        $result = ApprovalWorkflowService::advance(
            $renewal,
            $request->user(),
            $request->input('remarks'),
            "Contract Renewal ({$renewal->employee->full_name})",
            route('contracts.renewals')
        );

        if ($result['is_final']) {
            $renewal->update([
                'status'         => 'Approved',
                'approval_stage' => 4,
                'reviewed_by'    => $request->user()->id,
                'reviewed_at'    => now(),
            ]);
            $renewal->employee->update([
                'contract_end_date' => $renewal->proposed_end_date,
                'employment_status' => 'Active',
            ]);
            UserNotificationService::send(
                $renewal->employee->user,
                'contract_renewal_approved',
                'Contract Renewal Approved',
                "Your contract renewal until {$renewal->proposed_end_date->format('d M Y')} received final HR Director approval.",
                ['url' => route('contractor.dashboard')]
            );
        } else {
            $renewal->update([
                'approval_stage' => $result['next_stage'],
            ]);
            $nextRole = ApprovalWorkflowService::stageInfo($result['next_stage'])['short'];
            UserNotificationService::send(
                $renewal->employee->user,
                'contract_renewal_stage_updated',
                'Contract Renewal Progressed',
                "Your contract renewal passed Stage {$result['stage']} and is now awaiting {$nextRole} (Stage {$result['next_stage']}/3).",
                ['url' => route('contractor.dashboard')]
            );
        }

        return back()->with('success', $result['message']);
    }

    public function renewReject(Request $request, ContractRenewal $renewal): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $renewal->load('employee.user');
        abort_if($renewal->status !== 'Pending', 422, 'Only pending contract renewals can be rejected.');

        $trail = ApprovalWorkflowService::reject(
            $renewal,
            $request->user(),
            $data['rejection_reason'],
            "Contract Renewal ({$renewal->employee->full_name})"
        );

        $renewal->update([
            'status'           => 'Rejected',
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        UserNotificationService::send(
            $renewal->employee->user,
            'contract_renewal_rejected',
            'Contract Renewal Rejected',
            "Your contract renewal was rejected at Stage {$trail->stage}: {$data['rejection_reason']}",
            ['url' => route('contractor.dashboard')]
        );

        return back()->with('success', "Contract renewal rejected at Stage {$trail->stage} [{$trail->digital_signature}].");
    }

    public function grievances(): View
    {
        $grievances = Grievance::with(['employee', 'assignee'])->latest()->get();
        return view('compliance.grievances', compact('grievances'));
    }

    public function grievanceStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'     => 'required|string|max:180',
            'category'    => 'nullable|string|max:80',
            'description' => 'required|string|max:5000',
            'evidence'    => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);
        $path = $request->hasFile('evidence')
            ? $request->file('evidence')->store('grievances', 'private')
            : null;
        Grievance::create($data + [
            'evidence_path' => $path,
            'submitted_by'  => $request->user()->id,
            'employee_id'   => $request->user()->employee_id,
        ]);
        return back()->with('success', 'Confidential grievance submitted.');
    }
}
