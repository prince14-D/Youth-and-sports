@extends('layouts.app')
@section('title', 'Asset & Property Management Dashboard')
@section('page-title', 'Asset & Property Management Division')
@section('breadcrumb', 'Ministry of Youth & Sports › Asset Management Dashboard')

@section('content')
<div class="animate-in">
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <!-- Hero Command Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 55%, #065f46 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.18); font-size:11px; letter-spacing:0.8px;">
                        <i class="bi bi-box-seam-fill text-warning me-1"></i>ASSET &amp; PROPERTY MANAGEMENT DIVISION
                    </span>
                    <h3 class="fw-bold mb-1">{{ $greeting }}, {{ auth()->user()->name }}!</h3>
                    <p class="mb-0 small opacity-85">Manage government property, vehicles &amp; fleet, office equipment, inventory, staff custody, GSA tags, and maintenance logs.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('manage assets')
                    <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#registerPropertyModal">
                        <i class="bi bi-plus-circle-fill me-1"></i>Record Property / Vehicle
                    </button>
                    <button type="button" class="btn btn-light text-navy fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#maintenanceLogModal">
                        <i class="bi bi-wrench-adjustable me-1 text-primary"></i>Log Service / Inspection
                    </button>
                    @endcan
                    <a href="{{ route('assets.export.csv') }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export GSA CSV
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-table me-1"></i>Full Register
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Registered Assets</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ number_format($stats['totalItems']) }}</div>
                    <div class="small text-muted">{{ number_format($stats['totalUnits']) }} total units</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Vehicles / Fleet</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ number_format($stats['vehiclesCount']) }}</div>
                    <div class="small text-muted">Ministry motor pool</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Assigned Custody</div>
                    <div class="fs-2 fw-bold text-info mt-1">{{ number_format($stats['assignedCount']) }}</div>
                    <div class="small text-muted">In staff / dept use</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">In Central Store</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ number_format($stats['availableCount']) }}</div>
                    <div class="small text-muted">Ready to issue</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Maintenance / Fault</div>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ number_format($stats['maintenanceCount']) }}</div>
                    <div class="small text-danger">{{ $stats['damagedCount'] }} damaged</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Total Valuation</div>
                    <div class="fs-4 fw-bold text-success mt-1">${{ number_format($stats['totalValuation'], 0) }}</div>
                    <div class="small text-muted">YTD Service: ${{ number_format($stats['maintenanceSpendYtd'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- DMA-Approved Requisitions Ready for Issuance / Fulfillment -->
    @if($approvedRequests->isNotEmpty())
    <div class="card border-0 shadow-sm border-start border-4 border-success mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-check-circle-fill text-success me-2"></i>DMA-Approved Asset Requisitions Ready for Fulfillment ({{ $approvedRequests->count() }})</h5>
                <small class="text-muted">Issue items from store or procurement and mark fulfilled.</small>
            </div>
            <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-navy">All Requisitions</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-4">Req # &amp; Item</th><th>Department</th><th>Qty / Est. USD</th><th>Approved By</th><th class="text-end pe-4">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($approvedRequests as $req)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-navy font-monospace">{{ $req->request_no }}</span>
                                <div class="fw-bold text-navy mt-1">{{ $req->item_name }} <small class="text-muted fw-normal">({{ $req->category }})</small></div>
                            </td>
                            <td class="small">{{ $req->department?->name }}</td>
                            <td class="small"><strong>{{ $req->quantity }}x</strong> @if($req->estimated_cost_usd) &bull; ${{ number_format((float)$req->estimated_cost_usd, 2) }} @endif</td>
                            <td class="small">{{ $req->reviewer?->name ?? 'DMA' }} <span class="d-block text-muted">{{ $req->reviewed_at?->format('d M Y') }}</span></td>
                            <td class="text-end pe-4">
                                <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="Fulfilled">
                                    <button type="submit" class="btn btn-sm btn-success fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Mark Issued / Fulfilled</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Category Breakdown -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-grid-fill text-primary me-2"></i>Property &amp; Inventory by Category</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Category</th><th class="text-center">Records</th><th class="text-center">Total Units</th><th class="text-end pe-3">Valuation (USD)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($categoryBreakdown as $cat => $info)
                                <tr>
                                    <td class="ps-3 py-2 fw-semibold text-navy small">{{ $cat }}</td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $info['count'] }}</span></td>
                                    <td class="text-center small">{{ number_format($info['units']) }}</td>
                                    <td class="text-end pe-3 font-monospace small fw-bold text-success">${{ number_format($info['valuation'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No property or equipment recorded yet. Click "Record Property / Vehicle" above to start.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Departmental Holdings & Upcoming Service Alerts -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-building text-success me-2"></i>Assets by Ministry Department</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Department</th><th class="text-center">Items</th><th class="text-center">Units</th><th class="text-end pe-3">Value (USD)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($departmentBreakdown as $dept)
                                <tr>
                                    <td class="ps-3 py-2 small fw-semibold text-navy">{{ $dept->name }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $dept->asset_items_count }}</span></td>
                                    <td class="text-center small">{{ $dept->asset_units_count }}</td>
                                    <td class="text-end pe-3 font-monospace small">${{ number_format($dept->asset_valuation, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-3 text-muted small">No department-assigned assets yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-navy"><i class="bi bi-wrench-adjustable-circle text-warning me-2"></i>Upcoming Maintenance / Service Due (&le;30d)</h6>
                </div>
                <div class="list-group list-group-flush small">
                    @forelse($upcomingService as $a)
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-navy">{{ $a->name }}</strong>
                                <span class="badge bg-light text-dark border font-monospace ms-1">{{ $a->asset_tag }}</span>
                                @if($a->plate_number)<span class="badge bg-info-subtle text-info-emphasis ms-1">{{ $a->plate_number }}</span>@endif
                            </div>
                            <span class="badge {{ $a->next_maintenance_date->isPast() ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $a->next_maintenance_date->format('d M Y') }}
                            </span>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted">No vehicles or equipment due for service within 30 days.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Government Property / Vehicles & Maintenance Logs -->
    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-clock-history text-primary me-2"></i>Recent Property, Vehicles &amp; Inventory</h5>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-navy">Manage All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Asset / Tag / GSA</th><th>Dept &amp; Qty</th><th>Status</th><th class="pe-3 text-end">Value</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentAssets as $asset)
                                <tr>
                                    <td class="ps-3 py-2.5">
                                        <div class="fw-bold text-navy small">{{ $asset->name }}</div>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size:10px;">{{ $asset->asset_tag }}</span>
                                            @if($asset->gsa_code)<span class="badge bg-primary-subtle text-primary font-monospace" style="font-size:10px;">GSA: {{ $asset->gsa_code }}</span>@endif
                                            @if($asset->plate_number)<span class="badge bg-warning-subtle text-dark font-monospace" style="font-size:10px;">Plate: {{ $asset->plate_number }}</span>@endif
                                        </div>
                                    </td>
                                    <td class="small">
                                        <div>{{ $asset->department?->name ?? 'Central Store' }}</div>
                                        <small class="text-muted">{{ $asset->quantity ?? 1 }} {{ $asset->unit ?? 'Unit' }}</small>
                                    </td>
                                    <td><span class="badge {{ $asset->status === 'Assigned' ? 'bg-primary' : ($asset->status === 'Available' ? 'bg-success' : 'bg-warning text-dark') }}">{{ $asset->status }}</span></td>
                                    <td class="pe-3 text-end font-monospace small fw-semibold">${{ number_format($asset->total_valuation, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No assets recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-tools text-warning me-2"></i>Recent Service &amp; Inspection Logs</h5>
                    @can('manage assets')
                    <button type="button" class="btn btn-sm btn-outline-navy" data-bs-toggle="modal" data-bs-target="#maintenanceLogModal">+ Log</button>
                    @endcan
                </div>
                <div class="list-group list-group-flush small">
                    @forelse($recentMaintenance as $m)
                        <div class="list-group-item px-3 py-2.5">
                            <div class="d-flex justify-content-between">
                                <strong class="text-navy">{{ $m->asset?->name }} ({{ $m->asset?->asset_tag }})</strong>
                                <span class="badge bg-light text-dark border">{{ $m->service_date?->format('d M Y') }}</span>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-info-subtle text-info-emphasis">{{ $m->maintenance_type }}</span>
                                @if((float)$m->cost_usd > 0)<span class="fw-bold text-danger ms-1 font-monospace">${{ number_format((float)$m->cost_usd, 2) }}</span>@endif
                                @if($m->vendor)<span class="text-muted ms-1">&bull; {{ $m->vendor }}</span>@endif
                            </div>
                            <div class="text-muted mt-1">{{ \Illuminate\Support\Str::limit($m->notes, 80) }}</div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">No maintenance or inspection logs recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Government Property / Vehicle / Equipment Modal -->
@can('manage assets')
<div class="modal fade" id="registerPropertyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('assets.store') }}">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam-fill me-2"></i>Record Government Property / Vehicle / Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Ministry Asset Tag <span class="text-danger">*</span></label>
                            <input name="asset_tag" class="form-control" placeholder="e.g. MYS-VEH-001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GSA Property Code</label>
                            <input name="gsa_code" class="form-control" placeholder="e.g. GSA-MYS-2026-01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" id="dashAssetCat" class="form-select" required>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Property / Vehicle / Equipment Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. Toyota Land Cruiser Prado / HP ProBook / Conference Chairs" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" min="1" value="1" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <input name="unit" value="Unit" class="form-control" placeholder="Unit/Box">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assigned Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Central Store / Warehouse</option>
                                @foreach($departments as $dept)<option value="{{ $dept->id }}">{{ $dept->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Physical Location</label>
                            <input name="location" class="form-control" placeholder="e.g. SKD Complex, Motor Pool">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Condition <span class="text-danger">*</span></label>
                            <select name="condition" class="form-select" required>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Serial Number</label>
                            <input name="serial_number" class="form-control" placeholder="Equipment S/N">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vehicle Plate # <span class="text-muted">(if vehicle)</span></label>
                            <input name="plate_number" class="form-control" placeholder="e.g. RL-MYS-04">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Chassis / VIN <span class="text-muted">(if vehicle)</span></label>
                            <input name="chassis_number" class="form-control" placeholder="Vehicle VIN">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Value (USD)</label>
                            <input type="number" step="0.01" min="0" name="purchase_value" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Funding Source</label>
                            <select name="funding_source" class="form-select">
                                <option value="">Select Source</option>
                                @foreach($fundingSources as $fs)<option value="{{ $fs }}">{{ $fs }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier / Donor</label>
                            <input name="supplier" class="form-control" placeholder="e.g. GSA / UNDP / Vendor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Acquisition Date</label>
                            <input type="date" name="purchase_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Warranty Expiry</label>
                            <input type="date" name="warranty_expiry" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Next Service / Audit Date</label>
                            <input type="date" name="next_maintenance_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes / Specifications</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional specifications, accessories, or GSA remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-check-circle me-1"></i>Save to Asset Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Maintenance / Service / GSA Inspection Modal -->
<div class="modal fade" id="maintenanceLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('assets.maintenance.store') }}">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-wrench-adjustable me-2"></i>Record Maintenance / Inspection Log</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Select Asset / Vehicle <span class="text-danger">*</span></label>
                            <select name="asset_id" class="form-select" required>
                                <option value="">Choose Asset</option>
                                @foreach($assetsForDropdown as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->asset_tag }} — {{ $opt->name }} {{ $opt->plate_number ? '(' . $opt->plate_number . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Log Type <span class="text-danger">*</span></label>
                            <select name="maintenance_type" class="form-select" required>
                                @foreach($maintenanceTypes as $mt)<option value="{{ $mt }}">{{ $mt }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service / Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost (USD)</label>
                            <input type="number" step="0.01" min="0" name="cost_usd" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendor / Garage / Inspector</label>
                            <input name="vendor" class="form-control" placeholder="e.g. GSA Garage / ICT Unit">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Next Service Date</label>
                            <input type="date" name="next_service_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Update Condition</label>
                            <select name="new_condition" class="form-select">
                                <option value="">Keep current</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Update Status</label>
                            <select name="new_status" class="form-select">
                                <option value="">Keep current</option>
                                <option value="Available">Available</option>
                                <option value="Assigned">Assigned</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Work Done / Audit Notes <span class="text-danger">*</span></label>
                            <textarea name="notes" class="form-control" rows="3" required placeholder="Describe repairs, parts replaced, mileage, or GSA inspection outcome..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
