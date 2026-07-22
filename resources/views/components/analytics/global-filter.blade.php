@props([
    'action' => '',
    'method' => 'GET',
    'villages' => [],
    'selectedVillage' => '',
    'showDateRange' => true,
    'showDemographics' => false,
    'showEmployment' => false,
    'showIncome' => false,
    'compact' => false,
])

@php
    $filters = request()->all();
@endphp

<div class="card shadow-sm mb-4 {{ $compact ? '' : 'border-left border-primary' }}">
    <div class="card-header py-2 d-flex align-items-center justify-content-between bg-light">
        <h6 class="m-0 fw-bold text-primary">
            <i class="bi bi-funnel me-2"></i>Global Filters
        </h6>
        <a href="{{ request()->route()->getName() ? route(request()->route()->getName()) : request()->url() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Clear All
        </a>
    </div>
    <div class="card-body py-3">
        <form action="{{ $action }}" method="{{ $method }}" id="globalFilterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Village</label>
                    <select name="village_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Villages</option>
                        @foreach($villages as $v)
                            <option value="{{ $v->id }}" {{ ($selectedVillage == $v->id || request('village_id') == $v->id) ? 'selected' : '' }}>
                                {{ $v->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($showDateRange)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" onchange="this.form.submit()">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" onchange="this.form.submit()">
                </div>
                @endif

                @if($showDemographics)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Gender</label>
                    <select name="gender" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Age Group</label>
                    <select name="age_group" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Ages</option>
                        <option value="0-5" {{ request('age_group') === '0-5' ? 'selected' : '' }}>Infant (0-5)</option>
                        <option value="6-17" {{ request('age_group') === '6-17' ? 'selected' : '' }}>Child (6-17)</option>
                        <option value="18-30" {{ request('age_group') === '18-30' ? 'selected' : '' }}>Young Adult (18-30)</option>
                        <option value="31-59" {{ request('age_group') === '31-59' ? 'selected' : '' }}>Adult (31-59)</option>
                        <option value="60+" {{ request('age_group') === '60+' ? 'selected' : '' }}>Senior (60+)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Civil Status</label>
                    <select name="civil_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Single" {{ request('civil_status') === 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ request('civil_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Widowed" {{ request('civil_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ request('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                        <option value="Divorced" {{ request('civil_status') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                    </select>
                </div>
                @endif

                @if($showEmployment)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Employment</label>
                    <select name="employment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Employed" {{ request('employment_status') === 'Employed' ? 'selected' : '' }}>Employed</option>
                        <option value="Unemployed" {{ request('employment_status') === 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                        <option value="Self-Employed" {{ request('employment_status') === 'Self-Employed' ? 'selected' : '' }}>Self-Employed</option>
                        <option value="Retired" {{ request('employment_status') === 'Retired' ? 'selected' : '' }}>Retired</option>
                        <option value="Student" {{ request('employment_status') === 'Student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
                @endif

                @if($showIncome)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="form-label small fw-semibold">Income Range</label>
                    <select name="income_range" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Below ₱5,000" {{ request('income_range') === 'Below ₱5,000' ? 'selected' : '' }}>Below ₱5,000</option>
                        <option value="₱5,000 - ₱10,000" {{ request('income_range') === '₱5,000 - ₱10,000' ? 'selected' : '' }}>₱5,000 - ₱10,000</option>
                        <option value="₱10,001 - ₱20,000" {{ request('income_range') === '₱10,001 - ₱20,000' ? 'selected' : '' }}>₱10,001 - ₱20,000</option>
                        <option value="₱20,001 - ₱40,000" {{ request('income_range') === '₱20,001 - ₱40,000' ? 'selected' : '' }}>₱20,001 - ₱40,000</option>
                        <option value="₱40,001 - ₱60,000" {{ request('income_range') === '₱40,001 - ₱60,000' ? 'selected' : '' }}>₱40,001 - ₱60,000</option>
                        <option value="Above ₱60,000" {{ request('income_range') === 'Above ₱60,000' ? 'selected' : '' }}>Above ₱60,000</option>
                    </select>
                </div>
                @endif

                @if(!$showDemographics && !$showEmployment && !$showIncome)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i>Apply
                    </button>
                </div>
                @endif
            </div>

            @if(count(array_filter(request()->except(['page', 'q']))) > 0)
            <div class="mt-2 d-flex flex-wrap gap-1">
                @foreach(request()->except(['page', 'q']) as $key => $value)
                    @if($value && !is_array($value))
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        {{ ucwords(str_replace('_', ' ', $key)) }}: {{ $value }}
                        <a href="{{ request()->fullUrlWithQuery([$key => null]) }}" class="text-primary ms-1"><i class="bi bi-x"></i></a>
                    </span>
                    @endif
                @endforeach
            </div>
            @endif
        </form>
    </div>
</div>
