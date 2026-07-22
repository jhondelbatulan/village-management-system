@extends('layouts.app')

@section('title', 'Payment - ' . $payment->receipt_number)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Payment {{ $payment->receipt_number }}</h4>
            <p class="page-subtitle text-muted mb-0">
{{ $payment->village?->village_name ?? '—' }}
                <span class="badge {{ $payment->status_badge }} ms-2">{{ $payment->status_label }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            @if($canPrintReceipt)
            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-success btn-sm" target="_blank">
                <i class="bi bi-printer me-1"></i>Print Receipt
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

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Payment Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Receipt Number</label>
                        <div class="fw-semibold">{{ $payment->receipt_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Transaction Number</label>
                        <div class="fw-semibold">TXN-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Number</label>
                        <div class="fw-semibold">{{ $payment->billing?->billing_number ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Household</label>
                        <div class="fw-semibold">{{ $payment->household?->household_number ?? '—' }}</div>
                        <small class="text-muted">{{ $payment->household?->household_head_name ?? '' }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Village</label>
                        <div class="fw-semibold">{{ $payment->village?->village_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Type</label>
                        <div class="fw-semibold">{{ $payment->paymentType?->payment_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Period</label>
                        <div class="fw-semibold">{{ $payment->billing?->billing_period ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Amount Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Payment Amount</label>
                        <div class="fw-semibold">₱{{ number_format($payment->amount, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Discount</label>
                        <div class="fw-semibold">₱{{ number_format($payment->discount, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Total Paid</label>
                        <div class="fw-bold fs-5 text-success">₱{{ number_format($payment->total_paid, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Remaining Balance</label>
                        <div class="fw-bold fs-5 {{ ($remainingBalance ?? 0) <= 0 ? 'text-success' : 'text-danger' }}">
                            ₱{{ number_format($remainingBalance ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clock me-2 text-info"></i>Transaction Info</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Date</label>
                        <div class="fw-semibold">{{ $payment->payment_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Due Date</label>
                        <div class="fw-semibold">{{ $payment->due_date->format('M d, Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Method</label>
                        <div class="fw-semibold">{{ $payment->payment_method_label }}</div>
                    </div>
                    @if($payment->reference_number)
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Reference Number</label>
                        <div class="fw-semibold">{{ $payment->reference_number }}</div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Received By</label>
                        <div class="fw-semibold">{{ $payment->receivedByUser?->full_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Recorded At</label>
                        <div class="fw-semibold">{{ $payment->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($payment->remarks)
                    <div class="col-12">
                        <label class="form-label text-muted small">Remarks</label>
                        <div>{{ $payment->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-check-circle me-2 text-success"></i>Status</h6>
            </div>
            <div class="card-body text-center">
                <span class="badge {{ $payment->status_badge }} fs-5">{{ $payment->status_label }}</span>
                <div class="mt-2">
                    <small class="text-muted">Billing Status:</small>
                    <span class="badge {{ $payment->billing?->status_badge ?? 'bg-secondary' }} ms-1">
                        {{ $payment->billing?->status_label ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Actions</h6>
            </div>
            <div class="card-body">
                @if($canPrintReceipt)
                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-outline-success w-100 mb-2" target="_blank">
                    <i class="bi bi-printer me-1"></i>Print Receipt
                </a>
                @endif
                <a href="{{ route('billings.show', $payment->billing_id) }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-receipt me-1"></i>View Billing
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
