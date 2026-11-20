@props([
    'items' => [],
    'max' => 4,
    'size' => 32,
])

@php
    $itemList = is_array($items) 
        ? $items 
        : (is_object($items) && method_exists($items, 'all') ? $items->all() : (is_iterable($items) ? iterator_to_array($items) : []));
    $displayItems = array_slice($itemList, 0, $max);
    $remaining = count($itemList) - $max;
@endphp

<div class="d-flex align-items-center">
    <div class="d-flex align-items-center" style="margin-left: {{ $size * 0.25 }}px;">
        @foreach($displayItems as $item)
            @php
                $name = is_object($item) ? ($item->full_name ?? $item->name ?? 'User') : (is_array($item) ? ($item['name'] ?? 'User') : 'User');
                $photo = is_object($item) ? ($item->photo_url ?? null) : (is_array($item) ? ($item['photo_url'] ?? null) : null);
                $initials = strtoupper(substr($name, 0, 1));
            @endphp
            @if($photo)
                <img src="{{ $photo }}" alt="{{ $name }}" title="{{ $name }}" class="rounded-circle border border-2 border-white shadow-xs" style="width: {{ $size }}px; height: {{ $size }}px; object-fit: cover; margin-left: -{{ $size * 0.3 }}px;">
            @else
                <div class="rounded-circle border border-2 border-white bg-navy text-white d-flex align-items-center justify-content-center fw-bold shadow-xs" title="{{ $name }}" style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ $size * 0.4 }}px; margin-left: -{{ $size * 0.3 }}px;">
                    {{ $initials }}
                </div>
            @endif
        @endforeach

        @if($remaining > 0)
            <div class="rounded-circle border border-2 border-white bg-light text-muted d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ $size * 0.35 }}px; margin-left: -{{ $size * 0.3 }}px;">
                +{{ $remaining }}
            </div>
        @endif
    </div>
</div>
