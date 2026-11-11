@extends('layouts.app')

@section('title', $village->village_name . ' - Planning Analytics')

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('analytics.planning.index') }}">Planning Analytics</a></li>
            <li class="breadcrumb-item active">{{ $village->village_name }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>{{ $village->village_name }} — Planning Indicators</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Dependency Ratio" :value="number_format($indicators['dependency']['total_dependency_ratio'], 1) . '%'" icon="bi-people-fill" color="warning" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Pop. Density" :value="number_format($indicators['density']['population_density'], 1) . '/km²'" icon="bi-geo-alt-fill" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Annual Growth" :value="number_format($indicators['growth']['annual_growth'], 1) . '%'" icon="bi-graph-up-arrow" color="success" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Stability Index" :value="number_format($indicators['household_stability']['stability_index'], 1)" icon="bi-house-door-fill" color="primary" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Community Structure Radar" icon="bi-diagram-3" color="primary" canvasId="radarChart" height="350px" />
        </div>
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0"><h6 class="card-title mb-0"><i class="bi bi-clipboard-data me-2 text-info"></i>Community Structure Breakdown</h6></div>
                <div class="card-body">
                    @foreach($communityStructure as $category => $groups)
                    <h6 class="small fw-bold text-primary mt-2">{{ $category }}</h6>
                    @foreach($groups as $label => $data)
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold">{{ number_format($data['count']) }} ({{ number_format($data['pct'], 1) }}%)</span>
                    </div>
                    <div class="progress mb-1" style="height: 4px;"><div class="progress-bar" style="width: {{ $data['pct'] }}%"></div></div>
                    @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($execInsights as $insight)
        <div class="col-md-6">
            <div class="d-flex align-items-start">
                <div class="badge bg-{{ $insight['priority'] ?? 'secondary' }} me-3 mt-1"><i class="bi bi-{{ $insight['icon'] ?? 'info-circle' }}"></i></div>
                <div>
                    <div class="fw-semibold small">{{ $insight['title'] }}</div>
                    <div class="text-muted small">{{ $insight['description'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd' };
    const radarCtx = document.getElementById('radarChart');
    if (radarCtx) {
        new Chart(radarCtx, {
            type: 'radar',
            data: { labels: {!! json_encode($radarData['labels']) !!}, datasets: [{ label: 'Population %', data: {!! json_encode($radarData['values']) !!}, backgroundColor: colors.primary + '30', borderColor: colors.primary, pointBackgroundColor: colors.primary }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, max: 100 } } }
        });
    }
});
</script>
@endpush
