@props([
    'title' => '',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'height' => '250px',
    'id' => '',
    'canvasId' => '',
    'header' => null,
    'exportable' => false,
])

@php
    $chartId = $canvasId ?: ($id ?: 'chart-' . md5($title));
@endphp

<div class="card shadow-sm h-100 chart-card" id="card-{{ $chartId }}">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-{{ $color }}">
            <i class="bi {{ $icon }} me-2"></i>{{ $title }}
        </h6>
        <div class="d-flex align-items-center gap-2">
            @if($header)
                {{ $header }}
            @endif
            @if($exportable && $canvasId)
            <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('{{ $chartId }}')" title="Export as PNG">
                <i class="bi bi-download"></i>
            </button>
            @endif
        </div>
    </div>
    <div class="card-body chart-body">
        @if($slot)
            {{ $slot }}
        @else
            <div style="position: relative; height: {{ $height }};">
                <canvas id="{{ $chartId }}"></canvas>
            </div>
        @endif
    </div>
</div>

@if($exportable && $canvasId)
@push('scripts')
<script>
function exportChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = canvasId + '-chart.png';
    link.href = canvas.toDataURL('image/png', 1.0);
    link.click();
}
</script>
@endpush
@endif
