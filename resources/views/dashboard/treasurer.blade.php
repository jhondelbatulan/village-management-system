@extends('layouts.app')

@section('title', 'Treasurer Dashboard — ' . ($village->village_name ?? 'Village'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">{{ $village->village_name ?? 'Village' }} Treasurer Dashboard</h1>
        <p class="text-muted mb-0">Financial overview · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('billings.index') }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-receipt me-1"></i>Billings
        </a>
        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-info">
            <i class="bi bi-cash me-1"></i>Payments
        </a>
    </div>
</div>

@include('partials.village-photos-slideshow')

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Collected This Month"
            :value="'₱'.number_format($paymentStats['collected'] ?? 0, 2)"
            icon="bi-cash-stack"
            color="success"
            :previousValue="$paymentStats['previous'] ?? null"
            :change="$paymentStats['change'] ?? null"
            subtitle="Total collections"
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
            title="Outstanding Balance"
            :value="'₱'.number_format($paymentStats['outstanding'] ?? 0, 2)"
            icon="bi-exclamation-triangle"
            color="danger"
            subtitle="Unpaid billings"
            :large="true"
        />
    </div>
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Total Billings"
            :value="number_format($paymentStats['total_billings'] ?? 0)"
            icon="bi-receipt"
            color="primary"
            subtitle="All billing records"
            :large="true"
        />
    </div>
    <div class="col-12 col-md-6 col-xl">
        <x-analytics.summary-card
            title="Collection Rate"
            :value="($paymentStats['collection_rate'] ?? 0).'%'"
            icon="bi-graph-up-arrow"
            color="info"
            subtitle="Of total billings"
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
            :items="$recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d')])->toArray()"
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
            :items="$upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d')])->toArray()"
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
            title="Monthly Collection Trend"
            icon="bi-graph-up"
            color="success"
            canvasId="treasPaymentTrendChart"
            height="250px"
        />
    </div>
    <div class="col-lg-4">
        <x-analytics.quick-insights
            title="Financial Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$insights"
        />
    </div>
</div>
@endif

@if($dashboardWidgets['recent_activities'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.recent-activity
            title="My Recent Activity"
            icon="bi-activity"
            color="success"
            :activities="$recentActivity"
            :maxItems="10"
        />
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-cash-stack me-2"></i>Recent Payments
                </h6>
            </div>
            <div class="card-body p-0">
                @if($recentPayments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Household</th>
                                    <th class="small">Amount</th>
                                    <th class="small">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    <tr>
                                        <td class="small">{{ $payment->household->household_head_name ?? 'N/A' }}</td>
                                        <td class="small fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td class="small text-muted">{{ $payment->payment_date->format('M d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No recent payments</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($dashboardWidgets['analytics_summary'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.progress-card
            title="Collection Rate"
            :value="$paymentStats['collection_rate'] ?? 0"
            :max="100"
            color="success"
            icon="bi-graph-up-arrow"
            label="of total billings collected this month"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Revenue by Payment Type"
            icon="bi-pie-chart"
            color="info"
            canvasId="treasRevenueTypeChart"
            height="250px"
        />
    </div>
</div>
@endif

@php
    $predictionStats = app(\App\Services\Analytics\PredictionAnalyticsService::class)->getVillageStats($village->id);
    $behaviorService = app(\App\Services\Analytics\PaymentBehaviorService::class);
@endphp

@if($dashboardWidgets['analytics_summary'] ?? true && $predictionStats['total_households'] > 0)
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-graph-up-arrow me-2"></i>Payment Prediction Analytics
                </h6>
                <a href="{{ route('analytics.predictive-payment.index', ['village_id' => $village->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-bar-chart me-1"></i>Full Analysis
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-success">{{ $predictionStats['distribution']['excellent']['count'] ?? 0 }}</div>
                        <div class="small text-muted">Excellent Payers</div>
                        <div class="small text-success"><i class="bi bi-check-circle-fill me-1"></i>Score 85+</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-primary">{{ $predictionStats['distribution']['good']['count'] ?? 0 }}</div>
                        <div class="small text-muted">Good Payers</div>
                        <div class="small text-primary"><i class="bi bi-check-circle me-1"></i>Score 70-84</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-warning">{{ $predictionStats['distribution']['moderate']['count'] ?? 0 }}</div>
                        <div class="small text-muted">Moderate Payers</div>
                        <div class="small text-warning"><i class="bi bi-exclamation-circle me-1"></i>Score 50-69</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-danger">{{ $predictionStats['distribution']['at_risk']['count'] ?? 0 }}</div>
                        <div class="small text-muted">At-Risk Payers</div>
                        <div class="small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Score 30-49</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-dark">{{ $predictionStats['distribution']['high_risk']['count'] ?? 0 }}</div>
                        <div class="small text-muted">High-Risk Payers</div>
                        <div class="small text-dark"><i class="bi bi-x-circle-fill me-1"></i>Score &lt;30</div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-speedometer2 text-primary me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">Avg. Score: {{ $predictionStats['avg_score'] }}/100</div>
                                <small class="text-muted">Village reliability average</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-clock-history text-warning me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">On-Time: {{ $predictionStats['on_time_rate'] }}%</div>
                                <small class="text-muted">Payment punctuality rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-exclamation-triangle text-danger me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">Follow-Up: {{ ($predictionStats['high_risk_count'] + $predictionStats['at_risk_count']) }} households</div>
                                <small class="text-muted">Requires Treasurer attention</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTrendCtx = document.getElementById('treasPaymentTrendChart');
    if (paymentTrendCtx) {
        const trendData = {!! json_encode($monthlyPaymentTrend ?? []) !!};
        new Chart(paymentTrendCtx, {
            type: 'line',
            data: {
                labels: Object.keys(trendData),
                datasets: [{
                    label: 'Collections',
                    data: Object.values(trendData),
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

    const revenueCtx = document.getElementById('treasRevenueTypeChart');
    if (revenueCtx) {
        const revenueData = {!! json_encode($revenueByType ?? []) !!};
        const palette = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', '#6c757d', '#6f42c1', '#d63384', '#20c997', '#fd7e14'];
        new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(revenueData),
                datasets: [{
                    data: Object.values(revenueData),
                    backgroundColor: palette.slice(0, Object.keys(revenueData).length),
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
