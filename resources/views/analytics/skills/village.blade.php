@extends('layouts.app')

@section('title', 'Skills Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-tools me-2"></i>{{ $village->village_name }} — Skills Analytics
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.skills.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Residents" :value="number_format($summary['total'])" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Skill Coverage" :value="$summary['skill_coverage'] . '%'" icon="bi-bar-chart-fill" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Primary Skill" :value="number_format($summary['with_primary'])" icon="bi-star-fill" color="info" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Secondary Skill" :value="number_format($summary['with_secondary'])" icon="bi-star-half" color="warning" :large="true" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Combined Skills Distribution" icon="bi-bar-chart" color="primary" canvasId="combinedChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Skills Inventory by Category" icon="bi-pie-chart" color="success" canvasId="inventoryChart" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Primary Skills Distribution" icon="bi-star-fill" color="info" canvasId="primaryChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Secondary Skills Distribution" icon="bi-star-half" color="warning" canvasId="secondaryChart" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-warning"><i class="bi bi-trophy me-2"></i>Skills Ranking</h6>
            </div>
            <div class="card-body p-0">
                @if(count($rankings) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th class="text-center" style="width:60px;">Rank</th><th>Skill</th><th class="text-center">Count</th></tr>
                        </thead>
                        <tbody>
                            @foreach($rankings as $item)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item['rank'] <= 3 ? 'warning' : 'secondary' }} rounded-circle" style="width:24px;height:24px;line-height:18px;">
                                        {{ $item['rank'] }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $item['skill'] }}</td>
                                <td class="text-center">{{ $item['count'] }}</td>
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
                <h6 class="m-0 fw-bold text-info"><i class="bi bi-clipboard-data me-2"></i>Skills Inventory Breakdown</h6>
            </div>
            <div class="card-body p-0">
                @if(count($inventoryBreakdown) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Category</th><th class="text-center">Count</th></tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryBreakdown as $category => $count)
                            <tr>
                                <td class="fw-semibold">{{ $category }}</td>
                                <td class="text-center">{{ $count }}</td>
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
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];

    const combinedData = @json($combinedDist);
    if (Object.keys(combinedData).length > 0) {
        new Chart(document.getElementById('combinedChart'), {
            type: 'bar',
            data: { labels: Object.keys(combinedData), datasets: [{ label: 'Residents', data: Object.values(combinedData).map(v => v.count), backgroundColor: colors, borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const inventoryData = @json($inventory);
    if (Object.keys(inventoryData).length > 0) {
        new Chart(document.getElementById('inventoryChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(inventoryData), datasets: [{ data: Object.values(inventoryData).map(v => v.count), backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }

    const primaryData = @json($primaryDist);
    if (Object.keys(primaryData).length > 0) {
        new Chart(document.getElementById('primaryChart'), {
            type: 'bar',
            data: { labels: Object.keys(primaryData), datasets: [{ label: 'Residents', data: Object.values(primaryData).map(v => v.count), backgroundColor: '#4e73df', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }

    const secondaryData = @json($secondaryDist);
    if (Object.keys(secondaryData).length > 0) {
        new Chart(document.getElementById('secondaryChart'), {
            type: 'bar',
            data: { labels: Object.keys(secondaryData), datasets: [{ label: 'Residents', data: Object.values(secondaryData).map(v => v.count), backgroundColor: '#f6c23e', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
</script>
@endpush
