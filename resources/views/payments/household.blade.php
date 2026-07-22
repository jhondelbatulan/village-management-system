@extends('layouts.app')

@section('title', 'Household Payments - ' . $household->household_number)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Payment History</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $household->household_number }} | {{ $household->village?->village_name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Household
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack fs-2 text-success d-block mb-2"></i>
                <h4 class="mb-1">₱{{ number_format($balance['paid'], 2) }}</h4>
                <small class="text-muted">Total Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-circle fs-2 text-danger d-block mb-2"></i>
                <h4 class="mb-1">₱{{ number_format($balance['outstanding'], 2) }}</h4>
                <small class="text-muted">Outstanding Balance</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle fs-2 text-warning d-block mb-2"></i>
                <h4 class="mb-1">₱{{ number_format($balance['penalties'], 2) }}</h4>
                <small class="text-muted">Total Penalties</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Billings</h6>
    </div>
    <div class="card-body p-0">
        @if($billings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Billing #</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Penalty</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($billings as $billing)
                    <tr>
                        <td>
                            <a href="{{ route('billings.show', $billing) }}" class="text-primary fw-semibold">
                                {{ $billing->billing_number }}
                            </a>
                        </td>
                        <td>{{ $billing->paymentType?->payment_name ?? '—' }}</td>
                        <td>{{ $billing->billing_period }}</td>
                        <td>{{ $billing->due_date->format('M d, Y') }}</td>
                        <td class="text-end">₱{{ number_format($billing->amount, 2) }}</td>
                        <td class="text-end text-danger">₱{{ number_format($billing->penalty, 2) }}</td>
                        <td class="text-end fw-semibold">₱{{ number_format($billing->total_amount, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $billing->status_badge }}">{{ $billing->status_label }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
            <p class="mb-0">No billings found for this household.</p>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Payment History</h6>
    </div>
    <div class="card-body p-0">
        @if($payments->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction #</th>
                        <th>Receipt #</th>
                        <th>Billing #</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-center">Status</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>
                            <span class="text-muted">TXN-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('payments.receipt', $payment) }}" class="text-primary fw-semibold" target="_blank">
                                {{ $payment->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $payment->billing?->billing_number ?? '—' }}</td>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>{{ $payment->payment_method_label }}</td>
                        <td class="text-end fw-semibold">₱{{ number_format($payment->total_paid, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $payment->status_badge }}">{{ $payment->status_label }}</span>
                        </td>
                        <td>{{ $payment->receivedByUser?->full_name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <p class="mb-0">No payments recorded yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection
