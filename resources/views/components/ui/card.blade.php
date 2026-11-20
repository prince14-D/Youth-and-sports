@props([
    'title' => null,
    'icon' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'class' => '',
    'bodyClass' => 'p-4',
])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm ' . $class]) }}>
    @if($title || $icon || $actions)
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            @if($title)
            <h5 class="card-title mb-0 text-navy fw-bold">
                @if($icon) <i class="{{ $icon }} me-2 text-navy"></i> @endif
                {{ $title }}
            </h5>
            @endif
            @if($subtitle)
            <small class="text-muted" style="font-size: 11.5px;">{{ $subtitle }}</small>
            @endif
        </div>
        @if(isset($actions))
        <div>
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="card-footer bg-transparent border-top p-3">
        {{ $footer }}
    </div>
    @endif
</div>
