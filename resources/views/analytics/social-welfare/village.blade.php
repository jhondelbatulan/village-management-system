@extends('layouts.app')

@section('title', 'Social Welfare Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-people-fill me-2"></i>{{ $village->village_name }} — Social Welfare
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.social-welfare.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Senior Citizens" :value="number_format($summary['seniors'] ?? 0)" icon="bi-person-workspace" color="primary" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['seniors'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="PWD" :value="number_format($summary['pwd'] ?? 0)" icon="bi-universal-access" color="success" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['pwd'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Solo Parent" :value="number_format($summary['solo_parent'] ?? 0)" icon="bi-person-hearts" color="danger" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['solo_parent'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Indigenous" :value="number_format($summary['indigenous'] ?? 0)" icon="bi-leaf" color="warning" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['indigenous'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Veteran" :value="number_format($summary['veteran'] ?? 0)" icon="bi-award-fill" color="info" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Youth" :value="number_format($summary['youth'] ?? 0)" icon="bi-person-video" color="secondary" subtitle="residents" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Category Distribution" icon="bi-bar-chart" color="primary" canvasId="welfareChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Monthly Trends" icon="bi-graph-up" color="success" canvasId="trendChart" height="280px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Welfare Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

    const catData = @json($categoryDistribution);
    if (Object.keys(catData).length > 0) {
        new Chart(document.getElementById('welfareChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(catData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(catData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(catData).length),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const trendData = @json($trend);
    if (Object.keys(trendData).length > 0) {
        const monthLabels = Object.keys(trendData);
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: 'Seniors', data: monthLabels.map(m => trendData[m].seniors ?? 0), borderColor: '#4e73df', backgroundColor: '#4e73df20', fill: false, tension: 0.4, pointRadius: 4 },
                    { label: 'PWD', data: monthLabels.map(m => trendData[m].pwd ?? 0), borderColor: '#1cc88a', backgroundColor: '#1cc88a20', fill: false, tension: 0.4, pointRadius: 4 },
                    { label: 'Solo Parent', data: monthLabels.map(m => trendData[m].solo_parent ?? 0), borderColor: '#e74a3b', backgroundColor: '#e74a3b20', fill: false, tension: 0.4, pointRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
</script>
@endpush
