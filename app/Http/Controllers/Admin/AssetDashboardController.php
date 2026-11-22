<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenanceLog;
use App\Models\AssetRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $allAssets = Asset::with(['department', 'activeAssignment.employee', 'maintenanceLogs'])->get();

        $totalItems = $allAssets->count();
        $totalUnits = (int) $allAssets->sum(fn ($a) => max(1, (int) ($a->quantity ?? 1)));
        $vehiclesCount = $allAssets->filter(fn ($a) => $a->is_vehicle)->count();
        $assignedCount = $allAssets->where('status', 'Assigned')->count();
        $availableCount = $allAssets->where('status', 'Available')->count();
        $maintenanceCount = $allAssets->whereIn('status', ['Maintenance', 'Retired'])->count();
        $damagedCount = $allAssets->where('condition', 'Damaged')->count();
        $totalValuation = (float) $allAssets->sum(fn ($a) => $a->total_valuation);

        $upcomingService = $allAssets->filter(fn ($a) =>
            $a->next_maintenance_date && $a->next_maintenance_date->lte(today()->addDays(30))
        )->sortBy('next_maintenance_date')->take(6);

        $categoryBreakdown = $allAssets->groupBy('category')->map(fn ($grp) => [
            'count'     => $grp->count(),
            'units'     => (int) $grp->sum(fn ($a) => max(1, (int) ($a->quantity ?? 1))),
            'valuation' => (float) $grp->sum(fn ($a) => $a->total_valuation),
        ])->sortByDesc('count');

        $departmentBreakdown = Department::active()
            ->orderBy('name')
            ->get()
            ->map(function (Department $dept) use ($allAssets) {
                $deptAssets = $allAssets->where('department_id', $dept->id);
                $dept->asset_items_count = $deptAssets->count();
                $dept->asset_units_count = (int) $deptAssets->sum(fn ($a) => max(1, (int) ($a->quantity ?? 1)));
                $dept->asset_valuation   = (float) $deptAssets->sum(fn ($a) => $a->total_valuation);
                return $dept;
            })
            ->filter(fn ($d) => $d->asset_items_count > 0)
            ->sortByDesc('asset_items_count')
            ->values();

        $approvedRequests = AssetRequest::with(['department', 'requester', 'reviewer', 'document'])
            ->where('status', 'Approved')
            ->latest('reviewed_at')
            ->get();

        $pendingDmaRequests = AssetRequest::with(['department', 'requester', 'document'])
            ->where('status', 'Pending DMA')
            ->latest()
            ->take(8)
            ->get();

        $recentAssets = Asset::with(['department', 'activeAssignment.employee'])
            ->latest()
            ->take(10)
            ->get();

        $recentMaintenance = AssetMaintenanceLog::with(['asset', 'recorder'])
            ->latest('service_date')
            ->take(10)
            ->get();

        $maintenanceSpendYtd = (float) AssetMaintenanceLog::whereYear('service_date', now()->year)->sum('cost_usd');

        $departments = Department::active()->orderBy('name')->get();
        $employees = Employee::active()->with('department')->orderBy('first_name')->get();
        $categories = Asset::CATEGORIES;
        $fundingSources = Asset::FUNDING_SOURCES;
        $maintenanceTypes = AssetMaintenanceLog::TYPES;
        $assetsForDropdown = Asset::orderBy('asset_tag')->get(['id', 'asset_tag', 'name', 'category', 'plate_number']);

        $stats = compact(
            'totalItems', 'totalUnits', 'vehiclesCount', 'assignedCount',
            'availableCount', 'maintenanceCount', 'damagedCount', 'totalValuation',
            'maintenanceSpendYtd'
        );

        return view('assets.dashboard', compact(
            'stats',
            'upcomingService',
            'categoryBreakdown',
            'departmentBreakdown',
            'approvedRequests',
            'pendingDmaRequests',
            'recentAssets',
            'recentMaintenance',
            'departments',
            'employees',
            'categories',
            'fundingSources',
            'maintenanceTypes',
            'assetsForDropdown'
        ));
    }

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id'          => 'required|exists:assets,id',
            'maintenance_type'  => 'required|string|max:60',
            'service_date'      => 'required|date',
            'cost_usd'          => 'nullable|numeric|min:0',
            'vendor'            => 'nullable|string|max:150',
            'notes'             => 'required|string|max:2000',
            'next_service_date' => 'nullable|date|after_or_equal:service_date',
            'new_condition'     => 'nullable|in:Good,Fair,Damaged',
            'new_status'        => 'nullable|in:Available,Assigned,Maintenance,Retired',
        ]);

        $asset = Asset::findOrFail($data['asset_id']);

        $log = AssetMaintenanceLog::create([
            'asset_id'          => $asset->id,
            'maintenance_type'  => $data['maintenance_type'],
            'service_date'      => $data['service_date'],
            'cost_usd'          => $data['cost_usd'] ?: 0,
            'vendor'            => $data['vendor'] ?: null,
            'notes'             => $data['notes'],
            'next_service_date' => $data['next_service_date'] ?: null,
            'recorded_by'       => $request->user()->id,
        ]);

        $assetUpdate = [
            'last_maintenance_date' => $data['service_date'],
            'next_maintenance_date' => $data['next_service_date'] ?: $asset->next_maintenance_date,
        ];
        if (!empty($data['new_condition'])) {
            $assetUpdate['condition'] = $data['new_condition'];
        }
        if (!empty($data['new_status'])) {
            $assetUpdate['status'] = $data['new_status'];
        }
        $asset->update($assetUpdate);

        AuditService::log(
            'asset_maintenance_recorded',
            AssetMaintenanceLog::class,
            $log->id,
            [],
            $log->toArray(),
            "Recorded {$data['maintenance_type']} for asset {$asset->asset_tag} ({$asset->name})"
        );

        return back()->with('success', "Recorded {$data['maintenance_type']} log for {$asset->asset_tag} ({$asset->name}).");
    }

    public function exportCsv()
    {
        $assets = Asset::with(['department', 'activeAssignment.employee'])
            ->orderBy('category')
            ->orderBy('asset_tag')
            ->get();

        AuditService::log('exported', Asset::class, null, [], ['count' => $assets->count()], 'Exported Official Government Asset & Inventory Register CSV');

        return response()->streamDownload(function () use ($assets) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Asset Tag', 'GSA Code', 'Asset / Property Name', 'Category',
                'Department', 'Location', 'Quantity', 'Unit',
                'Serial Number', 'Plate Number', 'Chassis / VIN',
                'Condition', 'Status', 'Current Custodian',
                'Purchase Date', 'Unit Value (USD)', 'Total Value (USD)',
                'Supplier / Vendor', 'Funding Source', 'Next Service Date',
            ]);
            foreach ($assets as $a) {
                fputcsv($out, [
                    $a->asset_tag,
                    $a->gsa_code ?: '—',
                    $a->name,
                    $a->category,
                    $a->department?->name ?? 'Central Store',
                    $a->location ?: '—',
                    $a->quantity ?? 1,
                    $a->unit ?: 'Unit',
                    $a->serial_number ?: '—',
                    $a->plate_number ?: '—',
                    $a->chassis_number ?: '—',
                    $a->condition,
                    $a->status,
                    $a->activeAssignment?->employee?->full_name ?? 'Unassigned',
                    $a->purchase_date?->format('Y-m-d') ?? '—',
                    number_format((float) ($a->purchase_value ?? 0), 2, '.', ''),
                    number_format($a->total_valuation, 2, '.', ''),
                    $a->supplier ?: '—',
                    $a->funding_source ?: '—',
                    $a->next_maintenance_date?->format('Y-m-d') ?? '—',
                ]);
            }
            fclose($out);
        }, 'MYS-Government-Asset-Inventory-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
