@props([
    'title',
    'subtitle' => null,
    'breadcrumb' => null,
    'actions' => null,
])

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        @if($breadcrumb)
        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.5px;">{{ $breadcrumb }}</small>
        @endif
        <h1 class="h3 fw-bold text-navy mb-1 mt-0.5">{{ $title }}</h1>
        @if($subtitle)
        <p class="text-muted mb-0 small">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
    <div class="d-flex gap-2 align-items-center">
        {{ $actions }}
    </div>
    @endif
</div>
