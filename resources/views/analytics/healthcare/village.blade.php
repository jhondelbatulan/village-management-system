@extends('layouts.app')

@section('title', $village->village_name . ' - Healthcare Coverage Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('analytics.healthcare.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i>Back to All Villages
        </a>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-heart-pulse me-2"></i>{{ $village->village_name }} - Healthcare Coverage
        </h1>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="Total Residents"
            :value="$summary['total']"
            icon="bi-people"
            color="primary"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="PhilHealth Members"
            :value="$summary['philhealth_members']"
            icon="bi-shield-check"
            color="success"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="Without PhilHealth"
            :value="$summary['without_philhealth']"
            icon="bi-shield-x"
            color="danger"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="PhilHealth Rate"
            :value="$summary['philhealth_rate'] . '%'"
            icon="bi-graph-up"
            color="info"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="Senior Coverage"
            :value="$summary['senior_coverage_rate'] . '%'"
            icon="bi-person-heart"
            color="warning"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <x-analytics.summary-card
            title="PWD Coverage"
            :value="$summary['pwd_coverage_rate'] . '%'"
            icon="bi-person-wheelchair"
            color="secondary"
        />
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-12 mb-4">
        <x-analytics.chart-card
            title="Coverage Distribution"
            canvasId="coverageChart"
        />
    </div>
</div>

<!-- Insights -->
<x-analytics.quick-insights :insights="$insights" />
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];
const coverageLabels = @json(array_keys($coverageDistribution));
const coverageData = @json(array_values(array_map(function($item) { return $item['count']; }, $coverageDistribution)));

new Chart(document.getElementById('coverageChart'), {
    type: 'bar',
    data: {
        labels: coverageLabels,
        datasets: [{
            label: 'Residents',
            data: coverageData,
            backgroundColor: colors.slice(0, coverageLabels.length),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { maxRotation: 45 } }
        }
    }
});
</script>
@endpush
