@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
    'class' => '',
])

@php
$variantClass = match($variant) {
    'primary', 'navy' => 'btn-navy',
    'accent' => 'btn-accent',
    'secondary' => 'btn-secondary',
    'outline' => 'btn-outline-navy',
    'outline-danger' => 'btn-outline-danger',
    'danger' => 'btn-danger',
    'ghost' => 'btn-light border-0 text-muted',
    default => 'btn-navy',
};

$sizeClass = match($size) {
    'sm' => 'btn-sm',
    'lg' => 'btn-lg',
    default => '',
};
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => "btn {$variantClass} {$sizeClass} {$class}"]) }}>
    @if($icon) <i class="{{ $icon }} me-1.5"></i> @endif
    {{ $slot }}
</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => "btn {$variantClass} {$sizeClass} {$class}"]) }}>
    @if($icon) <i class="{{ $icon }} me-1.5"></i> @endif
    {{ $slot }}
</button>
@endif
