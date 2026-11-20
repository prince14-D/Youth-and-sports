@props([
    'title',
    'value',
    'subtitle' => null,
    'badgeText' => null,
    'badgeColor' => '#2563EB',
    'badgeBg' => '#EFF6FF',
    'icon' => null,
    'trend' => null,
    'trendType' => 'up',
    'avatars' => [],
    'actionLabel' => null,
    'actionUrl' => '#',
    'class' => '',
])

@php
    $displayBadge = $badgeText;
    if (!$displayBadge && !$icon) {
        $strValue = (string) $value;
        if (str_contains($strValue, '%')) {
            $displayBadge = '%';
        } elseif (str_contains($strValue, 'LRD')) {
            $displayBadge = 'L$';
        } elseif (str_contains($strValue, 'USD') || str_contains($strValue, '$')) {
            $displayBadge = '$';
        } elseif (strlen($strValue) <= 4) {
            $displayBadge = $strValue;
        } else {
            $displayBadge = substr(trim($strValue), 0, 2);
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100 p-4 transition-all position-relative ' . $class]) }} style="border-radius: 16px; background: #ffffff; border: 1px solid #E5E7EB !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" style="width: 46px; height: 46px; background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; font-size: 1.15rem;">
                @if($icon)
                    <i class="{{ $icon }}"></i>
                @else
                    {{ $displayBadge }}
                @endif
            </div>
            <div>
                <h6 class="fw-bold text-navy mb-0" style="font-size: 14.5px;">{{ $title }}</h6>
                @if($subtitle)
                <small class="text-muted d-block" style="font-size: 12px;">{{ $subtitle }}</small>
                @endif
            </div>
        </div>

        @if($trend)
        <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 11px; {{ $trendType == 'up' ? 'background-color: #DCFCE7; color: #166534;' : ($trendType == 'down' ? 'background-color: #FEE2E2; color: #DC2626;' : 'background-color: #F1F5F9; color: #475569;') }}">
            <i class="bi bi-arrow-{{ $trendType == 'up' ? 'up-right' : ($trendType == 'down' ? 'down-right' : 'right') }} me-1"></i>{{ $trend }}
        </span>
        @else
        <button type="button" class="btn btn-link text-muted p-0 border-0" style="font-size: 14px;">
            <i class="bi bi-three-dots"></i>
        </button>
        @endif
    </div>

    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-light">
        @if(!empty($avatars))
            <x-ui.avatar-group :items="$avatars" :max="4" :size="30" />
        @else
            <div class="fs-4 fw-bold text-navy">{{ $value }}</div>
        @endif

        @if($actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted fw-medium" style="font-size: 11.5px;">
            {{ $actionLabel }}
        </a>
        @endif
    </div>
</div>
