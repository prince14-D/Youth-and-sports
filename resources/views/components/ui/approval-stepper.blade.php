@props(['model', 'uid' => null])
@php
    $uniqueId = $uid ?? ('trail-' . class_basename($model) . '-' . $model->getKey());
    $currentStage = (int) ($model->approval_stage ?? 1);
    $status = $model->status ?? 'Pending';
    $isRejected = $status === 'Rejected';
    $isComplete = in_array($status, ['Approved', 'Paid'], true) || $currentStage >= 4;
    $isDraft = $status === 'Draft' || $currentStage === 0;
    $trails = $model->relationLoaded('approvalTrails') ? $model->approvalTrails : $model->approvalTrails()->get();

    $stages = [
        1 => ['short' => 'HR Officer', 'title' => 'Stage 1: HR Officer Review'],
        2 => ['short' => 'Asst. Director', 'title' => 'Stage 2: Assistant HR Director'],
        3 => ['short' => 'HR Director', 'title' => 'Stage 3: HR Director Final'],
    ];
@endphp

<div class="d-flex flex-column gap-1">
    <div class="d-flex align-items-center flex-wrap gap-1" style="font-size: 11px;">
        @foreach($stages as $num => $meta)
            @php
                $stageTrail = $trails->where('stage', $num)->whereIn('action', ['approved', 'rejected'])->last();
                if ($stageTrail && $stageTrail->action === 'rejected') {
                    $pillClass = 'bg-danger text-white';
                    $icon = 'bi-x-circle-fill';
                } elseif ($isComplete || ($stageTrail && $stageTrail->action === 'approved') || (!$isDraft && !$isRejected && $currentStage > $num)) {
                    $pillClass = 'bg-success-subtle text-success border border-success-subtle';
                    $icon = 'bi-check-circle-fill';
                } elseif (!$isDraft && !$isRejected && $currentStage === $num) {
                    $pillClass = 'bg-warning-subtle text-dark border border-warning fw-bold';
                    $icon = 'bi-hourglass-split';
                } else {
                    $pillClass = 'bg-light text-muted border';
                    $icon = 'bi-circle';
                }
            @endphp
            <span class="badge rounded-pill px-2 py-1 {{ $pillClass }}" title="{{ $meta['title'] }}{{ $stageTrail ? ' — ' . $stageTrail->actor_name . ' (' . $stageTrail->acted_at?->format('d M Y H:i') . ')' : '' }}">
                <i class="bi {{ $icon }} me-1"></i>{{ $num }}. {{ $meta['short'] }}
            </span>
            @if($num < 3)
                <i class="bi bi-chevron-right text-muted" style="font-size: 9px;"></i>
            @endif
        @endforeach
    </div>

    @if($trails->isNotEmpty())
        <div>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#{{ $uniqueId }}">
                <i class="bi bi-shield-check me-1"></i>Digital Trail ({{ $trails->count() }})
            </button>
        </div>

        <div class="modal fade" id="{{ $uniqueId }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i>Digital Approval Trail &amp; Signatures</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="small text-muted">Workflow Status:</span>
                            <span class="badge {{ $isComplete ? 'bg-success' : ($isRejected ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $model->current_stage_label }}</span>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($trails as $t)
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <span class="badge {{ $t->action === 'approved' ? 'bg-success' : ($t->action === 'rejected' ? 'bg-danger' : 'bg-secondary') }} me-1 text-uppercase" style="font-size:10px;">
                                                {{ $t->action }}
                                            </span>
                                            <strong class="text-navy small">{{ $t->stage_label }}</strong>
                                        </div>
                                        <span class="text-muted small">{{ $t->acted_at?->format('d M Y, h:i A') }}</span>
                                    </div>
                                    <div class="small mt-1">
                                        <strong>Actor:</strong> {{ $t->actor_name }}
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:10px;">{{ $t->actor_role }}</span>
                                    </div>
                                    @if($t->remarks)
                                        <div class="small text-muted mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $t->remarks }}</div>
                                    @endif
                                    <div class="mt-1 d-flex flex-wrap gap-3 text-muted" style="font-size:11px;">
                                        <span><i class="bi bi-fingerprint text-primary me-1"></i>Signature: <code class="text-navy fw-bold">{{ $t->digital_signature }}</code></span>
                                        @if($t->ip_address)
                                            <span><i class="bi bi-globe me-1"></i>IP: {{ $t->ip_address }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
