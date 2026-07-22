@extends('layouts.app')

@section('title', 'Planning Analytics Search')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-3"><i class="bi bi-search me-2"></i>Planning Analytics Search</h4>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('analytics.planning.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="q" value="{{ $query }}" placeholder="Search indicators (e.g., dependency, density, growth, migration...)">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="village_id">
                            <option value="">All Villages</option>
                            @foreach($villages as $v)
                            <option value="{{ $v->id }}" {{ ($villageId ?? '') == $v->id ? 'selected' : '' }}>{{ $v->village_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($query)
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Dependency Ratio" :value="number_format($results['dependency']['total_dependency_ratio'] ?? 0, 1) . '%'" icon="bi-people-fill" color="warning" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Pop. Density" :value="number_format($results['density']['population_density'] ?? 0, 1) . '/km²'" icon="bi-geo-alt-fill" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Annual Growth" :value="number_format($results['growth']['annual_growth'] ?? 0, 1) . '%'" icon="bi-graph-up-arrow" color="success" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Stability Index" :value="number_format($results['household_stability']['stability_index'] ?? 0, 1)" icon="bi-house-door-fill" color="primary" />
        </div>
    </div>
    @endif
</div>
@endsection
