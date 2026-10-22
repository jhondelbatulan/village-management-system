@extends('layouts.app')

@section('title', 'GCash Details')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">GCash Details</h4>
            <p class="page-subtitle text-muted mb-0">
                {{ $village?->village_name ?? '—' }} — residents use these details as their guide when paying online.
            </p>
        </div>
        <a href="{{ route('online-payments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-primary"></i>GCash Account Information</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('gcash-config.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="gcash_number" class="form-label fw-semibold">GCash Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('gcash_number') is-invalid @enderror"
                               id="gcash_number" name="gcash_number"
                               value="{{ old('gcash_number', $config?->gcash_number) }}"
                               placeholder="e.g. 09171234567" maxlength="20" required>
                        @error('gcash_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">The GCash mobile number residents will send their payment to.</small>
                    </div>

                    <div class="mb-4">
                        <label for="gcash_name" class="form-label fw-semibold">GCash Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('gcash_name') is-invalid @enderror"
                               id="gcash_name" name="gcash_name"
                               value="{{ old('gcash_name', $config?->gcash_name) }}"
                               placeholder="e.g. JUAN DELA CRUZ" maxlength="255" required>
                        @error('gcash_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">The name registered to the GCash account.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>{{ $config ? 'Update GCash Details' : 'Save GCash Details' }}
                        </button>
                        <a href="{{ route('online-payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>How It Works</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <ol class="mb-3 ps-3">
                    <li class="mb-2">Residents view your GCash number and account name as their payment guide.</li>
                    <li class="mb-2">They send the payment via GCash and attach a screenshot of their transaction on the bill's <strong>Pay Online</strong> form.</li>
                    <li class="mb-2">The request appears under <strong>Online Payments</strong> for your review.</li>
                    <li class="mb-2">You approve or decline it — approving redirects you to Record Payment with the details already filled in.</li>
                </ol>
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        <i class="bi bi-lightbulb me-1"></i>You can still record face-to-face payments manually through <strong>Record Payment</strong>.
                    </small>
                </div>
            </div>
        </div>

        @if($config)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-eye me-2 text-success"></i>What Residents See</h6>
            </div>
            <div class="card-body px-4 pb-4 text-center">
                <div class="bg-light rounded p-3">
                    <div class="small text-muted mb-1">Send your GCash payment to</div>
                    <div class="fs-5 fw-bold text-success">{{ $config->gcash_number }}</div>
                    <div class="fw-semibold">{{ $config->gcash_name }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
