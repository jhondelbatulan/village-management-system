@extends('layouts.app')

@section('title', $village->village_name . ' - Government Membership Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('analytics.government-membership.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i>Back to All Villages
        </a>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-card-checklist me-2"></i>{{ $village->village_name }} - Government Membership
        </h1>
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

<!-- Insights -->
<x-analytics.quick-insights :insights="$insights" />
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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
