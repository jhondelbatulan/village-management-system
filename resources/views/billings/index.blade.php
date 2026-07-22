@extends('layouts.app')

@section('title', 'Household Billings')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Household Billings</h4>
            <p class="page-subtitle text-muted mb-0">Manage billing records for all households.</p>
        </div>
        <div class="d-flex gap-2">
            @if($canGenerate)
            <a href="{{ route('billings.generate') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-lightning me-1"></i>Generate Billings
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('billings.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Search billing #, household #, head name..."
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
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" class="form-control form-control-sm" name="billing_period"
                           value="{{ request('billing_period') }}">
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
                        <th>Billing #</th>
                        <th>Household</th>
                        <th>Village</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $billing)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">{{ $billing->billing_number }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $billing->household?->household_number ?? '—' }}</div>
                                <small class="text-muted">{{ $billing->household?->household_head_name ?? '' }}</small>
                            </td>
                            <td>{{ $billing->village?->village_name ?? '—' }}</td>
                            <td>{{ $billing->paymentType?->payment_name ?? '—' }}</td>
                            <td>{{ $billing->billing_period }}</td>
                            <td>{{ $billing->due_date->format('M d, Y') }}</td>
                            <td class="text-end">₱{{ number_format($billing->total_amount, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $billing->status_badge }}">{{ $billing->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('billings.show', $billing) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No billing records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($billings->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $billings->links() }}
    </div>
    @endif
</div>
@endsection
