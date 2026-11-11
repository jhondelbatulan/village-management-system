@extends('layouts.app')

@section('title', 'Advanced Demographics & Planning Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-graph-up me-2"></i>Advanced Demographics & Planning Analytics</h4>
            <p class="text-muted mb-0 small">Comprehensive demographic indicators and planning intelligence</p>
        </div>
        <div class="d-flex gap-2">
            @if(!$villageId)
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('analytics.advanced-demographics.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('analytics.advanced-demographics.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>PDF</a></li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    @if(!$villageId)
    <div class="row g-3 mb-4">
        @foreach($villages as $v)
        <div class="col-md-4">
            <a href="{{ route('analytics.advanced-demographics.village', $v->id) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 {{ ($v->id ?? '') == request()->segment(3) ? 'border-primary' : '' }}">
                    <div class="card-body">
                        <h6 class="card-title mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $v->village_name }}</h6>
                        <small class="text-muted">Click to view village details</small>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Total Dependency Ratio" :value="number_format($dependencySummary['total_dependency_ratio'], 1) . '%'" icon="bi-people-fill" color="warning" />
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
            <x-analytics.summary-card title="Migration Rate" :value="number_format($migrationSummary['migration_rate'], 1) . '%'" icon="bi-people" color="secondary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="HH Stability Index" :value="number_format($householdSummary['stability_index'], 1)" icon="bi-house-door-fill" color="primary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Working Age %" :value="number_format($communitySummary['working_pct'], 1) . '%'" icon="bi-briefcase-fill" color="success" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Child Dep. Ratio" :value="number_format($dependencySummary['child_dependency_ratio'], 1) . '%'" icon="bi-mortarboard-fill" color="danger" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Dependency Ratio Trend" icon="bi-graph-up" color="warning" canvasId="depTrendChart" height="300px" />
        </div>
        <div class="col-lg-6">
            <x-analytics.chart-card title="Age Group Distribution" icon="bi-people" color="info" canvasId="ageDistChart" height="300px" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Population Growth Trend" icon="bi-graph-up-arrow" color="success" canvasId="growthTrendChart" height="300px" />
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
                    <h6 class="card-title mb-0"><i class="bi bi-building me-2 text-info"></i>Community Structure</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-primary">{{ number_format($communitySummary['working_population']) }}</div>
                                <small class="text-muted">Working Age</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-warning">{{ number_format($communitySummary['dependent_population']) }}</div>
                                <small class="text-muted">Dependent</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-info">{{ number_format($communitySummary['youth_population']) }}</div>
                                <small class="text-muted">Youth (15-30)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-secondary">{{ number_format($communitySummary['senior_population']) }}</div>
                                <small class="text-muted">Senior (60+)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-danger">{{ number_format($communitySummary['pwd_population']) }}</div>
                                <small class="text-muted">PWD</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <div class="fw-bold text-success">{{ number_format($communitySummary['volunteer_population']) }}</div>
                                <small class="text-muted">Volunteers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="card-title mb-0"><i class="bi bi-house-door me-2 text-primary"></i>Household Stability</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-4 text-center">
                            <div class="fw-bold text-primary fs-5">{{ number_format($householdSummary['avg_members'], 1) }}</div>
                            <small class="text-muted">Avg Members</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="fw-bold text-success fs-5">{{ $householdSummary['max_members'] }}</div>
                            <small class="text-muted">Largest HH</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="fw-bold text-info fs-5">{{ $householdSummary['min_members'] }}</div>
                            <small class="text-muted">Smallest HH</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="card-title mb-0"><i class="bi bi-people me-2 text-secondary"></i>Residency Duration</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($residencySummary['brackets'] as $label => $data)
                        <div class="col-6">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">{{ $label }}</small>
                                <small class="fw-semibold">{{ $data['count'] }}</small>
                            </div>
                            <div class="progress mb-2" style="height: 4px;">
                                <div class="progress-bar" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($villageComparison))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h6 class="card-title mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Village Demographic Comparison</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-end">Population</th>
                        <th class="text-end">Density/km²</th>
                        <th class="text-end">Growth</th>
                        <th class="text-end">Avg Age</th>
                        <th class="text-end">Dependency</th>
                        <th class="text-end">HH Density</th>
                        <th class="text-end">Avg Residency</th>
                        <th class="text-end">Working %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $row)
                    <tr>
                        <td><a href="{{ route('analytics.advanced-demographics.village', $row['village_id']) }}" class="text-decoration-none fw-semibold">{{ $row['village_name'] }}</a></td>
                        <td class="text-end">{{ number_format($row['population']) }}</td>
                        <td class="text-end">{{ number_format($row['population_density'], 1) }}</td>
                        <td class="text-end"><span class="badge bg-{{ $row['annual_growth'] > 0 ? 'success' : ($row['annual_growth'] < 0 ? 'danger' : 'secondary') }}">{{ number_format($row['annual_growth'], 1) }}%</span></td>
                        <td class="text-end">{{ number_format($row['average_age'], 1) }}</td>
                        <td class="text-end">{{ number_format($row['dependency_ratio'], 1) }}%</td>
                        <td class="text-end">{{ number_format($row['household_density'], 1) }}</td>
                        <td class="text-end">{{ number_format($row['avg_residency_years'], 1) }} yrs</td>
                        <td class="text-end">{{ number_format($row['working_pct'], 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(!empty($rankings))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h6 class="card-title mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Planning Rankings</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach([
                    'population' => ['Population', 'people', 'primary'],
                    'density' => ['Population Density', 'people/km²', 'info'],
                    'growth' => ['Growth Rate', '%', 'success'],
                    'dependency' => ['Dependency Ratio', '%', 'warning'],
                    'residency' => ['Avg Residency', 'yrs', 'secondary'],
                    'stability' => ['Stability Index', '', 'primary'],
                ] as $key => [$title, $unit, $color])
                <div class="col-md-4 col-lg-2">
                    <h6 class="small fw-bold text-{{ $color }}">{{ $title }}</h6>
                    @foreach($rankings[$key] as $r)
                    @php
                        $metricKeys = array_diff(array_keys($r), ['village_id', 'village_name', 'rank']);
                        $firstMetricKey = reset($metricKeys);
                        $metricValue = $r[$firstMetricKey] ?? null;
                    @endphp
                    <div class="d-flex justify-content-between small">
                        <span>{{ $r['rank'] }}. {{ $r['village_name'] }}</span>
                        <span class="text-muted">{{ is_numeric($metricValue) ? number_format((float) $metricValue, 1) : '' }}{{ $unit }}</span>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Executive Planning Insights</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($execInsights as $insight)
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="badge bg-{{ $insight['priority'] ?? 'secondary' }} me-3 mt-1">
                            <i class="bi bi-{{ $insight['icon'] ?? 'info-circle' }}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $insight['title'] }}</div>
                            <div class="text-muted small">{{ $insight['description'] }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center text-muted">No insights available.</div>
                @endforelse
            </div>
        </div>
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
            data: {
                labels: {!! json_encode(array_column($dependencyTrend, 'month')) !!},
                datasets: [
                    { label: 'Total Ratio', data: {!! json_encode(array_column($dependencyTrend, 'ratio')) !!}, borderColor: colors.warning, backgroundColor: colors.warning + '20', fill: true, tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const ageCtx = document.getElementById('ageDistChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($ageDistribution)) !!},
                datasets: [{ label: 'Residents', data: {!! json_encode(array_column($ageDistribution, 'count')) !!}, backgroundColor: palette.slice(0, {!! count($ageDistribution) !!}), borderRadius: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const groCtx = document.getElementById('growthTrendChart');
    if (groCtx) {
        new Chart(groCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($growthTrend, 'month')) !!},
                datasets: [
                    { label: 'Population', data: {!! json_encode(array_column($growthTrend, 'population')) !!}, borderColor: colors.success, backgroundColor: colors.success + '20', fill: true, tension: 0.4, yAxisID: 'y' },
                    { label: 'Growth Rate %', data: {!! json_encode(array_column($growthTrend, 'growth_rate')) !!}, borderColor: colors.primary, borderDash: [5, 5], tension: 0.4, yAxisID: 'y1' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, position: 'left' }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } }
        });
    }

    const migCtx = document.getElementById('migrationTrendChart');
    if (migCtx) {
        new Chart(migCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($migrationTrend, 'month')) !!},
                datasets: [{ label: 'New Residents', data: {!! json_encode(array_column($migrationTrend, 'new_residents')) !!}, backgroundColor: colors.secondary, borderRadius: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const radarCtx = document.getElementById('radarChart');
    if (radarCtx) {
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: {!! json_encode($radarData['labels']) !!},
                datasets: [{ label: 'Community Structure %', data: {!! json_encode($radarData['values']) !!}, backgroundColor: colors.primary + '30', borderColor: colors.primary, pointBackgroundColor: colors.primary }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, max: 100 } } }
        });
    }
});
</script>
@endpush
