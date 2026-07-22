@extends('layouts.app')

@section('title', 'Edit Household - ' . $household->household_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Edit Household</h1>
        <p class="page-subtitle text-muted">Update household information for <strong>{{ $household->household_number }}</strong></p>
    </div>
    <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Household
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('households.update', $household) }}">
    @csrf
    @method('PUT')

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-geo-alt me-2"></i> Village Assignment
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Village</label>
                    <input type="text" class="form-control" value="{{ $household->village?->village_name ?? '—' }}" readonly disabled>
                </div>
                <div class="col-md-4">
                    <label for="block_number" class="form-label fw-semibold">Block Number <span class="text-danger">*</span></label>
                    <input type="text" name="block_number" id="block_number"
                        class="form-control @error('block_number') is-invalid @enderror"
                        value="{{ old('block_number', $household->block_number) }}" required placeholder="e.g. 1, 2A">
                    @error('block_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="lot_number" class="form-label fw-semibold">Lot Number <span class="text-danger">*</span></label>
                    <input type="text" name="lot_number" id="lot_number"
                        class="form-control @error('lot_number') is-invalid @enderror"
                        value="{{ old('lot_number', $household->lot_number) }}" required placeholder="e.g. 15, 23B">
                    @error('lot_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="household_head_name" class="form-label fw-semibold">Household Head Name <span class="text-danger">*</span></label>
                    <input type="text" name="household_head_name" id="household_head_name"
                        class="form-control @error('household_head_name') is-invalid @enderror"
                        value="{{ old('household_head_name', $household->household_head_name) }}" required placeholder="Full name of household head">
                    @error('household_head_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="purok" class="form-label fw-semibold">Purok</label>
                    <input type="text" name="purok" id="purok"
                        class="form-control @error('purok') is-invalid @enderror"
                        value="{{ old('purok', $household->purok) }}" placeholder="Purok / Zone">
                    @error('purok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number"
                        class="form-control @error('contact_number') is-invalid @enderror"
                        value="{{ old('contact_number', $household->contact_number) }}" placeholder="09XXXXXXXXX">
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Household Number</label>
                    <input type="text" class="form-control" value="{{ $household->household_number }}" readonly disabled>
                    <small class="text-muted">Auto-generated, cannot be changed.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-map me-2"></i> Location
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea name="address" id="address" rows="2"
                        class="form-control @error('address') is-invalid @enderror"
                        required placeholder="Full address">{{ old('address', $household->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Pin Location on Map <small class="text-muted">(click map to update)</small></label>
                    <div class="map-picker-container" id="map-picker" style="height:320px;width:100%;position:relative;">
                        <div class="map-picker-hint" id="map-hint">Click anywhere to update location</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="latitude" class="form-label fw-semibold">Latitude</label>
                    <input type="text" name="latitude" id="latitude"
                        class="form-control @error('latitude') is-invalid @enderror"
                        value="{{ old('latitude', $household->latitude) }}" placeholder="e.g. 9.123456" readonly>
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="longitude" class="form-label fw-semibold">Longitude</label>
                    <input type="text" name="longitude" id="longitude"
                        class="form-control @error('longitude') is-invalid @enderror"
                        value="{{ old('longitude', $household->longitude) }}" placeholder="e.g. 125.123456" readonly>
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-house-door me-2"></i> House Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="household_type" class="form-label fw-semibold">Household Type <span class="text-danger">*</span></label>
                    <select name="household_type" id="household_type" class="form-select @error('household_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="Single-Family" {{ old('household_type', $household->household_type) == 'Single-Family' ? 'selected' : '' }}>Single-Family</option>
                        <option value="Multi-Family" {{ old('household_type', $household->household_type) == 'Multi-Family' ? 'selected' : '' }}>Multi-Family</option>
                        <option value="Extended" {{ old('household_type', $household->household_type) == 'Extended' ? 'selected' : '' }}>Extended</option>
                        <option value="Other" {{ old('household_type', $household->household_type) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('household_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="house_ownership" class="form-label fw-semibold">House Ownership <span class="text-danger">*</span></label>
                    <select name="house_ownership" id="house_ownership" class="form-select @error('house_ownership') is-invalid @enderror" required>
                        <option value="">Select Ownership</option>
                        <option value="Owned" {{ old('house_ownership', $household->house_ownership) == 'Owned' ? 'selected' : '' }}>Owned</option>
                        <option value="Rented" {{ old('house_ownership', $household->house_ownership) == 'Rented' ? 'selected' : '' }}>Rented</option>
                        <option value="Leased" {{ old('house_ownership', $household->house_ownership) == 'Leased' ? 'selected' : '' }}>Leased</option>
                        <option value="Inherited" {{ old('house_ownership', $household->house_ownership) == 'Inherited' ? 'selected' : '' }}>Inherited</option>
                        <option value="Other" {{ old('house_ownership', $household->house_ownership) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('house_ownership')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="house_status" class="form-label fw-semibold">House Status <span class="text-danger">*</span></label>
                    <select name="house_status" id="house_status" class="form-select @error('house_status') is-invalid @enderror" required>
                        <option value="">Select Status</option>
                        <option value="owned" {{ old('house_status', $household->house_status) == 'owned' ? 'selected' : '' }}>Owned</option>
                        <option value="rented" {{ old('house_status', $household->house_status) == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="leased" {{ old('house_status', $household->house_status) == 'leased' ? 'selected' : '' }}>Leased</option>
                        <option value="inherited" {{ old('house_status', $household->house_status) == 'inherited' ? 'selected' : '' }}>Inherited</option>
                        <option value="other" {{ old('house_status', $household->house_status) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('house_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="registration_date" class="form-label fw-semibold">Registration Date <span class="text-danger">*</span></label>
                    <input type="date" name="registration_date" id="registration_date"
                        class="form-control @error('registration_date') is-invalid @enderror"
                        value="{{ old('registration_date', $household->registration_date ? $household->registration_date->format('Y-m-d') : '') }}" required>
                    @error('registration_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $household->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $household->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="relocated" {{ old('status', $household->status) == 'relocated' ? 'selected' : '' }}>Relocated</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Update Household
        </button>
    </div>
</form>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var latVal = {{ $household->latitude ? $household->latitude : '9.2372' }};
    var lngVal = {{ $household->longitude ? $household->longitude : '125.5415' }};
    var zoom = {{ $household->latitude ? 15 : 13 }};
    var pickerMap = L.map('map-picker').setView([latVal, lngVal], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap', maxZoom: 19 }).addTo(pickerMap);
    var pickerMarker = null;
    var latIn = document.getElementById('latitude');
    var lngIn = document.getElementById('longitude');
    var hint = document.getElementById('map-hint');
    if (latIn.value && lngIn.value) {
        pickerMarker = L.marker([parseFloat(latIn.value), parseFloat(lngIn.value)]).addTo(pickerMap);
        if (hint) hint.style.display = 'none';
    }
    pickerMap.on('click', function(e) {
        latIn.value = e.latlng.lat.toFixed(8);
        lngIn.value = e.latlng.lng.toFixed(8);
        if (pickerMarker) pickerMap.removeLayer(pickerMarker);
        pickerMarker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(pickerMap);
        if (hint) hint.style.display = 'none';
    });
});
</script>
@endpush
@endsection
