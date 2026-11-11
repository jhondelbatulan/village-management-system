@extends('layouts.app')

@section('title', 'Employment Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-briefcase me-2"></i>Employment Analytics
        </h1>
        <p class="text-muted mb-0">Employment, Occupation & Income Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.employment.index') }}" class="btn btn-sm btn-outline-secondary">
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
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Employment Rate" :value="$summary['employment_rate'] . '%'" icon="bi-briefcase-fill" color="success" :large="true" subtitle="{{ number_format($summary['total_employed']) }} employed" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Unemployment Rate" :value="$summary['unemployment_rate'] . '%'" icon="bi-x-circle" color="danger" :large="true" subtitle="{{ number_format($summary['unemployed']) }} unemployed" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Average Income" :value="'₱' . number_format($summary['average_income'], 0)" icon="bi-cash-stack" color="info" :large="true" subtitle="monthly" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="OFWs" :value="number_format($summary['ofw'])" icon="bi-globe" color="warning" :large="true" subtitle="{{ $summary['ofw_rate'] }}%" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Self-Employed" :value="number_format($summary['self_employed'])" icon="bi-shop" color="primary" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Median Income" :value="'₱' . number_format($summary['median_income'], 0)" icon="bi-bar-chart" color="success" subtitle="monthly" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Retired" :value="number_format($summary['retired'])" icon="bi-person-x" color="secondary" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Income Data Points" :value="number_format($summary['income_data_count'])" icon="bi-database" color="info" subtitle="with income recorded" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Employment Status" icon="bi-pie-chart" color="primary" canvasId="employmentChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Income Range Distribution" icon="bi-bar-chart" color="success" canvasId="incomeRangeChart" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Top Occupations" icon="bi-list-task" color="info" canvasId="occupationChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Income Sources" icon="bi-cash" color="warning" canvasId="incomeSourceChart" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Work Experience" icon="bi-clock-history" color="secondary" canvasId="experienceChart" />
    </div>
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info"><i class="bi bi-gender-ambiguous me-2"></i>Average Income by Gender</h6>
            </div>
            <div class="card-body">
                @if(count($incomeByGender) > 0)
                <div class="row text-center">
                    @foreach($incomeByGender as $gender => $avg)
                    <div class="col-6">
                        <div class="p-3 rounded bg-{{ $gender === 'Male' ? 'primary' : 'danger' }}-soft">
                            <i class="bi bi-{{ $gender === 'Male' ? 'gender-male' : 'gender-female' }} fs-1 text-{{ $gender === 'Male' ? 'primary' : 'danger' }}"></i>
                            <h5 class="mb-0 mt-2 fw-bold">₱{{ number_format($avg, 0) }}</h5>
                            <small class="text-muted">{{ $gender }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <p class="text-muted text-center py-4">No income data</p>
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
                    <tr><th>Village</th><th class="text-center">Total</th><th class="text-center">Employment Rate</th><th class="text-center">Unemployment</th><th class="text-center">OFW Rate</th><th class="text-center">Avg Income</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.employment.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ $data['total'] }}</td>
                        <td class="text-center"><span class="badge bg-success">{{ $data['employment_rate'] }}%</span></td>
                        <td class="text-center"><span class="badge bg-danger">{{ $data['unemployment_rate'] }}%</span></td>
                        <td class="text-center">{{ $data['ofw_rate'] }}%</td>
                        <td class="text-center">₱{{ number_format($data['average_income'], 0) }}</td>
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
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];

    function applyFilters() {
        const villageId = document.getElementById('filterVillage').value;
        if (villageId) {
            window.location.href = '{{ route("analytics.employment.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.employment.index") }}';
        }
    }

    const empData = @json($employmentDist);
    if (Object.keys(empData).length > 0) {
        new Chart(document.getElementById('employmentChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(empData), datasets: [{ data: Object.values(empData).map(v => v.count), backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }

    const incomeRange = @json($incomeRangeDist);
    if (Object.keys(incomeRange).length > 0) {
        new Chart(document.getElementById('incomeRangeChart'), {
            type: 'bar',
            data: { labels: Object.keys(incomeRange), datasets: [{ label: 'Residents', data: Object.values(incomeRange).map(v => v.count), backgroundColor: '#1cc88a', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const occData = @json($occupationDist);
    if (Object.keys(occData).length > 0) {
        new Chart(document.getElementById('occupationChart'), {
            type: 'bar',
            data: { labels: Object.keys(occData), datasets: [{ label: 'Residents', data: Object.values(occData).map(v => v.count), backgroundColor: '#36b9cc', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }

    const srcData = @json($incomeSourceDist);
    if (Object.keys(srcData).length > 0) {
        new Chart(document.getElementById('incomeSourceChart'), {
            type: 'pie',
            data: { labels: Object.keys(srcData), datasets: [{ data: Object.values(srcData).map(v => v.count), backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }

    const expData = @json($experienceDist);
    if (Object.keys(expData).length > 0) {
        new Chart(document.getElementById('experienceChart'), {
            type: 'bar',
            data: { labels: Object.keys(expData), datasets: [{ label: 'Residents', data: Object.values(expData).map(v => v.count), backgroundColor: '#858796', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
</script>
@endpush
