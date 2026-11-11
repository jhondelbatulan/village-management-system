@extends('layouts.app')

@section('title', 'Executive Intelligence Center')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-lightbulb me-2"></i>Executive Intelligence Center
    </h1>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('analytics.executive-intelligence.export', 'csv') }}"><i class="bi bi-filetype-csv me-2"></i>CSV</a></li>
            <li><a class="dropdown-item" href="{{ route('analytics.executive-intelligence.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2"></i>PDF</a></li>
        </ul>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <x-analytics.summary-card title="Total Insights" :value="count($topInsights)" icon="bi-lightbulb" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <x-analytics.summary-card title="High Priority" :value="collect($topInsights)->where('priority', 'high')->count()" icon="bi-exclamation-triangle" color="danger" />
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <x-analytics.summary-card title="Villages Analyzed" :value="count($villageHighlights)" icon="bi-geo-alt" color="info" />
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <x-analytics.summary-card title="Total Villages" :value="$comparativeAnalysis['total_villages'] ?? 0" icon="bi-buildings" color="success" />
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <x-analytics.chart-card title="Priority Insights" icon="bi-lightbulb">
            @forelse($topInsights as $index => $insight)
                @php
                    $priorityClass = match($insight['priority'] ?? 'low') {
                        'high' => 'priority-danger',
                        'medium' => 'priority-warning',
                        default => 'priority-info',
                    };
                    $categoryClass = match($insight['category'] ?? 'general') {
                        'government' => 'bg-primary',
                        'healthcare' => 'bg-danger',
                        'social' => 'bg-warning text-dark',
                        'development' => 'bg-success',
                        'employment' => 'bg-info',
                        'education' => 'bg-primary',
                        'skills' => 'bg-secondary',
                        default => 'bg-dark',
                    };
                    $typeIcon = match($insight['type'] ?? 'info') {
                        'alert' => 'bi-exclamation-triangle text-danger',
                        'trend' => 'bi-graph-up text-success',
                        'concern' => 'bi-exclamation-circle text-warning',
                        default => 'bi-info-circle text-info',
                    };
                @endphp
                <div class="border-start border-4 {{ $priorityClass }} p-3 mb-3 bg-light rounded">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $typeIcon }}"></i>
                            <span class="badge {{ $categoryClass }}">{{ ucfirst($insight['category'] ?? 'general') }}</span>
                            <span class="badge {{ $priorityClass === 'priority-danger' ? 'bg-danger' : ($priorityClass === 'priority-warning' ? 'bg-warning text-dark' : 'bg-info') }}">{{ ucfirst($insight['priority'] ?? 'low') }}</span>
                        </div>
                        <small class="text-muted">#{{ $index + 1 }}</small>
                    </div>
                    <p class="mb-1 fw-semibold">{{ $insight['message'] ?? '' }}</p>
                    <p class="mb-0 small text-muted"><i class="bi bi-lightbulb me-1"></i>{{ $insight['recommendation'] ?? '' }}</p>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-1"></i>
                    <p class="mt-2">No priority insights detected</p>
                </div>
            @endforelse
        </x-analytics.chart-card>
    </div>
</div>

@if(!empty($villageHighlights))
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-geo-alt me-2"></i>Village Highlights</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-center">Population</th>
                        <th class="text-center">Employment Rate</th>
                        <th class="text-center">Avg Income</th>
                        <th class="text-center">Skill Coverage</th>
                        <th class="text-center">Education Rate</th>
                        <th class="text-center">Readiness</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villageHighlights as $vId => $highlight)
                    <tr>
                        <td><strong>{{ $highlight['village']['name'] ?? '' }}</strong> <small class="text-muted">({{ $highlight['village']['code'] ?? '' }})</small></td>
                        <td class="text-center">{{ number_format($highlight['population'] ?? 0) }}</td>
                        <td class="text-center">{{ $highlight['employment_rate'] ?? 0 }}%</td>
                        <td class="text-center">₱{{ number_format($highlight['average_income'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ $highlight['skill_coverage'] ?? 0 }}%</td>
                        <td class="text-center">{{ $highlight['education_rate'] ?? 0 }}%</td>
                        <td class="text-center">
                            <span class="badge bg-{{ ($highlight['readiness_score'] ?? 0) >= 70 ? 'success' : (($highlight['readiness_score'] ?? 0) >= 50 ? 'warning' : 'danger') }}">
                                {{ $highlight['readiness_rating'] ?? 'N/A' }} ({{ $highlight['readiness_score'] ?? 0 }})
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(!empty($comparativeAnalysis['rankings']))
<div class="row mb-4">
    <div class="col-12">
        <x-analytics.chart-card title="Comparative Analysis Rankings" icon="bi-bar-chart">
            @php $rankings = $comparativeAnalysis['rankings'] ?? []; @endphp
            @if(!empty($rankings['by_employment']))
            <h6 class="px-3 mt-2 mb-2 text-primary"><i class="bi bi-briefcase me-1"></i>By Employment Rate</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="text-center">#</th><th>Village</th><th class="text-center">Employment Rate</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rankings['by_employment'] as $i => $r)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $r['village_name'] ?? '' }}</td>
                            <td class="text-center">{{ $r['employment_rate'] ?? 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($rankings['by_readiness']))
            <h6 class="px-3 mt-2 mb-2 text-success"><i class="bi bi-check-circle me-1"></i>By Readiness Score</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th class="text-center">#</th><th>Village</th><th class="text-center">Readiness Score</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rankings['by_readiness'] as $i => $r)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $r['village_name'] ?? '' }}</td>
                            <td class="text-center">{{ $r['readiness_score'] ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($comparativeAnalysis['averages']))
            <h6 class="px-3 mt-2 mb-2 text-info"><i class="bi bi-calculator me-1"></i>System Averages</h6>
            <div class="row g-3 px-3 mb-3">
                @foreach($comparativeAnalysis['averages'] as $key => $avg)
                <div class="col-md-4 col-sm-6">
                    <div class="p-2 bg-light rounded text-center">
                        <small class="text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</small><br>
                        <strong>{{ number_format($avg, 1) }}{{ str_contains($key, 'rate') || str_contains($key, 'coverage') || str_contains($key, 'income') ? '%' : '' }}</strong>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-analytics.chart-card>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryCounts = @json(collect($topInsights)->pluck('category')->countBy()->toArray());
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categoryCounts),
                datasets: [{
                    data: Object.values(categoryCounts),
                    backgroundColor: ['#4e73df', '#e74a3b', '#f6c23e', '#1cc88a', '#36b9cc', '#858796', '#5a5c69']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
</script>
@endpush
@endsection
