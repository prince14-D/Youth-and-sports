<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentRoutingLog;
use App\Models\MinistryDocument;
use App\Models\StadiumEvent;
use App\Models\StadiumFacility;
use App\Models\StadiumMaintenanceLog;
use App\Models\StadiumSafetyInspection;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StadiumManagementController extends Controller
{
    public function dashboard(Request $request): View
    {
        $activeTab = (string) $request->query('tab', 'events');
        $facilityFilter = $request->query('facility_id');
        $statusFilter = (string) $request->query('status', '');

        $alerts = $this->runReadinessScanner();
        $activeAlertsCount = collect($alerts)->where('count', '>', 0)->count();

        $facilities = StadiumFacility::withCount([
            'events as upcoming_events_count' => fn ($q) => $q->whereDate('event_date', '>=', today())->whereNotIn('status', ['Cancelled', 'Completed']),
            'maintenanceLogs as open_maintenance_count' => fn ($q) => $q->whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding']),
        ])->with('latestSafetyInspection')->orderBy('name')->get();

        $eventsQuery = StadiumEvent::with(['facility', 'safetyClearer', 'document'])
            ->orderByDesc('event_date')
            ->orderByDesc('id');
        if ($facilityFilter) {
            $eventsQuery->where('stadium_facility_id', $facilityFilter);
        }
        if ($statusFilter !== '' && $activeTab === 'events') {
            $eventsQuery->where('status', $statusFilter);
        }
        $events = $eventsQuery->take(40)->get();

        $maintQuery = StadiumMaintenanceLog::with(['facility', 'reporter', 'completer', 'document'])
            ->latest('created_at');
        if ($facilityFilter) {
            $maintQuery->where('stadium_facility_id', $facilityFilter);
        }
        if ($statusFilter !== '' && $activeTab === 'maintenance') {
            $maintQuery->where('status', $statusFilter);
        }
        $maintenanceLogs = $maintQuery->take(40)->get();

        $inspectionsQuery = StadiumSafetyInspection::with(['facility', 'event', 'recorder'])
            ->orderByDesc('inspection_date')
            ->orderByDesc('id');
        if ($facilityFilter) {
            $inspectionsQuery->where('stadium_facility_id', $facilityFilter);
        }
        $inspections = $inspectionsQuery->take(40)->get();

        $avgSafetyRaw = StadiumSafetyInspection::avg('safety_score');
        $year = now()->format('Y');

        $kpis = [
            'total_facilities'       => $facilities->count(),
            'operational_facilities' => $facilities->where('operational_status', 'Operational')->count(),
            'total_capacity'         => (int) $facilities->sum('capacity'),
            'upcoming_events'        => StadiumEvent::whereDate('event_date', '>=', today())->whereNotIn('status', ['Cancelled', 'Completed'])->count(),
            'cleared_events'         => StadiumEvent::whereDate('event_date', '>=', today())->where('status', 'Safety Cleared')->count(),
            'open_work_orders'       => StadiumMaintenanceLog::whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding'])->count(),
            'critical_work_orders'   => StadiumMaintenanceLog::whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding'])->where(fn ($q) => $q->where('priority', 'Critical')->orWhere('maintenance_type', 'Emergency'))->count(),
            'avg_safety_score'       => $avgSafetyRaw !== null ? (int) round((float) $avgSafetyRaw) : null,
            'ytd_revenue_usd'        => (float) StadiumEvent::whereYear('event_date', $year)->whereNotIn('status', ['Cancelled'])->sum('revenue_fee_usd'),
            'active_alerts'          => $activeAlertsCount,
        ];

        $stadiumDept = Department::where('code', 'STADIUM')->first();
        $incomingDocs = $stadiumDept
            ? MinistryDocument::with(['originDepartment', 'sender'])
                ->where('current_department_id', $stadiumDept->id)
                ->whereNotIn('status', ['Completed', 'Rejected'])
                ->latest('updated_at')
                ->take(10)
                ->get()
            : collect();

        $upcomingEventsForSelect = StadiumEvent::with('facility')
            ->whereNotIn('status', ['Cancelled', 'Completed'])
            ->orderBy('event_date')
            ->take(30)
            ->get();

        return view('stadium.dashboard', [
            'kpis'                    => $kpis,
            'alerts'                  => $alerts,
            'facilities'              => $facilities,
            'events'                  => $events,
            'maintenanceLogs'         => $maintenanceLogs,
            'inspections'             => $inspections,
            'incomingDocs'            => $incomingDocs,
            'upcomingEventsForSelect' => $upcomingEventsForSelect,
            'facilityTypes'           => StadiumFacility::TYPES,
            'surfaceTypes'            => StadiumFacility::SURFACES,
            'floodlightStatuses'      => StadiumFacility::FLOODLIGHT_STATUSES,
            'operationalStatuses'     => StadiumFacility::OPERATIONAL_STATUSES,
            'eventTypes'              => StadiumEvent::EVENT_TYPES,
            'eventStatuses'           => StadiumEvent::STATUSES,
            'checklistFields'         => StadiumEvent::CHECKLIST_FIELDS,
            'zones'                   => StadiumMaintenanceLog::ZONES,
            'maintenanceTypes'        => StadiumMaintenanceLog::TYPES,
            'priorities'              => StadiumMaintenanceLog::PRIORITIES,
            'maintenanceStatuses'     => StadiumMaintenanceLog::STATUSES,
            'inspectionTypes'         => StadiumSafetyInspection::INSPECTION_TYPES,
            'inspectionRatings'       => StadiumSafetyInspection::RATINGS,
            'activeTab'               => $activeTab,
            'facilityFilter'          => $facilityFilter,
            'statusFilter'            => $statusFilter,
        ]);
    }

    // ────────────────────────────────────────────────────────────── Facilities

    public function storeFacility(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:200',
            'facility_type'             => ['required', Rule::in(StadiumFacility::TYPES)],
            'location'                  => 'required|string|max:180',
            'county'                    => 'required|string|max:80',
            'capacity'                  => 'required|integer|min:0|max:200000',
            'surface_type'              => ['required', Rule::in(StadiumFacility::SURFACES)],
            'floodlights_status'        => ['required', Rule::in(StadiumFacility::FLOODLIGHT_STATUSES)],
            'operational_status'        => ['required', Rule::in(StadiumFacility::OPERATIONAL_STATUSES)],
            'safety_certificate_expiry' => 'nullable|date',
            'manager_name'              => 'nullable|string|max:120',
            'notes'                     => 'nullable|string|max:2000',
        ]);

        $facility = StadiumFacility::create([
            'facility_code'             => StadiumFacility::generateFacilityCode(),
            'name'                      => $data['name'],
            'facility_type'             => $data['facility_type'],
            'location'                  => $data['location'],
            'county'                    => $data['county'],
            'capacity'                  => (int) $data['capacity'],
            'surface_type'              => $data['surface_type'],
            'floodlights_status'        => $data['floodlights_status'],
            'operational_status'        => $data['operational_status'],
            'safety_certificate_expiry' => ($data['safety_certificate_expiry'] ?? null) ?: null,
            'manager_name'              => ($data['manager_name'] ?? null) ?: null,
            'notes'                     => ($data['notes'] ?? null) ?: null,
            'created_by'                => $request->user()->id,
        ]);

        AuditService::log('stadium_facility_created', StadiumFacility::class, $facility->id, [],
            ['facility_code' => $facility->facility_code, 'name' => $facility->name],
            "Registered stadium facility {$facility->facility_code}: {$facility->name}");

        return redirect()->route('stadium.dashboard', ['tab' => 'facilities'])
            ->with('success', "Facility {$facility->facility_code} ({$facility->name}) registered.");
    }

    public function updateFacilityStatus(Request $request, StadiumFacility $facility): RedirectResponse
    {
        $data = $request->validate([
            'operational_status'        => ['required', Rule::in(StadiumFacility::OPERATIONAL_STATUSES)],
            'floodlights_status'        => ['required', Rule::in(StadiumFacility::FLOODLIGHT_STATUSES)],
            'safety_certificate_expiry' => 'nullable|date',
            'notes'                     => 'nullable|string|max:2000',
        ]);

        $old = $facility->only(['operational_status', 'floodlights_status']);
        $facility->update([
            'operational_status'        => $data['operational_status'],
            'floodlights_status'        => $data['floodlights_status'],
            'safety_certificate_expiry' => ($data['safety_certificate_expiry'] ?? null) ?: $facility->safety_certificate_expiry,
            'notes'                     => ($data['notes'] ?? null) ?: $facility->notes,
        ]);

        AuditService::log('stadium_facility_updated', StadiumFacility::class, $facility->id, $old,
            ['operational_status' => $data['operational_status']],
            "Updated {$facility->name} status to {$data['operational_status']}");

        return redirect()->route('stadium.dashboard', ['tab' => 'facilities'])
            ->with('success', "{$facility->name} updated to {$data['operational_status']}.");
    }

    // ────────────────────────────────────────────────────────────────── Events

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stadium_facility_id' => 'required|exists:stadium_facilities,id',
            'title'               => 'required|string|max:220',
            'event_type'          => ['required', Rule::in(StadiumEvent::EVENT_TYPES)],
            'organizer'           => 'required|string|max:180',
            'organizer_contact'   => 'nullable|string|max:100',
            'event_date'          => 'required|date',
            'start_time'          => 'nullable|string|max:10',
            'end_time'            => 'nullable|string|max:10',
            'expected_attendance' => 'required|integer|min:0|max:250000',
            'revenue_fee_usd'     => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string|max:2000',
            'escalate_to'         => ['nullable', Rule::in(['none', 'DMA', 'MINISTER'])],
        ]);

        $facility = StadiumFacility::findOrFail($data['stadium_facility_id']);
        if ($facility->operational_status === 'Closed / Unsafe') {
            return back()->withInput()->with('error', "{$facility->name} is currently marked Closed / Unsafe and cannot host new bookings.");
        }

        $event = StadiumEvent::create([
            'event_code'          => StadiumEvent::generateEventCode(),
            'stadium_facility_id' => $facility->id,
            'title'               => $data['title'],
            'event_type'          => $data['event_type'],
            'organizer'           => $data['organizer'],
            'organizer_contact'   => ($data['organizer_contact'] ?? null) ?: null,
            'event_date'          => $data['event_date'],
            'start_time'          => ($data['start_time'] ?? null) ?: null,
            'end_time'            => ($data['end_time'] ?? null) ?: null,
            'expected_attendance' => (int) $data['expected_attendance'],
            'revenue_fee_usd'     => isset($data['revenue_fee_usd']) && $data['revenue_fee_usd'] !== '' ? $data['revenue_fee_usd'] : 0,
            'status'              => 'Scheduled',
            'notes'               => ($data['notes'] ?? null) ?: null,
            'created_by'          => $request->user()->id,
        ]);

        $msg = "Event {$event->event_code} ({$event->title}) scheduled at {$facility->name}.";
        if ($event->exceeds_capacity) {
            $msg .= " Warning: expected attendance ({$event->expected_attendance}) exceeds facility capacity ({$facility->capacity}) — safety clearance will remain blocked until crowd cap is resolved.";
        }

        $escalateTo = $data['escalate_to'] ?? 'none';
        if (in_array($escalateTo, ['DMA', 'MINISTER'], true)) {
            $doc = $this->dispatchStadiumDocument(
                $escalateTo,
                "[STADIUM EVENT] {$event->event_code}: {$event->title}",
                "Venue: {$facility->name}\nDate: {$event->event_date->format('d M Y')}\nOrganizer: {$event->organizer}\nExpected Attendance: " . number_format($event->expected_attendance) . "\nFee: USD " . number_format((float) $event->revenue_fee_usd, 2),
                'High',
                (float) $event->revenue_fee_usd > 0 ? (float) $event->revenue_fee_usd : null,
                $request->user()
            );
            if ($doc) {
                $event->update(['ministry_document_id' => $doc->id]);
                $msg .= " Briefed {$escalateTo} ({$doc->tracking_number}).";
            }
        }

        AuditService::log('stadium_event_created', StadiumEvent::class, $event->id, [],
            ['event_code' => $event->event_code, 'facility' => $facility->name, 'event_date' => $data['event_date']],
            "Scheduled stadium event {$event->event_code}: {$event->title}");

        return redirect()->route('stadium.dashboard', ['tab' => 'events'])->with('success', $msg);
    }

    public function updateEventReadiness(Request $request, StadiumEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'status'                     => ['required', Rule::in(StadiumEvent::STATUSES)],
            'checklist_pitch_ready'      => 'nullable|boolean',
            'checklist_lighting_power'   => 'nullable|boolean',
            'checklist_medical_ems'      => 'nullable|boolean',
            'checklist_security_crowd'   => 'nullable|boolean',
            'checklist_sanitation_water' => 'nullable|boolean',
            'checklist_sound_scoreboard' => 'nullable|boolean',
            'notes'                      => 'nullable|string|max:2000',
        ]);

        $event->load('facility');

        $checks = [];
        $allChecked = true;
        foreach (array_keys(StadiumEvent::CHECKLIST_FIELDS) as $field) {
            $val = (bool) ($data[$field] ?? false);
            $checks[$field] = $val;
            if (!$val) {
                $allChecked = false;
            }
        }

        $targetStatus = $data['status'];
        if ($targetStatus === 'Safety Cleared') {
            if ($event->facility->operational_status === 'Closed / Unsafe') {
                return back()->with('error', "Cannot clear event: {$event->facility->name} is currently Closed / Unsafe.");
            }
            if ($event->exceeds_capacity) {
                return back()->with('error', "Cannot clear event: expected attendance ({$event->expected_attendance}) exceeds facility capacity ({$event->facility->capacity}).");
            }
            if (!$allChecked) {
                return back()->with('error', 'Cannot grant Safety Clearance until all 6 pre-event readiness checklist items are verified.');
            }
        }

        $update = array_merge($checks, [
            'status' => $targetStatus,
            'notes'  => ($data['notes'] ?? null) ?: $event->notes,
        ]);

        if ($targetStatus === 'Safety Cleared') {
            $update['safety_cleared_by'] = $request->user()->id;
            $update['safety_cleared_at'] = now();
        } elseif (in_array($targetStatus, ['Scheduled', 'Preparation In Progress', 'Postponed', 'Cancelled'], true)) {
            $update['safety_cleared_by'] = null;
            $update['safety_cleared_at'] = null;
        }

        $event->update($update);

        AuditService::log('stadium_event_updated', StadiumEvent::class, $event->id, [],
            ['event_code' => $event->event_code, 'status' => $targetStatus, 'checklist_pct' => $event->fresh()->checklist_percent],
            "Updated event {$event->event_code} readiness to {$targetStatus}");

        return redirect()->route('stadium.dashboard', ['tab' => 'events'])
            ->with('success', "Event {$event->event_code} updated to {$targetStatus} ({$event->fresh()->checklist_completed_count}/6 checks ready).");
    }

    // ───────────────────────────────────────────────────────────── Maintenance

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stadium_facility_id' => 'required|exists:stadium_facilities,id',
            'zone_area'           => ['required', Rule::in(StadiumMaintenanceLog::ZONES)],
            'title'               => 'required|string|max:200',
            'maintenance_type'    => ['required', Rule::in(StadiumMaintenanceLog::TYPES)],
            'priority'            => ['required', Rule::in(StadiumMaintenanceLog::PRIORITIES)],
            'scheduled_date'      => 'nullable|date',
            'estimated_cost_usd'  => 'nullable|numeric|min:0',
            'assigned_contractor' => 'nullable|string|max:160',
            'description'         => 'required|string|max:2500',
            'escalate_to'         => ['nullable', Rule::in(['none', 'DMA', 'PROC', 'FIN'])],
        ]);

        $facility = StadiumFacility::findOrFail($data['stadium_facility_id']);

        $log = StadiumMaintenanceLog::create([
            'work_order_no'       => StadiumMaintenanceLog::generateWorkOrderNo(),
            'stadium_facility_id' => $facility->id,
            'zone_area'           => $data['zone_area'],
            'title'               => $data['title'],
            'maintenance_type'    => $data['maintenance_type'],
            'priority'            => $data['priority'],
            'status'              => 'Open',
            'scheduled_date'      => ($data['scheduled_date'] ?? null) ?: null,
            'estimated_cost_usd'  => isset($data['estimated_cost_usd']) && $data['estimated_cost_usd'] !== '' ? $data['estimated_cost_usd'] : 0,
            'assigned_contractor' => ($data['assigned_contractor'] ?? null) ?: null,
            'description'         => $data['description'],
            'reported_by'         => $request->user()->id,
        ]);

        $msg = "Maintenance work order {$log->work_order_no} created for {$facility->name}.";
        $escalateTo = $data['escalate_to'] ?? 'none';
        if (in_array($escalateTo, ['DMA', 'PROC', 'FIN'], true)) {
            $doc = $this->dispatchStadiumDocument(
                $escalateTo,
                "[STADIUM MAINTENANCE {$log->priority}] {$log->work_order_no}: {$log->title}",
                "Facility: {$facility->name}\nZone: {$log->zone_area}\nType: {$log->maintenance_type}\nEstimated Cost: USD " . number_format((float) $log->estimated_cost_usd, 2) . "\nDetails: {$log->description}",
                $log->priority === 'Critical' ? 'Urgent' : 'High',
                (float) $log->estimated_cost_usd > 0 ? (float) $log->estimated_cost_usd : null,
                $request->user()
            );
            if ($doc) {
                $log->update(['ministry_document_id' => $doc->id]);
                $msg .= " Routed to {$escalateTo} ({$doc->tracking_number}).";
            }
        }

        AuditService::log('stadium_maintenance_created', StadiumMaintenanceLog::class, $log->id, [],
            ['work_order_no' => $log->work_order_no, 'priority' => $log->priority],
            "Created stadium maintenance work order {$log->work_order_no}: {$log->title}");

        return redirect()->route('stadium.dashboard', ['tab' => 'maintenance'])->with('success', $msg);
    }

    public function updateMaintenanceStatus(Request $request, StadiumMaintenanceLog $maintenance): RedirectResponse
    {
        $data = $request->validate([
            'status'           => ['required', Rule::in(StadiumMaintenanceLog::STATUSES)],
            'actual_cost_usd'  => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string|max:2000',
        ]);

        $update = [
            'status'           => $data['status'],
            'actual_cost_usd'  => isset($data['actual_cost_usd']) && $data['actual_cost_usd'] !== '' ? $data['actual_cost_usd'] : $maintenance->actual_cost_usd,
            'completion_notes' => ($data['completion_notes'] ?? null) ?: $maintenance->completion_notes,
        ];

        if ($data['status'] === 'Completed') {
            $update['completed_date'] = today()->toDateString();
            $update['completed_by'] = $request->user()->id;
        }

        $maintenance->update($update);

        AuditService::log('stadium_maintenance_updated', StadiumMaintenanceLog::class, $maintenance->id, [],
            ['work_order_no' => $maintenance->work_order_no, 'status' => $data['status']],
            "Updated work order {$maintenance->work_order_no} to {$data['status']}");

        return redirect()->route('stadium.dashboard', ['tab' => 'maintenance'])
            ->with('success', "Work order {$maintenance->work_order_no} updated to {$data['status']}.");
    }

    // ────────────────────────────────────────────────────── Safety Inspections

    public function storeSafetyInspection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stadium_facility_id'   => 'required|exists:stadium_facilities,id',
            'stadium_event_id'      => 'nullable|exists:stadium_events,id',
            'inspection_type'       => ['required', Rule::in(StadiumSafetyInspection::INSPECTION_TYPES)],
            'inspection_date'       => 'required|date',
            'overall_rating'        => ['required', Rule::in(StadiumSafetyInspection::RATINGS)],
            'safety_score'          => 'required|integer|min:0|max:100',
            'fire_exits_clear'      => 'nullable|boolean',
            'structural_ok'         => 'nullable|boolean',
            'medical_station_ready' => 'nullable|boolean',
            'security_briefed'      => 'nullable|boolean',
            'hazards_identified'    => 'nullable|string|max:2000',
            'corrective_actions'    => 'nullable|string|max:2000',
            'inspector_name'        => 'required|string|max:120',
            'escalate_to'           => ['nullable', Rule::in(['none', 'DMA', 'MINISTER'])],
        ]);

        $facility = StadiumFacility::findOrFail($data['stadium_facility_id']);

        $inspection = StadiumSafetyInspection::create([
            'inspection_no'         => StadiumSafetyInspection::generateInspectionNo(),
            'stadium_facility_id'   => $facility->id,
            'stadium_event_id'      => ($data['stadium_event_id'] ?? null) ?: null,
            'inspection_type'       => $data['inspection_type'],
            'inspection_date'       => $data['inspection_date'],
            'overall_rating'        => $data['overall_rating'],
            'safety_score'          => (int) $data['safety_score'],
            'fire_exits_clear'      => (bool) ($data['fire_exits_clear'] ?? false),
            'structural_ok'         => (bool) ($data['structural_ok'] ?? false),
            'medical_station_ready' => (bool) ($data['medical_station_ready'] ?? false),
            'security_briefed'      => (bool) ($data['security_briefed'] ?? false),
            'hazards_identified'    => ($data['hazards_identified'] ?? null) ?: null,
            'corrective_actions'    => ($data['corrective_actions'] ?? null) ?: null,
            'inspector_name'        => $data['inspector_name'],
            'recorded_by'           => $request->user()->id,
        ]);

        $extraMsg = '';
        if ($inspection->overall_rating === 'Failed / Hold Event' && $inspection->stadium_event_id) {
            $linkedEvent = StadiumEvent::find($inspection->stadium_event_id);
            if ($linkedEvent && $linkedEvent->status === 'Safety Cleared') {
                $linkedEvent->update([
                    'status'            => 'Postponed',
                    'safety_cleared_by' => null,
                    'safety_cleared_at' => null,
                ]);
                $extraMsg .= " Linked event {$linkedEvent->event_code} clearance revoked & placed on hold.";
            }
        }

        $escalateTo = $data['escalate_to'] ?? 'none';
        if (in_array($escalateTo, ['DMA', 'MINISTER'], true)) {
            $doc = $this->dispatchStadiumDocument(
                $escalateTo,
                "[STADIUM SAFETY {$inspection->overall_rating}] {$inspection->inspection_no}: {$facility->name}",
                "Inspection Type: {$inspection->inspection_type}\nSafety Score: {$inspection->safety_score}%\nInspector: {$inspection->inspector_name}\nHazards: " . ($inspection->hazards_identified ?: 'None noted') . "\nCorrective Actions: " . ($inspection->corrective_actions ?: 'None noted'),
                $inspection->overall_rating === 'Failed / Hold Event' ? 'Urgent' : 'High',
                null,
                $request->user()
            );
            if ($doc) {
                $extraMsg .= " Escalated to {$escalateTo} ({$doc->tracking_number}).";
            }
        }

        AuditService::log('stadium_safety_inspected', StadiumSafetyInspection::class, $inspection->id, [],
            ['inspection_no' => $inspection->inspection_no, 'rating' => $inspection->overall_rating, 'score' => $inspection->safety_score],
            "Recorded safety inspection {$inspection->inspection_no} at {$facility->name}: {$inspection->overall_rating} ({$inspection->safety_score}%)");

        return redirect()->route('stadium.dashboard', ['tab' => 'safety'])
            ->with('success', "Safety inspection {$inspection->inspection_no} recorded ({$inspection->overall_rating} — {$inspection->safety_score}%).{$extraMsg}");
    }

    // ───────────────────────────────────────────────────────────────── Exports

    public function eventClearancePdf(StadiumEvent $event)
    {
        $event->load(['facility', 'creator', 'safetyClearer', 'safetyInspections']);
        $checklistFields = StadiumEvent::CHECKLIST_FIELDS;
        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', StadiumEvent::class, $event->id, [], [], "Exported Event Readiness & Safety Clearance PDF for {$event->event_code}");

        return Pdf::loadView('stadium.clearance-pdf', compact('event', 'checklistFields', 'logoDataUri'))
            ->setPaper('a4', 'portrait')
            ->download("MYS-Event-Clearance-{$event->event_code}.pdf");
    }

    public function reportPdf(Request $request)
    {
        $facilities = StadiumFacility::with('latestSafetyInspection')->orderBy('name')->get();
        $events = StadiumEvent::with('facility')->orderByDesc('event_date')->take(30)->get();
        $maintenanceLogs = StadiumMaintenanceLog::with('facility')->latest('created_at')->take(30)->get();
        $inspections = StadiumSafetyInspection::with(['facility', 'event'])->orderByDesc('inspection_date')->take(25)->get();
        $alerts = $this->runReadinessScanner();

        $summary = [
            'facilities'       => $facilities->count(),
            'operational'      => $facilities->where('operational_status', 'Operational')->count(),
            'upcoming_events'  => StadiumEvent::whereDate('event_date', '>=', today())->whereNotIn('status', ['Cancelled', 'Completed'])->count(),
            'open_maintenance' => StadiumMaintenanceLog::whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding'])->count(),
            'avg_safety_score' => StadiumSafetyInspection::avg('safety_score') !== null ? (int) round((float) StadiumSafetyInspection::avg('safety_score')) : null,
            'active_alerts'    => collect($alerts)->where('count', '>', 0)->count(),
        ];

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        AuditService::log('exported', StadiumFacility::class, null, [], ['facilities' => $facilities->count()], 'Exported Stadium Management Operations & Safety Report PDF');

        return Pdf::loadView('stadium.report-pdf', compact('summary', 'alerts', 'facilities', 'events', 'maintenanceLogs', 'inspections', 'logoDataUri'))
            ->setPaper('a4', 'landscape')
            ->download('MYS-Stadium-Operations-Report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportMaintenanceCsv()
    {
        $logs = StadiumMaintenanceLog::with(['facility', 'reporter', 'completer'])->latest('created_at')->get();

        AuditService::log('exported', StadiumMaintenanceLog::class, null, [], ['count' => $logs->count()], 'Exported Stadium Maintenance Work Orders CSV');

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Work Order #', 'Facility', 'Zone / Area', 'Title', 'Type', 'Priority', 'Status',
                'Scheduled Date', 'Completed Date', 'Estimated (USD)', 'Actual (USD)', 'Assigned Contractor', 'Reported By',
            ]);
            foreach ($logs as $m) {
                fputcsv($out, [
                    $m->work_order_no,
                    $m->facility?->name ?? '—',
                    $m->zone_area,
                    $m->title,
                    $m->maintenance_type,
                    $m->priority,
                    $m->status,
                    $m->scheduled_date?->format('Y-m-d') ?? '—',
                    $m->completed_date?->format('Y-m-d') ?? '—',
                    number_format((float) $m->estimated_cost_usd, 2, '.', ''),
                    $m->actual_cost_usd !== null ? number_format((float) $m->actual_cost_usd, 2, '.', '') : '—',
                    $m->assigned_contractor ?: '—',
                    $m->reporter?->name ?? '—',
                ]);
            }
            fclose($out);
        }, 'MYS-Stadium-Maintenance-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ────────────────────────────────────────────────────────── Live Scanner

    public function runReadinessScanner(): array
    {
        $upcomingUncleared = StadiumEvent::whereBetween('event_date', [today(), today()->addDays(7)])
            ->whereIn('status', ['Scheduled', 'Preparation In Progress'])
            ->count();

        $overCapacity = StadiumEvent::with('facility')
            ->whereDate('event_date', '>=', today())
            ->whereNotIn('status', ['Cancelled', 'Completed'])
            ->get()
            ->filter(fn (StadiumEvent $e) => $e->exceeds_capacity)
            ->count();

        $expiredCerts = StadiumFacility::where('operational_status', '!=', 'Closed / Unsafe')
            ->where(function ($q) {
                $q->whereNull('safety_certificate_expiry')->orWhereDate('safety_certificate_expiry', '<', today());
            })->count();

        $criticalMaint = StadiumMaintenanceLog::whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding'])
            ->where(fn ($q) => $q->where('priority', 'Critical')->orWhere('maintenance_type', 'Emergency'))
            ->count();

        $failedSafety = StadiumFacility::with('latestSafetyInspection')
            ->get()
            ->filter(fn (StadiumFacility $f) => $f->latestSafetyInspection?->overall_rating === 'Failed / Hold Event')
            ->count();

        $overdueMaint = StadiumMaintenanceLog::whereIn('status', ['Open', 'In Progress', 'Awaiting Parts / Funding'])
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', today())
            ->count();

        $mk = fn (string $cat, string $sev, string $title, string $detail, int $count) => [
            'category' => $cat,
            'severity' => $count > 0 ? $sev : 'Clear',
            'title'    => $title,
            'detail'   => $detail,
            'count'    => $count,
        ];

        return [
            $mk('Event Readiness', 'High', 'Upcoming Events (Next 7 Days) Not Safety Cleared', 'Scheduled matches or civic events within 7 days still missing final pre-event clearance.', $upcomingUncleared),
            $mk('Crowd Safety', 'Critical', 'Events Exceeding Stadium Seating Capacity', 'Bookings whose expected attendance exceeds certified facility seating capacity.', $overCapacity),
            $mk('Emergency Maintenance', 'Critical', 'Open Critical / Emergency Maintenance Work Orders', 'Urgent structural, pitch, power, or safety repairs awaiting completion.', $criticalMaint),
            $mk('Safety Compliance', 'Critical', 'Facilities With Failed Latest Safety Inspection', 'Venues flagged Failed / Hold Event requiring immediate hazard remediation.', $failedSafety),
            $mk('Facility Certification', 'High', 'Facilities Missing or Expired Safety Certificate', 'Operational stadiums lacking a current statutory safety certificate.', $expiredCerts),
            $mk('Maintenance SLA', 'Medium', 'Overdue Scheduled Maintenance Orders', 'Preventive or corrective work orders past their scheduled completion date.', $overdueMaint),
        ];
    }

    private function dispatchStadiumDocument(string $targetCode, string $subject, string $summary, string $priority, ?float $amountUsd, $user): ?MinistryDocument
    {
        $stadiumDept = Department::where('code', 'STADIUM')->first();
        $targetDept = Department::where('code', $targetCode)->first();
        if (!$targetDept) {
            return null;
        }

        $originId = $stadiumDept?->id ?? $user->employee?->department_id ?? $targetDept->id;
        $allowed = array_values(array_unique(array_filter([$originId, $targetDept->id])));

        $doc = MinistryDocument::create([
            'tracking_number'               => MinistryDocument::generateTrackingNumber(),
            'subject'                       => $subject,
            'document_type'                 => $targetCode === 'PROC' ? 'Procurement Request' : 'Official Correspondence / Directive',
            'priority'                      => $priority,
            'confidentiality'               => 'Internal',
            'origin_department_id'          => $originId,
            'current_department_id'         => $targetDept->id,
            'destination_department_id'     => $targetDept->id,
            'allowed_department_ids'        => $allowed,
            'sender_id'                     => $user->id,
            'amount_usd'                    => $amountUsd,
            'summary'                       => $summary,
            'status'                        => 'Dispatched',
            'received_at_current_office_at' => now(),
        ]);

        DocumentRoutingLog::create([
            'ministry_document_id' => $doc->id,
            'from_department_id'   => $originId,
            'to_department_id'     => $targetDept->id,
            'action'               => 'Dispatched',
            'actor_id'             => $user->id,
            'actor_name'           => $user->name,
            'actor_role'           => $user->primary_role,
            'minutes_note'         => "Stadium Management dispatched {$doc->tracking_number} to {$targetDept->name}.",
            'digital_signature'    => 'STD-DOC-' . strtoupper(substr(hash('sha256', $doc->tracking_number . '|' . $targetCode . '|' . now()->toIso8601String()), 0, 10)),
            'acted_at'             => now(),
        ]);

        return $doc;
    }
}
