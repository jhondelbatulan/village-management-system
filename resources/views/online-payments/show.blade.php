@extends('layouts.app')

@section('title', 'Online Payment Details')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Online Payment Details</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $onlinePayment->billing?->billing_number ?? '—' }} |
                <span class="badge {{ $onlinePayment->status_badge }}">{{ $onlinePayment->status_label }}</span>
            </p>
        </div>
        <a href="{{ route('online-payments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Online Payments
        </a>
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

@if($onlinePayment->status === 'pending')
    <div class="alert alert-warning border-start border-4 border-warning" role="alert">
        <i class="bi bi-hourglass-split me-2"></i>This request is awaiting your review. Approve it to record the payment, or decline it to notify the resident.
    </div>
@elseif($onlinePayment->status === 'approved' && $onlinePayment->payment_id === null)
    <div class="alert alert-success border-start border-4 border-success d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" role="alert">
        <div>
            <i class="bi bi-check-circle me-2"></i>This request was approved but the payment has not been recorded yet.
        </div>
        <a href="{{ route('payments.create', ['online_payment' => $onlinePayment->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Continue to Record Payment
        </a>
    </div>
@elseif($onlinePayment->status === 'approved')
    <div class="alert alert-success border-start border-4 border-success" role="alert">
        <i class="bi bi-check-circle me-2"></i>Approved and recorded as payment
        <a href="{{ route('payments.show', $onlinePayment->payment_id) }}" class="fw-semibold">{{ $onlinePayment->payment?->receipt_number }}</a>.
    </div>
@elseif($onlinePayment->status === 'declined')
    <div class="alert alert-danger border-start border-4 border-danger" role="alert">
        <i class="bi bi-x-circle me-2"></i>Declined
        @if($onlinePayment->declined_reason)
            — {{ $onlinePayment->declined_reason }}
        @endif
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-primary"></i>Payment Request</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Amount Paid</label>
                        <div class="fw-bold text-success fs-5">₱{{ number_format($onlinePayment->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">GCash Transaction Number</label>
                        <div class="fw-semibold">{{ $onlinePayment->transaction_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Submitted By</label>
                        <div class="fw-semibold">{{ $onlinePayment->resident?->full_name ?? '—' }}</div>
                        <small class="text-muted">{{ $onlinePayment->household?->household_number ?? '' }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Submitted On</label>
                        <div class="fw-semibold">{{ $onlinePayment->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($onlinePayment->reviewed_at)
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Reviewed By</label>
                        <div class="fw-semibold">{{ $onlinePayment->reviewer?->full_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Reviewed On</label>
                        <div class="fw-semibold">{{ $onlinePayment->reviewed_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Bill Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Number</label>
                        <div class="fw-semibold">
                            <a href="{{ route('billings.show', $onlinePayment->billing_id) }}" class="text-primary">
                                {{ $onlinePayment->billing?->billing_number ?? '—' }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Type</label>
                        <div class="fw-semibold">{{ $onlinePayment->billing?->paymentType?->payment_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Period</label>
                        <div class="fw-semibold">{{ $onlinePayment->billing?->billing_period ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Total Amount</label>
                        <div class="fw-semibold">₱{{ number_format((float) ($onlinePayment->billing?->total_amount ?? 0), 2) }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Household</label>
                        <div class="fw-semibold">
                            {{ $onlinePayment->household?->household_head_name ?? '—' }}
                            <small class="text-muted">({{ $onlinePayment->household?->household_number ?? '—' }},
                                Block {{ $onlinePayment->household?->block_number ?? '—' }},
                                Lot {{ $onlinePayment->household?->lot_number ?? '—' }})</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-image me-2 text-primary"></i>Payment Screenshot (Proof)</h6>
            </div>
            <div class="card-body px-4 pb-4 text-center">
                <a href="{{ asset('storage/' . $onlinePayment->proof_path) }}" target="_blank">
                    <img src="{{ asset('storage/' . $onlinePayment->proof_path) }}" alt="Payment proof"
                         class="img-fluid rounded border" style="max-height: 320px;">
                </a>
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $onlinePayment->proof_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-zoom-in me-1"></i>View Full Size
                    </a>
                </div>
            </div>
        </div>

        @if($onlinePayment->status === 'pending')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clipboard-check me-2 text-warning"></i>Review Request</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('online-payments.approve', $onlinePayment) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Approve this online payment and record it?');">
                        <i class="bi bi-check-circle me-1"></i>Approve &amp; Record Payment
                    </button>
                    <small class="text-muted d-block mt-1">Redirects you to Record Payment with details filled in automatically.</small>
                </form>

                <hr>

                <form action="{{ route('online-payments.decline', $onlinePayment) }}" method="POST">
                    @csrf
                    <label for="declined_reason" class="form-label fw-semibold">Decline Reason <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control @error('declined_reason') is-invalid @enderror mb-2"
                              id="declined_reason" name="declined_reason" rows="2" maxlength="500"
                              placeholder="e.g. Screenshot is unclear, amount does not match...">{{ old('declined_reason') }}</textarea>
                    @error('declined_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Decline this online payment? The resident will be notified.');">
                        <i class="bi bi-x-circle me-1"></i>Decline Request
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
