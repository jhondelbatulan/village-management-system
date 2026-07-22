@extends('layouts.app')

@section('title', 'Payment Config - ' . $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">{{ $village->village_name }} — Payment Configuration</h4>
            <p class="page-subtitle text-muted mb-0">Village payment settings and payment types.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payment-config.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <a href="{{ route('payment-config.edit', $village) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
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
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-gear me-2 text-primary"></i>Payment Settings
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                @if($setting)
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Module</label>
                        <p class="mb-0">
                            @if($setting->payment_enabled)
                                <span class="badge bg-success fs-6">Enabled</span>
                            @else
                                <span class="badge bg-secondary fs-6">Disabled</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Manager</label>
                        <p class="fw-semibold mb-0">{{ ucfirst($setting->payment_manager_role ?? 'None') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Penalty Rate</label>
                        <p class="fw-semibold mb-0">{{ $setting->penalty_rate }}% per month</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Grace Period</label>
                        <p class="fw-semibold mb-0">{{ $setting->grace_period_days }} days</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Receipt Prefix</label>
                        <p class="fw-semibold mb-0">{{ $setting->receipt_prefix }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Billing Prefix</label>
                        <p class="fw-semibold mb-0">{{ $setting->billing_prefix }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Permit Prefix</label>
                        <p class="fw-semibold mb-0">{{ $setting->permit_prefix }}</p>
                    </div>
                    @if($setting->monthly_due_day)
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Monthly Due Day</label>
                        <p class="fw-semibold mb-0">Day {{ $setting->monthly_due_day }} of each month</p>
                    </div>
                    @endif
                    @if($setting->annual_due_month)
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Annual Due Month</label>
                        <p class="fw-semibold mb-0">{{ Carbon\Carbon::create()->month($setting->annual_due_month)->format('F') }}</p>
                    </div>
                    @endif
                    @if($setting->annual_due_day)
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Annual Due Day</label>
                        <p class="fw-semibold mb-0">Day {{ $setting->annual_due_day }} of the due month</p>
                    </div>
                    @endif
                    @if($setting->payment_notes)
                    <div class="col-12">
                        <label class="form-label text-muted small">Payment Notes</label>
                        <p class="mb-0">{{ $setting->payment_notes }}</p>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-gear fs-1 d-block mb-2"></i>
                    <p>No payment configuration set for this village.</p>
                    <a href="{{ route('payment-config.edit', $village) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i>Configure Now
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-primary"></i>Payment Types ({{ $paymentTypes->count() }})
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('household-amounts.index', $village) }}" class="btn btn-outline-success btn-sm" title="Manage per-household amounts">
                        <i class="bi bi-house-door me-1"></i>Household Amounts
                    </a>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentTypeModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Type
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($paymentTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Frequency</th>
                                <th class="text-end">Default Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentTypes as $type)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $type->payment_name }}</div>
                                        @if($type->description)
                                            <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($type->frequency) }}</span></td>
                                    <td class="text-end">₱{{ number_format($type->default_amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($type->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <p>No payment types configured.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payment-config.store-type') }}" method="POST">
                @csrf
                <input type="hidden" name="village_id" value="{{ $village->id }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Payment Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payment_name" name="payment_name" required
                               placeholder="e.g. Monthly House Rental">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="frequency" class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="frequency" name="frequency" required>
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                                <option value="one_time">One-Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="default_amount" class="form-label fw-semibold">Default Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="default_amount"
                                       name="default_amount" value="0.00" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
