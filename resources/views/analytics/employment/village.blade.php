@extends('layouts.app')

@section('title', 'Employment Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-briefcase me-2"></i>{{ $village->village_name }} — Employment Analytics
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.employment.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Employment Rate" :value="$summary['employment_rate'] . '%'" icon="bi-briefcase-fill" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Unemployment" :value="$summary['unemployment_rate'] . '%'" icon="bi-x-circle" color="danger" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Average Income" :value="'₱' . number_format($summary['average_income'], 0)" icon="bi-cash-stack" color="info" :large="true" subtitle="monthly" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="OFWs" :value="number_format($summary['ofw'])" icon="bi-globe" color="warning" :large="true" subtitle="{{ $summary['ofw_rate'] }}%" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Employment Status" icon="bi-pie-chart" color="primary" canvasId="employmentChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Income Ranges" icon="bi-bar-chart" color="success" canvasId="incomeRangeChart" />
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
                <h6 class="m-0 fw-bold text-info"><i class="bi bi-gender-ambiguous me-2"></i>Income by Gender</h6>
            </div>
            <div class="card-body">
                @if(count($incomeByGender) > 0)
                <div class="row text-center">
                    @foreach($incomeByGender as $gender => $avg)
                    <div class="col-6">
                        <div class="p-3 rounded bg-{{ $gender === 'Male' ? 'primary' : 'danger' }}-soft">
                            <h5 class="mb-0 fw-bold">₱{{ number_format($avg, 0) }}</h5>
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

@if(count($insights) > 0)
<x-analytics.quick-insights :insights="$insights" />
@endif
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'];

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
