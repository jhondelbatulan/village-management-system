@extends('layouts.app')

@section('title', 'Government Assistance Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-building-check me-2"></i>Government Assistance Analytics
        </h1>
        <p class="text-muted mb-0">Overview of government assistance programs across all villages</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter Village
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.government-assistance.index') }}" @if(!$villageId) @endif>All Villages</a></li>
                @foreach($villages as $village)
                    <li><a class="dropdown-item @if($villageId == $village->id) active @endif" href="{{ route('analytics.government-assistance.village', $village->id) }}">{{ $village->village_name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.government-assistance.export', 'csv') }}"><i class="bi bi-filetype-csv me-2"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.government-assistance.export', 'pdf') }}"><i class="bi bi-file-pdf me-2"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <x-analytics.summary-card
            title="Total Residents"
            :value="$summary['total']"
            icon="bi-people"
            color="primary"
        />
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <x-analytics.summary-card
            title="With Assistance"
            :value="$summary['with_assistance']"
            icon="bi-building-check"
            color="success"
        />
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <x-analytics.summary-card
            title="Coverage Rate"
            :value="$summary['coverage_rate'] . '%'"
            icon="bi-graph-up"
            color="info"
        />
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6 mb-4">
        <x-analytics.chart-card
            title="Program Distribution"
            canvasId="programChart"
        />
    </div>
    <div class="col-xl-6 col-lg-6 mb-4">
        <x-analytics.chart-card
            title="Beneficiary Distribution"
            canvasId="beneficiaryChart"
        />
    </div>
</div>

<!-- Program Counts Table -->
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-table me-1"></i>Program Counts
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Program</th>
                        <th class="text-center">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programCounts as $program => $count)
                    <tr>
                        <td>{{ $program }}</td>
                        <td class="text-center">{{ $count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
                        <th class="text-center">Total Residents</th>
                        <th class="text-center">With Assistance</th>
                        <th class="text-center">Coverage Rate</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $village)
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-center">{{ $village['total'] }}</td>
                        <td class="text-center">{{ $village['with_assistance'] }}</td>
                        <td class="text-center">{{ $village['rate'] }}%</td>
                        <td class="text-center">
                            <a href="{{ route('analytics.government-assistance.village', $village['village_id']) }}" class="btn btn-sm btn-outline-primary">
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
const programLabels = @json(array_keys($programDistribution));
const programData = @json(array_values(array_map(function($item) { return $item['count']; }, $programDistribution)));

new Chart(document.getElementById('programChart'), {
    type: 'bar',
    data: {
        labels: programLabels,
        datasets: [{
            label: 'Residents',
            data: programData,
            backgroundColor: colors.slice(0, programLabels.length),
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

const beneficiaryLabels = @json(array_keys($beneficiaryDistribution));
const beneficiaryData = @json(array_values(array_map(function($item) { return $item['count']; }, $beneficiaryDistribution)));

new Chart(document.getElementById('beneficiaryChart'), {
    type: 'doughnut',
    data: {
        labels: beneficiaryLabels,
        datasets: [{
            data: beneficiaryData,
            backgroundColor: colors.slice(0, beneficiaryLabels.length),
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 15 } }
        }
    }
});
</script>
@endpush
