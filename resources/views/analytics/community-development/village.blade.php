@extends('layouts.app')

@section('title', 'Community Development Indicators - ' . $village->village_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-graph-up-arrow me-2"></i>{{ $village->village_name }} — Community Development
        </h1>
        <p class="text-muted mb-0">{{ $village->village_code }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('analytics.community-development.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to System View
    </a>
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.quick-insights title="Development Insights" icon="bi-lightbulb" color="info" :insights="$insights" />
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];
</script>
@endpush
