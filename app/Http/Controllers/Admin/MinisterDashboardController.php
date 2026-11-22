<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\AuditLog;
use App\Models\ContractRenewal;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\MinistryDocument;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinisterDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $ministerDept = Department::where('code', 'MINISTER')->first();
        $dmaDept = Department::where('code', 'DMA')->first();

        $ministerInboxQuery = MinistryDocument::with(['originDepartment', 'currentDepartment', 'destinationDepartment', 'destinationUser', 'sender', 'latestFeedbackUser'])
            ->whereNotIn('status', ['Completed', 'Rejected'])
            ->where(function ($q) use ($ministerDept, $user) {
                if ($ministerDept) {
                    $q->where('current_department_id', $ministerDept->id);
                }
                if ($user) {
                    $q->orWhere('destination_user_id', $user->id);
                }
            });

        $kpis = [
            'total_workforce'   => Employee::active()->count(),
            'permanent_staff'   => Employee::active()->where('employment_type', 'Permanent')->count(),
            'contractors'       => Employee::active()->where('employment_type', 'Contract')->count(),
            'departments_count' => Department::active()->count(),
            'active_documents'  => MinistryDocument::whereNotIn('status', ['Completed', 'Rejected'])->count(),
            'approved_docs'     => MinistryDocument::whereIn('status', ['Approved', 'Completed'])->count(),
            'reverted_docs'     => MinistryDocument::where('status', 'Returned')->count(),
            'feedback_today'    => DocumentRoutingLog::whereDate('acted_at', today())
                ->whereIn('action', ['approved', 'returned', 'feedback', 'resubmitted', 'rejected'])
                ->count(),
            'minister_inbox'    => (clone $ministerInboxQuery)->count(),
            'dma_pending'       => ($dmaDept ? MinistryDocument::where('current_department_id', $dmaDept->id)->whereNotIn('status', ['Completed', 'Rejected'])->count() : 0)
                + AssetRequest::where('status', 'Pending DMA')->count(),
            'today_activities'  => AuditLog::whereDate('created_at', today())->count(),
            'total_assets'      => Asset::count(),
            'asset_value_usd'   => (float) Asset::sum('purchase_value'),
            'pending_leaves'    => LeaveApplication::where('status', 'Pending')->count(),
            'pending_renewals'  => ContractRenewal::where('status', 'Pending')->count(),
        ];

        // Department-by-Department Executive Overview Matrix
        $departments = Department::active()
            ->withCount([
                'employees as active_staff_count' => fn ($q) => $q->where('employment_status', 'Active'),
                'employees as contractor_count'   => fn ($q) => $q->where('employment_status', 'Active')->where('employment_type', 'Contract'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Department $dept) {
                $holdingDocs = MinistryDocument::where('current_department_id', $dept->id)
                    ->whereNotIn('status', ['Completed', 'Rejected'])
                    ->get();
                $dept->holding_docs_count = $holdingDocs->count();
                $dept->urgent_docs_count = $holdingDocs->where('priority', 'Urgent')->count();
                $dept->delayed_docs_count = $holdingDocs->filter(fn ($d) => $d->hours_in_current_office >= 48)->count();
                $dept->assets_count = Asset::where('department_id', $dept->id)->count();
                $dept->pending_asset_requests = AssetRequest::where('department_id', $dept->id)->where('status', 'Pending DMA')->count();
                return $dept;
            });

        // Documents currently sitting in Office of the Minister or directly addressed to Minister
        $ministerDocuments = (clone $ministerInboxQuery)
            ->latest('updated_at')
            ->take(10)
            ->get();

        // Live Ministry-wide Document Circulation Tracker (all active files across departments)
        $activeDocuments = MinistryDocument::with([
            'originDepartment',
            'currentDepartment',
            'destinationDepartment',
            'destinationUser',
            'sender',
            'latestFeedbackUser',
        ])
            ->whereNotIn('status', ['Completed', 'Rejected'])
            ->latest('updated_at')
            ->take(15)
            ->get();

        // Live Recorded Document Approvals, Reversions, Feedback & Resubmissions across the entire Ministry
        $recentDocumentFeedback = DocumentRoutingLog::with([
            'document.originDepartment',
            'document.destinationDepartment',
            'document.destinationUser',
            'fromDepartment',
            'toDepartment',
            'targetUser',
        ])
            ->whereHas('document')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->take(20)
            ->get();

        // Today's Live Daily Activity Stream across the Ministry
        $dailyActivities = AuditLog::latest('created_at')->take(20)->get();

        $latestPayroll = PayrollPeriod::withCount('records')->latest('start_date')->first();

        return view('minister.dashboard', compact(
            'kpis',
            'departments',
            'ministerDocuments',
            'activeDocuments',
            'recentDocumentFeedback',
            'dailyActivities',
            'latestPayroll'
        ));
    }
}
