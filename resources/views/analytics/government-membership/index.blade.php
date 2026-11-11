@extends('layouts.app')

@section('title', 'Government Membership Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-card-checklist me-2"></i>Government Membership Analytics
        </h1>
        <p class="text-muted mb-0">Overview of government membership programs across all villages</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter Village
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.government-membership.index') }}" @if(!$villageId) @endif>All Villages</a></li>
                @foreach($villages as $village)
                    <li><a class="dropdown-item @if($villageId == $village->id) active @endif" href="{{ route('analytics.government-membership.village', $village->id) }}">{{ $village->village_name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.government-membership.export', 'csv') }}"><i class="bi bi-filetype-csv me-2"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.government-membership.export', 'pdf') }}"><i class="bi bi-file-pdf me-2"></i>Export PDF</a></li>
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
            title="With Membership"
            :value="$summary['with_any_membership']"
            icon="bi-card-checklist"
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
    <div class="col-12 mb-4">
        <x-analytics.chart-card
            title="Membership Distribution"
            canvasId="membershipChart"
        />
    </div>
</div>

<!-- Membership Counts Table -->
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-table me-1"></i>Membership Counts
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Membership Program</th>
                        <th class="text-center">Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($membershipCounts as $program => $count)
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
                        <th class="text-center">With Membership</th>
                        <th class="text-center">Coverage Rate</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $village)
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-center">{{ $village['total'] }}</td>
                        <td class="text-center">{{ $village['covered'] }}</td>
                        <td class="text-center">{{ $village['coverage_rate'] }}%</td>
                        <td class="text-center">
                            <a href="{{ route('analytics.government-membership.village', $village['village_id']) }}" class="btn btn-sm btn-outline-primary">
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
const membershipLabels = @json(array_keys($membershipDistribution));
const membershipData = @json(array_values(array_map(function($item) { return $item['count']; }, $membershipDistribution)));

new Chart(document.getElementById('membershipChart'), {
    type: 'bar',
    data: {
        labels: membershipLabels,
        datasets: [{
            label: 'Residents',
            data: membershipData,
            backgroundColor: colors.slice(0, membershipLabels.length),
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
@endpush
