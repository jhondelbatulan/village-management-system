@extends('layouts.app')

@section('title', 'Predictive Payment Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">Predictive Payment Analytics</h1>
        <p class="text-muted mb-0">{{ $village ? $village->village_name . ' — ' : 'System-Wide — ' }}Payment Behavior Intelligence</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->role?->role_slug === 'super_admin')
        <form action="{{ route('analytics.predictive-payment.recalculate') }}" method="POST" class="d-inline">
            @csrf
            @if($villageId)<input type="hidden" name="village_id" value="{{ $villageId }}">@endif
            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Recalculate all payment predictions?')">
                <i class="bi bi-arrow-clockwise me-1"></i>Recalculate
            </button>
        </form>
        @endif
        <a href="{{ route('analytics.predictive-payment.export', ['format' => 'csv', 'village_id' => $villageId]) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>CSV
        </a>
        <a href="{{ route('analytics.predictive-payment.export', ['format' => 'pdf', 'village_id' => $villageId]) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<x-analytics.global-filter :villages="$villages" :selectedVillage="$villageId" />

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg. Reliability Score" :value="$stats['avg_score'].'/100'" icon="bi-speedometer2" color="primary" subtitle="Payment reliability" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Collection Rate" :value="$stats['collection_rate'].'%'" icon="bi-cash-stack" color="success" subtitle="Billing completion" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="High-Risk Households" :value="$stats['high_risk_count']" icon="bi-exclamation-triangle" color="danger" subtitle="Need immediate attention" :large="true" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="At-Risk Households" :value="$stats['at_risk_count']" icon="bi-exclamation-circle" color="warning" subtitle="Monitor closely" :large="true" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="On-Time Rate" :value="$stats['on_time_rate'].'%'" icon="bi-check-circle" color="success" subtitle="Payments on/before due" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Late Payment Rate" :value="$stats['late_rate'].'%'" icon="bi-clock-history" color="warning" subtitle="Payments after due" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Partial Payment Rate" :value="$stats['partial_rate'].'%'" icon="bi-pie-chart" color="info" subtitle="Incomplete payments" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg. Payment Delay" :value="$stats['avg_delay_days'].' days'" icon="bi-calendar-x" color="danger" subtitle="Average late days" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Payment Behavior Distribution" icon="bi-pie-chart" color="primary" height="300">
            <canvas id="behaviorDistributionChart"></canvas>
        </x-analytics.chart-card>
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card title="Reliability Score Distribution" icon="bi-bar-chart" color="success" height="300">
            <canvas id="scoreDistributionChart"></canvas>
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <x-analytics.chart-card title="Monthly Collection & Payment Trends" icon="bi-graph-up" color="info" height="350">
            <canvas id="monthlyTrendChart"></canvas>
        </x-analytics.chart-card>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card title="Payment Consistency Trend" icon="bi-activity" color="primary" height="300">
            <canvas id="consistencyTrendChart"></canvas>
        </x-analytics.chart-card>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-lightbulb me-2"></i>Payment Insights</h6>
            </div>
            <div class="card-body">
                @forelse($insights as $insight)
                <div class="d-flex align-items-start mb-3 p-2 rounded bg-{{ $insight['type'] }}-subtle border-start border-3 border-{{ $insight['type'] }}">
                    <i class="bi bi-{{ $insight['type'] === 'success' ? 'check-circle-fill' : ($insight['type'] === 'danger' ? 'exclamation-triangle-fill' : ($insight['type'] === 'warning' ? 'exclamation-circle-fill' : 'info-circle-fill')) }} text-{{ $insight['type'] }} me-2 mt-1"></i>
                    <span class="small">{{ $insight['message'] }}</span>
                </div>
                @empty
                <div class="text-center py-3">
                    <i class="bi bi-info-circle fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No insights available</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success"><i class="bi bi-trophy me-2"></i>Top Performers</h6>
            </div>
            <div class="card-body">
                @forelse($topPerformers as $household)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 {{ $loop->first ? 'bg-success-subtle' : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2" style="min-width: 24px;">{{ $household->payment_reliability_score }}</span>
                        <div>
                            <div class="fw-semibold small">{{ $household->household_head_name }}</div>
                            <small class="text-muted">{{ $household->household_number }} · {{ $household->block_number }}/{{ $household->lot_number }}</small>
                        </div>
                    </div>
                    <span class="badge bg-{{ \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$household->payment_behavior] ?? 'secondary' }}">
                        {{ \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$household->payment_behavior] ?? 'Unknown' }}
                    </span>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No data available</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>High-Risk Households</h6>
            </div>
            <div class="card-body">
                @forelse($atRiskHouseholds as $household)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 {{ $household->payment_behavior === 'high_risk' ? 'bg-danger-subtle' : 'bg-warning-subtle' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-{{ $household->payment_behavior === 'high_risk' ? 'danger' : 'warning' }} me-2" style="min-width: 24px;">{{ $household->payment_reliability_score }}</span>
                        <div>
                            <div class="fw-semibold small">{{ $household->household_head_name }}</div>
                            <small class="text-muted">{{ $household->household_number }} · {{ $household->block_number }}/{{ $household->lot_number }}</small>
                        </div>
                    </div>
                    <span class="badge bg-{{ \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$household->payment_behavior] ?? 'secondary' }}">
                        {{ \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$household->payment_behavior] ?? 'Unknown' }}
                    </span>
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
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-check me-2"></i>Smart Recommendations Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md">
                        <div class="text-center p-3 rounded border">
                            <div class="fs-2 fw-bold text-success">{{ $recommendations['no_action'] ?? 0 }}</div>
                            <div class="small text-muted">No Action Needed</div>
                            <div class="small text-success"><i class="bi bi-check-circle me-1"></i>Excellent</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-center p-3 rounded border">
                            <div class="fs-2 fw-bold text-primary">{{ $recommendations['send_reminders'] ?? 0 }}</div>
                            <div class="small text-muted">Send Reminders</div>
                            <div class="small text-primary"><i class="bi bi-bell me-1"></i>Good</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-center p-3 rounded border">
                            <div class="fs-2 fw-bold text-warning">{{ $recommendations['follow_up_needed'] ?? 0 }}</div>
                            <div class="small text-muted">Follow-Up Needed</div>
                            <div class="small text-warning"><i class="bi bi-exclamation-circle me-1"></i>Moderate</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-center p-3 rounded border">
                            <div class="fs-2 fw-bold text-danger">{{ $recommendations['treasurer_action'] ?? 0 }}</div>
                            <div class="small text-muted">Treasurer Action</div>
                            <div class="small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>At-Risk</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-center p-3 rounded border">
                            <div class="fs-2 fw-bold text-dark">{{ $recommendations['personal_visit'] ?? 0 }}</div>
                            <div class="small text-muted">Personal Visit</div>
                            <div class="small text-dark"><i class="bi bi-x-circle me-1"></i>High-Risk</div>
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
    const behaviorData = @json($distribution);
    const scoreData = @json($scoreDistribution);
    const monthlyData = @json($monthlyTrend);
    const consistencyData = @json($consistencyTrend);

    new Chart(document.getElementById('behaviorDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: ['Excellent', 'Good', 'Moderate', 'At-Risk', 'High-Risk'],
            datasets: [{
                data: [
                    behaviorData.excellent?.count || 0,
                    behaviorData.good?.count || 0,
                    behaviorData.moderate?.count || 0,
                    behaviorData.at_risk?.count || 0,
                    behaviorData.high_risk?.count || 0
                ],
                backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    if (scoreData && scoreData.labels) {
        new Chart(document.getElementById('scoreDistributionChart'), {
            type: 'bar',
            data: {
                labels: scoreData.labels,
                datasets: [{
                    label: 'Households',
                    data: scoreData.counts,
                    backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#0d6efd', '#198754']
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

    if (monthlyData && monthlyData.length) {
        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(m => m.month_short),
                datasets: [
                    {
                        label: 'Collection Rate (%)',
                        data: monthlyData.map(m => m.collection_rate),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'On-Time Rate (%)',
                        data: monthlyData.map(m => m.on_time_rate),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }

    if (consistencyData && consistencyData.length) {
        new Chart(document.getElementById('consistencyTrendChart'), {
            type: 'bar',
            data: {
                labels: consistencyData.map(c => c.month),
                datasets: [
                    { label: 'On-Time', data: consistencyData.map(c => c.on_time_rate), backgroundColor: '#198754' },
                    { label: 'Late', data: consistencyData.map(c => c.late_rate), backgroundColor: '#ffc107' },
                    { label: 'Partial', data: consistencyData.map(c => c.partial_rate), backgroundColor: '#0dcaf0' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { x: { stacked: true }, y: { stacked: true, max: 100 } }
            }
        });
    }
});
</script>
@endpush
