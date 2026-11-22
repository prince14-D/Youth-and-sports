@extends('layouts.app')
@section('title', 'Office of the Minister — Executive Dashboard')
@section('page-title', 'Office of the Minister — Ministry Oversight')
@section('breadcrumb', 'Ministry of Youth & Sports › Office of the Minister')

@section('content')
<div class="animate-in">
    <!-- Minister's Office Hero Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #000a3d 0%, #0b1f4b 50%, #123f7a 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(255,255,255,0.16); font-size:11px; letter-spacing:0.8px;">
                        <i class="bi bi-building-fill-check text-warning me-1"></i>OFFICE OF THE MINISTER &bull; EXECUTIVE COMMAND
                    </span>
                    <h3 class="fw-bold mb-1">Ministry-Wide Oversight &amp; Daily Activity</h3>
                    <p class="mb-0 small opacity-85">Live visibility across all {{ $kpis['departments_count'] }} departments, uploaded requests, approvals, reversions, officer feedback, assets, and daily operations.</p>
                </div>
                <div class="col-lg-5">
                    <form method="GET" action="{{ route('documents.track') }}" class="bg-white bg-opacity-10 rounded-3 p-3">
                        <div class="small fw-semibold mb-1.5"><i class="bi bi-geo-alt-fill text-warning me-1"></i>Track Any Ministry File by Number, Subject or Officer</div>
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control" placeholder="MYS-DOC-2026-0001 or keyword..." required>
                            <button type="submit" class="btn btn-warning text-dark fw-bold">Locate File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive KPI Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Active Ministry Workforce</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ number_format($kpis['total_workforce']) }}</div>
                    <div class="small text-muted">{{ $kpis['permanent_staff'] }} Permanent &bull; {{ $kpis['contractors'] }} Contractors</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Documents in Flow</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ $kpis['active_documents'] }}</div>
                    <div class="small text-danger fw-semibold">{{ $kpis['minister_inbox'] }} at Minister &bull; {{ $kpis['dma_pending'] }} at DMA</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Approvals, Reversions &amp; Feedback</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $kpis['approved_docs'] }} <span class="fs-6 text-muted fw-normal">approved</span></div>
                    <div class="small">
                        <span class="text-warning fw-semibold">{{ $kpis['reverted_docs'] }} Reverted</span> &bull;
                        <span class="text-primary fw-semibold">{{ $kpis['feedback_today'] }} actions today</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body">
                    <div class="text-muted small">Ministry Assets &amp; Daily Logs</div>
                    <div class="fs-2 fw-bold text-navy mt-1">{{ $kpis['total_assets'] }}</div>
                    <div class="small text-success fw-semibold">${{ number_format($kpis['asset_value_usd'], 0) }} &bull; {{ $kpis['today_activities'] }} logs today</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minister's Direct Inbox (files routed to Office of the Minister or directly addressed to Minister) -->
    @if($ministerDocuments->isNotEmpty())
    <div class="card border-0 shadow-sm border-start border-4 border-warning mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-inbox-fill text-warning me-2"></i>Files Currently in the Office of the Minister ({{ $ministerDocuments->count() }})</h5>
            <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="btn btn-sm btn-navy">Open Ministerial Inbox</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tracking # &amp; Uploaded File</th>
                        <th>Originating Dept</th>
                        <th>Latest Feedback</th>
                        <th>Priority / Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ministerDocuments as $md)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-navy font-monospace">{{ $md->tracking_number }}</span>
                                <div class="fw-bold text-navy mt-1">{{ $md->subject }}</div>
                                @if($md->attachment_path)
                                    <a href="{{ route('documents.attachment', [$md, 'preview' => 1]) }}" target="_blank" class="small text-primary text-decoration-none">
                                        <i class="bi bi-paperclip me-1"></i>{{ \Illuminate\Support\Str::limit($md->attachment_filename, 36) }}
                                    </a>
                                @endif
                            </td>
                            <td class="small">
                                <div>{{ $md->originDepartment?->name }}</div>
                                <small class="text-muted">By {{ $md->sender?->name }}</small>
                            </td>
                            <td class="small text-muted" style="max-width:240px;">
                                {{ $md->latest_feedback ? \Illuminate\Support\Str::limit($md->latest_feedback, 70) : 'Awaiting Ministerial review' }}
                            </td>
                            <td>
                                <span class="badge {{ $md->priority_badge_class }}">{{ $md->priority }}</span>
                                <span class="badge {{ $md->status_badge_class }} ms-1">{{ $md->display_status }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('documents.show', $md) }}" class="btn btn-sm btn-navy">Approve / Revert / Feedback</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- NEW: Live Document Approvals, Reversions & Officer Feedback Feed -->
    <div class="card border-0 shadow-sm border-top border-4 border-primary mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-chat-square-quote-fill text-primary me-2"></i>Live Document Approvals, Reversions &amp; Feedback Log</h5>
                <small class="text-muted">Real-time executive record of every uploaded request, approval, reversion, resubmission, and feedback note across all divisions.</small>
            </div>
            <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="btn btn-sm btn-outline-navy">Open Full Document Registry</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th class="ps-4">Decision / Time</th>
                        <th>Document &amp; Attachment</th>
                        <th>Officer &amp; Route</th>
                        <th>Recorded Feedback / Minute</th>
                        <th class="text-end pe-4">File</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDocumentFeedback as $log)
                        @php $d = $log->document; @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <span class="badge text-uppercase {{ $log->action_badge_class }}">{{ $log->action_label }}</span>
                                <div class="text-muted mt-1" style="font-size:11px;">{{ $log->acted_at?->format('d M Y, H:i') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-navy font-monospace" style="font-size:10px;">{{ $d->tracking_number }}</span>
                                <span class="badge {{ $d->status_badge_class }} ms-1" style="font-size:10px;">{{ $d->display_status }}</span>
                                <div class="fw-bold text-navy mt-1">{{ \Illuminate\Support\Str::limit($d->subject, 52) }}</div>
                                @if($log->attachment_path)
                                    <a href="{{ route('documents.logs.attachment', [$d, $log, 'preview' => 1]) }}" target="_blank" class="text-primary text-decoration-none" style="font-size:11px;">
                                        <i class="bi bi-paperclip me-1"></i>{{ \Illuminate\Support\Str::limit($log->attachment_original_name ?: basename($log->attachment_path), 34) }}
                                    </a>
                                @elseif($d->attachment_path)
                                    <a href="{{ route('documents.attachment', [$d, 'preview' => 1]) }}" target="_blank" class="text-muted text-decoration-none" style="font-size:11px;">
                                        <i class="bi bi-file-earmark-text me-1"></i>{{ \Illuminate\Support\Str::limit($d->attachment_filename, 34) }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->actor_name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $log->actor_role }}</div>
                                <div class="text-primary" style="font-size:11px;">
                                    {{ $log->fromDepartment?->code ?? '—' }} &rarr; {{ $log->toDepartment?->code ?? '—' }}
                                    @if($log->targetUser) (Attn: {{ $log->targetUser->name }})@endif
                                </div>
                            </td>
                            <td style="max-width:300px;">
                                <div class="text-dark">{{ \Illuminate\Support\Str::limit($log->minutes_note ?: '—', 130) }}</div>
                                <div class="font-monospace text-muted" style="font-size:10px;">{{ $log->digital_signature }}</div>
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <a href="{{ route('documents.show', $d) }}" class="btn btn-sm btn-outline-navy py-0 px-2">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No document approvals, reversions, or feedback recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Department-by-Department Executive Oversight Matrix -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-diagram-3-fill text-primary me-2"></i>All Ministry Departments — Live Status &amp; Workload Matrix</h5>
                <small class="text-muted">Overview of staffing, assets, and documents currently held in each office.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="btn btn-sm btn-outline-navy">All Documents</a>
                <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-primary">Ministry Assets</a>
                <a href="{{ route('reports.workforce') }}" class="btn btn-sm btn-outline-success">Workforce Report</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Department / Office</th>
                        <th class="text-center">Staff (Contractors)</th>
                        <th class="text-center">Assets</th>
                        <th class="text-center">Files Currently Held</th>
                        <th class="text-center">Bottlenecks (&ge;48h)</th>
                        <th class="text-center">Asset Req.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                        <tr>
                            <td class="ps-4 py-2.5">
                                <div class="fw-bold text-navy">{{ $dept->name }} <span class="badge bg-light text-dark border font-monospace ms-1" style="font-size:10px;">{{ $dept->code }}</span></div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($dept->description, 65) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-navy">{{ $dept->active_staff_count }}</span>
                                @if($dept->contractor_count > 0)<small class="text-muted">({{ $dept->contractor_count }})</small>@endif
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $dept->assets_count }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $dept->holding_docs_count > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">{{ $dept->holding_docs_count }}</span>
                                @if($dept->urgent_docs_count > 0)
                                    <span class="badge bg-danger ms-1">{{ $dept->urgent_docs_count }} urgent</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($dept->delayed_docs_count > 0)
                                    <span class="badge bg-danger">{{ $dept->delayed_docs_count }} delayed</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">On Track</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($dept->pending_asset_requests > 0)
                                    <span class="badge bg-warning text-dark">{{ $dept->pending_asset_requests }} pending</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Live Ministry Document Movement Monitor -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-send-check text-primary me-2"></i>Active Document Circulation Across Offices</h5>
                    <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="small text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-3">File &amp; Destined Officer</th><th>Current Office &amp; Feedback</th><th>Status</th><th class="pe-3 text-end">Open</th></tr>
                        </thead>
                        <tbody>
                            @forelse($activeDocuments as $doc)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <span class="badge bg-navy font-monospace" style="font-size:10px;">{{ $doc->tracking_number }}</span>
                                        @if($doc->attachment_path)
                                            <a href="{{ route('documents.attachment', [$doc, 'preview' => 1]) }}" target="_blank" class="badge bg-primary-subtle text-primary text-decoration-none ms-1" title="Preview Uploaded File">
                                                <i class="bi bi-paperclip"></i> File
                                            </a>
                                        @endif
                                        <div class="fw-semibold text-navy small mt-1">{{ \Illuminate\Support\Str::limit($doc->subject, 45) }}</div>
                                        <small class="text-muted">
                                            {{ $doc->originDepartment?->code }} &rarr; {{ $doc->destinationDepartment?->code }}
                                            @if($doc->destinationUser) &bull; Attn: <strong>{{ $doc->destinationUser->name }}</strong>@endif
                                        </small>
                                    </td>
                                    <td class="small">
                                        <strong class="text-primary">{{ $doc->currentDepartment?->name }}</strong>
                                        <small class="d-block text-muted">{{ ($doc->received_at_current_office_at ?? $doc->updated_at)?->diffForHumans() }}</small>
                                        @if($doc->latest_feedback)
                                            <small class="d-block text-dark fst-italic">"{{ \Illuminate\Support\Str::limit($doc->latest_feedback, 55) }}"</small>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $doc->status_badge_class }}">{{ $doc->display_status }}</span></td>
                                    <td class="pe-3 text-end"><a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-navy py-0 px-2">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No active documents in circulation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daily Ministry Activity Stream -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-activity text-success me-2"></i>Live Daily Ministry Activity</h5>
                    <a href="{{ route('audit.index') }}" class="small text-decoration-none">Full Audit</a>
                </div>
                <div class="list-group list-group-flush" style="max-height:460px; overflow-y:auto;">
                    @forelse($dailyActivities as $act)
                        <div class="list-group-item px-3 py-2.5 small">
                            <div class="d-flex justify-content-between">
                                <strong class="text-navy">{{ $act->user_name ?? 'System' }}</strong>
                                <span class="text-muted" style="font-size:11px;">{{ $act->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="text-muted">{{ $act->description }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">No activity recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
