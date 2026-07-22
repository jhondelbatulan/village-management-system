@extends('layouts.app')

@section('title', 'Education Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-mortarboard me-2"></i>Education Analytics
        </h1>
        <p class="text-muted mb-0">Education & Scholarship Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.education.index') }}" class="btn btn-sm btn-outline-secondary">
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
        <x-analytics.summary-card title="Total Residents" :value="number_format($summary['total_residents'])" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Education Rate" :value="$summary['education_rate'] . '%'" icon="bi-mortarboard-fill" color="success" :large="true" subtitle="with educational attainment" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Currently Enrolled" :value="number_format($summary['currently_enrolled'])" icon="bi-book-fill" color="info" :large="true" subtitle="{{ $summary['enrollment_rate'] }}%" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Scholars" :value="number_format($summary['scholars'])" icon="bi-award-fill" color="warning" :large="true" subtitle="{{ $summary['scholarship_rate'] }}%" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Students" :value="number_format($summary['students'])" icon="bi-person-workspace" color="danger" subtitle="{{ $summary['student_rate'] }}% of residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Scholarship Rate" :value="$summary['scholarship_rate'] . '%'" icon="bi-award" color="primary" subtitle="of educated residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Enrollment Rate" :value="$summary['enrollment_rate'] . '%'" icon="bi-journal-bookmark" color="info" subtitle="currently enrolled" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Educated Count" :value="number_format($summary['educated'])" icon="bi-check-circle" color="success" subtitle="with recorded attainment" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="Educational Attainment Distribution" icon="bi-bar-chart" color="primary" canvasId="attainmentChart">
            @if(count($attainmentDist) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
    <div class="col-xl-6">
        <x-analytics.chart-card title="Current Education Level" icon="bi-pie-chart" color="success" canvasId="educationLevelChart">
            @if(count($educationLevelDist) > 0)
            @else
                <p class="text-muted text-center py-4">No data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <x-analytics.chart-card title="School Enrollment Distribution" icon="bi-building" color="info" canvasId="schoolChart">
            @if(count($schoolDist) > 0)
            @else
                <p class="text-muted text-center py-4">No school data available</p>
            @endif
        </x-analytics.chart-card>
    </div>
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-warning">
                    <i class="bi bi-award me-2"></i>Scholarship by Attainment
                </h6>
            </div>
            <div class="card-body">
                @if(count($scholarshipByAttainment) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Attainment</th><th class="text-center">Total</th><th class="text-center">Scholarship</th><th class="text-center">Rate</th></tr>
                        </thead>
                        <tbody>
                            @foreach($scholarshipByAttainment as $att => $data)
                            <tr>
                                <td>{{ $att }}</td>
                                <td class="text-center">{{ $data['total'] }}</td>
                                <td class="text-center">{{ $data['with_scholarship'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $data['scholarship_rate'] > 20 ? 'success' : 'secondary' }}">{{ $data['scholarship_rate'] }}%</span>
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
                    <tr><th>Village</th><th class="text-center">Total</th><th class="text-center">Education Rate</th><th class="text-center">Enrollment</th><th class="text-center">Scholarship</th><th class="text-center">Students</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.education.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ $data['total'] }}</td>
                        <td class="text-center"><span class="badge bg-primary">{{ $data['education_rate'] }}%</span></td>
                        <td class="text-center">{{ $data['enrollment_rate'] }}%</td>
                        <td class="text-center">{{ $data['scholarship_rate'] }}%</td>
                        <td class="text-center">{{ $data['student_rate'] }}%</td>
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
    const colorsAlpha = colors.map(c => c + '33');

    function applyFilters() {
        const villageId = document.getElementById('filterVillage').value;
        if (villageId) {
            window.location.href = '{{ route("analytics.education.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.education.index") }}';
        }
    }

    // Attainment Chart
    const attainmentData = @json($attainmentDist);
    if (Object.keys(attainmentData).length > 0) {
        new Chart(document.getElementById('attainmentChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(attainmentData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(attainmentData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(attainmentData).length),
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

    // Education Level Chart
    const levelData = @json($educationLevelDist);
    if (Object.keys(levelData).length > 0) {
        new Chart(document.getElementById('educationLevelChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(levelData),
                datasets: [{
                    data: Object.values(levelData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(levelData).length)
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // School Chart
    const schoolData = @json($schoolDist);
    if (Object.keys(schoolData).length > 0) {
        new Chart(document.getElementById('schoolChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(schoolData),
                datasets: [{
                    label: 'Students',
                    data: Object.values(schoolData).map(v => v.count),
                    backgroundColor: '#36b9cc',
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
