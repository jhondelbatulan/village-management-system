@extends('layouts.app')

@section('title', 'Super Admin Executive Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">System Executive Dashboard</h1>
        <p class="text-muted mb-0">Cross-village overview · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('villages.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-geo-alt me-1"></i>All Villages
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i>Users
        </a>
    </div>
</div>

@include('partials.village-photos-slideshow')

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Total Residents"
            :value="number_format($populationStats['total'] ?? 0)"
            icon="bi-people"
            color="primary"
            :previousValue="$populationStats['previous'] ?? null"
            :change="$populationStats['change'] ?? null"
            route="{{ route('residents.index') }}"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Active Households"
            :value="number_format($householdStats['active'] ?? 0)"
            icon="bi-house-door"
            color="success"
            :previousValue="$householdStats['previous'] ?? null"
            :change="$householdStats['change'] ?? null"
            route="{{ route('households.index') }}"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Monthly Collection"
            :value="number_format($paymentStats['this_month_collected'] ?? 0)"
            icon="bi-cash-stack"
            color="info"
            :previousValue="$paymentStats['previous'] ?? null"
            :change="$paymentStats['change'] ?? null"
            subtitle="Collected this month"
            route="{{ route('dashboard.super_admin.monthly-collection') }}"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Pending Approvals"
            :value="number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0))"
            icon="bi-hourglass-split"
            color="warning"
            subtitle="Awaiting action"
            route="{{ route('approvals.households') }}"
            :large="true"
        />
    </div>
</div>

@if(($dashboardWidgets['announcements'] ?? true) || ($dashboardWidgets['calendar'] ?? true))
<div class="row g-4 mb-4">
    @if($dashboardWidgets['announcements'] ?? true)
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Recent Announcements"
            icon="bi-megaphone"
            color="primary"
            valueLabel="Date"
            :items="$recentAnnouncements->map(fn($a) => ['name' => $a->title . ($a->village ? ' — ' . $a->village->village_name : ' — All Villages'), 'value' => $a->created_at->format('M d')])->toArray()"
            :maxItems="5"
        />
    </div>
    @endif
    @if($dashboardWidgets['calendar'] ?? true)
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Upcoming Events"
            icon="bi-calendar-event"
            color="success"
            valueLabel="Date"
            :items="$upcomingEvents->map(fn($e) => ['name' => $e->title . ($e->village ? ' — ' . $e->village->village_name : ' — All Villages'), 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d')])->toArray()"
            :maxItems="5"
        />
    </div>
    @endif
</div>
@endif

@if($dashboardWidgets['analytics_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-analytics.chart-card
            title="Population by Village"
            icon="bi-bar-chart"
            color="primary"
        >
        </x-analytics.chart-card>
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights
            title="System Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$insights"
        />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Village Population Ranking"
            icon="bi-trophy"
            color="warning"
            valueLabel="Residents"
            :items="$villageComparison ?? []"
            :maxItems="5"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Village Collection Ranking"
            icon="bi-cash-coin"
            color="success"
            valueLabel="Amount"
            :items="$paymentComparison ?? []"
            :maxItems="5"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['analytics_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Monthly Payment Trend"
            icon="bi-graph-up"
            color="success"
            canvasId="dashPaymentTrendChart"
            height="250px"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Participation by Village"
            icon="bi-clipboard-check"
            color="primary"
            canvasId="dashParticipationChart"
            height="250px"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['system_notices'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-warning">
                    <i class="bi bi-hourglass-split me-2"></i>Pending Approvals
                </h6>
                <a href="{{ route('approvals.households') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                @if($pendingHouseholds->count() > 0 || $pendingResidents->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pendingHouseholds as $household)
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2 border-start border-warning border-3">
                                <div>
                                    <span class="fw-semibold small">Household: {{ $household->lot_number }}</span>
                                    <br><small class="text-muted">Block {{ $household->block_number }} · {{ $household->village?->village_name ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-{{ $household->approval_status === 'admin_approved' ? 'info' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $household->approval_status)) }}
                                </span>
                            </div>
                        @endforeach
                        @foreach($pendingResidents as $resident)
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2 border-start border-info border-3">
                                <div>
                                    <span class="fw-semibold small">Resident: {{ $resident->full_name }}</span>
                                    <br><small class="text-muted">Household: {{ $resident->household?->lot_number ?? 'N/A' }} · {{ $resident->village?->village_name ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-{{ $resident->approval_status === 'admin_approved' ? 'info' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $resident->approval_status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                        <p class="text-muted small mt-2 mb-0">All approvals up to date</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($dashboardWidgets['participation_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.progress-card
            title="System Approval Rate"
            :value="$approvalStats['approval_rate'] ?? 0"
            :max="100"
            color="success"
            icon="bi-check2-circle"
            label="Households and residents approved"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.map-summary
            title="GIS Coverage"
            :mappedHouseholds="$overview['mapped_households'] ?? 0"
            :totalHouseholds="$overview['total_households'] ?? 0"
            :coveragePct="$overview['coverage_pct'] ?? 0"
            :villagesMapped="$overview['villages_mapped'] ?? 0"
            :totalVillages="$overview['total_villages'] ?? 0"
            route="{{ route('gis.index') }}"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['population_summary'] ?? true)
{{-- Population Analytics Section --}}
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-people me-2 text-primary"></i>Population Analytics
    </h5>
    <a href="{{ route('analytics.population.index') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Total Population"
            :value="number_format($populationSummary['total_population'] ?? 0)"
            icon="bi-people-fill"
            color="primary"
            :change="$populationSummary['population_growth'] ?? null"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Approved"
            :value="number_format($populationSummary['approved'] ?? 0)"
            icon="bi-check-circle"
            color="success"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Pending"
            :value="number_format($populationSummary['pending'] ?? 0)"
            icon="bi-hourglass-split"
            color="warning"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Today"
            :value="number_format($populationSummary['registered_today'] ?? 0)"
            icon="bi-calendar-check"
            color="info"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="This Month"
            :value="number_format($populationSummary['registered_this_month'] ?? 0)"
            icon="bi-calendar-month"
            color="success"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Avg Age"
            :value="number_format($averageAge ?? 0, 1)"
            icon="bi-person-lines-fill"
            color="secondary"
        />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-analytics.chart-card
            title="Population by Village"
            icon="bi-bar-chart"
            color="primary"
            canvasId="dashVillageChart"
            height="280px"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights
            title="Population Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$populationInsights"
        />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Villages by Population"
            icon="bi-trophy"
            color="warning"
            valueLabel="Residents"
            :items="collect($villageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray()"
            :maxItems="5"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Gender Distribution"
            icon="bi-gender-ambiguous"
            color="info"
            canvasId="dashGenderChart"
            height="250px"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['household_summary'] ?? true)
{{-- Household Analytics Section --}}
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-house-door me-2 text-success"></i>Household Analytics
    </h5>
    <a href="{{ route('analytics.household.index') }}" class="btn btn-sm btn-success">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Total Households" :value="number_format($householdSummary['total_households'] ?? 0)" icon="bi-house-door-fill" color="success" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Approved" :value="number_format($householdSummary['approved'] ?? 0)" icon="bi-check-circle" color="success" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Pending" :value="number_format($householdSummary['pending'] ?? 0)" icon="bi-hourglass-split" color="warning" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Avg Size" :value="number_format($householdSummary['average_size'] ?? 0, 2)" icon="bi-people" color="info" subtitle="members" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Occupied Lots" :value="number_format($householdSummary['occupied_lots'] ?? 0)" icon="bi-check2-square" color="primary" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Available" :value="number_format($householdSummary['available_lots'] ?? 0)" icon="bi-plus-square" color="warning" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <x-analytics.chart-card title="Household Composition" icon="bi-diagram-3" color="warning" canvasId="dashCompositionChart" height="260px" />
    </div>
    <div class="col-lg-4">
        <x-analytics.chart-card title="Household Size" icon="bi-people" color="success" canvasId="dashHHSizeChart" height="260px" />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights title="Household Insights" icon="bi-lightbulb" color="success" :insights="$householdInsights" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Households by Village"
            icon="bi-trophy"
            color="success"
            valueLabel="Households"
            :items="collect($householdVillageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray()"
            :maxItems="5"
        />
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-success"><i class="bi bi-house-door me-2"></i>Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('analytics.demographic.index') }}" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-grid me-1"></i>Demographic Dashboard
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('analytics.household.index') }}" class="btn btn-outline-success w-100 py-2">
                            <i class="bi bi-house-door me-1"></i>Household Analytics
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('analytics.population.index') }}" class="btn btn-outline-info w-100 py-2">
                            <i class="bi bi-people me-1"></i>Population Analytics
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('gis.index') }}" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-map me-1"></i>GIS Map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($dashboardWidgets['resident_summary'] ?? true)
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Education Rate" :value="($educationSummary['education_rate'] ?? 0) . '%'" icon="bi-mortarboard-fill" color="primary" subtitle="{{ number_format($educationSummary['educated'] ?? 0) }} educated" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Currently Enrolled" :value="number_format($educationSummary['currently_enrolled'] ?? 0)" icon="bi-book-fill" color="info" subtitle="{{ $educationSummary['enrollment_rate'] ?? 0 }}%" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Employment Rate" :value="($employmentSummary['employment_rate'] ?? 0) . '%'" icon="bi-briefcase-fill" color="success" subtitle="{{ number_format($employmentSummary['total_employed'] ?? 0) }} employed" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg Monthly Income" :value="'₱' . number_format($employmentSummary['average_income'] ?? 0, 0)" icon="bi-cash-stack" color="warning" subtitle="median: ₱{{ number_format($employmentSummary['median_income'] ?? 0, 0) }}" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <x-analytics.summary-card title="Scholars" :value="number_format($educationSummary['scholars'] ?? 0)" icon="bi-award-fill" color="warning" subtitle="{{ $educationSummary['scholarship_rate'] ?? 0 }}% of educated" />
    </div>
    <div class="col-lg-4">
        <x-analytics.summary-card title="OFWs" :value="number_format($employmentSummary['ofw'] ?? 0)" icon="bi-globe" color="info" subtitle="{{ $employmentSummary['ofw_rate'] ?? 0 }}% of residents" />
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-mortarboard me-2"></i>Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('analytics.education.index') }}" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-mortarboard me-1"></i>Education
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('analytics.employment.index') }}" class="btn btn-outline-success w-100 py-2">
                            <i class="bi bi-briefcase me-1"></i>Employment
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('analytics.occupation.index') }}" class="btn btn-outline-info w-100 py-2">
                            <i class="bi bi-person-badge me-1"></i>Occupation
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('analytics.skills.index') }}" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-tools me-1"></i>Skills
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Occupation Analytics Section --}}
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-person-badge me-2 text-info"></i>Occupation Analytics
    </h5>
    <a href="{{ route('analytics.occupation.index') }}" class="btn btn-sm btn-info">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Total Residents" :value="number_format($occupationSummary['total'] ?? 0)" icon="bi-people-fill" color="primary" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="With Occupation" :value="number_format($occupationSummary['with_occupation'] ?? 0)" icon="bi-briefcase-fill" color="info" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Coverage Rate" :value="($occupationSummary['coverage_rate'] ?? 0) . '%'" icon="bi-check-circle" color="success" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Top Occupation" :value="$occupationSummary['top_occupation'] ?? '—'" icon="bi-trophy" color="warning" subtitle="{{ $occupationSummary['top_count'] ?? 0 }} residents" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Skill Coverage" :value="($skillsSummary['skill_coverage'] ?? 0) . '%'" icon="bi-tools" color="primary" subtitle="{{ number_format($skillsSummary['with_any_skill'] ?? 0) }} skilled" />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card title="Avg Income" :value="'₱' . number_format($incomeStats['average'] ?? 0, 0)" icon="bi-cash-stack" color="success" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Top Occupations" icon="bi-bar-chart" color="info" canvasId="dashOccChart" height="250px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Skills Distribution" icon="bi-tools" color="warning" canvasId="dashSkillChart" height="250px" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Livelihood Distribution" icon="bi-pie-chart" color="success" canvasId="dashLivelihoodChart" height="250px" />
    </div>
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Top Occupations"
            icon="bi-trophy"
            color="info"
            valueLabel="Residents"
            :items="collect($occupationTop ?? [])->map(fn($count, $occ) => ['name' => $occ, 'value' => number_format($count)])->take(5)->toArray()"
            :maxItems="5"
        />
    </div>
</div>

{{-- Population Pyramid Summary --}}
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-people me-2 text-secondary"></i>Population Pyramid
    </h5>
    <a href="{{ route('analytics.socio-economic.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Socio-Economic View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Population" :value="number_format($pyramidStats['total'] ?? 0)" icon="bi-people-fill" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Average Age" :value="number_format($pyramidStats['average_age'] ?? 0, 1)" icon="bi-person-lines-fill" color="info" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Male" :value="number_format($pyramidStats['total_male'] ?? 0)" icon="bi-gender-male" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Female" :value="number_format($pyramidStats['total_female'] ?? 0)" icon="bi-gender-female" color="danger" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <x-analytics.chart-card title="Population Pyramid" icon="bi-people" color="secondary" canvasId="dashPyramidChart" height="400px" />
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Government & Community Indicators</h5>
    <a href="{{ route('analytics.community-development.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Community Development View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Gov. Assistance Beneficiaries" :value="number_format($govAssistanceSummary['with_assistance'] ?? 0)" icon="bi-hands-helping" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="4PS Members" :value="number_format($govAssistanceProgramCounts['4Ps'] ?? 0)" icon="bi-house-heart-fill" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Healthcare Covered" :value="number_format($healthcareSummary['philhealth_members'] ?? 0)" icon="bi-heart-pulse-fill" color="success" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Village Development Score" :value="number_format($devScoreData['overall_score'] ?? 0, 1) . '/100'" icon="bi-speedometer2" color="info" />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="PhilHealth Members" :value="number_format($govMembershipCounts['PhilHealth'] ?? 0)" icon="bi-heart-pulse-fill" color="success" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="SSS Members" :value="number_format($govMembershipCounts['SSS'] ?? 0)" icon="bi-person-badge-fill" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Pag-IBIG Members" :value="number_format($govMembershipCounts['Pag-IBIG'] ?? 0)" icon="bi-bank-fill" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Voter Registration Rate" :value="number_format(($populationSummary['total_population'] ?? 0) > 0 ? (($govMembershipCounts['Registered Voter'] ?? 0) / ($populationSummary['total_population'] ?? 1) * 100) : 0, 1) . '%'" icon="bi-check2-square" color="info" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="card-title mb-0"><i class="bi bi-clipboard-data me-2 text-info"></i>Community Development Indicators</h6>
            </div>
            <div class="card-body">
                @forelse($communityIndicators ?? [] as $indicator)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">{{ $indicator['label'] }}</span>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px; width: 120px;">
                                <div class="progress-bar bg-{{ $indicator['status'] ?? 'info' }}" style="width: {{ $indicator['value'] ?? 0 }}%"></div>
                            </div>
                            <strong class="text-{{ $indicator['status'] ?? 'info' }}">{{ number_format($indicator['value'] ?? 0, 1) }}%</strong>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No development indicators available.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Executive Insights</h6>
            </div>
            <div class="card-body">
                @forelse($execInsights ?? [] as $insight)
                    <div class="d-flex align-items-start mb-3">
                        <div class="badge bg-{{ $insight['priority'] ?? 'secondary' }} me-2 mt-1">
                            <i class="bi bi-{{ $insight['icon'] ?? 'info-circle' }}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $insight['title'] ?? '' }}</div>
                            <div class="text-muted small">{{ $insight['description'] ?? '' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No insights available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Government Assistance Summary</h5>
    <a href="{{ route('analytics.government-assistance.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Gov. Assistance View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="TUPAD Beneficiaries" :value="number_format($govAssistanceProgramCounts['TUPAD'] ?? 0)" icon="bi-tools" color="primary" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="AKAP Beneficiaries" :value="number_format($govAssistanceProgramCounts['AKAP'] ?? 0)" icon="bi-hand-thumbs-up-fill" color="success" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Educational Assistance" :value="number_format($govAssistanceProgramCounts['Educational Assistance'] ?? 0)" icon="bi-book-fill" color="info" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="SLP Beneficiaries" :value="number_format($govAssistanceProgramCounts['SLP'] ?? 0)" icon="bi-graph-up-arrow" color="warning" />
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Planning & Demographic Indicators</h5>
    <a href="{{ route('analytics.planning.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Planning View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Dependency Ratio" :value="number_format($depSummary['total_dependency_ratio'] ?? 0, 1) . '%'" icon="bi-people-fill" color="warning" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Population Density" :value="number_format($densitySummary['population_density'] ?? 0, 1) . '/km²'" icon="bi-geo-alt-fill" color="info" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Annual Growth" :value="number_format($growthSummary['annual_growth'] ?? 0, 1) . '%'" icon="bi-graph-up-arrow" color="success" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="HH Occupancy Rate" :value="(($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 0)) > 0 ? number_format(($householdSummary['occupied_lots'] ?? 0) / (($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 1)) * 100, 1) . '%' : '0%'" icon="bi-house-door-fill" color="primary" subtitle="{{ number_format($householdSummary['occupied_lots'] ?? 0) }} occupied" />
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0">
        <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Planning Insights</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($planningInsights as $insight)
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="badge bg-{{ $insight['priority'] ?? 'secondary' }} me-2 mt-1"><i class="bi bi-{{ $insight['icon'] ?? 'info-circle' }}"></i></div>
                    <div>
                        <div class="fw-semibold small">{{ $insight['title'] }}</div>
                        <div class="text-muted small">{{ $insight['description'] }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-muted small">No planning insights available.</div>
            @endforelse
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, '#6f42c1', '#d63384', '#20c997', '#fd7e14'];

    const villageCtx = document.getElementById('dashVillageChart');
    if (villageCtx) {
        new Chart(villageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']?->village_name ?? '')->toArray()) !!},
                datasets: [{
                    label: 'Population',
                    data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total'])->toArray()) !!},
                    backgroundColor: palette.slice(0, count({!! json_encode(collect($villageComparison ?? [])->toArray()) !!})),
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const genderCtx = document.getElementById('dashGenderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($genderDist['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($genderDist['values'] ?? []) !!},
                    backgroundColor: [colors.primary, colors.danger, colors.secondary],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const compCtx = document.getElementById('dashCompositionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($householdComposition['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($householdComposition['values'] ?? []) !!},
                    backgroundColor: palette.slice(0, {!! json_encode(count($householdComposition['labels'] ?? [])) !!}),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const hhSizeCtx = document.getElementById('dashHHSizeChart');
    if (hhSizeCtx) {
        new Chart(hhSizeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($householdSizeDist['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($householdSizeDist['values'] ?? []) !!},
                    backgroundColor: palette.slice(0, {!! json_encode(count($householdSizeDist['labels'] ?? [])) !!}),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const occCtx = document.getElementById('dashOccChart');
    if (occCtx) {
        new Chart(occCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($occupationTop ?? [])) !!},
                datasets: [{
                    label: 'Residents',
                    data: {!! json_encode(array_values($occupationTop ?? [])) !!},
                    backgroundColor: '#0dcaf0',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const skillCtx = document.getElementById('dashSkillChart');
    if (skillCtx) {
        new Chart(skillCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($skillTop ?? [])) !!},
                datasets: [{
                    label: 'Residents',
                    data: {!! json_encode(array_values($skillTop ?? [])) !!},
                    backgroundColor: '#ffc107',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const liveCtx = document.getElementById('dashLivelihoodChart');
    if (liveCtx) {
        new Chart(liveCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($livelihoodDist ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_column($livelihoodDist ?? [], 'count')) !!},
                    backgroundColor: palette.slice(0, {!! json_encode(count($livelihoodDist ?? [])) !!}),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const pyramidCtx = document.getElementById('dashPyramidChart');
    if (pyramidCtx) {
        new Chart(pyramidCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($pyramidData['labels'] ?? []) !!},
                datasets: [
                    { label: 'Male', data: {!! json_encode($pyramidData['male'] ?? []) !!}, backgroundColor: '#0d6efd' },
                    { label: 'Female', data: {!! json_encode($pyramidData['female'] ?? []) !!}, backgroundColor: '#dc3545' }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: false } },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    const paymentTrendCtx = document.getElementById('dashPaymentTrendChart');
    if (paymentTrendCtx) {
        const paymentTrendData = {!! json_encode($monthlyPaymentTrend ?? []) !!};
        new Chart(paymentTrendCtx, {
            type: 'line',
            data: {
                labels: Object.keys(paymentTrendData),
                datasets: [{
                    label: 'Collections',
                    data: Object.values(paymentTrendData),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const participationCtx = document.getElementById('dashParticipationChart');
    if (participationCtx) {
        const participationData = {!! json_encode(collect($participationComparison ?? [])->map(fn($p) => ['name' => $p['village']?->village_name ?? '', 'events' => $p['total_events'] ?? 0, 'participants' => $p['total_participants'] ?? 0])->toArray()) !!};
        new Chart(participationCtx, {
            type: 'bar',
            data: {
                labels: participationData.map(p => p.name),
                datasets: [
                    { label: 'Events', data: participationData.map(p => p.events), backgroundColor: '#0d6efd', borderRadius: 4 },
                    { label: 'Household Participations', data: participationData.map(p => p.participants), backgroundColor: '#198754', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
@endpush
@endsection
