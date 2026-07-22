@extends('layouts.app')

@section('title', 'Generate Billings')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Generate Billings</h4>
            <p class="page-subtitle text-muted mb-0">Bulk generate billings for a village and billing period.</p>
        </div>
        <a href="{{ route('billings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('billings.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="village_id" class="form-label fw-semibold">Village <span class="text-danger">*</span></label>
                        <select class="form-select" id="village_id" name="village_id" required>
                            <option value="">Select Village</option>
                            @foreach($villages as $village)
                                <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                                    {{ $village->village_name }} ({{ $village->village_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="billing_period" class="form-label fw-semibold">Billing Period <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="billing_period" name="billing_period"
                               value="{{ now()->format('Y-m') }}" required>
                        <small class="text-muted">Select the month to generate billings for.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Payment Types <span class="text-danger">*</span></label>
                        <div id="payment_types_container" class="border rounded p-3 bg-light">
                            <div class="text-muted small">Select a village first to load payment types.</div>
                        </div>
                        <small class="text-muted">Check the payment types to generate billings for.</small>
                    </div>
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        This will generate billings for all <strong>approved active households</strong> in the selected village.
                        Duplicate billings (same household + type + period) will be skipped.
                    </div>
                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Generate billings for this period?')">
                        <i class="bi bi-lightning me-1"></i>Generate Billings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const villageSelect = document.getElementById('village_id');
    const container = document.getElementById('payment_types_container');

    villageSelect.addEventListener('change', async function() {
        const villageId = this.value;

        if (!villageId) {
            container.innerHTML = '<div class="text-muted small">Select a village first to load payment types.</div>';
            return;
        }

        container.innerHTML = '<div class="text-muted small"><i class="bi bi-arrow-repeat me-1"></i>Loading payment types...</div>';

        try {
            const response = await fetch(`/api/payment-config/${villageId}/payment-types`);
            const data = await response.json();
            if (data && data.length > 0) {
                let html = '<div class="form-check mb-2">';
                html += '<input class="form-check-input" type="checkbox" id="select_all" onchange="toggleAll(this)">';
                html += '<label class="form-check-label fw-semibold" for="select_all">Select All</label>';
                html += '</div><hr class="my-2">';
                data.forEach(type => {
                    const id = 'type_' + type.id;
                    html += '<div class="form-check">';
                    html += `<input class="form-check-input payment-type-checkbox" type="checkbox" name="payment_type_ids[]" value="${type.id}" id="${id}">`;
                    html += `<label class="form-check-label" for="${id}">${type.payment_name} (₱${parseFloat(type.default_amount).toFixed(2)}) <span class="badge bg-secondary">${type.frequency}</span></label>`;
                    html += '</div>';
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-muted small">No active payment types found for this village.</div>';
            }
        } catch (e) {
            console.error('Failed to load payment types:', e);
            container.innerHTML = '<div class="text-danger small">Failed to load payment types.</div>';
        }
    });
});

function toggleAll(source) {
    document.querySelectorAll('.payment-type-checkbox').forEach(cb => cb.checked = source.checked);
}
</script>
@endpush
@endsection