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
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="fully_paid" {{ request('status') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
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
                        <th class="text-end">Remaining Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $billing)
                        @php
                            $paid = (float) $billing->payments->sum('total_paid');
                            $billingData = [
                                'billing_number' => $billing->billing_number,
                                'household' => $billing->household?->household_number,
                                'head' => $billing->household?->household_head_name,
                                'village' => $billing->village?->village_name,
                                'type' => $billing->paymentType?->payment_name,
                                'period' => $billing->billing_period,
                                'due_display' => $billing->due_date->format('M d, Y'),
                                'amount' => number_format((float) $billing->total_amount, 2),
                                'paid' => number_format($paid, 2),
                                'remaining' => number_format((float) $billing->total_amount - $paid, 2),
                                'status_label' => $billing->status_label,
                                'status_badge' => $billing->status_badge,
                                'remarks' => $billing->remarks,
                                'show_url' => route('billings.show', $billing),
                            ];
                        @endphp
                        <tr data-billing="{{ json_encode($billingData) }}">
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
                            <td class="text-end fw-semibold">
                                ₱{{ number_format((float) $billing->total_amount - (float) $billing->payments->sum('total_paid'), 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $billing->status_badge }}">{{ $billing->status_label }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="View"
                                        data-bs-toggle="modal" data-bs-target="#billingViewModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
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

{{-- Quick billing info modal --}}
<div class="modal fade" id="billingViewModal" tabindex="-1" aria-labelledby="billingViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="billingViewLabel">Billing Details</h6>
                    <small class="text-muted" id="billViewNumber">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge" id="billViewStatus">—</span>
                    <span class="badge bg-light text-dark" id="billViewVillage">—</span>
                </div>

                <div class="row g-2 small mb-3">
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Amount</div>
                            <div class="fw-bold" id="billViewAmount">—</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Paid</div>
                            <div class="fw-bold text-success" id="billViewPaid">—</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Remaining</div>
                            <div class="fw-bold text-danger" id="billViewRemaining">—</div>
                        </div>
                    </div>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Household</dt>
                    <dd class="col-sm-8 fw-semibold" id="billViewHousehold">—</dd>
                    <dt class="col-sm-4 text-muted">Head</dt>
                    <dd class="col-sm-8 fw-semibold" id="billViewHead">—</dd>
                    <dt class="col-sm-4 text-muted">Payment Type</dt>
                    <dd class="col-sm-8 fw-semibold" id="billViewType">—</dd>
                    <dt class="col-sm-4 text-muted">Billing Period</dt>
                    <dd class="col-sm-8 fw-semibold" id="billViewPeriod">—</dd>
                    <dt class="col-sm-4 text-muted">Due Date</dt>
                    <dd class="col-sm-8 fw-semibold" id="billViewDue">—</dd>
                    <dt class="col-sm-4 text-muted">Remarks</dt>
                    <dd class="col-sm-8" id="billViewRemarks">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="billViewFull" class="btn btn-outline-secondary btn-sm">
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
        var trigger = e.target.closest('[data-bs-target="#billingViewModal"]');
        if (!trigger) return;

        var row = trigger.closest('tr[data-billing]');
        if (!row) return;

        var d;
        try { d = JSON.parse(row.getAttribute('data-billing')); }
        catch (error) { console.error('Billing row data could not be parsed:', error); return; }

        setText('billViewNumber', 'Billing #' + (d.billing_number || ''));
        setText('billViewAmount', d.amount ? '₱' + d.amount : '');
        setText('billViewPaid', d.paid != null ? '₱' + d.paid : '');
        setText('billViewRemaining', d.remaining != null ? '₱' + d.remaining : '');
        setText('billViewHousehold', d.household);
        setText('billViewHead', d.head);
        setText('billViewType', d.type);
        setText('billViewPeriod', d.period);
        setText('billViewDue', d.due_display);
        setText('billViewRemarks', d.remarks);
        setText('billViewVillage', d.village);

        var status = document.getElementById('billViewStatus');
        if (status) {
            status.textContent = d.status_label || '—';
            status.className = 'badge ' + (d.status_badge || 'bg-secondary');
        }

        var full = document.getElementById('billViewFull');
        if (full) full.href = d.show_url || '#';
    });
});
</script>
@endpush
