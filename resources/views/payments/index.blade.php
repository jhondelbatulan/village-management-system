@extends('layouts.app')

@section('title', 'Payment Records')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Payment Records</h4>
            <p class="page-subtitle text-muted mb-0">View all payment transactions.</p>
        </div>
        <div class="d-flex gap-2">
            @if($canRecordPayment)
            <a href="{{ route('payments.create') }}" class="btn btn-success btn-sm">
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Search receipt, billing, household..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="village_id">
                        <option value="">All Villages</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                                {{ $village->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="payment_type_id">
                        <option value="">All Types</option>
                        @foreach($paymentTypes as $type)
                            <option value="{{ $type->id }}" {{ request('payment_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->payment_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="payment_status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="month">
                        <option value="">Month</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->format('M') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="year">
                        <option value="">Year</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="payment_method">
                        <option value="">Method</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="gcash" {{ request('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="maya" {{ request('payment_method') === 'maya' ? 'selected' : '' }}>Maya</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Billing #</th>
                        <th>Household</th>
                        <th>Village</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th class="text-end">Total Paid</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php
                            $paymentData = [
                                'receipt' => $payment->receipt_number,
                                'billing' => $payment->billing?->billing_number,
                                'household' => $payment->household?->household_number,
                                'head' => $payment->household?->household_head_name,
                                'village' => $payment->village?->village_name,
                                'type' => $payment->paymentType?->payment_name,
                                'date_display' => $payment->payment_date->format('M d, Y'),
                                'amount' => number_format((float) $payment->amount, 2),
                                'penalty' => number_format((float) $payment->penalty, 2),
                                'discount' => number_format((float) $payment->discount, 2),
                                'total_paid' => number_format((float) $payment->total_paid, 2),
                                'method' => $payment->payment_method_label,
                                'reference' => $payment->reference_number,
                                'remarks' => $payment->remarks,
                                'status_label' => $payment->status_label,
                                'status_badge' => $payment->status_badge,
                                'show_url' => route('payments.show', $payment),
                                'receipt_url' => route('payments.receipt', $payment),
                            ];
                        @endphp
                        <tr data-payment="{{ json_encode($paymentData) }}">
                            <td>
                                <span class="fw-semibold text-primary">{{ $payment->receipt_number }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $payment->billing?->billing_number ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $payment->household?->household_number ?? '—' }}</div>
                                <small class="text-muted">{{ $payment->household?->household_head_name ?? '' }}</small>
                            </td>
                            <td>{{ $payment->village?->village_name ?? '—' }}</td>
                            <td>{{ $payment->paymentType?->payment_name ?? '—' }}</td>
                            <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td class="text-end fw-semibold">₱{{ number_format($payment->total_paid, 2) }}</td>
                            <td>{{ $payment->payment_method_label }}</td>
                            <td class="text-center">
                                <span class="badge {{ $payment->status_badge }}">{{ $payment->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="View"
                                        data-bs-toggle="modal" data-bs-target="#paymentViewModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success" title="Receipt" target="_blank">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No payment records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $payments->links() }}
    </div>
    @endif
</div>

{{-- Quick payment info modal --}}
<div class="modal fade" id="paymentViewModal" tabindex="-1" aria-labelledby="paymentViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="paymentViewLabel">Payment Details</h6>
                    <small class="text-muted" id="payViewReceipt">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge" id="payViewStatus">—</span>
                    <span class="badge bg-light text-dark" id="payViewVillage">—</span>
                </div>

                <div class="row g-2 small mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Amount</div>
                            <div class="fw-bold" id="payViewAmount">—</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Penalty / Discount</div>
                            <div class="fw-bold" id="payViewAdjust">—</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Total Paid</div>
                            <div class="fw-bold text-success" id="payViewTotal">—</div>
                        </div>
                    </div>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Billing #</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewBilling">—</dd>
                    <dt class="col-sm-4 text-muted">Household</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewHousehold">—</dd>
                    <dt class="col-sm-4 text-muted">Head</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewHead">—</dd>
                    <dt class="col-sm-4 text-muted">Payment Type</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewType">—</dd>
                    <dt class="col-sm-4 text-muted">Payment Date</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewDate">—</dd>
                    <dt class="col-sm-4 text-muted">Method</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewMethod">—</dd>
                    <dt class="col-sm-4 text-muted">Reference #</dt>
                    <dd class="col-sm-8 fw-semibold" id="payViewReference">—</dd>
                    <dt class="col-sm-4 text-muted">Remarks</dt>
                    <dd class="col-sm-8" id="payViewRemarks">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="payViewReceiptLink" class="btn btn-outline-success btn-sm" target="_blank">
                    <i class="bi bi-receipt me-1"></i> Receipt
                </a>
                <a href="#" id="payViewFull" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open full page
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = (value == null || value === '') ? '—' : value;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-bs-target="#paymentViewModal"]');
        if (!trigger) return;

        var row = trigger.closest('tr[data-payment]');
        if (!row) return;

        var d;
        try { d = JSON.parse(row.getAttribute('data-payment')); }
        catch (error) { console.error('Payment row data could not be parsed:', error); return; }

        setText('payViewReceipt', 'Receipt #' + (d.receipt || ''));
        setText('payViewAmount', d.amount ? '₱' + d.amount : '');
        setText('payViewAdjust', '₱' + (d.penalty || '0.00') + ' / ₱' + (d.discount || '0.00'));
        setText('payViewTotal', d.total_paid ? '₱' + d.total_paid : '');
        setText('payViewBilling', d.billing);
        setText('payViewHousehold', d.household);
        setText('payViewHead', d.head);
        setText('payViewType', d.type);
        setText('payViewDate', d.date_display);
        setText('payViewMethod', d.method);
        setText('payViewReference', d.reference);
        setText('payViewRemarks', d.remarks);
        setText('payViewVillage', d.village);

        var status = document.getElementById('payViewStatus');
        if (status) {
            status.textContent = d.status_label || '—';
            status.className = 'badge ' + (d.status_badge || 'bg-secondary');
        }

        var full = document.getElementById('payViewFull');
        if (full) full.href = d.show_url || '#';

        var receipt = document.getElementById('payViewReceiptLink');
        if (receipt) receipt.href = d.receipt_url || '#';
    });
});
</script>
@endpush
