@extends('layouts.app')
@section('title', 'DMA — Deputy Minister for Administration')
@section('page-title', 'Deputy Minister for Administration (DMA)')
@section('breadcrumb', 'Ministry of Youth & Sports › DMA Command Center')

@section('content')
<div class="animate-in">
    <!-- DMA Executive Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0b1f4b 0%, #1e3a8a 55%, #065f46 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.18); font-size:11px; letter-spacing:0.8px;">
                        <i class="bi bi-shield-check text-warning me-1"></i>HEAD OF ADMINISTRATION &bull; DMA OFFICE
                    </span>
                    <h3 class="fw-bold mb-1">Deputy Minister for Administration Dashboard</h3>
                    <p class="mb-0 small opacity-85">Approve HR salary/staffing requests, Finance &amp; Departmental memos, Asset Requisitions, and route approved actions across the Ministry.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-warning text-dark fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-folder2-open me-1"></i>Document Registry
                    </a>
                    <a href="{{ route('documents.track') }}" class="btn btn-light text-navy fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-search me-1"></i>Track File
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-light fw-semibold btn-sm px-3 py-2">
                        <i class="bi bi-box-seam me-1"></i>Asset Management
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- DMA KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small">Files Awaiting DMA Approval</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ $kpis['dma_docs_pending'] }}</div>
                    <div class="small text-danger fw-semibold">{{ $kpis['urgent_dma_docs'] }} marked Urgent</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-muted small">Pending Asset Requisitions</div>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ $kpis['pending_assets_req'] }}</div>
                    <div class="small text-muted">{{ $kpis['total_assets'] }} total Ministry assets</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-muted small">Payroll &amp; HR Pipelines</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $kpis['pending_payroll'] }} <span class="fs-6 fw-normal text-muted">Payroll</span></div>
                    <div class="small text-muted">{{ $kpis['pending_leaves'] }} leave &bull; {{ $kpis['pending_renewals'] }} renewals</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-navy">
                <div class="card-body">
                    <div class="text-muted small">Active Ministry Workforce</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ $kpis['active_workforce'] }}</div>
                    <div class="small text-muted">{{ $kpis['assigned_assets'] }} assets in staff custody</div>
                </div>
            </div>
        </div>
    </div>

    <!-- DMA Direct Approval Inbox (Documents Routed to DMA) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-inbox-fill text-primary me-2"></i>DMA Executive Approval Inbox ({{ $dmaInbox->count() }})</h5>
                <small class="text-muted">HR salary/approval requests, departmental requisitions, and official memos awaiting DMA action.</small>
            </div>
            <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="btn btn-sm btn-outline-navy">Open Full Registry</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tracking # &amp; Request</th>
                        <th>From Department</th>
                        <th>Amount</th>
                        <th>Priority / Wait</th>
                        <th class="text-end pe-4">DMA Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dmaInbox as $doc)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge bg-navy font-monospace">{{ $doc->tracking_number }}</span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size:10.5px;">{{ $doc->document_type }}</span>
                                <a href="{{ route('documents.show', $doc) }}" class="fw-bold text-navy text-decoration-none d-block mt-1">{{ $doc->subject }}</a>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($doc->summary, 75) }}</small>
                            </td>
                            <td class="small">
                                <div class="fw-semibold text-navy">{{ $doc->originDepartment?->name }}</div>
                                <small class="text-muted">By {{ $doc->sender?->name }}</small>
                            </td>
                            <td class="small font-monospace">
                                @if($doc->amount_lrd)<div class="fw-bold text-success">LRD {{ number_format((float)$doc->amount_lrd, 2) }}</div>@endif
                                @if($doc->amount_usd)<div class="fw-bold text-primary">USD {{ number_format((float)$doc->amount_usd, 2) }}</div>@endif
                                @if(!$doc->amount_lrd && !$doc->amount_usd)<span class="text-muted">—</span>@endif
                            </td>
                            <td class="small">
                                <span class="badge {{ $doc->priority_badge_class }}">{{ $doc->priority }}</span>
                                <small class="d-block text-muted mt-1">{{ ($doc->received_at_current_office_at ?? $doc->updated_at)?->diffForHumans() }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                    <form method="POST" action="{{ route('documents.action', $doc) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <input type="hidden" name="minutes_note" value="Approved by Deputy Minister for Administration (DMA).">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve {{ $doc->tracking_number }}?')">
                                            <i class="bi bi-check-circle me-1"></i>Approve
                                        </button>
                                    </form>
                                    <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-navy">Minute / Forward</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-patch-check-fill text-success fs-2 d-block mb-2"></i>
                                All DMA incoming requests have been processed!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Departmental Asset Requisitions -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-box-seam text-warning me-2"></i>Departmental Asset Requisitions Pending DMA</h5>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-navy">All Assets</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">Item &amp; Dept</th><th>Qty / Cost</th><th class="pe-3 text-end">Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($pendingAssetRequests as $req)
                                <tr>
                                    <td class="ps-3 py-2.5">
                                        <span class="badge bg-navy font-monospace" style="font-size:10px;">{{ $req->request_no }}</span>
                                        <div class="fw-bold text-navy small">{{ $req->item_name }}</div>
                                        <small class="text-muted">{{ $req->department?->name }} &bull; {{ \Illuminate\Support\Str::limit($req->justification, 50) }}</small>
                                    </td>
                                    <td class="small">
                                        <strong>{{ $req->quantity }}x</strong>
                                        @if($req->estimated_cost_usd)<div class="text-success font-monospace">${{ number_format((float)$req->estimated_cost_usd, 2) }}</div>@endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="Approved">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('assets.requests.review', $req) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="Rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No pending asset requisitions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Key Administrative Divisions Snapshot -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-building text-primary me-2"></i>Departments Under Oversight</h5>
                </div>
                <div class="list-group list-group-flush small">
                    @foreach($divisions as $div)
                        <div class="list-group-item px-3 py-2.5 d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-navy">{{ $div->name }}</strong>
                                <div class="text-muted">{{ $div->active_staff_count }} staff &bull; {{ $div->assets_count }} assets</div>
                            </div>
                            <span class="badge {{ $div->holding_docs > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">
                                {{ $div->holding_docs }} active {{ \Illuminate\Support\Str::plural('file', $div->holding_docs) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
