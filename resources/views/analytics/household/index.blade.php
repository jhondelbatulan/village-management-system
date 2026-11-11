@extends('layouts.app')

@section('title', 'Household Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-house-door me-2"></i>Household Analytics
        </h1>
        <p class="text-muted mb-0">Comprehensive Household Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.household.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>System View
            </a>
        @endif
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filters
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                <h6 class="dropdown-header">Filter Options</h6>
                <div class="mb-2">
                    <label class="form-label small">Village</label>
                    <select class="form-select form-select-sm" id="filterVillage">
                        <option value="">All Villages</option>
                        @foreach($villages as $v)
                            <option value="{{ $v->id }}" {{ ($villageId ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-sm btn-primary w-100 mt-2" onclick="applyFilters()">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Households" :value="number_format($summary['total_households'] ?? 0)" icon="bi-house-door-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Approved" :value="number_format($summary['approved'] ?? 0)" icon="bi-check-circle" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Pending" :value="number_format($summary['pending'] ?? 0)" icon="bi-hourglass-split" color="warning" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Rejected" :value="number_format($summary['rejected'] ?? 0)" icon="bi-x-circle" color="danger" :large="true" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Active" :value="number_format($summary['active'] ?? 0)" icon="bi-toggle2-on" color="success" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Inactive" :value="number_format($summary['inactive'] ?? 0)" icon="bi-toggle2-off" color="secondary" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Total Blocks" :value="number_format($summary['total_blocks'] ?? 0)" icon="bi-grid-3x3" color="info" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Occupied Lots" :value="number_format($summary['occupied_lots'] ?? 0)" icon="bi-check2-square" color="primary" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Available Lots" :value="number_format($summary['available_lots'] ?? 0)" icon="bi-plus-square" color="warning" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Draft" :value="number_format($summary['draft'] ?? 0)" icon="bi-pencil-square" color="secondary" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-analytics.chart-card title="Household Status Distribution" icon="bi-pie-chart" color="primary" canvasId="statusChart" height="300px" />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights title="Household Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Size Distribution" icon="bi-people" color="success" canvasId="sizeChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Monthly Registration Trend" icon="bi-graph-up" color="primary" canvasId="trendChart" height="300px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Composition" icon="bi-diagram-3" color="warning" canvasId="compositionChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Type Distribution" icon="bi-building" color="info" canvasId="typeChart" height="300px" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($summary['average_size'] ?? 0, 2) }}</div>
            <small class="text-muted">Avg Household Size</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($summary['largest_household'] ?? 0) }}</div>
            <small class="text-muted">Largest Household</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-warning fw-bold h4 mb-0">{{ number_format($summary['smallest_household'] ?? 0) }}</div>
            <small class="text-muted">Smallest Household</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-info fw-bold h4 mb-0">{{ number_format($summary['median_size'] ?? 0, 1) }}</div>
            <small class="text-muted">Median Size</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($summary['average_occupancy'] ?? 0, 2) }}</div>
            <small class="text-muted">Avg Occupancy</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-danger fw-bold h4 mb-0">{{ number_format($summary['highest_occupancy'] ?? 0) }}</div>
            <small class="text-muted">Highest Occupancy</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($summary['lowest_occupancy'] ?? 0) }}</div>
            <small class="text-muted">Lowest Occupancy</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-warning fw-bold h4 mb-0">{{ number_format($regStats['avg_monthly'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Monthly Registrations</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Distribution per Block" icon="bi-grid-3x3" color="secondary" canvasId="blockChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Yearly Trend" icon="bi-graph-up-arrow" color="success" canvasId="yearlyChart" height="300px" />
    </div>
</div>

@if(!$villageId && count($villageComparison) > 0)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Households by Village" icon="bi-bar-chart" color="primary" canvasId="villageCompChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Avg Household Size by Village" icon="bi-house-door" color="warning" canvasId="villageSizeChart" height="300px" />
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Size Frequency Distribution</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Size</th><th class="small text-end">Frequency</th><th class="small text-end">Percentage</th></tr>
                    </thead>
                    <tbody>
                        @foreach($sizeDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }} member(s)</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-warning"><i class="bi bi-clipboard-data me-2"></i>Composition Frequency Distribution</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Relationship</th><th class="small text-end">Frequency</th><th class="small text-end">Percentage</th></tr>
                    </thead>
                    <tbody>
                        @foreach($compositionDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info"><i class="bi bi-speedometer2 me-2"></i>Growth Analysis</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Weekly</div>
                            <div class="fw-bold {{ ($growth['weekly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['weekly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['weekly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['weekly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Monthly</div>
                            <div class="fw-bold {{ ($growth['monthly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['monthly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['monthly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['monthly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Quarterly</div>
                            <div class="fw-bold text-primary">+{{ number_format($growth['quarterly']['current'] ?? 0) }}</div>
                            <small class="text-muted">This quarter</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Yearly</div>
                            <div class="fw-bold {{ ($growth['yearly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['yearly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['yearly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['yearly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-secondary"><i class="bi bi-grid-3x3 me-2"></i>Block & Lot Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Total Blocks</div>
                            <div class="fw-bold text-primary h5 mb-0">{{ number_format($blockLotStats['total_blocks'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Total Lots</div>
                            <div class="fw-bold text-info h5 mb-0">{{ number_format($blockLotStats['total_lots'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Occupied</div>
                            <div class="fw-bold text-success h5 mb-0">{{ number_format($blockLotStats['occupied_lots'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Available</div>
                            <div class="fw-bold text-warning h5 mb-0">{{ number_format($blockLotStats['available_lots'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        primary: '#0d6efd', success: '#198754', danger: '#dc3545',
        warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d',
        dark: '#212529', purple: '#6f42c1', pink: '#d63384',
        teal: '#20c997', orange: '#fd7e14'
    };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, colors.purple, colors.pink, colors.teal, colors.orange];

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusDist['labels'] ?? []) !!},
                datasets: [{ data: {!! json_encode($statusDist['values'] ?? []) !!}, backgroundColor: [colors.success, colors.warning, colors.danger, colors.secondary, colors.info], borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const sizeCtx = document.getElementById('sizeChart');
    if (sizeCtx) {
        new Chart(sizeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($sizeDist['labels'] ?? []) !!},
                datasets: [{ label: 'Households', data: {!! json_encode($sizeDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($sizeDist['labels'] ?? [])) !!}), borderRadius: 6, borderSkipped: false }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const mL = {!! json_encode(array_keys($monthlyTrend ?? [])) !!};
        const mD = {!! json_encode(array_values($monthlyTrend ?? [])) !!};
        new Chart(trendCtx, {
            type: 'line',
            data: { labels: mL.map(m => { const [y,mo] = m.split('-'); return new Date(y,mo-1).toLocaleString('default',{month:'short',year:'2-digit'}); }), datasets: [{ label: 'Registrations', data: mD, borderColor: colors.primary, backgroundColor: colors.primary+'20', fill: true, tension: 0.4, pointRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const compCtx = document.getElementById('compositionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($compositionDist['labels'] ?? []) !!},
                datasets: [{ data: {!! json_encode($compositionDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($compositionDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($typeDist['labels'] ?? []) !!},
                datasets: [{ data: {!! json_encode($typeDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($typeDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const blockCtx = document.getElementById('blockChart');
    if (blockCtx) {
        new Chart(blockCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($blockDist['labels'] ?? []) !!},
                datasets: [{ label: 'Households', data: {!! json_encode($blockDist['values'] ?? []) !!}, backgroundColor: colors.secondary, borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const yearlyCtx = document.getElementById('yearlyChart');
    if (yearlyCtx) {
        const yL = {!! json_encode(array_keys($yearlyTrend ?? [])) !!};
        const yD = {!! json_encode(array_values($yearlyTrend ?? [])) !!};
        new Chart(yearlyCtx, {
            type: 'line',
            data: { labels: yL, datasets: [{ label: 'Annual', data: yD, borderColor: colors.success, backgroundColor: colors.success+'15', fill: true, tension: 0.4, pointRadius: 5, borderWidth: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const vcCtx = document.getElementById('villageCompChart');
    if (vcCtx) {
        new Chart(vcCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!},
                datasets: [{ label: 'Households', data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total'])->toArray()) !!}, backgroundColor: colors.primary, borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const vsCtx = document.getElementById('villageSizeChart');
    if (vsCtx) {
        new Chart(vsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!},
                datasets: [{ label: 'Avg Size', data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['average_size'])->toArray()) !!}, backgroundColor: palette.slice(0, count({!! json_encode(collect($villageComparison ?? [])->toArray()) !!})), borderRadius: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
});

function applyFilters() {
    const village = document.getElementById('filterVillage').value;
    if (village) {
        window.location.href = '{{ route("analytics.household.village", ":id") }}'.replace(':id', village);
    } else {
        window.location.href = '{{ route("analytics.household.index") }}';
    }
}
</script>
@endpush
