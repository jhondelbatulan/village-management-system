@extends('layouts.app')

@section('title', 'Voter Registration Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-check2-square me-2"></i>Voter Registration Analytics
        </h1>
        <p class="text-muted mb-0">Voter Registration Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.voter-registration.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.voter-registration.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.voter-registration.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Voting Age Population" :value="number_format($summary['voting_age_population'] ?? 0)" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Registered Voters" :value="number_format($summary['registered_voters'] ?? 0)" icon="bi-check-circle-fill" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Not Registered" :value="number_format($summary['non_registered_voters'] ?? 0)" icon="bi-x-circle-fill" color="danger" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Registration Rate" :value="number_format($summary['registration_rate'] ?? 0, 1) . '%'" icon="bi-percent" color="info" :large="true" subtitle="of voting age population" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Non-Registered" :value="number_format($summary['non_registered_voters'] ?? 0)" icon="bi-person-x" color="warning" subtitle="eligible voters not registered" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Registration by Gender" icon="bi-gender-ambiguous" color="info" canvasId="genderChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Registration Overview" icon="bi-pie-chart" color="success" canvasId="regChart" height="300px" />
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
                    <tr><th>Village</th><th class="text-center">Voting Age</th><th class="text-center">Registered</th><th class="text-center">Rate</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.voter-registration.village', $data['village_id']) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ number_format($data['voting_age']) }}</td>
                        <td class="text-center">{{ number_format($data['registered']) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ ($data['rate'] ?? 0) >= 70 ? 'success' : (($data['rate'] ?? 0) >= 50 ? 'warning' : 'danger') }}">{{ number_format($data['rate'] ?? 0, 1) }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Registration Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

    function applyFilters() {
        const villageId = document.getElementById('filterVillage').value;
        if (villageId) {
            window.location.href = '{{ route("analytics.voter-registration.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.voter-registration.index") }}';
        }
    }

    const genderData = @json($registrationByGender);
    if (Object.keys(genderData).length > 0) {
        new Chart(document.getElementById('genderChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(genderData),
                datasets: [
                    { label: 'Registered', data: Object.values(genderData).map(v => v.registered), backgroundColor: '#1cc88a', borderRadius: 6 },
                    { label: 'Total Voting Age', data: Object.values(genderData).map(v => v.total), backgroundColor: '#4e73df', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    new Chart(document.getElementById('regChart'), {
        type: 'doughnut',
        data: {
            labels: ['Registered Voters', 'Non-Registered'],
            datasets: [{
                data: [{!! json_encode($summary['registered_voters'] ?? 0) !!}, {!! json_encode($summary['non_registered_voters'] ?? 0) !!}],
                backgroundColor: ['#1cc88a', '#e74a3b'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endpush
