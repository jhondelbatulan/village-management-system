@extends('layouts.app')

@section('title', 'Admin Dashboard — ' . ($village->village_name ?? 'Village'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">{{ $village->village_name ?? 'Village' }} Dashboard</h1>
        <p class="text-muted mb-0">Admin overview · {{ now()->format('F d, Y') }}</p>
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
    <div class="col-12 col-md-6 col-xl">
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
    <div class="col-12 col-md-6 col-xl">
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
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Collection This Month"
            :value="number_format($paymentStats['collected'] ?? 0)"
            icon="bi-cash-stack"
            color="info"
            :previousValue="$paymentStats['previous'] ?? null"
            :change="$paymentStats['change'] ?? null"
            subtitle="Collected this month"
            :large="true"
        />
    </div>
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Monthly Dues Collected"
            :value="'₱'.number_format($monthlyDues['collected'] ?? 0, 2)"
            icon="bi-calendar-check"
            color="warning"
            :change="$monthlyDues['change'] ?? null"
            subtitle="Monthly dues · {{ $monthlyDues['label'] ?? now()->format('F Y') }}"
            :large="true"
            :route="route('billings.index')"
        />
    </div>
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Pending Approvals"
            :value="number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0))"
            icon="bi-hourglass-split"
            color="warning"
            subtitle="Awaiting your action"
            :large="true"
        />
    </div>
</div>

@if($dashboardWidgets['analytics_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-analytics.chart-card
            title="Resident Age Distribution"
            icon="bi-bar-chart"
            color="primary"
            canvasId="adminAgeDistChart"
            height="280px"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights
            title="Village Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$insights"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['recent_activities'] ?? true || $dashboardWidgets['system_notices'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.recent-activity
            title="Recent Village Activity"
            icon="bi-activity"
            color="primary"
            :activities="$recentActivity"
            :maxItems="10"
        />
    </div>
    <div class="col-lg-6">
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
                                <span class="badge bg-warning">Pending</span>
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

@if($dashboardWidgets['payment_summary'] ?? true || $dashboardWidgets['participation_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <x-analytics.progress-card
            title="Collection Rate"
            :value="$paymentStats['collection_rate'] ?? 0"
            :max="100"
            color="success"
            icon="bi-graph-up-arrow"
            label="of outstanding amount collected"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.progress-card
            title="Approval Rate"
            :value="$approvalStats['approval_rate'] ?? 0"
            :max="100"
            color="info"
            icon="bi-check2-circle"
            label="households and residents approved"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.map-summary
            title="GIS Coverage"
            :mappedHouseholds="$householdStats['mapped_households'] ?? 0"
            :totalHouseholds="$householdStats['active'] ?? 0"
            :coveragePct="$householdStats['coverage_pct'] ?? 0"
            :villagesMapped="1"
            :totalVillages="1"
            route="{{ route('gis.index') }}"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['announcements'] ?? true || $dashboardWidgets['calendar'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Recent Announcements"
            icon="bi-megaphone"
            color="primary"
            valueLabel="Date"
            :items="$recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d')])->toArray()"
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ageCtx = document.getElementById('adminAgeDistChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($ageDistribution['labels'] ?? []) !!},
                datasets: [{
                    label: 'Residents',
                    data: {!! json_encode($ageDistribution['values'] ?? []) !!},
                    backgroundColor: ['#dc3545', '#ffc107', '#198754', '#0d6efd', '#0dcaf0', '#6f42c1', '#6c757d'],
                    borderRadius: 6
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
});
</script>
@endpush
@endsection
