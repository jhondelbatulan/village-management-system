@extends('layouts.app')

@section('title', 'Community Development Indicators')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-graph-up-arrow me-2"></i>Community Development Indicators
        </h1>
        <p class="text-muted mb-0">Development Score & Socio-Economic Indicators · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.community-development.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>System View
            </a>
        @endif
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('analytics.community-development.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('analytics.community-development.export', 'pdf') }}"><i class="bi bi-filetype-pdf me-2 text-danger"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <x-analytics.progress-card
            title="Development Score"
            :value="$devScore['overall_score'] ?? 0"
            max="100"
            :color="($devScore['overall_score'] ?? 0) >= 70 ? 'success' : (($devScore['overall_score'] ?? 0) >= 50 ? 'warning' : 'danger')"
            :label="$devScore['rating'] ?? ''"
            icon="bi-graph-up-arrow"
        />
    </div>
    <div class="col-xl-4 col-md-6">
        <x-analytics.progress-card
            title="Socio-Economic Health"
            :value="$socioScore['overall_score'] ?? 0"
            max="100"
            :color="($socioScore['overall_score'] ?? 0) >= 70 ? 'success' : (($socioScore['overall_score'] ?? 0) >= 50 ? 'warning' : 'danger')"
            :label="$socioScore['rating'] ?? ''"
            icon="bi-heart-pulse"
        />
    </div>
    <div class="col-xl-4 col-md-6">
        <x-analytics.progress-card
            title="Community Readiness"
            :value="$readiness['overall_score'] ?? 0"
            max="100"
            :color="($readiness['overall_score'] ?? 0) >= 70 ? 'success' : (($readiness['overall_score'] ?? 0) >= 50 ? 'warning' : 'danger')"
            :label="$readiness['rating'] ?? ''"
            icon="bi-rocket-takeoff"
        />
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Development Indicators</h6>
    </div>
    <div class="card-body">
        @if(count($indicators) > 0)
        <div class="row g-3">
            @foreach($indicators as $indicator)
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card shadow-sm h-100 text-center py-3 border-left border-primary">
                    <div class="small fw-bold text-primary text-uppercase mb-1">{{ $indicator['label'] ?? '' }}</div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $indicator['formatted'] ?? $indicator['value'] ?? '' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <p class="text-muted text-center py-4">No indicator data available</p>
        @endif
    </div>
</div>

@if(!$villageId && count($villageRankings) > 0)
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-success"><i class="bi bi-trophy me-2"></i>Village Rankings</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th class="text-center" style="width:60px;">Rank</th><th>Village</th><th class="text-center">Development Score</th><th class="text-center">Rating</th></tr>
                </thead>
                <tbody>
                    @foreach($villageRankings as $rank)
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-{{ ($rank['rank'] ?? $loop->iteration) <= 3 ? 'warning' : 'secondary' }} rounded-circle" style="width:24px;height:24px;line-height:18px;">
                                {{ $rank['rank'] ?? $loop->iteration }}
                            </span>
                        </td>
                        <td><a href="{{ route('analytics.community-development.village', $rank['village_id'] ?? 0) }}">{{ $rank['village_name'] ?? '' }}</a></td>
                        <td class="text-center"><span class="badge bg-{{ ($rank['score'] ?? 0) >= 70 ? 'success' : (($rank['score'] ?? 0) >= 50 ? 'warning' : 'danger') }}">{{ number_format($rank['score'] ?? 0, 1) }}</span></td>
                        <td class="text-center">{{ $rank['rating'] ?? '' }}</td>
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
        <x-analytics.quick-insights title="Development Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];
</script>
@endpush
