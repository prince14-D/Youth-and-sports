@extends('layouts.app')
@section('title', $document->tracking_number . ' — Document File')
@section('page-title', 'Document File: ' . $document->tracking_number)
@section('breadcrumb', 'HRMS › Document Workflow › ' . $document->tracking_number)

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <span class="badge bg-navy font-monospace fs-6">{{ $document->tracking_number }}</span>
                <span class="badge {{ $document->status_badge_class }} fs-6">{{ $document->display_status }}</span>
                <span class="badge {{ $document->priority_badge_class }}">{{ $document->priority }}</span>
                @if($document->revision_count > 0)
                    <span class="badge bg-info text-dark">Revision #{{ $document->revision_count }}</span>
                @endif
            </div>
            <h1 class="mb-0">{{ $document->subject }}</h1>
            <p class="mb-0 small text-muted">{{ $document->document_type }} &bull; Dispatched {{ $document->created_at?->format('d M Y, h:i A') }} by {{ $document->sender?->name }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('documents.slip-pdf', $document) }}" class="btn btn-outline-danger fw-semibold">
                <i class="bi bi-file-earmark-pdf me-1"></i>Movement Docket PDF
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn-outline-navy fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Back to Registry
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ $errors->first() }}</div>
    @endif

    <!-- Live Current Location & Destined Person Banner -->
    <div class="alert {{ $document->status === 'Returned' ? 'alert-warning' : ($document->status === 'Approved' ? 'alert-success' : 'alert-primary') }} border-0 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4" style="border-radius:12px;">
        <div>
            <div class="small text-uppercase fw-bold"><i class="bi bi-geo-alt-fill me-1"></i>Current Office Holding File:</div>
            <div class="fs-5 fw-bold text-navy">{{ $document->currentDepartment?->name ?? '—' }}</div>
            <div class="small">
                Arrived {{ ($document->received_at_current_office_at ?? $document->updated_at)?->diffForHumans() }}
                @if($document->currentHandler) &bull; Last acted by <strong>{{ $document->currentHandler->name }}</strong>@endif
            </div>
        </div>
        <div class="small text-md-end">
            <div><strong>Origin:</strong> {{ $document->originDepartment?->name }}</div>
            <div><strong>Destination Office:</strong> {{ $document->destinationDepartment?->name }}</div>
            @if($document->destinationUser)
                <div class="mt-1">
                    <span class="badge bg-navy text-white">
                        <i class="bi bi-person-check-fill me-1"></i>Destined Person: {{ $document->destinationUser->name }} ({{ $document->destinationUser->primary_role }})
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Visual Lifecycle Stepper -->
    @php
        $step1 = true;
        $step2 = in_array($document->status, ['Received', 'Under Review', 'Returned', 'Approved', 'Completed', 'Rejected'], true);
        $step3 = $document->status === 'Returned' || $document->routingLogs->whereIn('action', ['feedback', 'returned', 'resubmitted'])->isNotEmpty();
        $step4 = in_array($document->status, ['Approved', 'Completed'], true);
    @endphp
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body py-3 px-4">
            <div class="row text-center g-2 small">
                <div class="col-3">
                    <span class="badge rounded-pill {{ $step1 ? 'bg-success' : 'bg-secondary' }} mb-1">1</span>
                    <div class="fw-semibold text-navy">Uploaded &amp; Dispatched</div>
                </div>
                <div class="col-3">
                    <span class="badge rounded-pill {{ $step2 ? 'bg-success' : 'bg-secondary' }} mb-1">2</span>
                    <div class="fw-semibold {{ $step2 ? 'text-navy' : 'text-muted' }}">Received / Reviewing</div>
                </div>
                <div class="col-3">
                    <span class="badge rounded-pill {{ $document->status === 'Returned' ? 'bg-warning text-dark' : ($step3 ? 'bg-info text-dark' : 'bg-light text-muted border') }} mb-1">3</span>
                    <div class="fw-semibold {{ $step3 ? 'text-navy' : 'text-muted' }}">{{ $document->status === 'Returned' ? 'Reverted for Revision' : 'Feedback / Review' }}</div>
                </div>
                <div class="col-3">
                    <span class="badge rounded-pill {{ $document->status === 'Rejected' ? 'bg-danger' : ($step4 ? 'bg-success' : 'bg-secondary') }} mb-1">4</span>
                    <div class="fw-semibold {{ $step4 ? 'text-success' : ($document->status === 'Rejected' ? 'text-danger' : 'text-muted') }}">{{ $document->status === 'Rejected' ? 'Rejected' : ($document->status === 'Completed' ? 'Completed' : 'Approved') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Document Body, Uploaded File + Routing & Feedback Trail -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request Details &amp; Uploaded Document</h5>
                    @if($document->latest_feedback_at)
                        <small class="text-muted">Latest feedback {{ $document->latest_feedback_at->diffForHumans() }}</small>
                    @endif
                </div>
                <div class="card-body">
                    @if($document->amount_lrd || $document->amount_usd)
                        <div class="row g-3 mb-3">
                            @if($document->amount_lrd)
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="small text-muted">Requested Amount (LRD)</div>
                                    <div class="fs-4 fw-bold text-success font-monospace">LRD {{ number_format((float)$document->amount_lrd, 2) }}</div>
                                </div>
                            </div>
                            @endif
                            @if($document->amount_usd)
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="small text-muted">Requested Amount (USD)</div>
                                    <div class="fs-4 fw-bold text-primary font-monospace">USD {{ number_format((float)$document->amount_usd, 2) }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endif

                    <div class="p-3 rounded bg-light border mb-3" style="white-space: pre-line;">{{ $document->summary }}</div>

                    @if($document->latest_feedback)
                        <div class="p-3 rounded border-start border-4 {{ $document->status === 'Returned' ? 'border-warning bg-warning-subtle' : ($document->status === 'Approved' ? 'border-success bg-success-subtle' : 'border-info bg-info-subtle') }} mb-3">
                            <div class="small fw-bold text-navy mb-1">
                                <i class="bi bi-chat-left-quote-fill me-1"></i>Latest Recorded Decision / Feedback
                                @if($document->latestFeedbackUser)
                                    — {{ $document->latestFeedbackUser->name }} ({{ $document->latestFeedbackUser->primary_role }})
                                @endif
                            </div>
                            <div class="small text-dark">{{ $document->latest_feedback }}</div>
                        </div>
                    @endif

                    @if($document->attachment_path)
                        <div class="p-3 rounded-3 border bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small text-navy">{{ $document->attachment_filename }}</div>
                                    <div class="text-muted" style="font-size:11.5px;">Current official uploaded file {{ $document->revision_count > 0 ? '(Revision #' . $document->revision_count . ')' : '' }}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('documents.attachment', [$document, 'preview' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Preview
                                </a>
                                <a href="{{ route('documents.attachment', $document) }}" class="btn btn-sm btn-navy">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 pt-3 border-top">
                        <div class="small fw-bold text-muted mb-2"><i class="bi bi-shield-lock-fill text-navy me-1"></i>Offices &amp; Officers Authorized to View This File ({{ $document->confidentiality }}):</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($markedDepartments as $md)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $md->name }}</span>
                            @endforeach
                            @if($document->destinationUser)
                                <span class="badge bg-warning text-dark">Destined: {{ $document->destinationUser->name }}</span>
                            @endif
                            <span class="badge bg-dark-subtle text-dark">Executive Oversight (Minister / DMA)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office-to-Office Minute, Feedback, Revert & Custody Trail -->
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-navy"><i class="bi bi-clock-history me-2 text-primary"></i>Official Approval, Revert, Feedback &amp; Routing Trail</h5>
                    <span class="badge bg-light text-dark border">{{ $document->routingLogs->count() }} Event(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($document->routingLogs as $log)
                            <div class="list-group-item px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="badge text-uppercase me-1 {{ $log->action_badge_class }}">
                                            {{ $log->action_label }}
                                        </span>
                                        <strong class="text-navy small">{{ $log->fromDepartment?->name ?? 'Origin' }} &rarr; {{ $log->toDepartment?->name ?? '—' }}</strong>
                                        @if($log->targetUser)
                                            <span class="badge bg-light text-primary border ms-1" style="font-size:10.5px;">Attn: {{ $log->targetUser->name }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $log->acted_at?->format('d M Y, h:i A') }}</small>
                                </div>
                                <div class="small mt-1"><strong>{{ $log->actor_name }}</strong> <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $log->actor_role }}</span></div>
                                @if($log->minutes_note)
                                    <div class="mt-2 p-2 px-3 rounded bg-light small text-dark border-start border-3 border-primary">{{ $log->minutes_note }}</div>
                                @endif
                                @if($log->attachment_path)
                                    <div class="mt-2">
                                        <a href="{{ route('documents.logs.attachment', [$document, $log, 'preview' => 1]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 small">
                                            <i class="bi bi-paperclip me-1"></i>{{ $log->attachment_original_name ?: basename($log->attachment_path) }}
                                        </a>
                                    </div>
                                @endif
                                <div class="mt-1 font-monospace text-primary" style="font-size:11px;">
                                    <i class="bi bi-fingerprint me-1"></i>{{ $log->digital_signature }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Destined Person / Destination Office Action Panel -->
        <div class="col-12 col-xl-4">
            {{-- Reverted Document Resubmission Card (for Sender / Originating Office) --}}
            @if($canResubmit)
            <div class="card border-0 shadow-sm border-top border-4 border-warning mb-4" style="border-radius:14px;">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Document Reverted — Revise &amp; Resubmit</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        This file was reverted by the destination with feedback. Upload your updated document and explain the revisions to resubmit it back to <strong>{{ $document->destinationDepartment?->name }}</strong>{{ $document->destinationUser ? ' (Attn: ' . $document->destinationUser->name . ')' : '' }}.
                    </p>
                    <form method="POST" action="{{ route('documents.resubmit', $document) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Upload Revised Document <span class="text-muted">(optional)</span></label>
                            <input type="file" name="revised_attachment" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Response to Feedback / Changes Made <span class="text-danger">*</span></label>
                            <textarea name="minutes_note" class="form-control form-control-sm" rows="3" required placeholder="Describe corrections made in response to the reviewer's feedback..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning text-dark fw-bold w-100">
                            <i class="bi bi-send-check-fill me-1"></i>Resubmit Revised File
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Destination Office / Destined Person Decision Panel --}}
            @if($canAct && $document->status !== 'Returned')
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-navy text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pen-fill me-2"></i>Destination Actions — Approve, Revert &amp; Feedback</h6>
                </div>
                <div class="card-body">
                    @if($document->status === 'In Transit')
                        <form method="POST" action="{{ route('documents.receive', $document) }}" class="mb-3 pb-3 border-bottom">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                <i class="bi bi-inbox-fill me-1"></i>Acknowledge Receipt at {{ $document->currentDepartment?->code }}
                            </button>
                        </form>
                    @endif

                    <!-- Approve / Revert / Give Feedback / Complete / Reject -->
                    <form method="POST" action="{{ route('documents.action', $document) }}" enctype="multipart/form-data" class="mb-4 pb-3 border-bottom">
                        @csrf
                        <label class="form-label small fw-bold">Official Decision / Feedback / Minute</label>
                        <textarea name="minutes_note" class="form-control form-control-sm mb-2" rows="3" placeholder="Enter approval remarks, feedback for sender, or reason for reverting..."></textarea>

                        <label class="form-label small fw-semibold text-muted">Attach Signed / Marked-Up Document <span class="text-muted">(optional)</span></label>
                        <input type="file" name="response_attachment" class="form-control form-control-sm mb-3" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">

                        <div class="d-grid gap-2">
                            <button type="submit" name="decision" value="approve" class="btn btn-success btn-sm fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i>Approve Request
                            </button>
                            <button type="submit" name="decision" value="feedback" class="btn btn-info text-dark btn-sm fw-semibold">
                                <i class="bi bi-chat-left-text-fill me-1"></i>Record Feedback (Keep Open)
                            </button>
                            <div class="d-flex gap-2">
                                <button type="submit" name="decision" value="revert" class="btn btn-warning text-dark btn-sm flex-fill fw-semibold">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Revert to Sender
                                </button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline-danger btn-sm flex-fill fw-semibold">
                                    <i class="bi bi-x-circle me-1"></i>Reject
                                </button>
                            </div>
                            <button type="submit" name="decision" value="complete" class="btn btn-outline-success btn-sm fw-semibold">
                                <i class="bi bi-archive-fill me-1"></i>Mark Completed / Archive
                            </button>
                        </div>
                    </form>

                    <!-- Minute & Forward to Another Office / Officer -->
                    <form method="POST" action="{{ route('documents.forward', $document) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label small fw-bold">Minute &amp; Forward to Another Office / Person</label>
                        <select name="to_department_id" class="form-select form-select-sm mb-2" required>
                            <option value="">Select Next Office</option>
                            @foreach($departments as $dept)
                                @if($dept->id !== $document->current_department_id)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endif
                            @endforeach
                        </select>
                        <select name="to_user_id" class="form-select form-select-sm mb-2">
                            <option value="">Destined Person (Optional)</option>
                            @foreach($recipientUsers as $ru)
                                <option value="{{ $ru->id }}">{{ $ru->name }} — {{ $ru->primary_role }}</option>
                            @endforeach
                        </select>
                        <textarea name="minutes_note" class="form-control form-control-sm mb-2" rows="2" required placeholder="e.g. Approved by DMA — forwarded to Finance for disbursement..."></textarea>
                        <input type="file" name="response_attachment" class="form-control form-control-sm mb-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn btn-navy btn-sm w-100 fw-semibold">
                            <i class="bi bi-forward-fill me-1"></i>Minute &amp; Forward File
                        </button>
                    </form>
                </div>
            </div>
            @elseif(!$canResubmit)
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-body text-center py-4 text-muted small">
                    <i class="bi bi-lock-fill fs-2 d-block mb-2 opacity-50"></i>
                    @if(in_array($document->status, ['Completed', 'Rejected']))
                        This file is closed (<strong>{{ $document->display_status }}</strong>).
                    @else
                        Primary approval/revert actions are assigned to <strong>{{ $document->currentDepartment?->name }}</strong>{{ $document->destinationUser ? ' (' . $document->destinationUser->name . ')' : '' }}.
                    @endif
                </div>
            </div>
            @endif

            {{-- Executive / Sender Quick Feedback Panel --}}
            @if($canFeedback)
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-light border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-navy"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Add Official Feedback / Directive</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.feedback', $document) }}" enctype="multipart/form-data">
                        @csrf
                        <textarea name="feedback_note" class="form-control form-control-sm mb-2" rows="3" required placeholder="Post official feedback, clarification, or executive directive (displayed on Minister's Dashboard)..."></textarea>
                        <input type="file" name="feedback_attachment" class="form-control form-control-sm mb-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                            <i class="bi bi-send me-1"></i>Post Recorded Feedback
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
