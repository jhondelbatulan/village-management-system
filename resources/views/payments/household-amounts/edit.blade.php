@extends('layouts.app')

@section('title', 'Edit Amounts - ' . $household->household_number)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Payment Amounts</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $household->household_number }} — {{ $household->household_head_name }} | {{ $village->village_name }}
            </p>
        </div>
        <a href="{{ route('household-amounts.index', $village) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold text-primary mb-3">
                    <i class="bi bi-house-door me-1"></i>Household Info
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Household #</label>
                        <div class="fw-semibold">{{ $household->household_number }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Head Name</label>
                        <div class="fw-semibold">{{ $household->household_head_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Location</label>
                        <div class="fw-semibold">Block {{ $household->block_number ?? '—' }} Lot {{ $household->lot_number ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('household-amounts.update', [$village, $household]) }}" method="POST">
            @csrf
            @method('PUT')

            @foreach($paymentTypes as $type)
                @php
                    $existing = $existingAmounts->get($type->id);
                @endphp
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="fw-semibold mb-0">{{ $type->payment_name }}</h6>
                                <small class="text-muted">{{ ucfirst($type->frequency) }} — Default: ₱{{ number_format($type->default_amount, 2) }}</small>
                            </div>
                            @if($existing)
                                <span class="badge bg-success">Custom Amount Set</span>
                            @else
                                <span class="badge bg-secondary">Using Default</span>
                            @endif
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Custom Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control"
                                           name="amounts[{{ $loop->index }}][custom_amount]"
                                           value="{{ $existing ? number_format($existing->custom_amount, 2, '.', '') : '' }}"
                                           min="0" placeholder="Leave blank or 0 to use default">
                                </div>
                                <small class="text-muted">Set to 0 or leave blank to remove custom amount</small>
                            </div>
                            <input type="hidden" name="amounts[{{ $loop->index }}][payment_type_id]" value="{{ $type->id }}">
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('household-amounts.index', $village) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Save Amounts
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
