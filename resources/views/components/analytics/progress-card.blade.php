@props([
    'title' => '',
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'label' => '',
    'icon' => 'bi-graph-up',
    'suffix' => '%',
])

@php
    $percentage = $max > 0 ? min(round(($value / $max) * 100, 1), 100) : 0;
@endphp

<div class="card shadow-sm h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="m-0 fw-bold text-{{ $color }}">
                <i class="bi {{ $icon }} me-2"></i>{{ $title }}
            </h6>
            <span class="badge bg-{{ $color }}">{{ $value }}{{ $suffix }}</span>
        </div>
        <div class="progress" style="height: 12px;">
            <div class="progress-bar bg-{{ $color }}" role="progressbar"
                 style="width: {{ $percentage }}%;"
                 aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="{{ $max }}">
            </div>
        </div>
        @if($label)
            <small class="text-muted mt-2 d-block">{{ $label }}</small>
        @endif
    </div>
</div>
