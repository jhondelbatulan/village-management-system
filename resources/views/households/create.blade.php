@extends('layouts.app')

@section('title', 'Register Household')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Register Household</h1>
        <p class="page-subtitle text-muted">Add a new household to the system</p>
    </div>
    <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Households
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

<form method="POST" action="{{ route('households.store') }}">
    @csrf

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-geo-alt me-2"></i> Village Assignment
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="village_id" class="form-label fw-semibold">Village <span class="text-danger">*</span></label>
                    <select name="village_id" id="village_id" class="form-select @error('village_id') is-invalid @enderror" required>
                        <option value="">Select Village</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" {{ old('village_id') == $village->id ? 'selected' : '' }}>
                                {{ $village->village_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('village_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="block_number" class="form-label fw-semibold">Block Number <span class="text-danger">*</span></label>
                    <input type="text" name="block_number" id="block_number"
                        class="form-control @error('block_number') is-invalid @enderror"
                        value="{{ old('block_number') }}" required placeholder="e.g. 1"
                        inputmode="numeric" pattern="[0-9]*" maxlength="50"
                        aria-describedby="block_number-hint">
                    <div class="form-text" id="block_number-hint">Numbers only.</div>
                    @error('block_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="lot_number" class="form-label fw-semibold">Lot Number <span class="text-danger">*</span></label>
                    <input type="text" name="lot_number" id="lot_number"
                        class="form-control @error('lot_number') is-invalid @enderror"
                        value="{{ old('lot_number') }}" required placeholder="e.g. 15"
                        inputmode="numeric" pattern="[0-9]*" maxlength="50"
                        aria-describedby="lot_number-hint">
                    <div class="form-text" id="lot_number-hint">Numbers only.</div>
                    @error('lot_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="household_head_name" class="form-label fw-semibold">Household Head Name <span class="text-danger">*</span></label>
                    <input type="text" name="household_head_name" id="household_head_name"
                        class="form-control @error('household_head_name') is-invalid @enderror"
                        value="{{ old('household_head_name') }}" required placeholder="Full name of household head"
                        maxlength="255" aria-describedby="household_head_name-hint">
                    <div class="form-text" id="household_head_name-hint">Letters only. First letter of every word is capitalized automatically.</div>
                    @error('household_head_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="purok" class="form-label fw-semibold">Purok</label>
                    <input type="text" name="purok" id="purok"
                        class="form-control @error('purok') is-invalid @enderror"
                        value="{{ old('purok') }}" placeholder="Purok / Zone">
                    @error('purok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number"
                        class="form-control @error('contact_number') is-invalid @enderror"
                        value="{{ old('contact_number') }}" placeholder="09XXXXXXXXX">
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                        required placeholder="Full address">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Pin Location on Map <small class="text-muted">(type the address to auto-pin, or click the map)</small></label>
                    <div class="map-picker-container" id="map-picker" style="height:320px;width:100%;position:relative;">
                        <div class="map-picker-hint" id="map-hint">Type an address to auto-pin, or click anywhere to pin location</div>
                    </div>
                    <div class="form-text">Blue-gray pins are the latest saved households, shown as your reference.</div>
                </div>
                <div class="col-md-6">
                    <label for="latitude" class="form-label fw-semibold">Latitude</label>
                    <input type="text" name="latitude" id="latitude"
                        class="form-control @error('latitude') is-invalid @enderror"
                        value="{{ old('latitude') }}" placeholder="e.g. 9.123456" readonly>
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="longitude" class="form-label fw-semibold">Longitude</label>
                    <input type="text" name="longitude" id="longitude"
                        class="form-control @error('longitude') is-invalid @enderror"
                        value="{{ old('longitude') }}" placeholder="e.g. 125.123456" readonly>
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
                    <label for="household_type" class="form-label fw-semibold">Family Count <span class="text-danger">*</span></label>
                    <select name="household_type" id="household_type" class="form-select @error('household_type') is-invalid @enderror" required>
                        <option value="">Select Family Count</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('household_type') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('household_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="house_ownership" class="form-label fw-semibold">House Ownership <span class="text-danger">*</span></label>
                    <select name="house_ownership" id="house_ownership" class="form-select @error('house_ownership') is-invalid @enderror" required>
                        <option value="">Select Ownership</option>
                        <option value="Owned" {{ old('house_ownership') == 'Owned' ? 'selected' : '' }}>Owned</option>
                        <option value="Leased" {{ old('house_ownership') == 'Leased' ? 'selected' : '' }}>Leased</option>
                    </select>
                    @error('house_ownership')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="house_status" class="form-label fw-semibold">House Material <span class="text-danger">*</span></label>
                    <select name="house_status" id="house_status" class="form-select @error('house_status') is-invalid @enderror" required>
                        <option value="">Select Material</option>
                        <option value="concrete" {{ old('house_status') == 'concrete' ? 'selected' : '' }}>Concrete</option>
                        <option value="wood_timber" {{ old('house_status') == 'wood_timber' ? 'selected' : '' }}>Wood/Timber</option>
                        <option value="steel" {{ old('house_status') == 'steel' ? 'selected' : '' }}>Steel</option>
                        <option value="brick_blocks" {{ old('house_status') == 'brick_blocks' ? 'selected' : '' }}>Brick/Blocks</option>
                        <option value="stone" {{ old('house_status') == 'stone' ? 'selected' : '' }}>Stone</option>
                        <option value="mixed" {{ old('house_status') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                        <option value="other" {{ old('house_status') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('house_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="registration_date" class="form-label fw-semibold">Registration Date <span class="text-danger">*</span></label>
                    <input type="date" name="registration_date" id="registration_date"
                        class="form-control @error('registration_date') is-invalid @enderror"
                        value="{{ old('registration_date', date('Y-m-d')) }}" required readonly
                        aria-describedby="registration_date-hint">
                    <div class="form-text" id="registration_date-hint">Set automatically to today's date. Cannot be edited.</div>
                    @error('registration_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="relocated" {{ old('status') == 'relocated' ? 'selected' : '' }}>Relocated</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="alert alert-info w-100 mb-0 py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        <small>Household number is auto-generated upon creation.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Register Household
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
    var pickerMap = L.map('map-picker').setView([9.2372, 125.5415], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap', maxZoom: 19 }).addTo(pickerMap);
    var pickerMarker = null;
    var latIn = document.getElementById('latitude');
    var lngIn = document.getElementById('longitude');
    var hint = document.getElementById('map-hint');
    var addressIn = document.getElementById('address');
    var villageSelect = document.getElementById('village_id');

    var referenceLayer = L.layerGroup().addTo(pickerMap);
    var latestHouseholds = @json($latestHouseholds ?? []);
    var pinVersion = 0;
    var addrTimer = null;

    var referenceIcon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:11px;height:11px;background:#6c757d;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.4);"></div>',
        iconSize: [15, 15], iconAnchor: [7, 7],
    });

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function setHint(text, show) {
        if (!hint) return;
        hint.textContent = text;
        hint.style.display = show ? '' : 'none';
    }

    function renderReferenceMarkers(list) {
        referenceLayer.clearLayers();
        list.forEach(function(h) {
            if (!h.latitude || !h.longitude) return;
            L.marker([h.latitude, h.longitude], { icon: referenceIcon })
                .bindPopup('<div class="gis-popup"><span class="badge bg-secondary">Latest saved</span><br><strong>' + escHtml(h.household_number) + '</strong><br><small>' + escHtml(h.household_head_name || '') + '</small><br><small>' + escHtml(h.village ? h.village.village_name : '') + '</small></div>')
                .addTo(referenceLayer);
        });
    }

    function fitToReference() {
        var layers = referenceLayer.getLayers();
        if (layers.length > 0) {
            pickerMap.fitBounds(L.featureGroup(layers).getBounds().pad(0.2));
        }
    }

    function loadReferenceMarkers() {
        var vid = villageSelect ? villageSelect.value : '';
        if (!vid) {
            renderReferenceMarkers(latestHouseholds);
            return;
        }
        fetch('/gis/village/' + vid)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.households) {
                    renderReferenceMarkers(res.households);
                    fitToReference();
                    if (res.center && res.center.lat) {
                        pickerMap.setView([res.center.lat, res.center.lng], 15);
                    }
                }
            })
            .catch(function() {});
    }

    renderReferenceMarkers(latestHouseholds);
    if (latIn.value && lngIn.value) {
        pickerMarker = L.marker([parseFloat(latIn.value), parseFloat(lngIn.value)]).addTo(pickerMap);
        pickerMap.setView([parseFloat(latIn.value), parseFloat(lngIn.value)], 15);
        setHint('Click anywhere to pin location', false);
    } else if (latestHouseholds.length > 0) {
        fitToReference();
    }

    if (villageSelect) {
        villageSelect.addEventListener('change', loadReferenceMarkers);
    }

    function setPin(lat, lng, moveMap) {
        latIn.value = lat.toFixed(8);
        lngIn.value = lng.toFixed(8);
        if (pickerMarker) pickerMap.removeLayer(pickerMarker);
        pickerMarker = L.marker([lat, lng]).addTo(pickerMap);
        setHint('Click anywhere to pin location', false);
        if (moveMap) pickerMap.setView([lat, lng], 16);
    }

    pickerMap.on('click', function(e) {
        pinVersion++;
        clearTimeout(addrTimer);
        setPin(e.latlng.lat, e.latlng.lng, false);
    });

    function geocodeAddress(q) {
        return fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (Array.isArray(json) && json.length > 0) {
                    return { lat: parseFloat(json[0].lat), lng: parseFloat(json[0].lon) };
                }
                return null;
            })
            .catch(function() { return null; });
    }

    function autoPinFromAddress() {
        if (!addressIn) return;
        var q = addressIn.value.trim();
        if (q.length < 6) return;

        var context = '';
        if (villageSelect && villageSelect.value) {
            var opt = villageSelect.selectedOptions[0];
            if (opt && opt.text && opt.text !== 'Select Village') context = ', ' + opt.text;
        }

        var seq = ++pinVersion;
        setHint('Searching address...', true);
        geocodeAddress(q + context).then(function(loc) {
            if (seq !== pinVersion) return;
            if (!loc) {
                setHint('Address not found — click the map to pin manually', true);
                return;
            }
            setPin(loc.lat, loc.lng, true);
        });
    }

    if (addressIn) {
        addressIn.addEventListener('input', function() {
            clearTimeout(addrTimer);
            addrTimer = setTimeout(autoPinFromAddress, 1000);
        });
        addressIn.addEventListener('blur', function() {
            clearTimeout(addrTimer);
            autoPinFromAddress();
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function clearInvalid(el) {
        if (el) el.classList.remove('is-invalid');
    }

    function titleCaseName(value) {
        value = value.replace(/\s+/g, ' ').trim();

        if (value === '') return '';

        if (value === value.toUpperCase() || value === value.toLowerCase()) {
            value = value.toLowerCase();
        }

        return value.split(' ').map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    ['block_number', 'lot_number'].forEach(function(id) {
        var field = document.getElementById(id);
        if (!field) return;

        field.addEventListener('input', function() {
            field.value = field.value.replace(/[^0-9]/g, '');
            clearInvalid(field);
        });
    });

    var headName = document.getElementById('household_head_name');

    if (headName) {
        headName.addEventListener('input', function() {
            headName.value = headName.value.replace(/[^\p{L} ]/gu, '').replace(/ {2,}/g, ' ');
            clearInvalid(headName);
        });

        headName.addEventListener('blur', function() {
            headName.value = titleCaseName(headName.value);
        });
    }
});
</script>
@endpush
@endsection
