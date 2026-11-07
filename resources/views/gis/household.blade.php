@extends('layouts.app')

@section('title', 'My Village Map')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">My Village Map</h1>
        <p class="page-subtitle text-muted">Your household pinpoint and your neighbors' locations in {{ $village?->village_name ?? 'your village' }}</p>
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
                    <i class="bi bi-map me-2"></i> {{ $household?->household_number ?? ($village?->village_name ?? 'Village Map') }}
                </h5>
                <span class="badge bg-primary">{{ $village?->village_name ?? ($household?->village?->village_name ?? 'N/A') }}</span>
            </div>
            <div class="card-body p-0">
                <div id="household-map" style="height: 450px; width: 100%;"></div>
            </div>
            <div class="card-footer d-flex flex-wrap gap-4 align-items-center">
                <span class="small"><span style="display:inline-block;width:12px;height:12px;background:#28a745;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.4);vertical-align:middle;margin-right:6px;"></span>Your household</span>
                <span class="small"><span style="display:inline-block;width:10px;height:10px;background:#1a73e8;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.4);vertical-align:middle;margin-right:6px;"></span>Neighbor ({{ $neighborCount }})</span>
            </div>
        </div>

        @if($household && $household->approval_status !== 'approved')
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-hourglass-split me-1"></i>
                Your household is still awaiting final approval — its location will be shown here once the Super Admin approves it.
            </div>
        @elseif(! $household || ! $household->latitude || ! $household->longitude)
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Your household does not have a saved pinpoint yet — contact your village secretary to set it.
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-house-door me-2"></i> Household Info</h6>
            </div>
            <div class="card-body">
                @if($household)
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted fw-semibold">Number</td><td>{{ $household->household_number }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Head</td><td>{{ $household->household_head_name }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Block</td><td>{{ $household->block_number }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Lot</td><td>{{ $household->lot_number }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Purok</td><td>{{ $household->purok ?? '-' }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Family Count</td><td>{{ $household->household_type }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Status</td>
                            <td>
                                <span class="badge bg-{{ $household->status === 'active' ? 'success' : ($household->status === 'inactive' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($household->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted mb-0 small">No household is linked to this account yet. Ask your village secretary to assign you to a household.</p>
                @endif
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Mapped neighbors</small>
                    <strong>{{ $neighborCount }}</strong>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2"></i> Coordinates</h6>
            </div>
            <div class="card-body">
                @if($household && $household->approval_status === 'approved' && $household->latitude && $household->longitude)
                    <p class="mb-1"><strong>Latitude:</strong> {{ $household->latitude }}</p>
                    <p class="mb-0"><strong>Longitude:</strong> {{ $household->longitude }}</p>
                @elseif($household && $household->approval_status !== 'approved')
                    <p class="mb-0 text-muted">Hidden until Super Admin approval.</p>
                @else
                    <p class="mb-0 text-muted">Not set yet.</p>
                @endif
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
    var neighbors = @json($neighbors);
    var ownId = {{ $household?->id ?? 'null' }};

    var map = L.map('household-map', {
        fullscreenControl: true,
        fullscreenControlOptions: { position: 'topleft' },
    }).setView([center.lat, center.lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    var ownVillage = <?php echo json_encode($village && $village->latitude !== null && $village->longitude !== null ? [
        'id' => $village->id,
        'village_name' => $village->village_name,
        'latitude' => (float) $village->latitude,
        'longitude' => (float) $village->longitude,
    ] : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

    if (ownVillage && ownVillage.latitude && ownVillage.longitude) {
        var villageMarkerIcon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:34px;height:34px;background:#1a73e8;border:4px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;"><i class="bi bi-building" style="color:#fff;font-size:15px;"></i></div>',
            iconSize: [42, 42], iconAnchor: [21, 21],
        });

        L.marker([ownVillage.latitude, ownVillage.longitude], { icon: villageMarkerIcon })
            .bindPopup('<div class="gis-popup"><span class="badge bg-primary">Village</span><br><strong style="color:#1a73e8;">' + escHtml(ownVillage.village_name) + '</strong></div>')
            .addTo(map);
    }

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    var ownMarker = null;
    var markers = [];

    neighbors.forEach(function(h) {
        if (!h.latitude || !h.longitude) return;

        var isOwn = ownId !== null && h.id == ownId;
        var icon = L.divIcon({
            className: 'gis-marker',
            html: isOwn
                ? '<div style="width:18px;height:18px;background:#28a745;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.5);"></div>'
                : '<div style="width:12px;height:12px;background:#1a73e8;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
            iconSize: isOwn ? [24, 24] : [16, 16],
            iconAnchor: isOwn ? [12, 12] : [8, 8],
        });

        var m = L.marker([h.latitude, h.longitude], { icon: icon });
        m.bindPopup(
            '<div class="gis-popup">' +
            (isOwn
                ? '<span class="badge bg-success">Your Household</span>'
                : '<span class="badge bg-primary">Neighbor</span>') +
            '<br><strong style="color:#1a73e8;">' + escHtml(h.household_number) + '</strong><br>' +
            '<small class="text-muted">' + escHtml(h.household_head_name || '') + '</small><br>' +
            '<small>Block/Lot: ' + escHtml(h.block_number || '-') + '/' + escHtml(h.lot_number || '-') + '</small>' +
            '</div>'
        );
        m.addTo(map);
        markers.push(m);
        if (isOwn) ownMarker = m;
    });

    if (markers.length > 1) {
        map.fitBounds(L.featureGroup(markers).getBounds().pad(0.15));
    } else if (markers.length === 1) {
        map.setView(markers[0].getLatLng(), 17);
    }

    if (ownMarker) {
        ownMarker.openPopup();
    }
});
</script>
<style>
.gis-popup { text-align: left; }
</style>
@endpush
@endsection
