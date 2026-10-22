@extends('layouts.app')

@section('title', 'Edit Payment Config - ' . $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Payment Configuration</h4>
            <p class="page-subtitle text-muted mb-0">{{ $village->village_name }}</p>
        </div>
        <a href="{{ route('payment-config.show', $village) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('payment-config.update', $village) }}" method="POST" novalidate>
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-gear me-1"></i>Payment Module Settings
                    </h6>
                    <hr>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="payment_enabled" name="payment_enabled" value="1"
                                       {{ ($setting && $setting->payment_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="payment_enabled">Enable Payment Module</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_manager_role" class="form-label fw-semibold">Payment Manager</label>
                            <select class="form-select" id="payment_manager_role" name="payment_manager_role">
                                <option value="">None</option>
                                <option value="treasurer" {{ ($setting->payment_manager_role ?? '') === 'treasurer' ? 'selected' : '' }}>Treasurer</option>
                                <option value="super_admin" {{ ($setting->payment_manager_role ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin (CPDO)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-gear me-1"></i>Billing Settings
                    </h6>
                    <hr>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="grace_period_days" class="form-label fw-semibold">Grace Period</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="grace_period_days"
                                       name="grace_period_days" value="{{ $setting->grace_period_days ?? '0' }}" min="0">
                                <span class="input-group-text">days</span>
                            </div>
                            <small class="text-muted">After due date before billing is marked overdue</small>
                        </div>
                        <div class="col-md-4">
                            <label for="billing_prefix" class="form-label fw-semibold">Billing # Prefix</label>
                            <input type="text" class="form-control" id="billing_prefix" name="billing_prefix"
                                   value="{{ $setting->billing_prefix ?? 'BILL' }}" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label for="receipt_prefix" class="form-label fw-semibold">Receipt # Prefix</label>
                            <input type="text" class="form-control" id="receipt_prefix" name="receipt_prefix"
                                   value="{{ $setting->receipt_prefix ?? 'RCPT' }}" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label for="permit_prefix" class="form-label fw-semibold">Permit # Prefix</label>
                            <input type="text" class="form-control" id="permit_prefix" name="permit_prefix"
                                   value="{{ $setting->permit_prefix ?? 'PERMIT' }}" maxlength="10">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-calendar me-1"></i>Due Date Settings
                    </h6>
                    <hr>

                    <div class="row g-3" id="dueDateFields">
                        <div class="col-md-4 due-monthly" style="{{ empty($paymentTypes->where('frequency', 'monthly')->first()) ? 'display:none' : '' }}">
                            <label for="monthly_due_day" class="form-label fw-semibold">Monthly Due Day</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="monthly_due_day"
                                       name="monthly_due_day" value="{{ $setting->monthly_due_day ?? 15 }}" min="1" max="28">
                                <span class="input-group-text">of next month</span>
                            </div>
                            <small class="text-muted">Day of next month when monthly billings are due (1-28)</small>
                        </div>
                        <div class="col-md-4 due-annual" style="{{ empty($paymentTypes->where('frequency', 'annual')->first()) ? 'display:none' : '' }}">
                            <label for="annual_due_month" class="form-label fw-semibold">Annual Due Month</label>
                            <select class="form-select" id="annual_due_month" name="annual_due_month">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ ($setting->annual_due_month ?? 1) == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            <small class="text-muted">Month when annual billings are due</small>
                        </div>
                        <div class="col-md-4 due-annual" style="{{ empty($paymentTypes->where('frequency', 'annual')->first()) ? 'display:none' : '' }}">
                            <label for="annual_due_day" class="form-label fw-semibold">Annual Due Day</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="annual_due_day"
                                       name="annual_due_day" value="{{ $setting->annual_due_day ?? 15 }}" min="1" max="28">
                                <span class="input-group-text">of the due month</span>
                            </div>
                            <small class="text-muted">Day of the due month (1-28)</small>
                        </div>
                        <div class="col-12 text-muted small" id="noPaymentTypesMsg" style="{{ $paymentTypes->count() > 0 ? 'display:none' : '' }}">
                            <i class="bi bi-info-circle me-1"></i>Add payment types first to configure due dates.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-journal-text me-1"></i>Notes
                    </h6>
                    <hr>

                    <div class="mb-0">
                        <label for="payment_notes" class="form-label fw-semibold">Payment Notes</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"
                                  placeholder="Optional notes about this village's payment policies...">{{ $setting->payment_notes ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>Village Info
                    </h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Village</span>
                        <span class="fw-semibold">{{ $village->village_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Code</span>
                        <span class="fw-semibold">{{ $village->village_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-{{ $village->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($village->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="{{ route('payment-config.show', $village) }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Save Configuration
        </button>
    </div>
</form>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-list-ul me-2 text-primary"></i>Payment Types ({{ $paymentTypes->count() }})
        </h6>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentTypeModal">
            <i class="bi bi-plus-lg me-1"></i>Add Type
        </button>
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
                        <th class="text-end">Actions</th>
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
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editType{{ $type->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($type->is_active)
                                <form action="{{ route('payment-config.destroy-type', $type) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Deactivate this payment type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Deactivate">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-list-ul fs-1 d-block mb-2"></i>
            <p>No payment types configured.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentTypeModal">
                <i class="bi bi-plus-lg me-1"></i>Add Payment Type
            </button>
        </div>
        @endif
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
                        <label for="add_payment_name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_payment_name" name="payment_name" required
                               placeholder="e.g. Monthly House Rental">
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_frequency" class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_frequency" name="frequency" required>
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                                <option value="one_time">One-Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_default_amount" class="form-label fw-semibold">Default Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="add_default_amount"
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

@foreach($paymentTypes as $type)
<div class="modal fade" id="editType{{ $type->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payment-config.update-type', $type) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Payment Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_payment_name_{{ $type->id }}" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_payment_name_{{ $type->id }}" name="payment_name"
                               value="{{ $type->payment_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description_{{ $type->id }}" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="edit_description_{{ $type->id }}" name="description" rows="2">{{ $type->description }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_frequency_{{ $type->id }}" class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_frequency_{{ $type->id }}" name="frequency" required>
                                <option value="monthly" {{ $type->frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="annual" {{ $type->frequency === 'annual' ? 'selected' : '' }}>Annual</option>
                                <option value="one_time" {{ $type->frequency === 'one_time' ? 'selected' : '' }}>One-Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_default_amount_{{ $type->id }}" class="form-label fw-semibold">Default Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="edit_default_amount_{{ $type->id }}"
                                       name="default_amount" value="{{ $type->default_amount }}" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_is_active_{{ $type->id }}"
                                   name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="edit_is_active_{{ $type->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
