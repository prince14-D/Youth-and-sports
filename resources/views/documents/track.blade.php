@extends('layouts.app')
@section('title', 'Track Document Location')
@section('page-title', 'Document Location Query')
@section('breadcrumb', 'HRMS › Document Workflow › Track')

@section('content')
<div class="animate-in" style="max-width:920px; margin:0 auto;">
    <div class="page-header">
        <div>
            <h1>Query Document Office, Decisions &amp; Feedback</h1>
            <p>Search by Ministry tracking number, subject, or destined officer to see which office holds the file, uploaded documents, approvals, reversions &amp; feedback.</p>
        </div>
        <a href="{{ route('documents.index') }}" class="btn btn-outline-navy"><i class="bi bi-arrow-left me-1"></i>Back to Registry</a>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('documents.track') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="q" value="{{ $query }}" class="form-control form-control-lg" placeholder="Tracking # (MYS-DOC-2026-0001), subject, or officer name..." required autofocus>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-navy btn-lg w-100"><i class="bi bi-search me-1"></i>Track File</button>
                </div>
            </form>
        </div>
    </div>

    @if($notFound)
        <div class="card border-0 shadow-sm text-center p-5" style="border-radius:14px;">
            <i class="bi bi-file-earmark-x text-muted" style="font-size:48px;"></i>
            <h5 class="fw-bold text-navy mt-3">No Document Found</h5>
            <p class="text-muted mb-0">No authorized file matched <code>{{ $query }}</code>. Please check the tracking code or keyword and try again.</p>
        </div>
    @elseif($accessDenied)
        <div class="card border-0 shadow-sm border-start border-4 border-danger p-4" style="border-radius:14px;">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-danger mb-1">Access Restricted — Office Not Marked</h5>
                    <p class="text-muted mb-0">
                        Document <code>{{ $query }}</code> exists in the Ministry registry, but <strong>your office was not marked by the sender</strong> as an authorized recipient or destined officer. For confidentiality, the sender, destination, and contents are hidden.
                    </p>
                </div>
            </div>
        </div>
    @elseif(isset($matches) && $matches->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-navy mb-0">Matching Authorized Documents ({{ $matches->count() }})</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($matches as $m)
                <a href="{{ route('documents.track', ['q' => $m->tracking_number]) }}" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="badge bg-navy font-monospace me-1">{{ $m->tracking_number }}</span>
                            <strong class="text-navy">{{ $m->subject }}</strong>
                        </div>
                        <span class="badge {{ $m->status_badge_class }}">{{ $m->display_status }}</span>
                    </div>
                    <div class="small text-muted mt-1">
                        Currently in: <strong>{{ $m->currentDepartment?->name }}</strong>
                        @if($m->destinationUser) &bull; Destined: <strong>{{ $m->destinationUser->name }}</strong>@endif
                        @if($m->latest_feedback) &bull; Latest feedback: "{{ \Illuminate\Support\Str::limit($m->latest_feedback, 70) }}"@endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    @elseif($document)
        <!-- Authorized Live Location Card -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius:14px;">
            <div class="p-4 text-white" style="background: linear-gradient(135deg, #0b1f4b, #123f7a);">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark font-monospace mb-2">{{ $document->tracking_number }}</span>
                        @if($document->revision_count > 0)
                            <span class="badge bg-info text-dark mb-2 ms-1">Revision #{{ $document->revision_count }}</span>
                        @endif
                        <h4 class="fw-bold mb-1 text-white">{{ $document->subject }}</h4>
                        <div class="small opacity-85">{{ $document->document_type }} &bull; {{ $document->confidentiality }}</div>
                    </div>
                    <span class="badge {{ $document->status_badge_class }} fs-6 px-3 py-2">{{ $document->display_status }}</span>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Big Current Office Highlight -->
                <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <div class="small text-uppercase fw-bold text-primary"><i class="bi bi-geo-alt-fill me-1"></i>Currently Located In Office:</div>
                        <div class="fs-4 fw-bold text-navy">{{ $document->currentDepartment?->name ?? '—' }}</div>
                        <div class="small text-muted">
                            In this office since {{ ($document->received_at_current_office_at ?? $document->updated_at)?->format('d M Y, h:i A') }}
                            ({{ ($document->received_at_current_office_at ?? $document->updated_at)?->diffForHumans() }})
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($document->attachment_path)
                            <a href="{{ route('documents.attachment', [$document, 'preview' => 1]) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-paperclip me-1"></i>View File</a>
                        @endif
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-navy"><i class="bi bi-folder2-open me-1"></i>Open / Respond</a>
                        <a href="{{ route('documents.slip-pdf', $document) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>Docket PDF</a>
                    </div>
                </div>

                <div class="row g-3 small mb-4">
                    <div class="col-md-3"><div class="text-muted">Originating Office</div><div class="fw-bold text-navy">{{ $document->originDepartment?->name }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Destination Office</div><div class="fw-bold text-primary">{{ $document->destinationDepartment?->name }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Destined Person</div><div class="fw-bold text-navy">{{ $document->destinationUser?->name ?? 'Office Queue' }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Dispatched By</div><div class="fw-bold text-navy">{{ $document->sender?->name }}</div></div>
                </div>

                @if($document->latest_feedback)
                <div class="p-3 rounded-3 border-start border-4 {{ $document->status === 'Returned' ? 'border-warning bg-warning-subtle' : 'border-info bg-light' }} mb-4 small">
                    <div class="fw-bold text-navy mb-1">
                        <i class="bi bi-chat-left-quote-fill me-1"></i>Latest Recorded Feedback
                        @if($document->latestFeedbackUser) — {{ $document->latestFeedbackUser->name }}@endif
                    </div>
                    <div>{{ $document->latest_feedback }}</div>
                </div>
                @endif

                <h6 class="fw-bold text-navy border-bottom pb-2 mb-3"><i class="bi bi-clock-history me-1 text-primary"></i>Office-to-Office Approval, Revert &amp; Feedback Trail</h6>
                <div class="list-group list-group-flush">
                    @foreach($document->routingLogs as $log)
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                <div>
                                    <span class="badge {{ $log->action_badge_class }} text-uppercase me-1" style="font-size:10px;">{{ $log->action_label }}</span>
                                    <strong class="small text-navy">{{ $log->fromDepartment?->name ?? 'Start' }} &rarr; {{ $log->toDepartment?->name ?? '—' }}</strong>
                                    @if($log->targetUser)
                                        <span class="badge bg-light text-primary border ms-1" style="font-size:10px;">Attn: {{ $log->targetUser->name }}</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $log->acted_at?->format('d M Y, h:i A') }}</small>
                            </div>
                            <div class="small mt-1">By <strong>{{ $log->actor_name }}</strong> ({{ $log->actor_role }})</div>
                            @if($log->minutes_note)
                                <div class="small text-dark mt-1 p-2 rounded bg-light"><i class="bi bi-chat-left-quote me-1 text-primary"></i>{{ $log->minutes_note }}</div>
                            @endif
                            @if($log->attachment_path)
                                <div class="mt-1">
                                    <a href="{{ route('documents.logs.attachment', [$document, $log, 'preview' => 1]) }}" target="_blank" class="small text-primary text-decoration-none">
                                        <i class="bi bi-paperclip me-1"></i>{{ $log->attachment_original_name ?: basename($log->attachment_path) }}
                                    </a>
                                </div>
                            @endif
                            <div class="font-monospace text-primary mt-1" style="font-size:11px;">{{ $log->digital_signature }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
