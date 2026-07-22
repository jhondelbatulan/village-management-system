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
                        <tr>
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
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
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
@endsection
