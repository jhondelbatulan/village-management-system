@extends('layouts.app')

@section('title', 'Analytics Search')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-search me-2"></i>Analytics Search
    </h1>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form action="{{ route('analytics.search') }}" method="GET">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Search residents by name, code, or occupation..." value="{{ $results['query'] ?? '' }}">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light">
        <a class="text-decoration-none" data-bs-toggle="collapse" href="#advancedFilters" role="button" aria-expanded="false">
            <i class="bi bi-funnel me-2"></i>Advanced Filters
            <i class="bi bi-chevron-down float-end mt-1"></i>
        </a>
    </div>
    <div class="collapse" id="advancedFilters">
        <div class="card-body">
            <form action="{{ route('analytics.filter') }}" method="GET">
                @if(!empty($results['query']))
                    <input type="hidden" name="q" value="{{ $results['query'] }}">
                @endif
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Village</label>
                        <select name="village" class="form-select form-select-sm">
                            <option value="">All Villages</option>
                            @foreach($filterOptions['villages'] ?? [] as $villageId => $villageName)
                                <option value="{{ $villageId }}" {{ request('village') == $villageId ? 'selected' : '' }}>{{ $villageName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Program</label>
                        <select name="program" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($filterOptions['programs'] ?? [] as $program)
                                <option value="{{ $program }}" {{ request('program') === $program ? 'selected' : '' }}>{{ $program }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Membership</label>
                        <select name="membership" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['memberships'] ?? [] as $membership)
                                <option value="{{ $membership }}" {{ request('membership') === $membership ? 'selected' : '' }}>{{ $membership }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Employment Status</label>
                        <select name="employment_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['employment_statuses'] ?? [] as $status)
                                <option value="{{ $status }}" {{ request('employment_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Education</label>
                        <select name="education" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['education_levels'] ?? [] as $edu)
                                <option value="{{ $edu }}" {{ request('education') === $edu ? 'selected' : '' }}>{{ $edu }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Income Range</label>
                        <select name="income_range" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['income_ranges'] ?? [] as $range)
                                <option value="{{ $range }}" {{ request('income_range') === $range ? 'selected' : '' }}>{{ $range }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Skills</label>
                        <select name="skills" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['skills'] ?? [] as $skill)
                                <option value="{{ $skill }}" {{ request('skills') === $skill ? 'selected' : '' }}>{{ $skill }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Healthcare</label>
                        <select name="healthcare" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['healthcare_coverage'] ?? [] as $health)
                                <option value="{{ $health }}" {{ request('healthcare') === $health ? 'selected' : '' }}>{{ $health }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Resident Type</label>
                        <select name="resident_type" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($filterOptions['resident_types'] ?? [] as $type)
                                <option value="{{ $type }}" {{ request('resident_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Apply</button>
                        <a href="{{ route('analytics.search') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($results) && $results['total'] > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-gray-800 mb-0">Results</h5>
        <span class="badge bg-primary fs-6">{{ $results['total'] }} found</span>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Resident Code</th>
                        <th>Name</th>
                        <th>Village</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Occupation</th>
                        <th>Skills</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results['residents'] as $resident)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $resident->resident_code ?? '' }}</span></td>
                            <td>
                                <strong>{{ $resident->first_name ?? '' }} {{ $resident->last_name ?? '' }}</strong>
                            </td>
                            <td>{{ $resident->village?->village_name ?? '' }}</td>
                            <td>{{ $resident->age ?? '' }}</td>
                            <td>
                                <span class="badge {{ ($resident->gender ?? '') === 'Male' ? 'bg-info' : 'bg-pink' }}">
                                    {{ $resident->gender ?? '' }}
                                </span>
                            </td>
                            <td>{{ $resident->occupation ?? '—' }}</td>
                            <td>
                                @foreach(explode(',', $resident->skills ?? '') as $skill)
                                    @if(trim($skill) !== '')
                                        <span class="badge bg-light text-dark border">{{ trim($skill) }}</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('residents.show', $resident->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No residents match your search criteria
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif(isset($results) && !empty($results['query']))
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-search fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No results found</h5>
            <p class="text-muted">Try adjusting your search query or filters</p>
        </div>
    </div>
@endif
@endsection