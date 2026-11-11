@extends('layouts.app')

@section('title', 'Healthcare Coverage Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-heart-pulse me-2"></i>Healthcare Coverage Analytics
        </h1>
        <p class="text-muted mb-0">Overview of healthcare coverage across all villages</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter Village
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.healthcare.index') }}" @if(!$villageId) @endif>All Villages</a></li>
                @foreach($villages as $village)
                    <li><a class="dropdown-item @if($villageId == $village->id) active @endif" href="{{ route('analytics.healthcare.village', $village->id) }}">{{ $village->village_name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.healthcare.export', 'csv') }}"><i class="bi bi-filetype-csv me-2"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.healthcare.export', 'pdf') }}"><i class="bi bi-file-pdf me-2"></i>Export PDF</a></li>
            </ul>
        </div>
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

<!-- Village Comparison -->
@if(!$villageId && count($villageComparison) > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-bar-chart me-1"></i>Village Comparison
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">PhilHealth Rate</th>
                        <th class="text-center">Senior Coverage</th>
                        <th class="text-center">PWD Coverage</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $village)
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-center">{{ $village['total'] }}</td>
                        <td class="text-center">{{ $village['philhealth_rate'] }}%</td>
                        <td class="text-center">{{ $village['senior_rate'] }}%</td>
                        <td class="text-center">{{ $village['pwd_rate'] }}%</td>
                        <td class="text-center">
                            <a href="{{ route('analytics.healthcare.village', $village['village_id']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

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
