@extends('layouts.app')

@section('title', 'Edit Village')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Village</h4>
            <p class="page-subtitle text-muted mb-0">Update information for {{ $village->village_name }}.</p>
        </div>
        <a href="{{ route('villages.show', $village) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

<form action="{{ route('villages.update', $village) }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-building me-1"></i>Basic Information
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="village_name" class="form-label fw-semibold">Village Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('village_name') is-invalid @enderror"
                           id="village_name" name="village_name" value="{{ old('village_name', $village->village_name) }}" required>
                    @error('village_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="village_code" class="form-label fw-semibold">Village Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('village_code') is-invalid @enderror"
                           id="village_code" name="village_code" value="{{ old('village_code', $village->village_code) }}" required>
                    @error('village_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description', $village->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                           id="contact_number" name="contact_number" value="{{ old('contact_number', $village->contact_number) }}">
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email', $village->email) }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="established_date" class="form-label fw-semibold">Established Date</label>
                    <input type="date" class="form-control @error('established_date') is-invalid @enderror"
                           id="established_date" name="established_date" value="{{ old('established_date', $village->established_date ? \Carbon\Carbon::parse($village->established_date)->format('Y-m-d') : '') }}">
                    @error('established_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-geo-alt me-1"></i>Village Location
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                           id="address" name="address" value="{{ old('address', $village->address) }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Pin Village Location <small class="text-muted">(click map to place marker, drag to adjust)</small></label>
                    <div id="village-map-picker" style="height:350px;width:100%;border:2px solid #dee2e6;border-radius:8px;position:relative;">
                        <div id="map-hint" style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);z-index:1000;background:rgba(26,75,140,.9);color:#fff;padding:4px 14px;border-radius:20px;font-size:0.8rem;pointer-events:none;">
                            Click to place village marker
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="latitude" class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                    <input type="text" step="any" class="form-control @error('latitude') is-invalid @enderror"
                           id="latitude" name="latitude" value="{{ old('latitude', $village->latitude) }}" readonly required>
                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="longitude" class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                    <input type="text" step="any" class="form-control @error('longitude') is-invalid @enderror"
                           id="longitude" name="longitude" value="{{ old('longitude', $village->longitude) }}" readonly required>
                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="total_area" class="form-label fw-semibold">Total Area</label>
                    <input type="number" step="any" class="form-control @error('total_area') is-invalid @enderror"
                           id="total_area" name="total_area" value="{{ old('total_area', $village->total_area) }}">
                    @error('total_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            @if(!$village->latitude || !$village->longitude)
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <small><strong>Village location not configured.</strong> Please click on the map to set the village GPS coordinates.</small>
            </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-toggle-on me-1"></i>Status
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror"
                            id="status" name="status" required>
                        <option value="active" {{ old('status', $village->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $village->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('villages.show', $village) }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Update Village
        </button>
    </div>
</form>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h6 class="fw-semibold text-primary mb-0">
            <i class="bi bi-images me-1"></i>Village Photos <span class="badge bg-secondary ms-1">Max 3</span>
        </h6>
        <hr>

        @if($village->photos->count() > 0)
            <div class="row g-3 mb-3">
                @foreach($village->photos as $photo)
                    <div class="col-md-4">
                        <div class="position-relative border rounded p-2 text-center">
                            <img src="{{ $photo->photo_url }}" alt="Village Photo" class="img-fluid rounded" style="height:150px;object-fit:cover;width:100%;">
                            <form action="{{ route('villages.photos.delete', [$village, $photo]) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this photo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted small mb-3">No photos uploaded yet.</p>
        @endif

        @if($village->photos->count() < 3)
            <form action="{{ route('villages.photos.upload', $village) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label for="photo" class="form-label fw-semibold">Add Photo</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" required>
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-info py-2 mb-0">
                <i class="bi bi-info-circle me-1"></i><small>Maximum of 3 photos reached. Remove an existing photo to upload a new one.</small>
            </div>
        @endif
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .map-picker-container { cursor: crosshair; }
    .map-picker-container:hover { border-color: var(--mvms-primary); }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var latVal = {{ $village->latitude ? $village->latitude : '9.2372' }};
    var lngVal = {{ $village->longitude ? $village->longitude : '125.5415' }};
    var zoom = {{ $village->latitude ? 15 : 12 }};

    var pickerMap = L.map('village-map-picker').setView([latVal, lngVal], zoom);

    // Satellite imagery (default) and the street map, switchable live through
    // the layer control — no page reload, position and pins are kept.
    // Esri World Imagery Wayback (release WB_2026_R04) is cloud-free over the
    // three villages and serves real imagery through z18 only.
    var satelliteLayer = L.tileLayer('https://wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
        maxZoom: 18,
    });

    var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    });

    satelliteLayer.addTo(pickerMap);

    L.control.layers(
        { 'Satellite View': satelliteLayer, 'Street Map': streetLayer },
        null,
        { position: 'topleft', collapsed: window.innerWidth < 768 }
    ).addTo(pickerMap);

    var pickerMarker = null;
    var latIn = document.getElementById('latitude');
    var lngIn = document.getElementById('longitude');
    var hint = document.getElementById('map-hint');

    if (latIn.value && lngIn.value) {
        pickerMarker = L.marker([parseFloat(latIn.value), parseFloat(lngIn.value)], { draggable: true }).addTo(pickerMap);
        if (hint) hint.style.display = 'none';

        pickerMarker.on('dragend', function(e) {
            var pos = e.target.getLatLng();
            latIn.value = pos.lat.toFixed(7);
            lngIn.value = pos.lng.toFixed(7);
        });
    }

    pickerMap.on('click', function(e) {
        latIn.value = e.latlng.lat.toFixed(7);
        lngIn.value = e.latlng.lng.toFixed(7);
        if (pickerMarker) pickerMap.removeLayer(pickerMarker);
        pickerMarker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(pickerMap);
        if (hint) hint.style.display = 'none';

        pickerMarker.on('dragend', function(ev) {
            var pos = ev.target.getLatLng();
            latIn.value = pos.lat.toFixed(7);
            lngIn.value = pos.lng.toFixed(7);
        });
    });
});
</script>
@endpush
@endsection
