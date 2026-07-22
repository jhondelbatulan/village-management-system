@extends('layouts.app')

@section('title', 'Skills Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-tools me-2"></i>Skills Analytics
        </h1>
        <p class="text-muted mb-0">Skills & Occupancy Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
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
                            <option value="{{ $v->id }}" {{ ($villageId ?? '') == $v->id ? 'selected' : '' }}>{{ $v->village_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-sm btn-primary w-100 mt-2" onclick="applyFilters()">Apply</button>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.skills.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.skills.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Residents" :value="number_format($summary['total'])" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Skill Coverage" :value="$summary['skill_coverage'] . '%'" icon="bi-bar-chart-fill" color="success" :large="true" subtitle="residents with any skill" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Primary Skill" :value="number_format($summary['with_primary'])" icon="bi-star-fill" color="info" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Secondary Skill" :value="number_format($summary['with_secondary'])" icon="bi-star-half" color="warning" :large="true" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="With Any Skill" :value="number_format($summary['with_any_skill'])" icon="bi-check-circle-fill" color="success" subtitle="{{ $summary['skill_coverage'] }}% of total" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Combined Skills Distribution" icon="bi-bar-chart" color="primary" canvasId="combinedChart">
            @if(count($combinedDist) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Skills Inventory by Category" icon="bi-pie-chart" color="success" canvasId="inventoryChart">
            @if(count($inventory) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Primary Skills Distribution" icon="bi-star-fill" color="info" canvasId="primaryChart">
            @if(count($primaryDist) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Secondary Skills Distribution" icon="bi-star-half" color="warning" canvasId="secondaryChart">
            @if(count($secondaryDist) > 0)
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

@if(!$villageId && count($villageComparison) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-line me-2"></i>Village Comparison</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Village</th><th class="text-center">Skills</th><th class="text-center">With Skill</th><th class="text-center">Coverage</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.skills.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ count($data['skills']) }}</td>
                        <td class="text-center">{{ $data['total_with_skill'] }}</td>
                        <td class="text-center">
                            @php $coverage = $summary['total'] > 0 ? round(($data['total_with_skill'] / max($summary['total'], 1)) * 100, 1) : 0; @endphp
                            <span class="badge bg-{{ $coverage > 50 ? 'success' : 'secondary' }}">{{ $coverage }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(!$villageId && count($villageInventoryComparison) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-success"><i class="bi bi-box-seam me-2"></i>Village Inventory Comparison</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Village</th><th class="text-center">Categories</th><th class="text-center">Total Skill Residents</th></tr>
                </thead>
                <tbody>
                    @foreach($villageInventoryComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.skills.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ count($data['categories']) }}</td>
                        <td class="text-center">{{ $data['total_skill_residents'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(count($insights) > 0)
<x-analytics.quick-insights :insights="$insights" />
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

    function applyFilters() {
        const villageId = document.getElementById('filterVillage').value;
        if (villageId) {
            window.location.href = '{{ route("analytics.skills.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.skills.index") }}';
        }
    }

    // Combined Skills Distribution Chart
    const combinedData = @json($combinedDist);
    if (Object.keys(combinedData).length > 0) {
        new Chart(document.getElementById('combinedChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(combinedData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(combinedData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(combinedData).length),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Skills Inventory Category Chart
    const inventoryData = @json($inventory);
    if (Object.keys(inventoryData).length > 0) {
        new Chart(document.getElementById('inventoryChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(inventoryData),
                datasets: [{
                    data: Object.values(inventoryData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(inventoryData).length)
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // Primary Skills Distribution Chart
    const primaryData = @json($primaryDist);
    if (Object.keys(primaryData).length > 0) {
        new Chart(document.getElementById('primaryChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(primaryData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(primaryData).map(v => v.count),
                    backgroundColor: '#4e73df',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Secondary Skills Distribution Chart
    const secondaryData = @json($secondaryDist);
    if (Object.keys(secondaryData).length > 0) {
        new Chart(document.getElementById('secondaryChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(secondaryData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(secondaryData).map(v => v.count),
                    backgroundColor: '#f6c23e',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
</script>
@endpush
