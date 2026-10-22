@extends('layouts.app')

@section('title', 'Billing - ' . $billing->billing_number)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Billing {{ $billing->billing_number }}</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $billing->village?->village_name ?? '—' }} | {{ $billing->billing_period }}
                <span class="badge {{ $billing->status_badge }} ms-2">{{ $billing->status_label }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billings.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            @if($canPayOnline)
            <a href="{{ route('billings.pay-online', $billing) }}" class="btn btn-success btn-sm">
                <i class="bi bi-phone me-1"></i>Pay Online (GCash)
            </a>
            @endif
            @if($canRecordPayment && in_array($billing->status, ['pending', 'overdue', 'partially_paid']))
            <a href="{{ route('payments.create') }}?billing_id={{ $billing->id }}@if($activeOnlinePayment && $activeOnlinePayment->status === 'approved')&online_payment={{ $activeOnlinePayment->id }}@endif"
               class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Record Payment
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($activeOnlinePayment)
    <div class="alert alert-info border-start border-4 border-info" role="alert">
        @if($activeOnlinePayment->status === 'pending')
            <i class="bi bi-hourglass-split me-2"></i>
            An online GCash payment request of <strong>₱{{ number_format($activeOnlinePayment->amount, 2) }}</strong>
            (txn #{{ $activeOnlinePayment->transaction_number }}) submitted on
            {{ $activeOnlinePayment->created_at->format('M d, Y') }} is <strong>awaiting treasurer approval</strong>.
        @else
            <i class="bi bi-check-circle me-2"></i>
            An approved online GCash payment of <strong>₱{{ number_format($activeOnlinePayment->amount, 2) }}</strong>
            is <strong>awaiting recording</strong> by the treasurer.
        @endif
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Billing Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Number</label>
                        <div class="fw-semibold">{{ $billing->billing_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Household</label>
                        <div class="fw-semibold">{{ $billing->household?->household_number ?? '—' }}</div>
                        <small class="text-muted">{{ $billing->household?->household_head_name ?? '' }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Village</label>
                        <div class="fw-semibold">{{ $billing->village?->village_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Type</label>
                        <div class="fw-semibold">{{ $billing->paymentType?->payment_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Period</label>
                        <div class="fw-semibold">{{ $billing->billing_period }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Due Date</label>
                        <div class="fw-semibold">{{ $billing->due_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created By</label>
                        <div class="fw-semibold">{{ $billing->creator?->full_name ?? 'System' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created Date</label>
                        <div class="fw-semibold">{{ $billing->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($billing->remarks)
                    <div class="col-12">
                        <label class="form-label text-muted small">Remarks</label>
                        <div>{{ $billing->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-primary"></i>Payment Records</h6>
            </div>
            <div class="card-body p-0">
                @if($billing->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction #</th>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total Paid</th>
                                <th>Status</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($billing->payments as $payment)
                            <tr>
                                <td>
                                    <span class="text-muted">TXN-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('payments.show', $payment) }}" class="text-primary fw-semibold">
                                        {{ $payment->receipt_number }}
                                    </a>
                                </td>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>{{ $payment->payment_method_label }}</td>
                                <td class="text-end">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-end">₱{{ number_format($payment->discount, 2) }}</td>
                                <td class="text-end fw-semibold">₱{{ number_format($payment->total_paid, 2) }}</td>
                                <td><span class="badge {{ $payment->status_badge }}">{{ $payment->status_label }}</span></td>
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
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2 text-primary"></i>Amount Summary</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Base Amount</span>
                    <span class="fw-semibold">₱{{ number_format($billing->amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Discount</span>
                    <span class="fw-semibold text-success">-₱{{ number_format($billing->discount, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Due</span>
                    <span class="fw-bold fs-5">₱{{ number_format($billing->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Paid</span>
                    <span class="fw-semibold text-success">₱{{ number_format($totalPaid, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted fw-semibold">Balance</span>
                    <span class="fw-bold fs-5 {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        ₱{{ number_format($balance, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
