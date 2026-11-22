@extends('layouts.app')
@section('title', 'Stadium Management')
@section('page-title', 'Stadium Management')
@section('breadcrumb', 'HRMS › Stadium Management')

@section('content')
<div class="animate-in">
    {{-- Executive Header Banner --}}
    <div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #047857 52%, #1e3a8a 100%);">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="text-uppercase small fw-semibold opacity-75 mb-1" style="letter-spacing:1.1px;">
                    <i class="bi bi-trophy me-1"></i> Ministry of Youth &amp; Sports &bull; Republic of Liberia
                </div>
                <h4 class="fw-bold mb-1">Stadium Operations, Maintenance &amp; Safety Command Center</h4>
                <p class="mb-0 small opacity-75">
                    Manage national &amp; county stadiums, event preparation checklists, facility maintenance work orders, and crowd safety compliance.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('manage stadium management')
                <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#newEventModal">
                    <i class="bi bi-calendar-plus me-1"></i>Schedule Event
                </button>
                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newMaintenanceModal">
                    <i class="bi bi-wrench-adjustable me-1"></i>Log Work Order
                </button>
                <button type="button" class="btn btn-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newSafetyModal">
                    <i class="bi bi-shield-check me-1"></i>Safety Inspection
                </button>
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#newFacilityModal">
                    <i class="bi bi-building-add me-1"></i>Add Facility
                </button>
                @endcan
                <a href="{{ route('stadium.report.pdf') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Operations PDF
                </a>
                <a href="{{ route('stadium.maintenance.csv') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-download me-1"></i>Maintenance CSV
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    {{-- KPI Summary Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Managed Facilities</div>
                <div class="fs-3 fw-bold text-navy">{{ $kpis['operational_facilities'] }}/{{ $kpis['total_facilities'] }}</div>
                <div class="small text-muted">{{ number_format($kpis['total_capacity']) }} total seats</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Upcoming Events</div>
                <div class="fs-3 fw-bold text-primary">{{ $kpis['upcoming_events'] }}</div>
                <div class="small text-success fw-semibold">{{ $kpis['cleared_events'] }} Safety Cleared</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Open Work Orders</div>
                <div class="fs-3 fw-bold text-warning">{{ $kpis['open_work_orders'] }}</div>
                <div class="small text-danger fw-semibold">{{ $kpis['critical_work_orders'] }} Critical / Emergency</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Avg Safety Score</div>
                <div class="fs-3 fw-bold {{ ($kpis['avg_safety_score'] ?? 100) >= 80 ? 'text-success' : 'text-warning' }}">
                    {{ $kpis['avg_safety_score'] !== null ? $kpis['avg_safety_score'] . '%' : '—' }}
                </div>
                <div class="small text-muted">Facility safety audits</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">YTD Booking Fees</div>
                <div class="fs-4 fw-bold text-success">${{ number_format($kpis['ytd_revenue_usd'], 0) }}</div>
                <div class="small text-muted">Scheduled &amp; completed</div>
            </div></div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Readiness Alerts</div>
                <div class="fs-3 fw-bold {{ $kpis['active_alerts'] > 0 ? 'text-danger' : 'text-success' }}">{{ $kpis['active_alerts'] }}</div>
                <div class="small text-muted">Live safety checks</div>
            </div></div>
        </div>
    </div>

    {{-- Live Stadium Readiness & Safety Alert Monitor --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-broadcast me-2 text-danger"></i>Live Stadium Readiness &amp; Safety Monitor</h6>
                <small class="text-muted">Automated checks across event safety clearance, seating capacity limits, certificates &amp; emergency repairs</small>
            </div>
            <span class="badge {{ $kpis['active_alerts'] > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-2">
                {{ $kpis['active_alerts'] > 0 ? $kpis['active_alerts'] . ' Active Alert(s)' : 'All Stadium Checks Clear' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($alerts as $item)
                @php
                    $border = match ($item['severity']) { 'Critical' => 'border-danger', 'High' => 'border-warning', 'Medium' => 'border-info', default => 'border-success' };
                    $badge  = match ($item['severity']) { 'Critical' => 'bg-danger', 'High' => 'bg-warning text-dark', 'Medium' => 'bg-info text-dark', default => 'bg-success' };
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="p-3 rounded-3 border border-start border-4 {{ $border }} h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase fw-semibold text-muted" style="font-size:10px;letter-spacing:.6px;">{{ $item['category'] }}</span>
                            <span class="badge {{ $badge }}">{{ $item['severity'] }} &bull; {{ $item['count'] }}</span>
                        </div>
                        <div class="fw-semibold small mb-1">{{ $item['title'] }}</div>
                        <div class="text-muted" style="font-size:11.5px;">{{ $item['detail'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <ul class="nav nav-pills mb-3 gap-2">
        @foreach([
            'events'      => ['bi-calendar-event', 'Event Preparation & Readiness'],
            'facilities'  => ['bi-building', 'Stadiums & Facilities (' . $facilities->count() . ')'],
            'maintenance' => ['bi-wrench-adjustable', 'Maintenance & Work Orders'],
            'safety'      => ['bi-shield-check', 'Safety Inspections'],
            'inbox'       => ['bi-inbox', 'Stadium Inbox'],
        ] as $key => [$icon, $label])
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === $key ? 'active' : 'bg-white border text-dark' }}" href="{{ route('stadium.dashboard', ['tab' => $key]) }}">
                <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                @if($key === 'maintenance' && $kpis['open_work_orders'] > 0)<span class="badge bg-warning text-dark ms-1">{{ $kpis['open_work_orders'] }}</span>@endif
                @if($key === 'inbox' && $incomingDocs->count() > 0)<span class="badge bg-danger ms-1">{{ $incomingDocs->count() }}</span>@endif
            </a>
        </li>
        @endforeach
    </ul>

    {{-- TAB 1: Event Preparation & Readiness --}}
    @if($activeTab === 'events')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy">Scheduled Events &amp; Pre-Event Readiness Checklists</h6>
                <small class="text-muted">All 6 readiness items (Pitch, Lighting, Medical, Security, Sanitation, PA/Scoreboard) must pass before Safety Clearance</small>
            </div>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="events">
                <select name="facility_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Stadiums</option>
                    @foreach($facilities as $f)<option value="{{ $f->id }}" {{ (string)$facilityFilter === (string)$f->id ? 'selected' : '' }}>{{ $f->name }}</option>@endforeach
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($eventStatuses as $st)<option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>@endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Event #</th>
                        <th>Event &amp; Venue</th>
                        <th>Date &amp; Time</th>
                        <th>Crowd / Fee</th>
                        <th>Readiness Checklist</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $ev)
                    <tr>
                        <td class="font-monospace small fw-semibold">{{ $ev->event_code }}</td>
                        <td class="small">
                            <div class="fw-semibold">{{ $ev->title }}</div>
                            <div class="text-muted">{{ $ev->facility?->name }} &bull; {{ $ev->event_type }}</div>
                            <div class="text-muted" style="font-size:11px;">Organizer: {{ $ev->organizer }}</div>
                        </td>
                        <td class="small text-nowrap">
                            <div class="fw-semibold">{{ $ev->event_date?->format('d M Y') }}</div>
                            <div class="text-muted">{{ $ev->start_time ?: 'TBD' }}{{ $ev->end_time ? ' – ' . $ev->end_time : '' }}</div>
                        </td>
                        <td class="small">
                            <div class="{{ $ev->exceeds_capacity ? 'text-danger fw-bold' : '' }}">
                                {{ number_format($ev->expected_attendance) }}
                                @if($ev->facility?->capacity)/ {{ number_format($ev->facility->capacity) }}@endif
                            </div>
                            @if($ev->exceeds_capacity)<span class="badge bg-danger">Over Capacity!</span>@endif
                            <div class="text-muted">${{ number_format((float)$ev->revenue_fee_usd, 2) }}</div>
                        </td>
                        <td style="min-width:165px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar {{ $ev->is_checklist_complete ? 'bg-success' : 'bg-info' }}" style="width:{{ $ev->checklist_percent }}%"></div>
                                </div>
                                <span class="small fw-bold">{{ $ev->checklist_completed_count }}/6</span>
                            </div>
                            @if($ev->safety_cleared_at)
                                <div class="text-success" style="font-size:11px;">Cleared {{ $ev->safety_cleared_at->format('d M') }} by {{ $ev->safetyClearer?->name }}</div>
                            @endif
                        </td>
                        <td class="small">
                            <span class="badge {{ $ev->status_badge_class }}">{{ $ev->status }}</span>
                            @if($ev->document)
                                <div class="mt-1"><a href="{{ route('documents.show', $ev->document) }}" class="badge bg-navy text-white text-decoration-none">{{ $ev->document->tracking_number }}</a></div>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('stadium.events.clearance.pdf', $ev) }}" class="btn btn-sm btn-outline-secondary" title="Clearance Certificate PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @can('manage stadium management')
                            <button type="button" class="btn btn-sm btn-navy"
                                onclick="openEventChecklist({{ $ev->id }}, @js($ev->event_code), @js($ev->title), @js($ev->status), {
                                    checklist_pitch_ready: {{ $ev->checklist_pitch_ready ? 'true' : 'false' }},
                                    checklist_lighting_power: {{ $ev->checklist_lighting_power ? 'true' : 'false' }},
                                    checklist_medical_ems: {{ $ev->checklist_medical_ems ? 'true' : 'false' }},
                                    checklist_security_crowd: {{ $ev->checklist_security_crowd ? 'true' : 'false' }},
                                    checklist_sanitation_water: {{ $ev->checklist_sanitation_water ? 'true' : 'false' }},
                                    checklist_sound_scoreboard: {{ $ev->checklist_sound_scoreboard ? 'true' : 'false' }}
                                })"
                                data-bs-toggle="modal" data-bs-target="#eventReadinessModal">
                                <i class="bi bi-list-check me-1"></i>Prep
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No stadium events scheduled yet. Click <strong>Schedule Event</strong> to register a fixture, tournament, or civic event.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 2: Stadiums & Facilities --}}
    @if($activeTab === 'facilities')
    <div class="row g-3">
        @forelse($facilities as $f)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="font-monospace small text-muted">{{ $f->facility_code }}</span>
                            <span class="badge {{ $f->status_badge_class }}">{{ $f->operational_status }}</span>
                        </div>
                        <h6 class="fw-bold text-navy mb-1">{{ $f->name }}</h6>
                        <div class="small text-muted mb-3">
                            <i class="bi bi-geo-alt me-1"></i>{{ $f->location }} ({{ $f->county }}) &bull; {{ $f->facility_type }}
                        </div>
                        <div class="row g-2 small mb-3">
                            <div class="col-6"><div class="p-2 rounded bg-light"><div class="text-muted" style="font-size:11px;">Capacity</div><strong>{{ number_format($f->capacity) }}</strong></div></div>
                            <div class="col-6"><div class="p-2 rounded bg-light"><div class="text-muted" style="font-size:11px;">Surface</div><strong>{{ $f->surface_type }}</strong></div></div>
                            <div class="col-6"><div class="p-2 rounded bg-light"><div class="text-muted" style="font-size:11px;">Floodlights</div><strong>{{ $f->floodlights_status }}</strong></div></div>
                            <div class="col-6"><div class="p-2 rounded bg-light"><div class="text-muted" style="font-size:11px;">Safety Cert</div>
                                <strong class="{{ $f->safety_certificate_expired || !$f->safety_certificate_expiry ? 'text-danger' : 'text-success' }}">
                                    {{ $f->safety_certificate_expiry?->format('d M Y') ?? 'Missing' }}
                                </strong>
                            </div></div>
                        </div>
                        <div class="small text-muted mb-2">
                            {{ $f->upcoming_events_count }} upcoming event(s) &bull; {{ $f->open_maintenance_count }} open work order(s)
                            @if($f->latestSafetyInspection)
                                <br>Latest Safety Audit: <span class="badge {{ $f->latestSafetyInspection->rating_badge_class }}">{{ $f->latestSafetyInspection->overall_rating }} ({{ $f->latestSafetyInspection->safety_score }}%)</span>
                            @endif
                        </div>
                    </div>
                    @can('manage stadium management')
                    <div class="pt-2 border-top d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="openFacilityStatus({{ $f->id }}, @js($f->name), @js($f->operational_status), @js($f->floodlights_status), @js($f->safety_certificate_expiry?->toDateString()))"
                            data-bs-toggle="modal" data-bs-target="#facilityStatusModal">
                            Update Status / Cert
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12"><div class="card border-0 shadow-sm p-5 text-center text-muted">No facilities registered yet.</div></div>
        @endforelse
    </div>
    @endif

    {{-- TAB 3: Maintenance & Work Orders --}}
    @if($activeTab === 'maintenance')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy">Facility Maintenance &amp; Repair Work Orders</h6>
                <small class="text-muted">Track turf care, electrical/floodlights, structural repairs, generators &amp; escalated DMA/Procurement requests</small>
            </div>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="maintenance">
                <select name="facility_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Stadiums</option>
                    @foreach($facilities as $f)<option value="{{ $f->id }}" {{ (string)$facilityFilter === (string)$f->id ? 'selected' : '' }}>{{ $f->name }}</option>@endforeach
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($maintenanceStatuses as $st)<option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>@endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>Work Order #</th>
                        <th>Facility &amp; Zone</th>
                        <th>Issue / Scope</th>
                        <th>Priority</th>
                        <th class="text-end">Est. / Actual</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceLogs as $m)
                    <tr>
                        <td class="font-monospace small fw-semibold">
                            {{ $m->work_order_no }}
                            <div class="text-muted fw-normal" style="font-size:11px;">{{ $m->scheduled_date?->format('d M Y') ?? 'Unscheduled' }}</div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $m->facility?->name }}</div>
                            <div class="text-muted">{{ $m->zone_area }} &bull; {{ $m->maintenance_type }}</div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $m->title }}</div>
                            <div class="text-muted">{{ \Illuminate\Support\Str::limit($m->description, 95) }}</div>
                            @if($m->assigned_contractor)<div class="text-primary" style="font-size:11px;">Contractor: {{ $m->assigned_contractor }}</div>@endif
                        </td>
                        <td><span class="badge {{ $m->priority_badge_class }}">{{ $m->priority }}</span></td>
                        <td class="text-end small">
                            <div class="fw-semibold">${{ number_format((float)$m->estimated_cost_usd, 2) }}</div>
                            @if($m->actual_cost_usd !== null)<div class="text-success">Act: ${{ number_format((float)$m->actual_cost_usd, 2) }}</div>@endif
                        </td>
                        <td class="small">
                            <span class="badge {{ $m->status_badge_class }}">{{ $m->status }}</span>
                            @if($m->document)
                                <div class="mt-1"><a href="{{ route('documents.show', $m->document) }}" class="badge bg-navy text-white text-decoration-none">{{ $m->document->tracking_number }}</a></div>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('manage stadium management')
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="openMaintenanceStatus({{ $m->id }}, @js($m->work_order_no), @js($m->title), @js($m->status), @js($m->actual_cost_usd))"
                                data-bs-toggle="modal" data-bs-target="#maintenanceStatusModal">Update</button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No maintenance work orders logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 4: Safety Inspections --}}
    @if($activeTab === 'safety')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-navy">Stadium Safety Inspections &amp; Crowd Control Audits</h6>
                <small class="text-muted">Fire exits, structural stand integrity, emergency medical stations &amp; LNP security briefings</small>
            </div>
            @can('manage stadium management')
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#newSafetyModal">
                <i class="bi bi-plus-circle me-1"></i>Record Inspection
            </button>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th>Ref #</th>
                        <th>Facility &amp; Event</th>
                        <th>Type / Date</th>
                        <th>Safety Pillars</th>
                        <th>Score &amp; Rating</th>
                        <th>Inspector &amp; Findings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspections as $ins)
                    <tr>
                        <td class="font-monospace fw-semibold">{{ $ins->inspection_no }}</td>
                        <td>
                            <div class="fw-semibold">{{ $ins->facility?->name }}</div>
                            @if($ins->event)<div class="text-muted">{{ $ins->event->event_code }}: {{ $ins->event->title }}</div>@endif
                        </td>
                        <td>
                            <div>{{ $ins->inspection_type }}</div>
                            <div class="text-muted">{{ $ins->inspection_date?->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $ins->fire_exits_clear ? 'bg-success-subtle text-success' : 'bg-danger' }}">Exits</span>
                            <span class="badge {{ $ins->structural_ok ? 'bg-success-subtle text-success' : 'bg-danger' }}">Structure</span>
                            <span class="badge {{ $ins->medical_station_ready ? 'bg-success-subtle text-success' : 'bg-danger' }}">EMS</span>
                            <span class="badge {{ $ins->security_briefed ? 'bg-success-subtle text-success' : 'bg-danger' }}">Security</span>
                        </td>
                        <td>
                            <span class="fw-bold me-1">{{ $ins->safety_score }}%</span>
                            <span class="badge {{ $ins->rating_badge_class }}">{{ $ins->overall_rating }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $ins->inspector_name }}</div>
                            @if($ins->hazards_identified)<div class="text-danger">Hazards: {{ \Illuminate\Support\Str::limit($ins->hazards_identified, 80) }}</div>@endif
                            @if($ins->corrective_actions)<div class="text-muted">Action: {{ \Illuminate\Support\Str::limit($ins->corrective_actions, 80) }}</div>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No safety inspections recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 5: Stadium Inbox --}}
    @if($activeTab === 'inbox')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-inbox me-1"></i>Files Routed to Stadium Management</h6>
            <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">Open Document Workflow</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse($incomingDocs as $doc)
            <a href="{{ route('documents.show', $doc) }}" class="list-group-item list-group-item-action py-3">
                <div class="d-flex justify-content-between">
                    <span class="font-monospace small fw-bold text-primary">{{ $doc->tracking_number }}</span>
                    <span class="badge {{ $doc->priority_badge_class }}">{{ $doc->priority }}</span>
                </div>
                <div class="fw-semibold small mt-1">{{ $doc->subject }}</div>
                <div class="text-muted" style="font-size:11px;">From {{ $doc->originDepartment?->name }} &bull; {{ $doc->hours_in_current_office }}h in office</div>
            </a>
            @empty
            <div class="p-4 text-center text-muted small">No active paperless requests currently sitting in the Stadium Management office.</div>
            @endforelse
        </div>
    </div>
    @endif
</div>

@can('manage stadium management')
{{-- Modal: Add Facility --}}
<div class="modal fade" id="newFacilityModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('stadium.facilities.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Register Stadium / Sports Facility</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-8"><label class="form-label small fw-semibold">Facility Name *</label><input name="name" class="form-control" required maxlength="200"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Facility Type *</label><select name="facility_type" class="form-select" required>@foreach($facilityTypes as $t)<option>{{ $t }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Location / Address *</label><input name="location" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label small fw-semibold">County *</label><input name="county" class="form-control" value="Montserrado" required></div>
        <div class="col-md-3"><label class="form-label small fw-semibold">Seating Capacity *</label><input type="number" min="0" name="capacity" class="form-control" required value="5000"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Playing Surface *</label><select name="surface_type" class="form-select">@foreach($surfaceTypes as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Floodlights *</label><select name="floodlights_status" class="form-select">@foreach($floodlightStatuses as $fl)<option>{{ $fl }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Operational Status *</label><select name="operational_status" class="form-select">@foreach($operationalStatuses as $os)<option>{{ $os }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Safety Certificate Expiry</label><input type="date" name="safety_certificate_expiry" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Facility Manager / Supervisor</label><input name="manager_name" class="form-control"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Save Facility</button></div>
</form></div></div>

{{-- Modal: Update Facility Status --}}
<div class="modal fade" id="facilityStatusModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="facilityStatusForm" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Update Facility — <span id="fsFacName"></span></h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label small fw-semibold">Operational Status</label><select name="operational_status" id="fsOpStatus" class="form-select">@foreach($operationalStatuses as $os)<option>{{ $os }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Floodlights Status</label><select name="floodlights_status" id="fsFlood" class="form-select">@foreach($floodlightStatuses as $fl)<option>{{ $fl }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Safety Certificate Expiry</label><input type="date" name="safety_certificate_expiry" id="fsCert" class="form-control"></div>
        <div class="mb-0"><label class="form-label small fw-semibold">Operational Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Update Facility</button></div>
</form></div></div>

{{-- Modal: Schedule Event --}}
<div class="modal fade" id="newEventModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('stadium.events.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-warning"><h6 class="modal-title fw-bold">Schedule Stadium Event / Booking</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label small fw-semibold">Stadium / Venue *</label>
            <select name="stadium_facility_id" class="form-select" required>@foreach($facilities as $f)<option value="{{ $f->id }}">{{ $f->name }} (Cap: {{ number_format($f->capacity) }})</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Event Type *</label>
            <select name="event_type" class="form-select" required>@foreach($eventTypes as $et)<option>{{ $et }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label small fw-semibold">Event Title *</label><input name="title" class="form-control" required placeholder="e.g. Lone Star vs Ghana — AFCON Qualifier"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Organizer / Federation *</label><input name="organizer" class="form-control" required placeholder="e.g. Liberia Football Association (LFA)"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Organizer Contact</label><input name="organizer_contact" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Event Date *</label><input type="date" name="event_date" class="form-control" required value="{{ now()->addDays(5)->toDateString() }}"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Start Time</label><input type="time" name="start_time" class="form-control" value="16:00"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">End Time</label><input type="time" name="end_time" class="form-control" value="19:00"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Expected Attendance *</label><input type="number" min="0" name="expected_attendance" class="form-control" required value="15000"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Venue Booking Fee (USD)</label><input type="number" step="0.01" min="0" name="revenue_fee_usd" class="form-control" value="0"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Executive Briefing via Paperless Workflow</label>
            <select name="escalate_to" class="form-select"><option value="none">No executive memo needed</option><option value="DMA">Notify DMA (Operations &amp; Security Coordination)</option><option value="MINISTER">Brief Office of the Minister (VIP / International Match)</option></select></div>
        <div class="col-12"><label class="form-label small fw-semibold">Preparation Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning fw-semibold">Schedule Event</button></div>
</form></div></div>

{{-- Modal: Event Readiness Checklist --}}
<div class="modal fade" id="eventReadinessModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="eventReadinessForm" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Event Readiness &amp; Safety Clearance</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="p-2 rounded bg-light border mb-3 small"><div class="font-monospace fw-bold" id="erCode"></div><div id="erTitle" class="text-muted"></div></div>
        <div class="mb-3">
            <div class="small fw-bold text-navy mb-2">Mandatory Pre-Event Readiness Checklist (6/6 required for Clearance)</div>
            @foreach($checklistFields as $field => $label)
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" value="1" name="{{ $field }}" id="chk_{{ $field }}">
                <label class="form-check-label small" for="chk_{{ $field }}">{{ $label }}</label>
            </div>
            @endforeach
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold">Event Status</label>
            <select name="status" id="erStatus" class="form-select">@foreach($eventStatuses as $st)<option>{{ $st }}</option>@endforeach</select>
        </div>
        <div class="mb-0"><label class="form-label small fw-semibold">Clearance / Ops Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Save Readiness</button></div>
</form></div></div>

{{-- Modal: Log Maintenance Work Order --}}
<div class="modal fade" id="newMaintenanceModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('stadium.maintenance.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Create Stadium Maintenance Work Order</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label small fw-semibold">Facility *</label><select name="stadium_facility_id" class="form-select" required>@foreach($facilities as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Zone / Area *</label><select name="zone_area" class="form-select" required>@foreach($zones as $z)<option>{{ $z }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label small fw-semibold">Work Order Title *</label><input name="title" class="form-control" required placeholder="e.g. Repair East Stand Floodlight Tower Ballasts"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Maintenance Type *</label><select name="maintenance_type" class="form-select">@foreach($maintenanceTypes as $mt)<option>{{ $mt }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Priority *</label><select name="priority" class="form-select">@foreach($priorities as $p)<option {{ $p === 'High' ? 'selected' : '' }}>{{ $p }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Scheduled Date</label><input type="date" name="scheduled_date" class="form-control" value="{{ now()->addDays(2)->toDateString() }}"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Estimated Cost (USD)</label><input type="number" step="0.01" min="0" name="estimated_cost_usd" class="form-control" value="0"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Assigned Technician / Contractor</label><input name="assigned_contractor" class="form-control"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Work Scope &amp; Defect Details *</label><textarea name="description" rows="2" class="form-control" required></textarea></div>
        <div class="col-12"><label class="form-label small fw-semibold">Escalate Funding / Parts Request via Paperless Workflow</label>
            <select name="escalate_to" class="form-select"><option value="none">Internal maintenance team only</option><option value="DMA">Route to DMA for Emergency Repair Approval</option><option value="PROC">Route to Procurement Unit for Parts / Contractor Sourcing</option><option value="FIN">Route to Finance Division for Budget Release</option></select></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Log Work Order</button></div>
</form></div></div>

{{-- Modal: Update Maintenance Status --}}
<div class="modal fade" id="maintenanceStatusModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="maintenanceStatusForm" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Update Work Order</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="p-2 rounded bg-light border mb-3 small"><div class="font-monospace fw-bold" id="msNo"></div><div id="msTitle" class="text-muted"></div></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Status</label><select name="status" id="msStatus" class="form-select">@foreach($maintenanceStatuses as $st)<option>{{ $st }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Actual Cost (USD)</label><input type="number" step="0.01" min="0" name="actual_cost_usd" id="msCost" class="form-control"></div>
        <div class="mb-0"><label class="form-label small fw-semibold">Completion / Progress Notes</label><textarea name="completion_notes" rows="2" class="form-control"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Save Update</button></div>
</form></div></div>

{{-- Modal: Safety Inspection --}}
<div class="modal fade" id="newSafetyModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('stadium.safety.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-danger text-white"><h6 class="modal-title fw-bold">Record Stadium Safety Inspection</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label small fw-semibold">Facility *</label><select name="stadium_facility_id" class="form-select" required>@foreach($facilities as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Linked Upcoming Event (Optional)</label><select name="stadium_event_id" class="form-select"><option value="">Routine / Non-Event Inspection</option>@foreach($upcomingEventsForSelect as $ev)<option value="{{ $ev->id }}">{{ $ev->event_code }} — {{ $ev->title }}</option>@endforeach</select></div>
        <div class="col-md-5"><label class="form-label small fw-semibold">Inspection Type *</label><select name="inspection_type" class="form-select" required>@foreach($inspectionTypes as $it)<option>{{ $it }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small fw-semibold">Date *</label><input type="date" name="inspection_date" class="form-control" value="{{ today()->toDateString() }}" required></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Lead Inspector *</label><input name="inspector_name" class="form-control" required value="{{ auth()->user()?->name }}"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Overall Safety Rating *</label><select name="overall_rating" class="form-select" required>@foreach($inspectionRatings as $ir)<option>{{ $ir }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Safety Score (0–100%) *</label><input type="number" min="0" max="100" name="safety_score" class="form-control" value="90" required></div>
        <div class="col-12">
            <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border small">
                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="fire_exits_clear" value="1" checked>Fire Exits &amp; Gates Clear</label>
                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="structural_ok" value="1" checked>Stands &amp; Railings Structurally Sound</label>
                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="medical_station_ready" value="1" checked>Medical &amp; Ambulance Ready</label>
                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="security_briefed" value="1" checked>Security &amp; Crowd Control Briefed</label>
            </div>
        </div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Hazards Identified</label><textarea name="hazards_identified" rows="2" class="form-control"></textarea></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Corrective Actions</label><textarea name="corrective_actions" rows="2" class="form-control"></textarea></div>
        <div class="col-12"><label class="form-label small fw-semibold">Escalate Safety Hold / Urgent Hazard</label>
            <select name="escalate_to" class="form-select"><option value="none">Record in Stadium Safety Register only</option><option value="DMA">Escalate Safety Report to DMA</option><option value="MINISTER">Escalate Safety Hold to Office of the Minister</option></select></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Save Safety Inspection</button></div>
</form></div></div>
@endcan
@endsection

@push('scripts')
<script>
function openFacilityStatus(id, name, opStatus, flood, cert) {
    document.getElementById('facilityStatusForm').action = "{{ url('/stadium/facilities') }}/" + id + "/status";
    document.getElementById('fsFacName').textContent = name;
    document.getElementById('fsOpStatus').value = opStatus;
    document.getElementById('fsFlood').value = flood;
    document.getElementById('fsCert').value = cert || '';
}
function openEventChecklist(id, code, title, status, checks) {
    document.getElementById('eventReadinessForm').action = "{{ url('/stadium/events') }}/" + id + "/readiness";
    document.getElementById('erCode').textContent = code;
    document.getElementById('erTitle').textContent = title;
    document.getElementById('erStatus').value = status;
    Object.keys(checks).forEach(function (k) {
        var el = document.getElementById('chk_' + k);
        if (el) el.checked = !!checks[k];
    });
}
function openMaintenanceStatus(id, no, title, status, cost) {
    document.getElementById('maintenanceStatusForm').action = "{{ url('/stadium/maintenance') }}/" + id + "/status";
    document.getElementById('msNo').textContent = no;
    document.getElementById('msTitle').textContent = title;
    document.getElementById('msStatus').value = status;
    document.getElementById('msCost').value = cost ?? '';
}
</script>
@endpush
