@extends('layouts.app')

@section('title', 'Secretary Dashboard — ' . ($village->village_name ?? 'Village'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">{{ $village->village_name ?? 'Village' }} Secretary Dashboard</h1>
        <p class="text-muted mb-0">Secretary overview · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('households.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-house-door me-1"></i>Households
        </a>
        <a href="{{ route('residents.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i>Residents
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
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Pending Approvals"
            :value="number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0))"
            icon="bi-hourglass-split"
            color="warning"
            subtitle="Awaiting Admin review"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Draft Announcements"
            :value="number_format($announcementStats['draft'] ?? 0)"
            icon="bi-file-earmark-text"
            color="info"
            subtitle="Not yet published"
            :large="true"
        />
    </div>
</div>

@if($dashboardWidgets['analytics_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-analytics.chart-card
            title="Resident Gender Distribution"
            icon="bi-gender-ambiguous"
            color="primary"
            canvasId="secGenderDistChart"
            height="280px"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights
            title="Quick Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$insights"
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
                                    <br><small class="text-muted">Block {{ $household->block_number }}</small>
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
                                    <br><small class="text-muted">Household: {{ $resident->household?->lot_number ?? 'N/A' }}</small>
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

@if($dashboardWidgets['population_summary'] ?? true || $dashboardWidgets['household_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <x-analytics.progress-card
            title="Population Accuracy"
            :value="$householdStats['accuracy_pct'] ?? 0"
            :max="100"
            color="success"
            icon="bi-clipboard-check"
            label="Households with verified data"
        />
    </div>
    <div class="col-lg-8">
        <div class="row g-4">
            <div class="col-6">
                <x-analytics.summary-card
                    title="Recent Households"
                    :value="$recentHouseholds->count()"
                    icon="bi-house-add"
                    color="primary"
                    subtitle="Last 5 entries"
                />
            </div>
            <div class="col-6">
                <x-analytics.summary-card
                    title="Recent Announcements"
                    :value="$recentAnnouncements->count()"
                    icon="bi-megaphone"
                    color="info"
                    subtitle="Last 5 entries"
                />
            </div>
        </div>
    </div>
</div>
@endif

@if($dashboardWidgets['household_summary'] ?? true || $dashboardWidgets['calendar'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Recent Households"
            icon="bi-house-door"
            color="primary"
            valueLabel="Date"
            :items="$recentHouseholds->map(fn($h) => ['name' => 'Block '.$h->block_number.', Lot '.$h->lot_number, 'value' => $h->created_at->format('M d')])->toArray()"
            :maxItems="5"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Upcoming Events"
            icon="bi-calendar-event"
            color="success"
            valueLabel="Date"
            :items="$upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d')])->toArray()"
            :maxItems="5"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['announcements'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-12">
        <x-analytics.ranking-card
            title="Recent Announcements"
            icon="bi-megaphone"
            color="info"
            valueLabel="Date"
            :items="$recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d')])->toArray()"
            :maxItems="5"
        />
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const genderCtx = document.getElementById('secGenderDistChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($genderDist['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($genderDist['values'] ?? []) !!},
                    backgroundColor: ['#0d6efd', '#dc3545', '#6c757d'],
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
});
</script>
@endpush
@endsection
