<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\ContractRenewal;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\MinistryDocument;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DmaDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $dmaDeptIds = Department::whereIn('code', ['DMA', 'ADMIN'])->pluck('id')->toArray();

        $dmaInbox = MinistryDocument::with(['originDepartment', 'currentDepartment', 'destinationDepartment', 'sender', 'routingLogs'])
            ->whereIn('current_department_id', $dmaDeptIds)
            ->whereNotIn('status', ['Completed', 'Rejected'])
            ->latest('updated_at')
            ->get();

        $pendingAssetRequests = AssetRequest::with(['department', 'requester', 'document'])
            ->where('status', 'Pending DMA')
            ->latest()
            ->get();

        $recentAllDocs = MinistryDocument::with(['originDepartment', 'currentDepartment', 'destinationDepartment'])
            ->latest('updated_at')
            ->take(12)
            ->get();

        $adminDivisionCodes = ['DMA', 'ADMIN', 'HR', 'FIN', 'PROC', 'ICT', 'ME', 'PDU', 'NYPP'];
        $divisions = Department::whereIn('code', $adminDivisionCodes)
            ->withCount(['employees as active_staff_count' => fn ($q) => $q->where('employment_status', 'Active')])
            ->orderBy('name')
            ->get()
            ->map(function (Department $dept) {
                $dept->holding_docs = MinistryDocument::where('current_department_id', $dept->id)
                    ->whereNotIn('status', ['Completed', 'Rejected'])
                    ->count();
                $dept->assets_count = Asset::where('department_id', $dept->id)->count();
                return $dept;
            });

        $kpis = [
            'dma_docs_pending'    => $dmaInbox->count(),
            'urgent_dma_docs'     => $dmaInbox->where('priority', 'Urgent')->count(),
            'pending_assets_req'  => $pendingAssetRequests->count(),
            'pending_payroll'     => PayrollPeriod::whereIn('status', ['Draft', 'Processing'])->count(),
            'pending_renewals'    => ContractRenewal::where('status', 'Pending')->count(),
            'pending_leaves'      => LeaveApplication::where('status', 'Pending')->count(),
            'total_assets'        => Asset::count(),
            'assigned_assets'     => Asset::where('status', 'Assigned')->count(),
            'active_workforce'    => Employee::active()->count(),
        ];

        $departments = Department::active()->orderBy('name')->get();
        $payrollCycles = PayrollPeriod::withCount('records')->latest('start_date')->take(5)->get();

        return view('dma.dashboard', compact(
            'kpis',
            'dmaInbox',
            'pendingAssetRequests',
            'recentAllDocs',
            'divisions',
            'departments',
            'payrollCycles'
        ));
    }
}
