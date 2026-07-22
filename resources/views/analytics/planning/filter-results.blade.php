@extends('layouts.app')

@section('title', 'Planning Analytics - Filtered Results')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-3"><i class="bi bi-funnel me-2"></i>Filtered Planning Results</h4>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('analytics.planning.filter') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" name="village_id">
                            <option value="">All Villages</option>
                            @foreach($villages as $v)
                            <option value="{{ $v->id }}" {{ ($filters['village_id'] ?? '') == $v->id ? 'selected' : '' }}>{{ $v->village_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="year">
                            <option value="">Year</option>
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="quarter">
                            <option value="">Quarter</option>
                            @foreach(['Q1', 'Q2', 'Q3', 'Q4'] as $q)
                            <option value="{{ $q }}" {{ ($filters['quarter'] ?? '') == $q ? 'selected' : '' }}>{{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="age_group">
                            <option value="">Age Group</option>
                            @foreach(['0-14 (Children)', '15-24 (Young Adult)', '25-44 (Adult)', '45-59 (Middle-Aged)', '60+ (Senior)'] as $ag)
                            <option value="{{ $ag }}" {{ ($filters['age_group'] ?? '') == $ag ? 'selected' : '' }}>{{ $ag }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply Filter</button>
                        <a href="{{ route('analytics.planning.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

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

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Avg Residency" :value="number_format($results['residency']['average_years'] ?? 0, 1) . ' yrs'" icon="bi-house-heart-fill" color="secondary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Migration Rate" :value="number_format($results['migration']['migration_rate'] ?? 0, 1) . '%'" icon="bi-arrow-left-right" color="info" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="HH Size" :value="number_format($results['household_stability']['avg_members'] ?? 0, 1)" icon="bi-house-door-fill" color="primary" />
        </div>
        <div class="col-xl-3 col-md-6">
            <x-analytics.summary-card title="Working Age %" :value="number_format($results['community_structure']['working_pct'] ?? 0, 1) . '%'" icon="bi-briefcase-fill" color="success" />
        </div>
    </div>
</div>
@endsection
