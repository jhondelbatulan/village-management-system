@props([
    'title' => '',
    'value' => '',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => '',
    'previousValue' => null,
    'change' => null,
    'route' => null,
    'large' => false,
])

@php
    $cardClass = $large ? 'border-left-3 h-100 py-3' : 'border-left h-100 py-2';
    $colorClass = "border-{$color}";
    $iconBg = "bg-{$color}-soft";
@endphp

<div class="card shadow-sm {{ $cardClass }} {{ $colorClass }}" @if($route) style="cursor:pointer;" @endif>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="text-xs fw-bold text-{{ $color }} text-uppercase mb-1">{{ $title }}</div>
                <div class="{{ $large ? 'h3' : 'h5' }} mb-0 fw-bold text-gray-800">{{ $value }}</div>
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
                @if($change !== null)
                    <div class="mt-1">
                        @if($change > 0)
                            <span class="text-success small fw-semibold"><i class="bi bi-arrow-up me-1"></i>+{{ $change }}%</span>
                            <span class="text-muted small">vs last month</span>
                        @elseif($change < 0)
                            <span class="text-danger small fw-semibold"><i class="bi bi-arrow-down me-1"></i>{{ $change }}%</span>
                            <span class="text-muted small">vs last month</span>
                        @else
                            <span class="text-muted small"><i class="bi bi-dash me-1"></i>No change</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="{{ $iconBg }} rounded-circle p-3">
                <i class="bi {{ $icon }} fs-4 text-{{ $color }}"></i>
            </div>
        </div>
    </div>
    @if($route)
        <a href="{{ $route }}" class="card-footer bg-{{ $color }}-soft border-0 text-{{ $color }} small fw-semibold py-2">
            View Details <i class="bi bi-arrow-right ms-1"></i>
        </a>
    @endif
</div>
