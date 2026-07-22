@extends('layouts.app')

@section('title', 'Demographic Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-grid me-2"></i>Demographic Dashboard
        </h1>
        <p class="text-muted mb-0">Population & Household Demographics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.demographic.index') }}" class="btn btn-sm btn-outline-secondary">
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
        <x-analytics.summary-card title="Total Population" :value="number_format($summary['total_population'] ?? 0)" icon="bi-people-fill" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Households" :value="number_format($summary['total_households'] ?? 0)" icon="bi-house-door-fill" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg Household Size" :value="number_format($summary['average_household_size'] ?? 0, 2)" icon="bi-people" color="info" :large="true" subtitle="members" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Senior Citizens" :value="number_format($summary['seniors'] ?? 0)" icon="bi-person-workspace" color="warning" :large="true" subtitle="{{ number_format($summary['senior_pct'] ?? 0, 1) }}%" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Male" :value="number_format($summary['male'] ?? 0)" icon="bi-gender-male" color="primary" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Female" :value="number_format($summary['female'] ?? 0)" icon="bi-gender-female" color="danger" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Youth (15-30)" :value="number_format($summary['youth'] ?? 0)" icon="bi-person-video" color="success" subtitle="{{ number_format($summary['youth_pct'] ?? 0, 1) }}%" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Children (<15)" :value="number_format($summary['children'] ?? 0)" icon="bi-emoji-smile" color="info" subtitle="{{ number_format($summary['children_pct'] ?? 0, 1) }}%" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Gender Distribution" icon="bi-gender-ambiguous" color="info" canvasId="genderChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Age Distribution (Fine)" icon="bi-bar-chart" color="success" canvasId="fineAgeChart" height="300px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Population Pyramid" icon="bi-bar-chart-steps" color="primary" canvasId="pyramidChart" height="350px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Civil Status Distribution" icon="bi-heart" color="danger" canvasId="civilChart" height="300px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Household Size Distribution" icon="bi-house-door" color="warning" canvasId="householdSizeChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Residency Duration" icon="bi-clock-history" color="secondary" canvasId="residencyChart" height="300px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Special Population" icon="bi-people-fill" color="primary" canvasId="specialChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Demographic Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>

@if(!$villageId && count($villageComparison) > 0)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Population by Village" icon="bi-bar-chart" color="primary" canvasId="villagePopChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Households by Village" icon="bi-house-door" color="success" canvasId="villageHHChart" height="300px" />
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Gender</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0"><thead class="table-light"><tr><th class="small">Gender</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                <tbody>@foreach($genderDist['data'] as $item)<tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>@endforeach</tbody></table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="bi bi-clipboard-data me-2"></i>Civil Status</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0"><thead class="table-light"><tr><th class="small">Status</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                <tbody>@foreach($civilDist['data'] as $item)<tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>@endforeach</tbody></table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-warning"><i class="bi bi-clipboard-data me-2"></i>Residency</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0"><thead class="table-light"><tr><th class="small">Duration</th><th class="small text-end">Freq</th><th class="small text-end">%</th></tr></thead>
                <tbody>@foreach($residencyDist['data'] as $item)<tr><td class="small fw-semibold">{{ $item['label'] }}</td><td class="small text-end">{{ $item['count'] }}</td><td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td></tr>@endforeach</tbody></table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($residencyDist['average_years'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Years of Residency</small>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($residencyDist['new_residents'] ?? 0) }}</div>
            <small class="text-muted">New Residents (1 Year)</small>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm text-center py-3">
            <div class="text-info fw-bold h4 mb-0">{{ number_format($residencyDist['long_term_residents'] ?? 0) }}</div>
            <small class="text-muted">Long-term (5+ Years)</small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d', purple: '#6f42c1', pink: '#d63384', teal: '#20c997', orange: '#fd7e14' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, colors.purple, colors.pink, colors.teal, colors.orange];

    const gCtx = document.getElementById('genderChart');
    if (gCtx) new Chart(gCtx, { type: 'pie', data: { labels: {!! json_encode($genderDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($genderDist['values'] ?? []) !!}, backgroundColor: [colors.primary, colors.danger, colors.secondary], borderWidth: 2, borderColor: '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });

    const faCtx = document.getElementById('fineAgeChart');
    if (faCtx) new Chart(faCtx, { type: 'bar', data: { labels: {!! json_encode($fineAgeDist['labels'] ?? []) !!}, datasets: [{ label: 'Residents', data: {!! json_encode($fineAgeDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($fineAgeDist['labels'] ?? [])) !!}), borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const pyCtx = document.getElementById('pyramidChart');
    if (pyCtx) new Chart(pyCtx, { type: 'bar', data: { labels: {!! json_encode($fineAgeDist['labels'] ?? []) !!}, datasets: [{ label: 'Male', data: {!! json_encode($fineAgeDist['male_labels'] ?? []) !!}, backgroundColor: colors.primary, borderRadius: 4 }, { label: 'Female', data: {!! json_encode($fineAgeDist['female_labels'] ?? []) !!}, backgroundColor: colors.danger, borderRadius: 4 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const cCtx = document.getElementById('civilChart');
    if (cCtx) new Chart(cCtx, { type: 'doughnut', data: { labels: {!! json_encode($civilDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($civilDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($civilDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });

    const hsCtx = document.getElementById('householdSizeChart');
    if (hsCtx) new Chart(hsCtx, { type: 'doughnut', data: { labels: {!! json_encode($householdDist['labels'] ?? []) !!}, datasets: [{ data: {!! json_encode($householdDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($householdDist['labels'] ?? [])) !!}), borderWidth: 2, borderColor: '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });

    const rCtx = document.getElementById('residencyChart');
    if (rCtx) new Chart(rCtx, { type: 'bar', data: { labels: {!! json_encode($residencyDist['labels'] ?? []) !!}, datasets: [{ label: 'Residents', data: {!! json_encode($residencyDist['values'] ?? []) !!}, backgroundColor: palette.slice(0, {!! json_encode(count($residencyDist['labels'] ?? [])) !!}), borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const spCtx = document.getElementById('specialChart');
    if (spCtx) new Chart(spCtx, { type: 'bar', data: { labels: ['Senior Citizens', 'Youth', 'Children', 'PWD', 'Solo Parents'], datasets: [{ label: 'Population', data: [{!! json_encode($specialPop['senior_citizens']['count'] ?? 0) !!}, {!! json_encode($specialPop['youth']['count'] ?? 0) !!}, {!! json_encode($specialPop['children']['count'] ?? 0) !!}, {!! json_encode($specialPop['pwd']['count'] ?? 0) !!}, {!! json_encode($specialPop['solo_parents']['count'] ?? 0) !!}], backgroundColor: [colors.warning, colors.success, colors.info, colors.purple, colors.pink], borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const vpCtx = document.getElementById('villagePopChart');
    if (vpCtx) new Chart(vpCtx, { type: 'bar', data: { labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!}, datasets: [{ label: 'Population', data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total_population'])->toArray()) !!}, backgroundColor: colors.primary, borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

    const vhCtx = document.getElementById('villageHHChart');
    if (vhCtx) new Chart(vhCtx, { type: 'bar', data: { labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!}, datasets: [{ label: 'Households', data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total_households'])->toArray()) !!}, backgroundColor: colors.success, borderRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
});

function applyFilters() {
    const village = document.getElementById('filterVillage').value;
    if (village) {
        window.location.href = '{{ route("analytics.demographic.village", ":id") }}'.replace(':id', village);
    } else {
        window.location.href = '{{ route("analytics.demographic.index") }}';
    }
}
</script>
@endpush
