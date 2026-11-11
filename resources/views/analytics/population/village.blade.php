@extends('layouts.app')

@section('title', $village->village_name . ' — Population Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-people me-2"></i>{{ $village->village_name }} Population Analytics
        </h1>
        <p class="text-muted mb-0">Village-level descriptive statistical analysis · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.population.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>System View
    </a>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Total Population"
            :value="number_format($summary['total_population'] ?? 0)"
            icon="bi-people-fill"
            color="primary"
            :change="$summary['population_growth'] ?? null"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Approved"
            :value="number_format($summary['approved'] ?? 0)"
            icon="bi-check-circle"
            color="success"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Pending"
            :value="number_format($summary['pending'] ?? 0)"
            icon="bi-hourglass-split"
            color="warning"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Rejected"
            :value="number_format($summary['rejected'] ?? 0)"
            icon="bi-x-circle"
            color="danger"
            :large="true"
        />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Today" :value="number_format($summary['registered_today'] ?? 0)" icon="bi-calendar-check" color="info" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="This Week" :value="number_format($summary['registered_this_week'] ?? 0)" icon="bi-calendar-week" color="primary" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="This Month" :value="number_format($summary['registered_this_month'] ?? 0)" icon="bi-calendar-month" color="success" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="This Year" :value="number_format($summary['registered_this_year'] ?? 0)" icon="bi-calendar" color="warning" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Avg/Month" :value="number_format($summary['avg_monthly_registration'] ?? 0, 1)" icon="bi-graph-up" color="info" />
    </div>
    <div class="col-md-2 col-4">
        <x-analytics.summary-card title="Avg/Year" :value="number_format($summary['avg_annual_registration'] ?? 0, 1)" icon="bi-graph-up-arrow" color="secondary" />
    </div>
</div>

{{-- Charts --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Gender Distribution" icon="bi-gender-ambiguous" color="info" canvasId="genderChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Age Distribution" icon="bi-bar-chart" color="success" canvasId="ageChart" height="280px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Monthly Registration Trend" icon="bi-graph-up" color="primary" canvasId="regTrendChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Size Distribution" icon="bi-house-door" color="warning" canvasId="hhSizeChart" height="280px" />
    </div>
</div>

{{-- Frequency Tables --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Gender Frequency Distribution</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Gender</th><th class="small text-end">Frequency</th><th class="small text-end">Percentage</th></tr>
                    </thead>
                    <tbody>
                        @foreach($genderDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success"><i class="bi bi-clipboard-data me-2"></i>Age Group Frequency Distribution</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="small">Age Group</th><th class="small text-end">Frequency</th><th class="small text-end">Percentage</th></tr>
                    </thead>
                    <tbody>
                        @foreach($ageDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="bi bi-clipboard-data me-2"></i>Civil Status</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Status</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($civilDist['data'] as $item)
                            <tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-info"><i class="bi bi-clipboard-data me-2"></i>Education</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Level</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($educationDist['data'] as $item)
                            <tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-secondary"><i class="bi bi-clipboard-data me-2"></i>Employment</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="small">Status</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                    <tbody>
                        @foreach($employmentDist['data'] as $item)
                            <tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Averages --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($householdDist['average_size'] ?? 0, 2) }}</div>
            <small class="text-muted">Avg Household Size</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($averageAge ?? 0, 1) }}</div>
            <small class="text-muted">Average Age</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-info fw-bold h4 mb-0">{{ number_format($summary['avg_monthly_registration'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Monthly Registration</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-warning fw-bold h4 mb-0">{{ number_format($summary['avg_annual_registration'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Annual Registration</small>
        </div>
    </div>
</div>

{{-- Growth + Insights --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Yearly Trend" icon="bi-graph-up-arrow" color="success" canvasId="yearlyTrendChart" height="280px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Village Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d', purple: '#6f42c1', pink: '#d63384', teal: '#20c997', orange: '#fd7e14' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, colors.purple, colors.pink, colors.teal, colors.orange];

    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) new Chart(genderCtx, {
        type: 'pie',
        data: { labels: {!! json_encode($genderDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($genderDist['values'] ?? []) !!}, backgroundColor: [colors.primary, colors.danger, colors.secondary], borderWidth: 2, borderColor: '#fff' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    const ageCtx = document.getElementById('ageChart');
    if (ageCtx) new Chart(ageCtx, {
        type: 'bar',
        data: { labels: {!! json_encode($ageDist['labels'] ?? []) !!}, datasets: [{ label: 'Residents', data: {!! json_encode($ageDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($ageDist['labels'] ?? [])) !!}), borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    const regCtx = document.getElementById('regTrendChart');
    if (regCtx) {
        const mL = {!! json_encode(array_keys($monthlyTrend ?? [])) !!};
        const mD = {!! json_encode(array_values($monthlyTrend ?? [])) !!};
        new Chart(regCtx, {
            type: 'line',
            data: { labels: mL.map(m => { const [y,mo] = m.split('-'); return new Date(y,mo-1).toLocaleString('default',{month:'short',year:'2-digit'}); }), datasets: [{ label: 'Registrations', data: mD, borderColor: colors.primary, backgroundColor: colors.primary+'20', fill: true, tension: 0.4, pointRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const hhCtx = document.getElementById('hhSizeChart');
    if (hhCtx) new Chart(hhCtx, {
        type: 'doughnut',
        data: { labels: {!! json_encode($householdDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($householdDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($householdDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    const ytCtx = document.getElementById('yearlyTrendChart');
    if (ytCtx) {
        const yL = {!! json_encode(array_keys($yearlyTrend ?? [])) !!};
        const yD = {!! json_encode(array_values($yearlyTrend ?? [])) !!};
        new Chart(ytCtx, {
            type: 'line',
            data: { labels: yL, datasets: [{ label: 'Annual', data: yD, borderColor: colors.success, backgroundColor: colors.success+'15', fill: true, tension: 0.4, pointRadius: 5, borderWidth: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
});
</script>
@endpush
