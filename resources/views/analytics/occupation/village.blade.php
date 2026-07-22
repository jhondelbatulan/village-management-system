@extends('layouts.app')

@section('title', 'Occupation Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-briefcase me-2"></i>{{ $village->village_name }} — Occupation Analytics
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.occupation.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Residents" :value="number_format($summary['total'])" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Occupation" :value="number_format($summary['with_occupation'])" icon="bi-briefcase-fill" color="success" :large="true" subtitle="employed residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Coverage Rate" :value="$summary['coverage_rate'] . '%'" icon="bi-graph-up" color="info" :large="true" subtitle="occupation recorded" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Top Occupation" :value="$summary['top_occupation'] ?? 'N/A'" icon="bi-trophy-fill" color="warning" :large="true" subtitle="{{ $summary['top_count'] ?? 0 }} residents" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Occupation Distribution" icon="bi-bar-chart" color="primary" canvasId="occupationChart">
            @if(count($distribution) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Occupation by Gender" icon="bi-people" color="success" canvasId="genderChart">
            @if(count($byGender) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info">
                    <i class="bi bi-list-ol me-2"></i>Top Occupations
                </h6>
            </div>
            <div class="card-body">
                @if(count($topOccupations) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th class="text-center">#</th><th>Occupation</th><th class="text-center">Count</th><th class="text-center">Percentage</th></tr>
                        </thead>
                        <tbody>
                            @foreach($rankings as $rank)
                            <tr>
                                <td class="text-center">
                                    @if($rank['rank'] <= 3)
                                        <span class="badge bg-warning text-dark">{{ $rank['rank'] }}</span>
                                    @else
                                        {{ $rank['rank'] }}
                                    @endif
                                </td>
                                <td>{{ $rank['occupation'] }}</td>
                                <td class="text-center">{{ number_format($rank['count']) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $rank['percentage'] }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted text-center py-4">No data available</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-secondary">
                    <i class="bi bi-arrow-down-circle me-2"></i>Least Common Occupations
                </h6>
            </div>
            <div class="card-body">
                @if(count($leastCommon) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Occupation</th><th class="text-center">Count</th></tr>
                        </thead>
                        <tbody>
                            @foreach($leastCommon as $occ => $count)
                            <tr>
                                <td>{{ $occ }}</td>
                                <td class="text-center">{{ number_format($count) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted text-center py-4">No data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if(count($insights) > 0)
<x-analytics.quick-insights :insights="$insights" />
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];

    // Occupation Distribution Bar Chart
    const occupationData = @json($distribution);
    if (Object.keys(occupationData).length > 0) {
        new Chart(document.getElementById('occupationChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(occupationData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(occupationData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(occupationData).length),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Occupation by Gender Stacked Bar Chart
    const genderData = @json($byGender);
    const genderLabels = Object.keys(genderData);
    if (genderLabels.length > 0) {
        const maleData = genderLabels.map(k => genderData[k]['Male'] || 0);
        const femaleData = genderLabels.map(k => genderData[k]['Female'] || 0);

        new Chart(document.getElementById('genderChart'), {
            type: 'bar',
            data: {
                labels: genderLabels,
                datasets: [
                    {
                        label: 'Male',
                        data: maleData,
                        backgroundColor: '#4e73df',
                        borderRadius: 6
                    },
                    {
                        label: 'Female',
                        data: femaleData,
                        backgroundColor: '#e74a3b',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush
