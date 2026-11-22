<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\Employee;
use App\Models\MinistryDocument;
use App\Services\AuditService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $deptFilter = $request->query('department_id');
        $statusFilter = $request->query('status');
        $categoryFilter = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        $query = Asset::with(['department', 'activeAssignment.employee', 'assignments.employee']);

        if ($deptFilter) {
            $query->where('department_id', $deptFilter);
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $assets = $query->latest()->paginate(20)->withQueryString();
        $employees = Employee::active()->with('department')->orderBy('first_name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $categories = Asset::CATEGORIES;

        $requestsQuery = AssetRequest::with(['department', 'requester', 'reviewer', 'document'])->latest();
        if (!$user->can('approve asset requests') && $user->employee?->department_id) {
            $requestsQuery->where('department_id', $user->employee->department_id);
        }
        $assetRequests = $requestsQuery->take(25)->get();

        $stats = [
            'total'            => Asset::count(),
            'assigned'         => Asset::where('status', 'Assigned')->count(),
            'available'        => Asset::where('status', 'Available')->count(),
            'maintenance'      => Asset::whereIn('status', ['Maintenance', 'Retired'])->count(),
            'total_value_usd'  => (float) Asset::all()->sum(fn ($a) => $a->total_valuation),
            'pending_requests' => AssetRequest::where('status', 'Pending DMA')->count(),
        ];

        $fundingSources = Asset::FUNDING_SOURCES;

        return view('assets.index', compact(
            'assets',
            'employees',
            'departments',
            'categories',
            'fundingSources',
            'assetRequests',
            'stats',
            'deptFilter',
            'statusFilter',
            'categoryFilter',
            'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_tag'             => 'required|string|max:50|unique:assets,asset_tag',
            'gsa_code'              => 'nullable|string|max:60',
            'name'                  => 'required|string|max:150',
            'category'              => 'required|string|max:60',
            'department_id'         => 'nullable|exists:departments,id',
            'location'              => 'nullable|string|max:150',
            'serial_number'         => 'nullable|string|max:100',
            'plate_number'          => 'nullable|string|max:40',
            'chassis_number'        => 'nullable|string|max:80',
            'condition'             => 'required|in:Good,Fair,Damaged',
            'quantity'              => 'nullable|integer|min:1|max:100000',
            'unit'                  => 'nullable|string|max:30',
            'purchase_date'         => 'nullable|date',
            'warranty_expiry'       => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'purchase_value'        => 'nullable|numeric|min:0',
            'supplier'              => 'nullable|string|max:150',
            'funding_source'        => 'nullable|string|max:100',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $data['quantity'] = $data['quantity'] ?: 1;
        $data['unit'] = $data['unit'] ?: 'Unit';

        $asset = Asset::create($data + ['status' => 'Available']);

        AuditService::log('created', Asset::class, $asset->id, [], $asset->toArray(), "Registered Ministry asset {$asset->asset_tag} ({$asset->name})");

        return back()->with('success', "Asset {$asset->asset_tag} ({$asset->name}) registered successfully.");
    }

    public function assign(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'assigned_date'      => 'required|date',
            'condition_on_issue' => 'nullable|string|max:50',
            'notes'              => 'nullable|string|max:500',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $asset->assignments()->whereNull('returned_date')->update(['returned_date' => today()]);
        $asset->assignments()->create($data + ['assigned_by' => $request->user()->id]);
        $asset->update([
            'status'        => 'Assigned',
            'department_id' => $employee->department_id ?: $asset->department_id,
        ]);

        AuditService::log('updated', Asset::class, $asset->id, [], ['assigned_to' => $employee->full_name], "Assigned asset {$asset->asset_tag} to {$employee->full_name}");

        return back()->with('success', "Asset {$asset->asset_tag} assigned to {$employee->full_name}.");
    }

    public function returnAsset(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'condition_on_return' => 'required|in:Good,Fair,Damaged',
            'notes'               => 'nullable|string|max:500',
        ]);

        $active = $asset->assignments()->whereNull('returned_date')->latest()->first();
        if ($active) {
            $active->update([
                'returned_date'       => today(),
                'condition_on_return' => $data['condition_on_return'],
                'notes'               => trim(($active->notes ? $active->notes . ' | ' : '') . 'Return: ' . ($data['notes'] ?: $data['condition_on_return'])),
            ]);
        }

        $newStatus = $data['condition_on_return'] === 'Damaged' ? 'Maintenance' : 'Available';
        $asset->update([
            'status'    => $newStatus,
            'condition' => $data['condition_on_return'],
        ]);

        AuditService::log('updated', Asset::class, $asset->id, [], ['status' => $newStatus, 'condition' => $data['condition_on_return']], "Checked in asset {$asset->asset_tag} ({$data['condition_on_return']})");

        return back()->with('success', "Asset {$asset->asset_tag} returned and marked as {$newStatus}.");
    }

    public function updateStatus(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'status'        => 'required|in:Available,Assigned,Maintenance,Retired',
            'condition'     => 'required|in:Good,Fair,Damaged',
            'department_id' => 'nullable|exists:departments,id',
            'location'      => 'nullable|string|max:150',
        ]);

        $asset->update($data);
        AuditService::log('updated', Asset::class, $asset->id, [], $data, "Updated asset {$asset->asset_tag} status to {$data['status']}");

        return back()->with('success', "Asset {$asset->asset_tag} updated.");
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'department_id'      => 'nullable|exists:departments,id',
            'item_name'          => 'required|string|max:160',
            'category'           => 'required|string|max:60',
            'quantity'           => 'required|integer|min:1|max:500',
            'estimated_cost_usd' => 'nullable|numeric|min:0',
            'priority'           => 'required|in:Normal,High,Urgent',
            'justification'      => 'required|string|max:2000',
        ]);

        $deptId = $data['department_id'] ?: ($user->employee?->department_id ?: Department::value('id'));
        $dmaDeptId = Department::where('code', 'DMA')->value('id') ?: Department::where('code', 'ADMIN')->value('id') ?: $deptId;
        $dept = Department::find($deptId);
        $requestNo = AssetRequest::generateRequestNo();

        // Automatically create a tracked paperless document routed to DMA!
        $trackingNo = MinistryDocument::generateTrackingNumber();
        $doc = MinistryDocument::create([
            'tracking_number'               => $trackingNo,
            'subject'                       => "Asset Requisition ({$requestNo}): {$data['quantity']}x {$data['item_name']}",
            'document_type'                 => 'Asset / Equipment Requisition',
            'priority'                      => $data['priority'],
            'confidentiality'               => 'Restricted',
            'origin_department_id'          => $deptId,
            'current_department_id'         => $dmaDeptId,
            'destination_department_id'     => $dmaDeptId,
            'allowed_department_ids'        => array_values(array_unique([$deptId, $dmaDeptId])),
            'sender_id'                     => $user->id,
            'amount_usd'                    => $data['estimated_cost_usd'] ?: null,
            'summary'                       => "Department: {$dept?->name} | Item: {$data['quantity']}x {$data['item_name']} ({$data['category']}). Justification: {$data['justification']}",
            'status'                        => 'In Transit',
            'received_at_current_office_at' => now(),
        ]);

        DocumentRoutingLog::create([
            'ministry_document_id' => $doc->id,
            'from_department_id'   => $deptId,
            'to_department_id'     => $dmaDeptId,
            'action'               => 'dispatched',
            'actor_id'             => $user->id,
            'actor_name'           => $user->name,
            'actor_role'           => $user->primary_role,
            'minutes_note'         => "Asset requisition {$requestNo} submitted to DMA for approval.",
            'digital_signature'    => 'DOC-SIG-' . strtoupper(substr(hash('sha256', $trackingNo . '|' . now()->toIso8601String()), 0, 10)),
            'acted_at'             => now(),
        ]);

        $assetReq = AssetRequest::create([
            'request_no'           => $requestNo,
            'department_id'        => $deptId,
            'requested_by'         => $user->id,
            'item_name'            => $data['item_name'],
            'category'             => $data['category'],
            'quantity'             => $data['quantity'],
            'estimated_cost_usd'   => $data['estimated_cost_usd'] ?: null,
            'priority'             => $data['priority'],
            'justification'        => $data['justification'],
            'status'               => 'Pending DMA',
            'ministry_document_id' => $doc->id,
        ]);

        AuditService::log('created', AssetRequest::class, $assetReq->id, [], $assetReq->toArray(), "Submitted asset request {$requestNo} ({$data['item_name']}) routed to DMA as {$trackingNo}");

        return back()->with('success', "Asset requisition {$requestNo} submitted to DMA for approval (Document Tracking #: {$trackingNo}).");
    }

    public function reviewRequest(Request $request, AssetRequest $assetRequest): RedirectResponse
    {
        $data = $request->validate([
            'decision'     => 'required|in:Approved,Rejected,Fulfilled',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        if (in_array($data['decision'], ['Approved', 'Rejected'], true)) {
            abort_unless($request->user()->can('approve asset requests'), 403, 'Only DMA / Executive Leadership can approve or reject asset requisitions.');
        } else {
            abort_unless($request->user()->can('manage assets') || $request->user()->can('approve asset requests'), 403);
        }

        $assetRequest->update([
            'status'       => $data['decision'],
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
            'review_notes' => $data['review_notes'] ?? null,
        ]);

        if ($assetRequest->document) {
            $doc = $assetRequest->document;
            $docStatus = match ($data['decision']) {
                'Approved'  => 'Approved',
                'Fulfilled' => 'Completed',
                default     => 'Rejected',
            };
            $doc->update([
                'status'             => $docStatus,
                'current_handler_id' => $request->user()->id,
                'completed_at'       => in_array($docStatus, ['Completed', 'Rejected'], true) ? now() : null,
            ]);
            DocumentRoutingLog::create([
                'ministry_document_id' => $doc->id,
                'from_department_id'   => $doc->current_department_id,
                'to_department_id'     => $doc->current_department_id,
                'action'               => strtolower($data['decision']),
                'actor_id'             => $request->user()->id,
                'actor_name'           => $request->user()->name,
                'actor_role'           => $request->user()->primary_role,
                'minutes_note'         => $data['review_notes'] ?: "Asset requisition {$assetRequest->request_no} marked {$data['decision']} by {$request->user()->primary_role}.",
                'digital_signature'    => 'DOC-SIG-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $data['decision'] . '|' . now()->toIso8601String()), 0, 10)),
                'acted_at'             => now(),
            ]);
        }

        UserNotificationService::send(
            $assetRequest->requester,
            'asset_request_' . strtolower($data['decision']),
            "Asset Request {$assetRequest->request_no}: {$data['decision']}",
            "Your request for {$assetRequest->quantity}x {$assetRequest->item_name} was marked {$data['decision']} by {$request->user()->name}.",
            ['url' => route('assets.index')]
        );

        AuditService::log('updated', AssetRequest::class, $assetRequest->id, [], $data, "Marked asset request {$assetRequest->request_no} as {$data['decision']}");

        return back()->with('success', "Asset requisition {$assetRequest->request_no} marked as {$data['decision']}.");
    }
}
