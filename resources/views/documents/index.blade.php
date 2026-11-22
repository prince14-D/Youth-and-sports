@extends('layouts.app')
@section('title', 'Document & Request Workflow')
@section('page-title', 'Paperless Document & Request Workflow')
@section('breadcrumb', 'HRMS › Document Workflow')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap gap-3">
        <div>
            <h1>Paperless Document &amp; Request Registry</h1>
            <p>Upload &amp; dispatch requests to specific Ministry offices or destined officers with live tracking, approvals, reversions, feedback &amp; executive oversight.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('documents.track') }}" class="btn btn-outline-navy fw-semibold">
                <i class="bi bi-search me-1"></i>Track Document Location
            </a>
            @can('create document workflow')
            <button type="button" class="btn btn-navy fw-semibold" data-bs-toggle="modal" data-bs-target="#dispatchDocModal">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i>Upload &amp; Dispatch Request
            </button>
            @endcan
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ $errors->first() }}</div>
    @endif

    <!-- Quick Tracking Query Strip -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 100%);">
        <div class="card-body p-3 px-4 text-white">
            <form method="GET" action="{{ route('documents.track') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="fw-bold small"><i class="bi bi-geo-alt-fill text-warning me-1"></i>Instant Document Location &amp; Status Query</div>
                    <div style="font-size:11.5px; opacity:0.85;">Search by tracking #, subject, or destined officer (marked offices only).</div>
                </div>
                <div class="col-8 col-md-6">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Enter Tracking # (MYS-DOC-2026-0001) or subject keyword..." required>
                </div>
                <div class="col-4 col-md-2">
                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold w-100">
                        <i class="bi bi-search me-1"></i>Track File
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ $tab === 'inbox' ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">In My Office / Assigned to Me</div>
                        <div class="fs-2 fw-bold text-primary mt-1">{{ $counts['inbox'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('documents.index', ['tab' => 'reverted']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ $tab === 'reverted' ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Reverted (Needs Revision)</div>
                        <div class="fs-2 fw-bold text-warning mt-1">{{ $counts['reverted'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('documents.index', ['tab' => 'outbox']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ $tab === 'outbox' ? 'border-start border-4 border-success' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">Dispatched by My Office</div>
                        <div class="fs-2 fw-bold text-success mt-1">{{ $counts['outbox'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ $tab === 'all' ? 'border-start border-4 border-navy' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small">{{ $isExecutive ? 'All Ministry Files' : 'Visible to My Office' }}</div>
                        <div class="fs-2 fw-bold text-navy mt-1">{{ $counts['all'] }}</div>
                        <div class="small text-danger fw-semibold">{{ $counts['urgent'] }} Urgent Active</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Bar & Documents Table -->
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="btn {{ $tab === 'inbox' ? 'btn-navy' : 'btn-outline-navy' }}">Inbox / Assigned ({{ $counts['inbox'] }})</a>
                <a href="{{ route('documents.index', ['tab' => 'reverted']) }}" class="btn {{ $tab === 'reverted' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-warning text-dark' }}">Reverted ({{ $counts['reverted'] }})</a>
                <a href="{{ route('documents.index', ['tab' => 'outbox']) }}" class="btn {{ $tab === 'outbox' ? 'btn-navy' : 'btn-outline-navy' }}">Dispatched ({{ $counts['outbox'] }})</a>
                <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="btn {{ $tab === 'all' ? 'btn-navy' : 'btn-outline-navy' }}">{{ $isExecutive ? 'All Ministry' : 'All Marked' }} ({{ $counts['all'] }})</a>
            </div>
            <form method="GET" action="{{ route('documents.index') }}" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search tracking # or subject..." style="min-width:210px;">
                <select name="status" class="form-select form-select-sm" style="width:150px;">
                    <option value="">All Status</option>
                    @foreach(['In Transit' => 'In Transit', 'Received' => 'Received', 'Under Review' => 'Under Review', 'Approved' => 'Approved', 'Returned' => 'Reverted', 'Rejected' => 'Rejected', 'Completed' => 'Completed'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $statusFilter === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-navy">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tracking # &amp; Uploaded Document</th>
                        <th>Destination &amp; Destined Person</th>
                        <th>Current Office &amp; Latest Feedback</th>
                        <th>Priority / Privacy</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    <span class="badge bg-navy font-monospace">{{ $doc->tracking_number }}</span>
                                    <span class="badge bg-light text-dark border" style="font-size:10.5px;">{{ $doc->document_type }}</span>
                                    @if($doc->revision_count > 0)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:10px;">Rev #{{ $doc->revision_count }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('documents.show', $doc) }}" class="fw-bold text-navy text-decoration-none d-block mt-1">{{ $doc->subject }}</a>
                                @if($doc->attachment_path)
                                    <a href="{{ route('documents.attachment', [$doc, 'preview' => 1]) }}" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-1">
                                        <i class="bi bi-paperclip me-1"></i>{{ \Illuminate\Support\Str::limit($doc->attachment_filename, 34) }}
                                    </a>
                                @endif
                                @if($doc->amount_lrd || $doc->amount_usd)
                                    <div class="small text-success fw-semibold font-monospace">
                                        {{ $doc->amount_lrd ? 'LRD ' . number_format((float)$doc->amount_lrd, 2) : '' }}
                                        {{ ($doc->amount_lrd && $doc->amount_usd) ? ' | ' : '' }}
                                        {{ $doc->amount_usd ? 'USD ' . number_format((float)$doc->amount_usd, 2) : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="small">
                                <div><span class="text-muted">From:</span> <strong>{{ $doc->originDepartment?->name ?? '—' }}</strong></div>
                                <div><span class="text-muted">To:</span> <strong class="text-primary">{{ $doc->destinationDepartment?->name ?? '—' }}</strong></div>
                                @if($doc->destinationUser)
                                    <div class="mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-person-check-fill me-1"></i>Attn: {{ $doc->destinationUser->name }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="small">
                                <div class="fw-bold text-navy"><i class="bi bi-building-fill text-primary me-1"></i>{{ $doc->currentDepartment?->name ?? '—' }}</div>
                                <small class="{{ $doc->hours_in_current_office >= 48 && !in_array($doc->status, ['Completed', 'Rejected']) ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    <i class="bi bi-clock me-1"></i>{{ ($doc->received_at_current_office_at ?? $doc->updated_at)?->diffForHumans() }}
                                </small>
                                @if($doc->latest_feedback)
                                    <div class="text-muted fst-italic mt-1" style="font-size:11.5px; max-width:260px;" title="{{ $doc->latest_feedback }}">
                                        <i class="bi bi-chat-left-quote text-primary me-1"></i>"{{ \Illuminate\Support\Str::limit($doc->latest_feedback, 65) }}"
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $doc->priority_badge_class }}">{{ $doc->priority }}</span>
                                <div class="small text-muted mt-1" style="font-size:11px;">
                                    <i class="bi bi-shield-lock-fill text-navy me-1"></i>{{ $doc->confidentiality }}
                                </div>
                            </td>
                            <td><span class="badge {{ $doc->status_badge_class }}">{{ $doc->display_status }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-navy">
                                    <i class="bi bi-folder2-open me-1"></i>Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-check fs-2 d-block mb-2 opacity-50"></i>
                                No documents in this view.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="card-footer bg-white border-top">{{ $documents->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>

<!-- Upload & Dispatch New Document Modal -->
<div class="modal fade" id="dispatchDocModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload &amp; Dispatch Paperless Request / Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 small py-2 mb-3">
                        <i class="bi bi-shield-lock-fill me-1"></i><strong>Privacy &amp; Executive Tracking:</strong> Upload your official file and assign the Destination Office and/or Destined Person. They can <strong>Approve</strong>, <strong>Revert</strong>, or <strong>Give Feedback</strong> — and all decisions &amp; feedback are recorded live on the <strong>Minister's Dashboard</strong>.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Document / Request Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Request for November Salary Payroll Approval" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Document / Request Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                @foreach($documentTypes as $dt)
                                    <option value="{{ $dt }}">{{ $dt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Confidentiality Level <span class="text-danger">*</span></label>
                            <select name="confidentiality" class="form-select" required>
                                <option value="Restricted">Restricted (Marked Offices &amp; Destined Person Only)</option>
                                <option value="Confidential">Confidential (Executive &amp; Marked Only)</option>
                                <option value="Internal">Internal Ministry Official</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Originating Department</label>
                            <select name="origin_department_id" class="form-select">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $userDeptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Destination Office <span class="text-danger">*</span></label>
                            <select name="destination_department_id" class="form-select" required>
                                <option value="">Select Destination Office</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ ($dmaDept && $dmaDept->id === $dept->id) ? 'selected' : '' }}>{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Destined Person <span class="text-primary">(Approver / Officer)</span></label>
                            <select name="destination_user_id" class="form-select">
                                <option value="">Any Authorized Officer in Office</option>
                                @foreach($recipientUsers as $ru)
                                    <option value="{{ $ru->id }}">{{ $ru->name }} — {{ $ru->primary_role }}{{ $ru->employee?->department ? ' (' . $ru->employee->department->code . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 border border-2 border-primary-subtle bg-light">
                                <label class="form-label fw-bold text-navy small mb-1">
                                    <i class="bi bi-file-earmark-arrow-up-fill text-primary me-1"></i>Upload Official Document / Memo / Supporting File
                                </label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                                <div class="form-text">Supported: PDF, Word, Excel, PowerPoint, JPG/PNG (max 15 MB). Stored securely — accessible only to authorized offices, destined person &amp; executive leadership.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Additional Marked / Authorized Offices <span class="text-muted">(Hold Ctrl/Cmd to mark multiple — unmarked offices are blocked!)</span></label>
                            <select name="marked_department_ids[]" class="form-select" multiple size="3">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Request Value (LRD) <span class="text-muted">(optional)</span></label>
                            <input type="number" step="0.01" min="0" name="amount_lrd" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Request Value (USD) <span class="text-muted">(optional)</span></label>
                            <input type="number" step="0.01" min="0" name="amount_usd" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Request Summary / Cover Minute <span class="text-danger">*</span></label>
                            <textarea name="summary" class="form-control" rows="3" required placeholder="State clear details of the request, justification, or instructions for the destined officer/office..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy"><i class="bi bi-send-fill me-1"></i>Upload, Dispatch &amp; Generate Tracking #</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
