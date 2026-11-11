@extends('layouts.app')

@section('title', 'Social Welfare Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-people-fill me-2"></i>Social Welfare Analytics
        </h1>
        <p class="text-muted mb-0">Social Welfare & Beneficiary Statistics · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.social-welfare.index') }}" class="btn btn-sm btn-outline-secondary">
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
                <li><a class="dropdown-item" href="{{ route('analytics.social-welfare.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.social-welfare.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Senior Citizens" :value="number_format($summary['seniors'] ?? 0)" icon="bi-person-workspace" color="primary" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['seniors'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="PWD" :value="number_format($summary['pwd'] ?? 0)" icon="bi-universal-access" color="success" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['pwd'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Solo Parent" :value="number_format($summary['solo_parent'] ?? 0)" icon="bi-person-hearts" color="danger" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['solo_parent'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Indigenous" :value="number_format($summary['indigenous'] ?? 0)" icon="bi-leaf" color="warning" :large="true" subtitle="{{ $summary['total'] > 0 ? number_format(($summary['indigenous'] ?? 0) / $summary['total'] * 100, 1) : 0 }}% of total" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Veteran" :value="number_format($summary['veteran'] ?? 0)" icon="bi-award-fill" color="info" subtitle="residents" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Youth" :value="number_format($summary['youth'] ?? 0)" icon="bi-person-video" color="secondary" subtitle="residents" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Category Distribution" icon="bi-bar-chart" color="primary" canvasId="welfareChart" height="300px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Monthly Trends" icon="bi-graph-up" color="success" canvasId="trendChart" height="300px" />
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
                    <tr><th>Village</th><th class="text-center">Seniors</th><th class="text-center">PWD</th><th class="text-center">Solo Parent</th><th class="text-center">Indigenous</th><th class="text-center">Veteran</th><th class="text-center">Total</th></tr>
                </thead>
                <tbody>
                    @foreach($villageComparison as $name => $data)
                    <tr>
                        <td><a href="{{ route('analytics.social-welfare.village', $data['village_id'] ?? $name) }}">{{ $name }}</a></td>
                        <td class="text-center">{{ $data['seniors'] ?? 0 }}</td>
                        <td class="text-center">{{ $data['pwd'] ?? 0 }}</td>
                        <td class="text-center">{{ $data['solo_parent'] ?? 0 }}</td>
                        <td class="text-center">{{ $data['indigenous'] ?? 0 }}</td>
                        <td class="text-center">{{ $data['veteran'] ?? 0 }}</td>
                        <td class="text-center"><strong>{{ ($data['seniors'] ?? 0) + ($data['pwd'] ?? 0) + ($data['solo_parent'] ?? 0) + ($data['indigenous'] ?? 0) + ($data['veteran'] ?? 0) }}</strong></td>
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
        <x-analytics.quick-insights title="Welfare Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
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
            window.location.href = '{{ route("analytics.social-welfare.index") }}/village/' + villageId;
        } else {
            window.location.href = '{{ route("analytics.social-welfare.index") }}';
        }
    }

    const catData = @json($categoryDistribution);
    if (Object.keys(catData).length > 0) {
        new Chart(document.getElementById('welfareChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(catData),
                datasets: [{
                    label: 'Residents',
                    data: Object.values(catData).map(v => v.count),
                    backgroundColor: colors.slice(0, Object.keys(catData).length),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const trendData = @json($trend);
    if (Object.keys(trendData).length > 0) {
        const monthLabels = Object.keys(trendData);
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: 'Seniors', data: monthLabels.map(m => trendData[m].seniors ?? 0), borderColor: '#4e73df', backgroundColor: '#4e73df20', fill: false, tension: 0.4, pointRadius: 4 },
                    { label: 'PWD', data: monthLabels.map(m => trendData[m].pwd ?? 0), borderColor: '#1cc88a', backgroundColor: '#1cc88a20', fill: false, tension: 0.4, pointRadius: 4 },
                    { label: 'Solo Parent', data: monthLabels.map(m => trendData[m].solo_parent ?? 0), borderColor: '#e74a3b', backgroundColor: '#e74a3b20', fill: false, tension: 0.4, pointRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
</script>
@endpush
