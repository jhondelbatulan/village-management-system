@extends('layouts.app')

@section('title', 'Payment Prediction - ' . ($household->household_number ?? ''))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-person-badge me-2 text-primary"></i>Payment Prediction
        </h1>
        <p class="text-muted mb-0">{{ $household->household_number }} — {{ $household->head_name ?? $household->household_head_name }} · {{ $village->village_name }}</p>
    </div>
    <a href="{{ route('households.show', $household) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Household
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center py-4">
                @php
                    $behaviorColor = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$prediction['behavior']] ?? 'secondary';
                    $behaviorIcon = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_ICONS[$prediction['behavior']] ?? 'bi-question-circle';
                    $behaviorLabel = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$prediction['behavior']] ?? 'Unknown';
                @endphp
                <div class="mb-3">
                    <i class="bi {{ $behaviorIcon }} text-{{ $behaviorColor }}" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold text-{{ $behaviorColor }}">{{ $behaviorLabel }}</h4>
                <div class="text-muted small mb-3">Payment Behavior Classification</div>

                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <div class="fs-3 fw-bold text-{{ $behaviorColor }}">{{ $prediction['score'] }}</div>
                            <div class="small text-muted">/ 100</div>
                            <div class="small fw-semibold">Reliability Score</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-3">
                            <div class="fs-3 fw-bold text-primary">{{ $prediction['confidence'] }}%</div>
                            <div class="small text-muted">&nbsp;</div>
                            <div class="small fw-semibold">Prediction Confidence</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Reasons for Prediction</h6>
            </div>
            <div class="card-body">
                @foreach($prediction['reasons'] as $reason)
                <div class="d-flex align-items-center mb-2 p-2 rounded bg-light">
                    <i class="bi bi-check2-circle text-{{ $behaviorColor }} me-2"></i>
                    <span class="small">{{ $reason }}</span>
                </div>
                @endforeach

                <hr class="my-3">

                <h6 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2"></i>Smart Recommendations</h6>
                @foreach($recommendation['details'] as $detail)
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-arrow-right text-{{ $recommendation['color'] }} me-2"></i>
                    <span class="small">{{ $detail }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Billings" :value="$paymentHistory['total_billings']" icon="bi-receipt" color="info" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Total Paid" :value="'₱'.number_format($paymentHistory['total_paid'], 2)" icon="bi-cash" color="success" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Outstanding" :value="'₱'.number_format($paymentHistory['total_outstanding'], 2)" icon="bi-wallet2" color="danger" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card title="Avg. Payment" :value="'₱'.number_format($paymentHistory['avg_payment_amount'], 2)" icon="bi-cash-coin" color="primary" />
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart me-2"></i>Payment History Summary</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex justify-content-between p-2 rounded bg-success-subtle">
                            <span class="small fw-semibold">On-Time Payments</span>
                            <span class="badge bg-success">{{ $paymentHistory['on_time'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between p-2 rounded bg-warning-subtle">
                            <span class="small fw-semibold">Late Payments</span>
                            <span class="badge bg-warning">{{ $paymentHistory['late'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between p-2 rounded bg-danger-subtle">
                            <span class="small fw-semibold">Missed Payments</span>
                            <span class="badge bg-danger">{{ $paymentHistory['missed'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between p-2 rounded bg-info-subtle">
                            <span class="small fw-semibold">Partial Payments</span>
                            <span class="badge bg-info">{{ $paymentHistory['partial'] }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $total = $paymentHistory['total_payments'];
                    $onTimePct = $total > 0 ? round(($paymentHistory['on_time'] / $total) * 100) : 0;
                    $latePct = $total > 0 ? round(($paymentHistory['late'] / $total) * 100) : 0;
                @endphp
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">On-Time Rate</small>
                        <small class="fw-bold text-success">{{ $onTimePct }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $onTimePct }}%"></div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Late Rate</small>
                        <small class="fw-bold text-warning">{{ $latePct }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $latePct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-list-task me-2"></i>Recent Payment Records</h6></div>
            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                @forelse($payments->take(15) as $payment)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-1 {{ $payment->payment_status === 'paid' ? 'bg-success-subtle' : ($payment->payment_status === 'overdue' ? 'bg-danger-subtle' : ($payment->payment_status === 'partially_paid' ? 'bg-warning-subtle' : 'bg-light')) }}">
                    <div>
                        <div class="small fw-semibold">{{ $payment->paymentType->name ?? 'Payment' }}</div>
                        <small class="text-muted">{{ $payment->payment_date?->format('M d, Y') ?? 'N/A' }}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">₱{{ number_format($payment->total_paid, 2) }}</div>
                        <span class="badge bg-{{ $payment->payment_status === 'paid' ? 'success' : ($payment->payment_status === 'overdue' ? 'danger' : ($payment->payment_status === 'partially_paid' ? 'warning' : 'secondary')) }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_status)) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">No payment records found</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
