@extends('layouts.app')

@section('title', 'My Household Location')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">My Household Location</h1>
        <p class="page-subtitle text-muted">View your household on the map</p>
    </div>
    <a href="{{ route('dashboard.resident') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-map me-2"></i> {{ $household->household_number }}
                </h5>
                <span class="badge bg-primary">{{ $household->village->village_name ?? 'N/A' }}</span>
            </div>
            <div class="card-body p-0">
                <div id="household-map" style="height: 450px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-house-door me-2"></i> Household Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted fw-semibold">Number</td><td>{{ $household->household_number }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Head</td><td>{{ $household->household_head_name }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Block</td><td>{{ $household->block_number }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Lot</td><td>{{ $household->lot_number }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Purok</td><td>{{ $household->purok ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Type</td><td>{{ $household->household_type }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Status</td>
                        <td>
                            <span class="badge bg-{{ $household->status === 'active' ? 'success' : ($household->status === 'inactive' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($household->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2"></i> Coordinates</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Latitude:</strong> {{ $household->latitude }}</p>
                <p class="mb-0"><strong>Longitude:</strong> {{ $household->longitude }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-fullscreen@1.0.2/dist/Leaflet.fullscreen.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-fullscreen@1.0.2/dist/leaflet.fullscreen.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    var center = @json($center);
    var map = L.map('household-map', {
        fullscreenControl: true,
        fullscreenControlOptions: { position: 'topleft' },
    }).setView([center.lat, center.lng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    var icon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:18px;height:18px;background:#28a745;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.5);"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });

    L.marker([center.lat, center.lng], { icon: icon })
        .bindPopup('<strong>{{ $household->household_number }}</strong><br><small>{{ $household->household_head_name }}</small>')
        .addTo(map)
        .openPopup();
});
</script>
<style>
.gis-popup { text-align: left; }
</style>
@endpush
@endsection
