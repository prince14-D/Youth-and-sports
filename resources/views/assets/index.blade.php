@extends('layouts.app')
@section('title', 'Asset Management')
@section('page-title', 'Ministry Asset Management & Requisitions')
@section('breadcrumb', 'HRMS › Administration › Assets')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-3">
        <div>
            <h1>Ministry Asset Management &amp; Departmental Requisitions</h1>
            <p>Track equipment by department (HR, Finance, M&amp;E, PDU, NYPP, DMA, ICT), manage staff custody, and route asset requests to DMA.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('assets.dashboard') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-speedometer2 me-1"></i>Asset Dashboard
            </a>
            <a href="{{ route('assets.export.csv') }}" class="btn btn-outline-success fw-semibold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
            @can('manage asset requests')
            <button type="button" class="btn btn-outline-navy fw-semibold" data-bs-toggle="modal" data-bs-target="#assetRequestModal">
                <i class="bi bi-cart-plus-fill me-1"></i>Request Asset (To DMA)
            </button>
            @endcan
            @can('manage assets')
            <button type="button" class="btn btn-navy fw-semibold" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                <i class="bi bi-plus-circle-fill me-1"></i>Register Asset
            </button>
            @endcan
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Total Assets</div>
                <div class="fs-3 fw-bold text-navy">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Assigned</div>
                <div class="fs-3 fw-bold text-primary">{{ $stats['assigned'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Available</div>
                <div class="fs-3 fw-bold text-success">{{ $stats['available'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Maintenance</div>
                <div class="fs-3 fw-bold text-warning">{{ $stats['maintenance'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Pending DMA Req.</div>
                <div class="fs-3 fw-bold text-danger">{{ $stats['pending_requests'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Total Valuation</div>
                <div class="fs-5 fw-bold text-success mt-1">${{ number_format($stats['total_value_usd'], 0) }}</div>
            </div></div>
        </div>
    </div>

    <!-- Tabs: Asset Register vs Departmental Requisitions -->
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" data-bs-toggle="pill" data-bs-target="#tabAssets" type="button">
                <i class="bi bi-box-seam me-1"></i>Asset Register ({{ $stats['total'] }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="pill" data-bs-target="#tabRequests" type="button">
                <i class="bi bi-clipboard2-check me-1"></i>Departmental Requisitions
                @if($stats['pending_requests'] > 0)<span class="badge bg-danger ms-1">{{ $stats['pending_requests'] }}</span>@endif
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB 1: Asset Inventory -->
        <div class="tab-pane fade show active" id="tabAssets">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <form method="GET" action="{{ route('assets.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Tag, item, serial, location...">
                        </div>
                        <div class="col-md-3">
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string)$deptFilter === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $categoryFilter === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                @foreach(['Available', 'Assigned', 'Maintenance', 'Retired'] as $st)
                                    <option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-navy flex-fill">Filter</button>
                            <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Asset &amp; Tag</th>
                                <th>Department / Location</th>
                                <th>Condition &amp; Status</th>
                                <th>Current Custodian</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-navy">{{ $asset->name }}</div>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border font-monospace">{{ $asset->asset_tag }}</span>
                                            {{ $asset->category }}
                                            @if($asset->serial_number) &bull; S/N: {{ $asset->serial_number }}@endif
                                        </div>
                                        @if($asset->purchase_value)
                                            <small class="text-success fw-semibold">Value: ${{ number_format((float)$asset->purchase_value, 2) }}</small>
                                        @endif
                                    </td>
                                    <td class="small">
                                        <div class="fw-semibold text-navy">{{ $asset->department?->name ?? 'Central Store' }}</div>
                                        <div class="text-muted">{{ $asset->location ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $asset->status === 'Assigned' ? 'bg-primary' : ($asset->status === 'Available' ? 'bg-success' : 'bg-warning text-dark') }}">{{ $asset->status }}</span>
                                        <small class="d-block text-muted mt-1">{{ $asset->condition }}</small>
                                    </td>
                                    <td class="small">
                                        @if($asset->activeAssignment)
                                            <div class="fw-bold text-navy">{{ $asset->activeAssignment->employee?->full_name }}</div>
                                            <small class="text-muted">Since {{ $asset->activeAssignment->assigned_date?->format('d M Y') }}</small>
                                        @else
                                            <span class="text-muted">In Store / Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('manage assets')
                                            @if($asset->status !== 'Assigned')
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignAsset-{{ $asset->id }}" title="Assign to Staff">
                                                    <i class="bi bi-person-plus"></i> Assign
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#returnAsset-{{ $asset->id }}" title="Check In / Return">
                                                    <i class="bi bi-box-arrow-in-left"></i> Return
                                                </button>
                                            @endif
                                        @endcan
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#historyAsset-{{ $asset->id }}" title="Custody History">
                                            <i class="bi bi-clock-history"></i> ({{ $asset->assignments->count() }})
                                        </button>
                                    </td>
                                </tr>

                                @can('manage assets')
                                <!-- Assign Modal -->
                                <div class="modal fade" id="assignAsset-{{ $asset->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('assets.assign', $asset) }}">
                                                @csrf
                                                <div class="modal-header bg-navy text-white">
                                                    <h5 class="modal-title">Assign {{ $asset->asset_tag }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Assign to Employee / Contractor <span class="text-danger">*</span></label>
                                                        <select name="employee_id" class="form-select" required>
                                                            <option value="">Select Staff Member</option>
                                                            @foreach($employees as $emp)
                                                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_no }} — {{ $emp->department?->code ?? 'HQ' }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label">Issue Date</label>
                                                            <input type="date" name="assigned_date" class="form-control" value="{{ today()->toDateString() }}" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Condition on Issue</label>
                                                            <select name="condition_on_issue" class="form-select"><option>Good</option><option>Fair</option></select>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <label class="form-label">Notes</label>
                                                        <input type="text" name="notes" class="form-control" placeholder="Accessories, charger, bag...">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-navy">Confirm Assignment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Return Modal -->
                                <div class="modal fade" id="returnAsset-{{ $asset->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('assets.return', $asset) }}">
                                                @csrf
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title">Check In Asset {{ $asset->asset_tag }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Condition on Return <span class="text-danger">*</span></label>
                                                        <select name="condition_on_return" class="form-select" required>
                                                            <option value="Good">Good (Mark Available)</option>
                                                            <option value="Fair">Fair (Mark Available)</option>
                                                            <option value="Damaged">Damaged (Send to Maintenance)</option>
                                                        </select>
                                                    </div>
                                                    <label class="form-label">Return Notes</label>
                                                    <input type="text" name="notes" class="form-control" placeholder="Optional inspection remarks...">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning text-dark fw-semibold">Confirm Return</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endcan

                                <!-- History Modal -->
                                <div class="modal fade" id="historyAsset-{{ $asset->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold text-navy">Custody History — {{ $asset->asset_tag }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                <div class="list-group list-group-flush small">
                                                    @forelse($asset->assignments as $asg)
                                                        <div class="list-group-item">
                                                            <div class="fw-bold text-navy">{{ $asg->employee?->full_name }}</div>
                                                            <div class="text-muted">Issued: {{ $asg->assigned_date?->format('d M Y') }} &bull; Returned: {{ $asg->returned_date?->format('d M Y') ?? 'Active' }}</div>
                                                            @if($asg->notes)<div class="text-secondary mt-1">{{ $asg->notes }}</div>@endif
                                                        </div>
                                                    @empty
                                                        <div class="p-4 text-center text-muted">No assignment history yet.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No assets registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($assets->hasPages())
                    <div class="card-footer bg-white border-top">{{ $assets->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>

        <!-- TAB 2: Departmental Asset Requisitions -->
        <div class="tab-pane fade" id="tabRequests">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-clipboard2-check text-primary me-2"></i>Departmental Asset Requisitions (Routed to DMA)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Req # &amp; Item</th>
                                <th>Department</th>
                                <th>Qty &amp; Est. Cost</th>
                                <th>Linked File</th>
                                <th>Status</th>
                                <th class="text-end pe-4">DMA Review</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assetRequests as $req)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="badge bg-navy font-monospace">{{ $req->request_no }}</span>
                                        <span class="badge {{ $req->priority === 'Urgent' ? 'bg-danger' : 'bg-light text-dark border' }} ms-1">{{ $req->priority }}</span>
                                        <div class="fw-bold text-navy mt-1">{{ $req->item_name }} <span class="text-muted fw-normal small">({{ $req->category }})</span></div>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($req->justification, 70) }}</small>
                                    </td>
                                    <td class="small">
                                        <div class="fw-semibold text-navy">{{ $req->department?->name }}</div>
                                        <small class="text-muted">By {{ $req->requester?->name }}</small>
                                    </td>
                                    <td class="small">
                                        <div class="fw-bold">Qty: {{ $req->quantity }}</div>
                                        @if($req->estimated_cost_usd)<div class="text-success font-monospace">${{ number_format((float)$req->estimated_cost_usd, 2) }}</div>@endif
                                    </td>
                                    <td>
                                        @if($req->document)
                                            <a href="{{ route('documents.show', $req->document) }}" class="badge bg-primary-subtle text-primary text-decoration-none font-monospace">
                                                {{ $req->document->tracking_number }}
                                            </a>
                                        @else — @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $req->status === 'Approved' || $req->status === 'Fulfilled' ? 'bg-success' : ($req->status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('approve asset requests')
                                            @if($req->status === 'Pending DMA')
                                                <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="Approved">
                                                    <button type="submit" class="btn btn-sm btn-success" title="DMA Approve"><i class="bi bi-check-lg"></i> Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="Rejected">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                                </form>
                                            @elseif($req->status === 'Approved')
                                                <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="Fulfilled">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Mark Fulfilled</button>
                                                </form>
                                            @else
                                                <span class="small text-muted">{{ $req->reviewer?->name ?? 'Closed' }}</span>
                                            @endif
                                        @else
                                            <span class="small text-muted">{{ $req->status }}</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">No departmental asset requisitions submitted yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Asset Modal -->
@can('manage assets')
<div class="modal fade" id="addAssetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('assets.store') }}">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Register New Ministry Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Asset Tag <span class="text-danger">*</span></label><input name="asset_tag" class="form-control" placeholder="MYS-LAP-001" required></div>
                        <div class="col-md-8"><label class="form-label">Asset Name / Model <span class="text-danger">*</span></label><input name="name" class="form-control" placeholder="e.g. HP ProBook 450 G10 / Toyota Hilux" required></div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Central Store</option>
                                @foreach($departments as $dept)<option value="{{ $dept->id }}">{{ $dept->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Office / Location</label><input name="location" class="form-control" placeholder="e.g. SKD Complex, Room 204"></div>
                        <div class="col-md-4"><label class="form-label">Serial Number</label><input name="serial_number" class="form-control"></div>
                        <div class="col-md-4">
                            <label class="form-label">Condition</label>
                            <select name="condition" class="form-select"><option>Good</option><option>Fair</option><option>Damaged</option></select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Purchase Value (USD)</label><input type="number" step="0.01" min="0" name="purchase_value" class="form-control" placeholder="0.00"></div>
                        <div class="col-md-6"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Register Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Departmental Asset Requisition Modal -->
@can('manage asset requests')
<div class="modal fade" id="assetRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('assets.requests.store') }}">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-cart-plus-fill me-2"></i>Submit Departmental Asset Request to DMA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 small py-2 mb-3">
                        <i class="bi bi-send-check me-1"></i>This request will automatically create a tracked paperless document routed to the <strong>Deputy Minister for Administration (DMA)</strong>.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Requesting Department</label>
                            <select name="department_id" class="form-select">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ auth()->user()->employee?->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Item Needed <span class="text-danger">*</span></label><input name="item_name" class="form-control" placeholder="e.g. Field Data Tablets for M&E Team" required></div>
                        <div class="col-md-2"><label class="form-label">Qty <span class="text-danger">*</span></label><input type="number" name="quantity" min="1" value="1" class="form-control" required></div>
                        <div class="col-md-2"><label class="form-label">Est. USD</label><input type="number" step="0.01" min="0" name="estimated_cost_usd" class="form-control" placeholder="0.00"></div>
                        <div class="col-md-2">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select"><option>Normal</option><option>High</option><option>Urgent</option></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Official Justification <span class="text-danger">*</span></label>
                            <textarea name="justification" class="form-control" rows="3" required placeholder="Explain operational need and departmental program supported..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Submit Request to DMA</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
