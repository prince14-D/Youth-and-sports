@props([
    'title' => 'No records found',
    'message' => 'There are currently no items matching your request.',
    'icon' => 'bi bi-folder-x',
    'actionLabel' => null,
    'actionRoute' => null,
    'actionEvent' => null,
])

<div class="text-center py-5 text-muted my-3">
    <div class="mb-3">
        <i class="{{ $icon }} fs-1 text-muted opacity-50 d-inline-block p-3 rounded-circle bg-light"></i>
    </div>
    <h6 class="fw-bold text-navy mb-1">{{ $title }}</h6>
    <p class="small text-muted mb-3" style="max-width: 400px; margin: 0 auto;">{{ $message }}</p>

    @if($actionLabel && $actionRoute)
    <a href="{{ $actionRoute }}" class="btn btn-navy btn-sm rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i>{{ $actionLabel }}
    </a>
    @elseif($actionLabel && $actionEvent)
    <button type="button" wire:click="{{ $actionEvent }}" class="btn btn-navy btn-sm rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i>{{ $actionLabel }}
    </button>
    @endif
</div>
