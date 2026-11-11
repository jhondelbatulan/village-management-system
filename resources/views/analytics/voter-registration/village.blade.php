@extends('layouts.app')

@section('title', 'Voter Registration Analytics - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-check2-square me-2"></i>{{ $village->village_name }} — Voter Registration
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.voter-registration.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
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
        <x-analytics.chart-card title="Registration by Gender" icon="bi-gender-ambiguous" color="info" canvasId="genderChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Registration Overview" icon="bi-pie-chart" color="success" canvasId="regChart" height="280px" />
    </div>
</div>

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
