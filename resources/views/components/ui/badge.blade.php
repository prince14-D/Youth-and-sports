@props([
    'variant' => 'navy',
    'class' => '',
])

@php
$badgeStyle = match($variant) {
    'success', 'active' => 'background-color: #dcfce7 !important; color: #166534 !important; border: 1px solid #86efac !important;',
    'warning', 'leave' => 'background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a !important;',
    'danger', 'red', 'role' => 'background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fca5a5 !important;',
    'info' => 'background-color: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #7dd3fc !important;',
    'navy' => 'background-color: #e0e7ff !important; color: #000a60 !important; border: 1px solid #c7d2fe !important;',
    default => 'background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1 !important;',
};
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill px-3 py-2 fw-bold ' . $class]) }} style="font-size: 11.5px; {{ $badgeStyle }}">
    {{ $slot }}
</span>
