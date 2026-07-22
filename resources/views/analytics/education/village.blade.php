@extends('layouts.app')

@section('title', 'Education Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-mortarboard me-2"></i>{{ $village->village_name }} — Education Analytics
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.education.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Residents" :value="number_format($summary['total_residents'])" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Education Rate" :value="$summary['education_rate'] . '%'" icon="bi-mortarboard-fill" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Enrolled" :value="number_format($summary['currently_enrolled'])" icon="bi-book-fill" color="info" :large="true" subtitle="{{ $summary['enrollment_rate'] }}%" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Scholars" :value="number_format($summary['scholars'])" icon="bi-award-fill" color="warning" :large="true" subtitle="{{ $summary['scholarship_rate'] }}%" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Educational Attainment" icon="bi-bar-chart" color="primary" canvasId="attainmentChart" />
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Education Level" icon="bi-pie-chart" color="success" canvasId="levelChart" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Schools" icon="bi-building" color="info" canvasId="schoolChart" />
    </div>
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-warning"><i class="bi bi-award me-2"></i>Scholarship by Attainment</h6>
            </div>
            <div class="card-body">
                @if(count($scholarshipByAttainment) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Attainment</th><th class="text-center">Total</th><th class="text-center">Scholars</th><th class="text-center">Rate</th></tr>
                        </thead>
                        <tbody>
                            @foreach($scholarshipByAttainment as $att => $data)
                            <tr>
                                <td>{{ $att }}</td>
                                <td class="text-center">{{ $data['total'] }}</td>
                                <td class="text-center">{{ $data['with_scholarship'] }}</td>
                                <td class="text-center"><span class="badge bg-{{ $data['scholarship_rate'] > 20 ? 'success' : 'secondary' }}">{{ $data['scholarship_rate'] }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-muted text-center py-4">No data</p>
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

    const attainmentData = @json($attainmentDist);
    if (Object.keys(attainmentData).length > 0) {
        new Chart(document.getElementById('attainmentChart'), {
            type: 'bar',
            data: { labels: Object.keys(attainmentData), datasets: [{ label: 'Residents', data: Object.values(attainmentData).map(v => v.count), backgroundColor: colors, borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const levelData = @json($educationLevelDist);
    if (Object.keys(levelData).length > 0) {
        new Chart(document.getElementById('levelChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(levelData), datasets: [{ data: Object.values(levelData).map(v => v.count), backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    }

    const schoolData = @json($schoolDist);
    if (Object.keys(schoolData).length > 0) {
        new Chart(document.getElementById('schoolChart'), {
            type: 'bar',
            data: { labels: Object.keys(schoolData), datasets: [{ label: 'Students', data: Object.values(schoolData).map(v => v.count), backgroundColor: '#36b9cc', borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
</script>
@endpush
