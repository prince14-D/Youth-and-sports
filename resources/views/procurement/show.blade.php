@extends('layouts.app')
@section('title', $tender->tender_no)
@section('page-title', 'Tender ' . $tender->tender_no)
@section('breadcrumb', 'HRMS › Procurement › ' . $tender->tender_no)

@section('content')
<div class="animate-in">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('procurement.dashboard') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Procurement Unit</a>
            <h4 class="fw-bold text-navy mb-1 mt-1">{{ $tender->title }}</h4>
            <div class="text-muted small">
                <span class="font-monospace fw-semibold">{{ $tender->tender_no }}</span> &bull; {{ $tender->method }} &bull; {{ $tender->category }}
                &bull; {{ $tender->requestingDepartment?->name ?? 'Ministry-Wide' }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge {{ $tender->status_badge_class }} fs-6">{{ $tender->status }}</span>
            <a href="{{ route('procurement.tenders.pdf', $tender) }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Evaluation Report PDF</a>
        </div>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold text-navy">Tender Details</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Estimated cost</span><strong>${{ number_format((float)$tender->estimated_cost_usd, 2) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Budget line</span><span>{{ $tender->budgetAllocation?->budget_code ?? 'Not linked' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Advertised</span><span>{{ $tender->advertised_date?->format('d M Y') ?? '—' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Bid deadline</span><span class="{{ $tender->status === 'Open for Bids' && $tender->bid_deadline?->lt(today()) ? 'text-danger fw-semibold' : '' }}">{{ $tender->bid_deadline?->format('d M Y') ?? '—' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Created by</span><span>{{ $tender->creator?->name }}</span></li>
                    @if($tender->awardedSupplier)
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Awarded to</span><strong class="text-success">{{ $tender->awardedSupplier->name }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Contract value</span><strong>${{ number_format((float)$tender->awarded_amount_usd, 2) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Awarded on</span><span>{{ $tender->awarded_at?->format('d M Y') }} by {{ $tender->awarder?->name }}</span></li>
                    @endif
                    @if($tender->document)
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">DMA dossier</span>
                        <a href="{{ route('documents.show', $tender->document) }}" class="font-monospace">{{ $tender->document->tracking_number }}</a></li>
                    @endif
                    @if($tender->contract_signed_date)
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Contract signed</span><span>{{ $tender->contract_signed_date->format('d M Y') }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Delivery due</span><span class="{{ $tender->status === 'Contract Signed' && $tender->delivery_due_date?->lt(today()) ? 'text-danger fw-semibold' : '' }}">{{ $tender->delivery_due_date?->format('d M Y') }}</span></li>
                    @endif
                </ul>
                @if($tender->description)<div class="card-body border-top small"><strong>Specification:</strong><br>{{ $tender->description }}</div>@endif
                @if($tender->justification)<div class="card-body border-top small"><strong>Justification:</strong><br>{{ $tender->justification }}</div>@endif
                @if($tender->award_note)<div class="card-body border-top small"><strong>Award notes:</strong><br>{!! nl2br(e($tender->award_note)) !!}</div>@endif
                @if($tender->cancellation_reason)<div class="card-body border-top small text-danger"><strong>Cancelled:</strong> {{ $tender->cancellation_reason }}</div>@endif
            </div>

            @can('manage procurement')
            @if(!in_array($tender->status, ['Completed', 'Cancelled']))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold text-navy">Workflow Actions</div>
                <div class="card-body d-grid gap-2">
                    @if($tender->status === 'Draft' && !$tender->is_sole_source)
                        <form method="POST" action="{{ route('procurement.tenders.status', $tender) }}" class="d-grid gap-2">@csrf
                            <input type="hidden" name="status" value="Open for Bids">
                            <label class="small fw-semibold">Bid submission deadline</label>
                            <input type="date" name="bid_deadline" class="form-control form-control-sm" value="{{ $tender->bid_deadline?->toDateString() }}" min="{{ today()->toDateString() }}" required>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-megaphone me-1"></i>Advertise / Open for Bids</button>
                        </form>
                    @endif
                    @if(($tender->status === 'Open for Bids') || ($tender->status === 'Draft' && $tender->is_sole_source))
                        <form method="POST" action="{{ route('procurement.tenders.status', $tender) }}">@csrf
                            <input type="hidden" name="status" value="Bid Evaluation">
                            <button class="btn btn-info btn-sm w-100"><i class="bi bi-clipboard-data me-1"></i>Close Bidding &amp; Start Evaluation</button>
                        </form>
                    @endif
                    @if($tender->status === 'Awarded')
                        <form method="POST" action="{{ route('procurement.tenders.status', $tender) }}" class="d-grid gap-2">@csrf
                            <input type="hidden" name="status" value="Contract Signed">
                            <label class="small fw-semibold">Contract signing date</label>
                            <input type="date" name="contract_signed_date" class="form-control form-control-sm" value="{{ today()->toDateString() }}" required>
                            <label class="small fw-semibold">Delivery due date</label>
                            <input type="date" name="delivery_due_date" class="form-control form-control-sm" required>
                            <button class="btn btn-success btn-sm"><i class="bi bi-pen me-1"></i>Record Contract Signed</button>
                        </form>
                    @endif
                    @if($tender->status === 'Contract Signed')
                        <form method="POST" action="{{ route('procurement.tenders.status', $tender) }}">@csrf
                            <input type="hidden" name="status" value="Completed">
                            <button class="btn btn-dark btn-sm w-100"><i class="bi bi-check2-circle me-1"></i>Mark Delivered / Completed</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('procurement.tenders.status', $tender) }}" class="d-grid gap-2 border-top pt-2" onsubmit="return confirm('Cancel this tender?');">@csrf
                        <input type="hidden" name="status" value="Cancelled">
                        <input type="text" name="cancellation_reason" class="form-control form-control-sm" placeholder="Reason for cancellation" required>
                        <button class="btn btn-outline-danger btn-sm">Cancel Tender</button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-navy">Bids &amp; Evaluation</span>
                        <div class="small text-muted">Score = {{ (int)(\App\Models\ProcurementRequest::TECH_WEIGHT*100) }}% technical + {{ (int)(\App\Models\ProcurementRequest::FIN_WEIGHT*100) }}% financial (lowest responsive bid = 100). Min. {{ \App\Models\ProcurementRequest::MIN_COMPETITIVE_BIDS }} bids for competitive methods.</div>
                    </div>
                    @can('manage procurement')
                    @if(in_array($tender->status, ['Open for Bids']) || ($tender->status === 'Draft' && $tender->is_sole_source))
                    <button class="btn btn-sm btn-warning fw-semibold" data-bs-toggle="modal" data-bs-target="#addBidModal"><i class="bi bi-plus-circle me-1"></i>Record Bid</button>
                    @endif
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light text-uppercase"><tr><th>Rank</th><th>Supplier</th><th class="text-end">Bid (USD)</th><th>Tech</th><th>Fin.</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse($bids as $i => $bid)
                            <tr class="{{ $bid->status === 'Awarded' ? 'table-success' : '' }}">
                                <td class="fw-bold">{{ $bid->total_score !== null ? '#' . ($i + 1) : '—' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $bid->supplier->name }}</div>
                                    <div class="text-muted">{{ $bid->supplier->supplier_no }}@if($bid->delivery_days) &bull; {{ $bid->delivery_days }} days @endif</div>
                                    @if($bid->supplier->ineligibilityReasons())<div class="text-danger" style="font-size:11px;"><i class="bi bi-exclamation-triangle"></i> {{ implode('; ', $bid->supplier->ineligibilityReasons()) }}</div>@endif
                                </td>
                                <td class="text-end fw-bold">${{ number_format((float)$bid->bid_amount_usd, 2) }}</td>
                                <td>{{ $bid->technical_score ?? '—' }}</td>
                                <td>{{ $bid->financial_score !== null ? number_format((float)$bid->financial_score, 1) : '—' }}</td>
                                <td class="fw-bold">{{ $bid->total_score !== null ? number_format((float)$bid->total_score, 1) : '—' }}</td>
                                <td>
                                    <span class="badge {{ $bid->status === 'Awarded' ? 'bg-success' : ($bid->status === 'Rejected' ? 'bg-secondary' : 'bg-info text-dark') }}">{{ $bid->status }}</span>
                                    @if(!$bid->is_responsive)<div class="text-danger" style="font-size:11px;">Non-responsive</div>@endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('manage procurement')
                                    @if(in_array($tender->status, ['Draft', 'Open for Bids', 'Bid Evaluation']))
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scoreBidModal"
                                        onclick="openScore({{ $bid->id }}, @js($bid->supplier->name), {{ $bid->technical_score ?? 'null' }}, {{ $bid->is_responsive ? 'true' : 'false' }})">Evaluate</button>
                                    @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No bids recorded yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @can('manage procurement')
                @if($tender->status === 'Bid Evaluation' && $bids->count())
                <div class="card-body border-top bg-light">
                    <form method="POST" action="{{ route('procurement.tenders.award', $tender) }}" class="row g-3">@csrf
                        <div class="col-12"><h6 class="fw-bold text-navy mb-0"><i class="bi bi-trophy me-1"></i>Award Contract</h6></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Winning bid *</label>
                            <select name="bid_id" class="form-select form-select-sm" required>
                                @foreach($bids->where('is_responsive', true) as $bid)
                                <option value="{{ $bid->id }}">{{ $bid->supplier->name }} — ${{ number_format((float)$bid->bid_amount_usd, 2) }} @if($bid->total_score !== null)(score {{ number_format((float)$bid->total_score, 1) }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Deviation reason <span class="text-muted">(if not top-ranked)</span></label>
                            <input name="award_note" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Waiver justification <span class="text-muted">(required if fewer than {{ \App\Models\ProcurementRequest::MIN_COMPETITIVE_BIDS }} responsive bids)</span></label>
                            <input name="waiver_note" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <small class="text-muted">Awards ≥ ${{ number_format(\App\Models\ProcurementRequest::DMA_APPROVAL_THRESHOLD_USD) }} are automatically routed to the DMA for approval via the paperless workflow.</small>
                            <button class="btn btn-success btn-sm fw-semibold" onclick="return confirm('Award this contract?');">Award Contract</button>
                        </div>
                    </form>
                </div>
                @endif
                @endcan
            </div>

            @if($evaluations->count())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold text-navy">Supplier Performance on this Tender</div>
                <ul class="list-group list-group-flush small">
                    @foreach($evaluations as $e)
                    <li class="list-group-item">{{ $e->supplier->name }} — <strong>{{ number_format((float)$e->overall_score, 1) }}/5</strong> by {{ $e->evaluator?->name }} <span class="text-muted">{{ $e->comments }}</span></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>

@can('manage procurement')
<div class="modal fade" id="addBidModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" action="{{ route('procurement.bids.store', $tender) }}" class="modal-content">@csrf
    <div class="modal-header bg-warning"><h6 class="modal-title fw-bold">Record Bid / Quotation</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-12"><label class="form-label small fw-semibold">Supplier *</label>
            <select name="supplier_id" class="form-select" required>@forelse($availableSuppliers as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->supplier_no }})</option>@empty<option value="">No eligible suppliers — register one first</option>@endforelse</select></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Bid amount (USD) *</label><input type="number" step="0.01" min="0.01" name="bid_amount_usd" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Submitted on *</label><input type="date" name="submitted_date" class="form-control" value="{{ today()->toDateString() }}" required></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Delivery (days)</label><input type="number" min="1" name="delivery_days" class="form-control"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning fw-semibold">Save Bid</button></div>
</form></div></div>

<div class="modal fade" id="scoreBidModal" tabindex="-1"><div class="modal-dialog">
<form method="POST" id="scoreBidForm" class="modal-content">@csrf
    <div class="modal-header bg-navy text-white"><h6 class="modal-title fw-bold">Evaluate Bid — <span id="sbName"></span></h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <div class="col-12"><label class="form-label small fw-semibold">Responsive to specifications?</label>
            <select name="is_responsive" id="sbResp" class="form-select"><option value="1">Yes — substantially responsive</option><option value="0">No — non-responsive (reject)</option></select></div>
        <div class="col-12"><label class="form-label small fw-semibold">Technical score (0–100)</label><input type="number" min="0" max="100" name="technical_score" id="sbTech" class="form-control"></div>
        <div class="col-12"><label class="form-label small fw-semibold">Evaluation notes</label><textarea name="notes" rows="2" class="form-control"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-navy">Save Evaluation</button></div>
</form></div></div>
@endcan
@endsection

@push('scripts')
<script>
function openScore(id, name, tech, resp) {
    document.getElementById('scoreBidForm').action = "{{ url('/procurement/bids') }}/" + id + "/score";
    document.getElementById('sbName').textContent = name;
    document.getElementById('sbTech').value = tech === null ? '' : tech;
    document.getElementById('sbResp').value = resp ? '1' : '0';
}
</script>
@endpush
