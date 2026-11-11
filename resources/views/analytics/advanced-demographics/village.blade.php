@extends('layouts.app')

@section('title', $village->village_name . ' - Advanced Demographics')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('analytics.advanced-demographics.index') }}">Advanced Demographics</a></li>
                    <li class="breadcrumb-item active">{{ $village->village_name }}</li>
                </ol>
            </nav>
            <h4 class="mb-0"><i class="bi bi-geo-alt me-2"></i>{{ $village->village_name }} — Planning Indicators</h4>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('analytics.advanced-demographics.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.advanced-demographics.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>PDF</a></li>
            </ul>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Dependency Ratio" :value="number_format($dependencySummary['total_dependency_ratio'], 1) . '%'" icon="bi-people-fill" color="warning" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Population Density" :value="number_format($densitySummary['population_density'], 1) . '/km²'" icon="bi-geo-alt-fill" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Annual Growth" :value="number_format($growthSummary['annual_growth'], 1) . '%'" icon="bi-graph-up-arrow" color="success" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Avg Residency" :value="number_format($residencySummary['average_years'], 1) . ' yrs'" icon="bi-house-heart-fill" color="primary" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Population" :value="number_format($densitySummary['population'])" icon="bi-people" color="primary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Households" :value="number_format($densitySummary['households'])" icon="bi-house-door-fill" color="secondary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="HH Stability Index" :value="number_format($householdSummary['stability_index'], 1)" icon="bi-speedometer2" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Migration Rate" :value="number_format($migrationSummary['migration_rate'], 1) . '%'" icon="bi-arrow-left-right" color="warning" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Dependency Trend (12 months)" icon="bi-graph-up" color="warning" canvasId="depTrendChart" height="300px" />
        </div>
        <div class="col-lg-6">
            <x-analytics.chart-card title="Age Distribution" icon="bi-people" color="info" canvasId="ageDistChart" height="300px" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Population Growth" icon="bi-graph-up-arrow" color="success" canvasId="growthTrendChart" height="300px" />
        </div>
        <div class="col-lg-6">
            <x-analytics.chart-card title="Migration Trend" icon="bi-people" color="secondary" canvasId="migrationTrendChart" height="300px" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <x-analytics.chart-card title="Community Structure Radar" icon="bi-diagram-3" color="primary" canvasId="radarChart" height="350px" />
        </div>
        <div class="col-lg-5">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="card-title mb-0"><i class="bi bi-clipboard-data me-2 text-primary"></i>Dependency Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1"><span class="small">Child Dependency</span><span class="fw-semibold">{{ number_format($dependencySummary['child_dependency_ratio'], 1) }}%</span></div>
                        <div class="progress" style="height: 8px;"><div class="progress-bar bg-info" style="width: {{ $dependencySummary['child_dependency_ratio'] }}%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1"><span class="small">Senior Dependency</span><span class="fw-semibold">{{ number_format($dependencySummary['senior_dependency_ratio'], 1) }}%</span></div>
                        <div class="progress" style="height: 8px;"><div class="progress-bar bg-warning" style="width: {{ $dependencySummary['senior_dependency_ratio'] }}%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1"><span class="small">Working Age %</span><span class="fw-semibold">{{ number_format($dependencySummary['working_pct'], 1) }}%</span></div>
                        <div class="progress" style="height: 8px;"><div class="progress-bar bg-success" style="width: {{ $dependencySummary['working_pct'] }}%"></div></div>
                    </div>
                    <hr>
                    <div class="row g-2 text-center">
                        <div class="col-4"><div class="fw-bold text-primary">{{ number_format($communitySummary['working_population']) }}</div><small class="text-muted">Working</small></div>
                        <div class="col-4"><div class="fw-bold text-warning">{{ number_format($communitySummary['dependent_population']) }}</div><small class="text-muted">Dependent</small></div>
                        <div class="col-4"><div class="fw-bold text-info">{{ number_format($communitySummary['youth_population']) }}</div><small class="text-muted">Youth</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="card-title mb-0"><i class="bi bi-house-door me-2"></i>Household Stability</h6></div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-4"><div class="fw-bold text-primary fs-5">{{ number_format($householdSummary['avg_members'], 1) }}</div><small class="text-muted">Avg Size</small></div>
                        <div class="col-4"><div class="fw-bold text-success fs-5">{{ $householdSummary['max_members'] }}</div><small class="text-muted">Largest</small></div>
                        <div class="col-4"><div class="fw-bold text-info fs-5">{{ $householdSummary['min_members'] }}</div><small class="text-muted">Smallest</small></div>
                    </div>
                    <hr>
                    @foreach($householdSummary['size_distribution'] as $label => $data)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold">{{ $data['count'] }} ({{ number_format($data['percentage'], 1) }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0"><h6 class="card-title mb-0"><i class="bi bi-people me-2"></i>Residency Duration</h6></div>
                <div class="card-body">
                    @foreach($residencyBrackets as $label => $data)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold">{{ $data['count'] }} ({{ number_format($data['percentage'], 1) }}%)</span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $data['percentage'] }}%"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach(array_merge($dependencyInsights, $densityInsights, $growthInsights, $residencyInsights, $migrationInsights, $householdInsights, $communityInsights) as $insight)
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
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, '#6f42c1', '#d63384', '#20c997', '#fd7e14'];

    const depCtx = document.getElementById('depTrendChart');
    if (depCtx) {
        new Chart(depCtx, {
            type: 'line',
            data: { labels: {!! json_encode(array_column($dependencyTrend, 'month')) !!}, datasets: [{ label: 'Dependency Ratio', data: {!! json_encode(array_column($dependencyTrend, 'ratio')) !!}, borderColor: colors.warning, backgroundColor: colors.warning + '20', fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    }

    const ageCtx = document.getElementById('ageDistChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: { labels: {!! json_encode(array_keys($ageDistribution)) !!}, datasets: [{ label: 'Residents', data: {!! json_encode(array_column($ageDistribution, 'count')) !!}, backgroundColor: palette.slice(0, {!! count($ageDistribution) !!}), borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const groCtx = document.getElementById('growthTrendChart');
    if (groCtx) {
        new Chart(groCtx, {
            type: 'line',
            data: { labels: {!! json_encode(array_column($growthTrend, 'month')) !!}, datasets: [{ label: 'Population', data: {!! json_encode(array_column($growthTrend, 'population')) !!}, borderColor: colors.success, fill: true, backgroundColor: colors.success + '20', tension: 0.4, yAxisID: 'y' }, { label: 'Growth %', data: {!! json_encode(array_column($growthTrend, 'growth_rate')) !!}, borderColor: colors.primary, borderDash: [5,5], tension: 0.4, yAxisID: 'y1' }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, position: 'left' }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } }
        });
    }

    const migCtx = document.getElementById('migrationTrendChart');
    if (migCtx) {
        new Chart(migCtx, {
            type: 'bar',
            data: { labels: {!! json_encode(array_column($migrationTrend, 'month')) !!}, datasets: [{ label: 'New Residents', data: {!! json_encode(array_column($migrationTrend, 'new_residents')) !!}, backgroundColor: colors.secondary, borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

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
