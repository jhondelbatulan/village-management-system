@extends('layouts.app')

@section('title', $village->village_name . ' - Predictive Payment Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-graph-up-arrow me-2 text-primary"></i>{{ $village->village_name }} — Payment Predictions
        </h1>
        <p class="text-muted mb-0">Village-level payment behavior intelligence and forecasting</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('analytics.predictive-payment.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('analytics.predictive-payment.export', ['format' => 'csv', 'village_id' => $villageId]) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>CSV
        </a>
        <a href="{{ route('analytics.predictive-payment.export', ['format' => 'pdf', 'village_id' => $villageId]) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg. Score" :value="$stats['avg_score'].'/100'" icon="bi-speedometer2" color="primary" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Collection Rate" :value="$stats['collection_rate'].'%'" icon="bi-cash-stack" color="success" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="High-Risk" :value="$stats['high_risk_count']" icon="bi-exclamation-triangle" color="danger" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Households" :value="$stats['total_households']" icon="bi-house-door" color="info" :large="true" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Payment Behavior Distribution" icon="bi-pie-chart" color="primary" height="300">
            <canvas id="behaviorChart"></canvas>
        </x-analytics.chart-card>
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Score Distribution" icon="bi-bar-chart" color="success" height="300">
            <canvas id="scoreChart"></canvas>
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <x-analytics.chart-card title="Monthly Trends" icon="bi-graph-up" color="info" height="350">
            <canvas id="trendChart"></canvas>
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-success"><i class="bi bi-trophy me-2"></i>Top Performers</h6></div>
            <div class="card-body">
                @forelse($topPerformers as $h)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">{{ $h->payment_reliability_score }}</span>
                        <div>
                            <div class="fw-semibold small">{{ $h->household_head_name }}</div>
                            <small class="text-muted">{{ $h->household_number }}</small>
                        </div>
                    </div>
                    <a href="{{ route('analytics.predictive-payment.household', $h->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No data</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>At-Risk Households</h6></div>
            <div class="card-body">
                @forelse($atRiskHouseholds as $h)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 {{ $h->payment_behavior === 'high_risk' ? 'bg-danger-subtle' : 'bg-warning-subtle' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-{{ $h->payment_behavior === 'high_risk' ? 'danger' : 'warning' }} me-2">{{ $h->payment_reliability_score }}</span>
                        <div>
                            <div class="fw-semibold small">{{ $h->household_head_name }}</div>
                            <small class="text-muted">{{ $h->household_number }}</small>
                        </div>
                    </div>
                    <a href="{{ route('analytics.predictive-payment.household', $h->id) }}" class="btn btn-sm btn-outline-danger">View</a>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No at-risk households</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-lightbulb me-2"></i>Insights & Recommendations</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        @forelse($insights as $insight)
                        <div class="d-flex align-items-start mb-2 p-2 rounded bg-{{ $insight['type'] }}-subtle border-start border-3 border-{{ $insight['type'] }}">
                            <span class="small">{{ $insight['message'] }}</span>
                        </div>
                        @empty
                        <div class="text-muted small">No insights available</div>
                        @endforelse
                    </div>
                    <div class="col-md-5">
                        <div class="row g-2">
                            <div class="col-6"><div class="text-center p-2 rounded border"><div class="fs-4 fw-bold text-success">{{ $recommendations['no_action'] ?? 0 }}</div><div class="small text-muted">Excellent</div></div></div>
                            <div class="col-6"><div class="text-center p-2 rounded border"><div class="fs-4 fw-bold text-primary">{{ $recommendations['send_reminders'] ?? 0 }}</div><div class="small text-muted">Good</div></div></div>
                            <div class="col-6"><div class="text-center p-2 rounded border"><div class="fs-4 fw-bold text-warning">{{ $recommendations['follow_up_needed'] ?? 0 }}</div><div class="small text-muted">Moderate</div></div></div>
                            <div class="col-6"><div class="text-center p-2 rounded border"><div class="fs-4 fw-bold text-danger">{{ $recommendations['treasurer_action'] ?? 0 }}</div><div class="small text-muted">At-Risk</div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bd = @json($distribution);
    const sd = @json($scoreDistribution);
    const md = @json($monthlyTrend);

    new Chart(document.getElementById('behaviorChart'), {
        type: 'doughnut',
        data: {
            labels: ['Excellent', 'Good', 'Moderate', 'At-Risk', 'High-Risk'],
            datasets: [{ data: [bd.excellent?.count||0, bd.good?.count||0, bd.moderate?.count||0, bd.at_risk?.count||0, bd.high_risk?.count||0], backgroundColor: ['#198754','#0d6efd','#ffc107','#fd7e14','#dc3545'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    if (sd && sd.labels) {
        new Chart(document.getElementById('scoreChart'), {
            type: 'bar',
            data: { labels: sd.labels, datasets: [{ label: 'Households', data: sd.counts, backgroundColor: ['#dc3545','#fd7e14','#ffc107','#0d6efd','#198754'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    if (md && md.length) {
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: md.map(m => m.month_short),
                datasets: [
                    { label: 'Collection Rate', data: md.map(m => m.collection_rate), borderColor: '#198754', fill: false, tension: 0.4 },
                    { label: 'On-Time Rate', data: md.map(m => m.on_time_rate), borderColor: '#0d6efd', fill: false, tension: 0.4 },
                    { label: 'Late Rate', data: md.map(m => m.total_payments > 0 ? (m.late / m.total_payments * 100) : 0), borderColor: '#ffc107', fill: false, tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
        });
    }
});
</script>
@endpush
