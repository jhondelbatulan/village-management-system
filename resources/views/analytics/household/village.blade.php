@extends('layouts.app')

@section('title', $village->village_name . ' — Household Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-house-door me-2"></i>{{ $village->village_name }} Household Analytics
        </h1>
        <p class="text-muted mb-0">Village-level household statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.household.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>System View
    </a>
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
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Active" :value="number_format($summary['active'] ?? 0)" icon="bi-toggle2-on" color="success" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Blocks" :value="number_format($summary['total_blocks'] ?? 0)" icon="bi-grid-3x3" color="info" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Occupied" :value="number_format($summary['occupied_lots'] ?? 0)" icon="bi-check2-square" color="primary" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Available" :value="number_format($summary['available_lots'] ?? 0)" icon="bi-plus-square" color="warning" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Avg Size" :value="number_format($summary['average_size'] ?? 0, 2)" icon="bi-people" color="secondary" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Avg/Month" :value="number_format($regStats['avg_monthly'] ?? 0, 1)" icon="bi-graph-up" color="info" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Status" icon="bi-pie-chart" color="primary" canvasId="statusChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Size" icon="bi-people" color="success" canvasId="sizeChart" height="280px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Monthly Trend" icon="bi-graph-up" color="primary" canvasId="trendChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Composition" icon="bi-diagram-3" color="warning" canvasId="compositionChart" height="280px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Block Distribution" icon="bi-grid-3x3" color="secondary" canvasId="blockChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($summary['largest_household'] ?? 0) }}</div>
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
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($summary['average_occupancy'] ?? 0, 2) }}</div>
            <small class="text-muted">Avg Occupancy</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Size Frequency</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Size</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($sizeDist['data'] as $item)
                            <tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="bi bi-clipboard-data me-2"></i>Composition</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Relationship</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($compositionDist['data'] as $item)
                            <tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d', purple: '#6f42c1', pink: '#d63384', teal: '#20c997', orange: '#fd7e14' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, colors.purple, colors.pink, colors.teal, colors.orange];

    const sCtx = document.getElementById('statusChart');
    if (sCtx) new Chart(sCtx, { type: 'doughnut', data: { labels: {!! json_encode($statusDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($statusDist['values'] ?? []) !!}, backgroundColor: [colors.success, colors.warning, colors.danger, colors.secondary, colors.info], borderWidth: 2, borderColor: '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });

    const szCtx = document.getElementById('sizeChart');
    if (szCtx) new Chart(szCtx, { type: 'bar', data: { labels: {!! json_encode($sizeDist['labels'] ?? []) !!}, datasets: [{ label: 'Households', data: {!! json_encode($sizeDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($sizeDist['labels'] ?? [])) !!}), borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const tCtx = document.getElementById('trendChart');
    if (tCtx) { const mL = {!! json_encode(array_keys($monthlyTrend ?? [])) !!}; const mD = {!! json_encode(array_values($monthlyTrend ?? [])) !!}; new Chart(tCtx, { type: 'line', data: { labels: mL.map(m => { const [y,mo] = m.split('-'); return new Date(y,mo-1).toLocaleString('default',{month:'short',year:'2-digit'}); }), datasets: [{ label: 'Registrations', data: mD, borderColor: colors.primary, backgroundColor: colors.primary+'20', fill: true, tension: 0.4, pointRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } }); }

    const cCtx = document.getElementById('compositionChart');
    if (cCtx) new Chart(cCtx, { type: 'pie', data: { labels: {!! json_encode($compositionDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($compositionDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($compositionDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });

    const bCtx = document.getElementById('blockChart');
    if (bCtx) new Chart(bCtx, { type: 'bar', data: { labels: {!! json_encode($blockDist['labels'] ?? []) !!}, datasets: [{ label: 'Households', data: {!! json_encode($blockDist['values'] ?? []) !!}, backgroundColor: colors.secondary, borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
});
</script>
@endpush
