@props([
    'title',
    'value' => '—',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => null,
])

<div class="col-sm-6 col-xl-4 col-xxl-3">
    <div class="card stat-card shadow-sm h-100 border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label text-muted mb-1">{{ $title }}</p>
                    <h3 class="stat-value mb-0">{{ $value }}</h3>
                    @if($subtitle)
                        <small class="text-muted">{{ $subtitle }}</small>
                    @endif
                </div>
                <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}">
                    <i class="bi {{ $icon }}"></i>
                </div>
            </div>
        </div>
    </div>
</div>
