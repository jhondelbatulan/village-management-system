@extends('layouts.app')

@section('title', 'Occupation Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-briefcase me-2"></i>Occupation Analytics
        </h1>
        <p class="text-muted mb-0">Occupation & Employment Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.occupation.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>System View
            </a>
        @endif
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
            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('analytics.occupation.export', 'csv') }}">
                    <i class="bi bi-filetype-csv me-2"></i>Export CSV
                </a>
                <a class="dropdown-item" href="{{ route('analytics.occupation.export', 'pdf') }}">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Export PDF
                </a>
            </div>
        </div>
    </div>
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
        <x-analytics.chart-card title="Occupation Distribution (Top 10)" icon="bi-bar-chart" color="primary" canvasId="occupationChart">
            @if(count($topOccupations) > 0)
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
                    <i class="bi bi-list-ol me-2"></i>Occupation Rankings
                </h6>
            </div>
            <div class="card-body">
                @if(count($rankings) > 0)
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

@if(!$villageId && count($villageComparison) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-line me-2"></i>Village Comparison</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Village</th><th class="text-center">Total</th><th class="text-center">Coverage Rate</th><th class="text-center">Top Occupation</th><th class="text-center">Top 3</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.occupation.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ $data['total'] }}</td>
                        <td class="text-center"><span class="badge bg-primary">{{ $data['coverage_rate'] }}%</span></td>
                        <td class="text-center">{{ $data['top_occupation'] ?? 'N/A' }}</td>
                        <td class="text-center">
                            @foreach($data['top_3'] as $occ => $cnt)
                                <span class="badge bg-secondary me-1">{{ $occ }} ({{ $cnt }})</span>
                            @endforeach
                        </td>
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
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];
    const colorsAlpha = colors.map(c => c + '33');

    function applyFilters() {
        const villageId = document.getElementById('filterVillage').value;
        if (villageId) {
            window.location.href = '{{ route("analytics.occupation.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.occupation.index") }}';
        }
    }

    // Occupation Distribution Bar Chart
    const occupationData = @json($topOccupations);
    if (Object.keys(occupationData).length > 0) {
        new Chart(document.getElementById('occupationChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(occupationData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(occupationData),
                    backgroundColor: colors.slice(0, Object.keys(occupationData).length),
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
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
</script>
@endpush
