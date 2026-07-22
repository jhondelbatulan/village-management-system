@extends('layouts.app')

@section('title', 'Household Payment Amounts - ' . $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Household Payment Amounts</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $village->village_name }} — Customize billing amounts per household
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payment-config.show', $village) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Config
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

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Set custom billing amounts for individual households. Leave a field at <strong>0</strong> or blank to use the default amount from payment configuration.
    <br><small class="text-muted">Custom amounts apply to the next billing generation. Existing billings are not affected.</small>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Head Name</th>
                        <th>Block / Lot</th>
                        @foreach($paymentTypes as $type)
                            <th class="text-end">{{ $type->payment_name }}</th>
                        @endforeach
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($households as $household)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">{{ $household->household_number }}</span>
                            </td>
                            <td>{{ $household->household_head_name }}</td>
                            <td>
                                <span class="text-muted">Block {{ $household->block_number ?? '—' }} Lot {{ $household->lot_number ?? '—' }}</span>
                            </td>
                            @foreach($paymentTypes as $type)
                                @php
                                    $customAmount = $household->paymentAmounts->firstWhere('payment_type_id', $type->id);
                                @endphp
                                <td class="text-end">
                                    @if($customAmount)
                                        <span class="fw-semibold text-success">₱{{ number_format($customAmount->custom_amount, 2) }}</span>
                                        <br><small class="text-muted">Default: ₱{{ number_format($type->default_amount, 2) }}</small>
                                    @else
                                        <span class="text-muted">₱{{ number_format($type->default_amount, 2) }}</span>
                                        <br><small class="text-muted">Using default</small>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end">
                                <a href="{{ route('household-amounts.edit', [$village, $household]) }}"
                                   class="btn btn-sm btn-outline-warning" title="Edit Amounts">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $paymentTypes->count() }}" class="text-center py-4">
                                <i class="bi bi-house-door fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No approved active households found in this village.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
