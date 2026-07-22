@extends('layouts.app')

@section('title', 'Planning Analytics Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-clipboard-data me-2"></i>Planning Analytics Dashboard</h4>
            <p class="text-muted mb-0 small">Integrated demographic indicators for planning and decision-making</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('analytics.planning.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('analytics.planning.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>PDF</a></li>
                </ul>
            </div>
        </div>
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
            <x-analytics.summary-card title="HH Stability" :value="number_format($indicators['household_stability']['stability_index'], 1)" icon="bi-house-door-fill" color="primary" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Avg Residency" :value="number_format($indicators['residency']['average_years'], 1) . ' yrs'" icon="bi-house-heart-fill" color="secondary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Migration Rate" :value="number_format($indicators['migration']['migration_rate'], 1) . '%'" icon="bi-arrow-left-right" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="HH Density" :value="number_format($indicators['density']['household_density'], 1) . '/km²'" icon="bi-grid-3x3-gap-fill" color="secondary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Working Age %" :value="number_format($indicators['community_structure']['working_pct'], 1) . '%'" icon="bi-briefcase-fill" color="success" />
        </div>
    </div>

    @if(!empty($comparison))
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Dependency Ratio Comparison" icon="bi-people" color="warning" canvasId="depCompChart" height="300px" />
        </div>
        <div class="col-lg-6">
            <x-analytics.chart-card title="Population Density Comparison" icon="bi-geo-alt" color="info" canvasId="denCompChart" height="300px" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-analytics.chart-card title="Annual Growth Comparison" icon="bi-graph-up" color="success" canvasId="groCompChart" height="300px" />
        </div>
        <div class="col-lg-6">
            <x-analytics.chart-card title="Stability Index Comparison" icon="bi-speedometer2" color="primary" canvasId="stabCompChart" height="300px" />
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h6 class="card-title mb-0"><i class="bi bi-table me-2 text-primary"></i>Full Village Comparison</h6>
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
                        <th class="text-end">HH Size</th>
                        <th class="text-end">Stability</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparison as $row)
                    <tr>
                        <td><a href="{{ route('analytics.planning.village', $row['village_id']) }}" class="text-decoration-none fw-semibold">{{ $row['village_name'] }}</a></td>
                        <td class="text-end">{{ number_format($row['population']) }}</td>
                        <td class="text-end">{{ number_format($row['population_density'], 1) }}</td>
                        <td class="text-end"><span class="badge bg-{{ $row['annual_growth'] > 0 ? 'success' : ($row['annual_growth'] < 0 ? 'danger' : 'secondary') }}">{{ number_format($row['annual_growth'], 1) }}%</span></td>
                        <td class="text-end">{{ number_format($row['average_age'], 1) }}</td>
                        <td class="text-end">{{ number_format($row['dependency_ratio'], 1) }}%</td>
                        <td class="text-end">{{ number_format($row['household_density'], 1) }}</td>
                        <td class="text-end">{{ number_format($row['avg_residency_years'], 1) }} yrs</td>
                        <td class="text-end">{{ number_format($row['avg_household_size'], 1) }}</td>
                        <td class="text-end">{{ number_format($row['stability_index'], 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="card-title mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Planning Rankings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach([
                            'population' => ['Population', 'primary'],
                            'density' => ['Density/km²', 'info'],
                            'growth' => ['Growth Rate', 'success'],
                            'dependency' => ['Dependency', 'warning'],
                            'residency' => ['Residency', 'secondary'],
                            'stability' => ['Stability', 'primary'],
                        ] as $key => [$title, $color])
                        <div class="col-md-4 col-lg-2">
                            <h6 class="small fw-bold text-{{ $color }}">{{ $title }}</h6>
                            @foreach($rankings[$key] as $r)
                            <div class="d-flex justify-content-between small">
                                <span>{{ $r['rank'] }}. {{ $r['village_name'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Planning Insights</h6>
                </div>
                <div class="card-body">
                    @forelse($execInsights as $insight)
                    <div class="d-flex align-items-start mb-3">
                        <div class="badge bg-{{ $insight['priority'] ?? 'secondary' }} me-2 mt-1"><i class="bi bi-{{ $insight['icon'] ?? 'info-circle' }}"></i></div>
                        <div>
                            <div class="fw-semibold small">{{ $insight['title'] }}</div>
                            <div class="text-muted small">{{ $insight['description'] }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No insights available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, '#6f42c1', '#d63384', '#20c997', '#fd7e14'];

    const depLabels = {!! json_encode(collect($comparison)->pluck('village_name')->toArray()) !!};
    const depData = {!! json_encode(collect($comparison)->pluck('dependency_ratio')->toArray()) !!};

    const depCtx = document.getElementById('depCompChart');
    if (depCtx) new Chart(depCtx, { type: 'bar', data: { labels: depLabels, datasets: [{ label: 'Dependency Ratio %', data: depData, backgroundColor: palette.slice(0, count(depLabels)), borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });

    const denCtx = document.getElementById('denCompChart');
    if (denCtx) new Chart(denCtx, { type: 'bar', data: { labels: depLabels, datasets: [{ label: 'Pop. Density', data: {!! json_encode(collect($comparison)->pluck('population_density')->toArray()) !!}, backgroundColor: colors.info, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });

    const groCtx = document.getElementById('groCompChart');
    if (groCtx) new Chart(groCtx, { type: 'bar', data: { labels: depLabels, datasets: [{ label: 'Annual Growth %', data: {!! json_encode(collect($comparison)->pluck('annual_growth')->toArray()) !!}, backgroundColor: colors.success, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });

    const stabCtx = document.getElementById('stabCompChart');
    if (stabCtx) new Chart(stabCtx, { type: 'bar', data: { labels: depLabels, datasets: [{ label: 'Stability Index', data: {!! json_encode(collect($comparison)->pluck('stability_index')->toArray()) !!}, backgroundColor: colors.primary, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
});
</script>
@endpush
