@extends('layouts.app')
@section('title', 'Procurement Unit')
@section('page-title', 'Procurement Unit')
@section('breadcrumb', 'HRMS › Procurement Unit')

@section('content')
<div class="animate-in">
    <div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #0f766e 55%, #1e3a8a 100%);">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="text-uppercase small fw-semibold opacity-75 mb-1" style="letter-spacing:1.1px;">
                    <i class="bi bi-cart-check me-1"></i> Ministry of Youth &amp; Sports &bull; Republic of Liberia
                </div>
                <h4 class="fw-bold mb-1">Procurement Unit Command Center</h4>
                <p class="mb-0 small opacity-75">Purchasing, tendering &amp; bidding, bid evaluation, supplier registry and procurement compliance.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('manage procurement')
                <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#newTenderModal">
                    <i class="bi bi-plus-circle me-1"></i>New Tender / Requisition
                </button>
                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                    <i class="bi bi-building-add me-1"></i>Register Supplier
                </button>
                @endcan
                <a href="{{ route('procurement.report.pdf') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Report PDF</a>
                <a href="{{ route('procurement.tenders.csv') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-download me-1"></i>Tender Register CSV</a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Active Tenders</div>
            <div class="fs-3 fw-bold text-primary">{{ $kpis['active_tenders'] }}</div>
            <div class="small text-muted">{{ $kpis['closing_soon'] }} closing in 7 days</div>
        </div></div></div>
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Pipeline Value</div>
            <div class="fs-4 fw-bold text-navy">${{ number_format($kpis['pipeline_usd'], 0) }}</div>
            <div class="small text-muted">{{ $kpis['drafts'] }} draft(s)</div>
        </div></div></div>
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Awarded This Year</div>
            <div class="fs-4 fw-bold text-success">${{ number_format($kpis['awarded_usd'], 0) }}</div>
            <div class="small text-muted">{{ $kpis['awarded_count'] }} contract(s)</div>
        </div></div></div>
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Savings vs Estimate</div>
            <div class="fs-4 fw-bold {{ $kpis['savings_usd'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($kpis['savings_usd'], 0) }}</div>
            <div class="small text-muted">Awarded vs budgeted</div>
        </div></div></div>
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Suppliers</div>
            <div class="fs-3 fw-bold text-navy">{{ $kpis['suppliers_active'] }}</div>
            <div class="small {{ $kpis['suppliers_flagged'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $kpis['suppliers_flagged'] }} flagged &bull; avg {{ $kpis['avg_rating'] ? number_format($kpis['avg_rating'], 1) . '/5' : '—' }}</div>
        </div></div></div>
        <div class="col-6 col-xl-2"><div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Compliance Flags</div>
            <div class="fs-3 fw-bold {{ $kpis['compliance_flags'] > 0 ? 'text-danger' : 'text-success' }}">{{ $kpis['compliance_flags'] }}</div>
            <div class="small text-muted">Automated checks</div>
        </div></div></div>
    </div>

    {{-- Compliance Scanner --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-clipboard2-pulse me-2 text-danger"></i>Procurement Compliance Monitor</h6>
                <small class="text-muted">Live checks: bidding rules, thresholds, sole-source use, price reasonableness &amp; supplier documents</small>
            </div>
            <span class="badge {{ $kpis['compliance_flags'] > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-2">
                {{ $kpis['compliance_flags'] > 0 ? $kpis['compliance_flags'] . ' Active Flag(s)' : 'All Checks Clear' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($anomalies as $item)
                @php
                    $border = match ($item['severity']) { 'Critical' => 'border-danger', 'High' => 'border-warning', 'Medium' => 'border-info', default => 'border-success' };
                    $badge = match ($item['severity']) { 'Critical' => 'bg-danger', 'High' => 'bg-warning text-dark', 'Medium' => 'bg-info text-dark', default => 'bg-success' };
                @endphp
                <div class="col-md-6 col-xl-3">
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

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-3 gap-2">
        @foreach(['tenders' => ['bi-file-earmark-text', 'Tender Register'], 'suppliers' => ['bi-building', 'Supplier Registry'], 'performance' => ['bi-star-half', 'Supplier Performance'], 'inbox' => ['bi-inbox', 'Procurement Inbox']] as $key => [$icon, $label])
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === $key ? 'active' : 'bg-white border text-dark' }}" href="{{ route('procurement.dashboard', ['tab' => $key]) }}">
                <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                @if($key === 'inbox' && $incomingDocs->count())<span class="badge bg-danger ms-1">{{ $incomingDocs->count() }}</span>@endif
            </a>
        </li>
        @endforeach
    </ul>

    @if($activeTab === 'tenders')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-navy">Tenders &amp; Purchase Requisitions</h6>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="tenders">
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search tender # or title">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)<option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary">Go</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr><th>Tender #</th><th>Title / Requester</th><th>Method</th><th class="text-end">Estimate</th><th>Status</th><th>Bids</th><th>Awarded To</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($tenders as $t)
                    <tr>
                        <td class="font-monospace small fw-semibold">{{ $t->tender_no }}</td>
                        <td class="small">
                            <div class="fw-semibold">{{ $t->title }}</div>
                            <div class="text-muted">{{ $t->requestingDepartment?->name ?? 'Ministry-Wide' }} &bull; {{ $t->category }}</div>
                        </td>
                        <td class="small">{{ $t->method }}</td>
                        <td class="text-end small fw-bold">${{ number_format((float)$t->estimated_cost_usd, 2) }}</td>
                        <td>
                            <span class="badge {{ $t->status_badge_class }}">{{ $t->status }}</span>
                            @if($t->status === 'Open for Bids' && $t->bid_deadline)
                                <div class="small {{ $t->bid_deadline->lt(today()) ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size:11px;">Closes {{ $t->bid_deadline->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $t->bids_count }}</td>
                        <td class="small">
                            {{ $t->awardedSupplier?->name ?? '—' }}
                            @if($t->awarded_amount_usd)<div class="text-success fw-semibold">${{ number_format((float)$t->awarded_amount_usd, 2) }}</div>@endif
                        </td>
                        <td class="text-end"><a href="{{ route('procurement.tenders.show', $t) }}" class="btn btn-sm btn-navy">Manage</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No tenders found. Click <strong>New Tender / Requisition</strong> to start a procurement.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeTab === 'suppliers')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-navy">Registered Suppliers &amp; Vendors</h6>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="suppliers">
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search supplier">
                <select name="supplier_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($supplierStatuses as $st)<option value="{{ $st }}" {{ $supplierStatusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary">Go</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr><th>Supplier #</th><th>Name &amp; Category</th><th>Contact</th><th>TIN</th><th>Statutory Documents</th><th>Rating</th><th>Status</th><th>Activity</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        <td class="font-monospace small fw-semibold">{{ $s->supplier_no }}</td>
                        <td class="small"><div class="fw-semibold">{{ $s->name }}</div><div class="text-muted">{{ $s->category }}</div></td>
                        <td class="small">{{ $s->contact_person ?: '—' }}<div class="text-muted">{{ $s->phone ?: $s->email }}</div></td>
                        <td class="small font-monospace">{{ $s->tin_number ?: '—' }}</td>
                        <td class="small">
                            <div class="{{ $s->tax_clearance_expired ? 'text-danger fw-semibold' : '' }}">Tax: {{ $s->tax_clearance_expiry?->format('d M Y') ?? 'not on file' }}</div>
                            <div class="{{ $s->license_expired ? 'text-danger fw-semibold' : '' }}">License: {{ $s->business_license_expiry?->format('d M Y') ?? 'not on file' }}</div>
                        </td>
                        <td class="small fw-bold">{{ $s->performance_rating ? number_format((float)$s->performance_rating, 1) . '/5' : '—' }}</td>
                        <td>
                            <span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span>
                            @if($s->status_reason)<div class="text-muted" style="font-size:11px;" title="{{ $s->status_reason }}">{{ \Illuminate\Support\Str::limit($s->status_reason, 35) }}</div>@endif
                        </td>
                        <td class="small text-muted">{{ $s->bids_count }} bids &bull; {{ $s->awarded_tenders_count }} won</td>
                        <td class="text-end text-nowrap">
                            @can('manage procurement')
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#supplierStatusModal"
                                onclick="openSupplierStatus({{ $s->id }}, @js($s->name), @js($s->status), @js($s->tax_clearance_expiry?->toDateString()), @js($s->business_license_expiry?->toDateString()))">Update</button>
                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#evaluateSupplierModal"
                                onclick="openEvaluate({{ $s->id }}, @js($s->name))">Rate</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">No suppliers registered yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeTab === 'performance')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><h6 class="fw-bold mb-0 text-navy">Recent Supplier Performance Evaluations</h6></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase"><tr><th>Date</th><th>Supplier</th><th>Tender</th><th>Quality</th><th>Delivery</th><th>Compliance</th><th>Overall</th><th>Evaluator / Comments</th></tr></thead>
                <tbody>
                @forelse($recentEvaluations as $e)
                    <tr>
                        <td>{{ $e->created_at?->format('d M Y') }}</td>
                        <td class="fw-semibold">{{ $e->supplier?->name }}</td>
                        <td class="font-monospace">{{ $e->tender?->tender_no ?? '—' }}</td>
                        <td>{{ $e->quality_score }}/5</td><td>{{ $e->delivery_score }}/5</td><td>{{ $e->compliance_score }}/5</td>
                        <td><span class="badge {{ (float)$e->overall_score >= 4 ? 'bg-success' : ((float)$e->overall_score >= 3 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ number_format((float)$e->overall_score, 1) }}</span></td>
                        <td>{{ $e->evaluator?->name }}<div class="text-muted">{{ \Illuminate\Support\Str::limit($e->comments, 70) }}</div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No evaluations yet. Use <strong>Rate</strong> in the Supplier Registry after delivery.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeTab === 'inbox')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-inbox me-1"></i>Requests Routed to the Procurement Office</h6>
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
                <div class="text-muted" style="font-size:11px;">{{ $doc->document_type }} &bull; From {{ $doc->originDepartment?->name }} &bull; {{ $doc->hours_in_current_office }}h in office</div>
            </a>
            @empty
            <div class="p-4 text-center text-muted small">No active requests are currently sitting in the Procurement office.</div>
            @endforelse
        </div>
    </div>
    @endif
</div>

@can('manage procurement')
{{-- New Tender --}}
<div class="modal fade" id="newTenderModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('procurement.tenders.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-warning"><h6 class="modal-title fw-bold">New Tender / Purchase Requisition</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-12"><label class="form-label small fw-semibold">Title *</label><input name="title" class="form-control" required maxlength="220" placeholder="e.g. Supply of sports kits for County Youth Games"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Requesting Department</label>
            <select name="requesting_department_id" class="form-select"><option value="">Ministry-Wide</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Category *</label>
            <select name="category" class="form-select" required>@foreach($categories as $c)<option>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Procurement Method *</label>
            <select name="method" class="form-select" required>@foreach($methods as $m)<option>{{ $m }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small fw-semibold">Estimated Cost (USD) *</label><input type="number" step="0.01" min="0" name="estimated_cost_usd" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label small fw-semibold">Bid Deadline</label><input type="date" name="bid_deadline" class="form-control" min="{{ today()->toDateString() }}"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Budget Line</label>
            <select name="budget_allocation_id" class="form-select"><option value="">Not linked</option>@foreach($budgets as $b)<option value="{{ $b->id }}">{{ $b->budget_code }} — {{ $b->department?->name }} ({{ $b->category }})</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label small fw-semibold">Specification / Description</label><textarea name="description" rows="2" class="form-control"></textarea></div>
        <div class="col-12"><label class="form-label small fw-semibold">Justification <span class="text-muted">(mandatory for Sole Source)</span></label><textarea name="justification" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning fw-semibold">Create Draft Tender</button></div>
</form></div></div>

{{-- New Supplier --}}
<div class="modal fade" id="newSupplierModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('procurement.suppliers.store') }}" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Register Supplier / Vendor</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-md-8"><label class="form-label small fw-semibold">Business Name *</label><input name="name" class="form-control" required maxlength="200"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Category *</label><select name="category" class="form-select" required>@foreach($supplierCategories as $c)<option>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Business Reg. No.</label><input name="business_reg_no" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">TIN</label><input name="tin_number" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Contact Person</label><input name="contact_person" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Phone</label><input name="phone" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Address</label><input name="address" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Tax Clearance Expiry</label><input type="date" name="tax_clearance_expiry" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Business License Expiry</label><input type="date" name="business_license_expiry" class="form-control"></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Register Supplier</button></div>
</form></div></div>

{{-- Supplier Status --}}
<div class="modal fade" id="supplierStatusModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="supplierStatusForm" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Update Supplier — <span id="ssName"></span></h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label small fw-semibold">Status</label><select name="status" id="ssStatus" class="form-select">@foreach($supplierStatuses as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Reason <span class="text-muted">(required for Suspended / Blacklisted)</span></label><textarea name="status_reason" rows="2" class="form-control"></textarea></div>
        <div class="row g-2"><div class="col"><label class="form-label small fw-semibold">Tax Clearance Expiry</label><input type="date" name="tax_clearance_expiry" id="ssTax" class="form-control"></div>
        <div class="col"><label class="form-label small fw-semibold">License Expiry</label><input type="date" name="business_license_expiry" id="ssLic" class="form-control"></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Save</button></div>
</form></div></div>

{{-- Evaluate Supplier --}}
<div class="modal fade" id="evaluateSupplierModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="evaluateSupplierForm" class="modal-content">@csrf
    <div class="modal-header bg-warning"><h6 class="modal-title fw-bold">Performance Rating — <span id="evName"></span></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        @foreach(['quality_score' => 'Quality of goods/work', 'delivery_score' => 'Timeliness of delivery', 'compliance_score' => 'Documentation & compliance'] as $f => $l)
        <div class="col-12"><label class="form-label small fw-semibold">{{ $l }} (1–5)</label><select name="{{ $f }}" class="form-select">@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></div>
        @endforeach
        <div class="col-12"><label class="form-label small fw-semibold">Comments</label><textarea name="comments" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning fw-semibold">Save Rating</button></div>
</form></div></div>
@endcan
@endsection

@push('scripts')
<script>
function openSupplierStatus(id, name, status, tax, lic) {
    document.getElementById('supplierStatusForm').action = "{{ url('/procurement/suppliers') }}/" + id + "/status";
    document.getElementById('ssName').textContent = name;
    document.getElementById('ssStatus').value = status;
    document.getElementById('ssTax').value = tax || '';
    document.getElementById('ssLic').value = lic || '';
}
function openEvaluate(id, name) {
    document.getElementById('evaluateSupplierForm').action = "{{ url('/procurement/suppliers') }}/" + id + "/evaluations";
    document.getElementById('evName').textContent = name;
}
</script>
@endpush
