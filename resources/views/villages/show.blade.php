@extends('layouts.app')

@section('title', $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">
                {{ $village->village_name }}
                @if($village->status === 'active')
                    <span class="badge bg-success fs-6 align-middle ms-2">Active</span>
                @else
                    <span class="badge bg-secondary fs-6 align-middle ms-2">Inactive</span>
                @endif
            </h4>
            <p class="page-subtitle text-muted mb-0">Village profile and overview.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('villages.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Villages
            </a>
            @if(isset($canEdit) && $canEdit)
                <a href="{{ route('villages.edit', $village) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endif
        </div>
    </div>
</div>

@if($village->cover_photo)
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <img src="{{ asset('storage/' . $village->cover_photo) }}" class="card-img-top" alt="{{ $village->village_name }} Cover" style="max-height: 300px; object-fit: cover;">
    </div>
@endif

@if($village->photos->count() > 0)
    <div class="mb-4">
        @include('partials.village-photos-slideshow', ['villagePhotos' => $village->photos])
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold text-primary mb-3">
                    <i class="bi bi-building me-1"></i>Village Information
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Village Code</p>
                        <p class="fw-semibold mb-0">{{ $village->village_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Village Name</p>
                        <p class="fw-semibold mb-0">{{ $village->village_name }}</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1">Description</p>
                        <p class="mb-0">{{ $village->description ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Address</p>
                        <p class="mb-0">{{ $village->address ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Contact Number</p>
                        <p class="mb-0">{{ $village->contact_number ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Email</p>
                        <p class="mb-0">{{ $village->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Established Date</p>
                        <p class="mb-0">{{ $village->established_date ? \Carbon\Carbon::parse($village->established_date)->format('M d, Y') : '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Latitude</p>
                        <p class="mb-0">{{ $village->latitude ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Longitude</p>
                        <p class="mb-0">{{ $village->longitude ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Total Area</p>
                        <p class="mb-0">{{ $village->total_area ? $village->total_area . ' ha' : '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Status</p>
                        <p class="mb-0">
                            @if($village->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($village->latitude && $village->longitude)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success bg-opacity-10 py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2"></i> Village Map</h6>
            </div>
            <div class="card-body p-0">
                <div id="village-map" style="height:220px;width:100%;"></div>
            </div>
            <div class="card-footer bg-white border-0 text-center py-2">
                <a href="{{ route('gis.village-api', $village->id) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-arrows-fullscreen me-1"></i> View Full Map
                </a>
            </div>
        </div>
        @endif

        @if($village->logo)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <img src="{{ asset('storage/' . $village->logo) }}" alt="{{ $village->village_name }} Logo"
                         class="img-fluid rounded" style="max-height: 150px;">
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-semibold text-primary mb-3">
                    <i class="bi bi-info-circle me-1"></i>Quick Info
                </h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Code</span>
                    <span class="fw-semibold">{{ $village->village_code }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status</span>
                    <span class="fw-semibold">{{ ucfirst($village->status) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Created</span>
                    <span class="fw-semibold">{{ $village->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <x-stat-card title="Total Households" value="{{ $stats['total_households'] ?? 0 }}" icon="bi-house-door" color="primary" />
    <x-stat-card title="Total Residents" value="{{ $stats['total_residents'] ?? 0 }}" icon="bi-people" color="success" />
    <x-stat-card title="Active Officers" value="{{ $stats['active_officers'] ?? 0 }}" icon="bi-person-badge" color="info" />
    <x-stat-card title="Total Documents" value="{{ $stats['total_documents'] ?? 0 }}" icon="bi-file-earmark-text" color="warning" />
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-person-badge me-1"></i>Village Officers
        </h6>
        <a href="{{ route('villages.officers.index', $village) }}" class="btn btn-outline-primary btn-sm">
            View All <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Position</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Term Start</th>
                        <th scope="col">Term End</th>
                        <th scope="col" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($village->officers->take(5) as $officer)
                        <tr>
                            <td><span class="badge bg-primary">{{ $officer->position }}</span></td>
                            <td class="fw-semibold">{{ $officer->full_name }}</td>
                            <td>{{ $officer->term_start ? \Carbon\Carbon::parse($officer->term_start)->format('M d, Y') : '—' }}</td>
                            <td>{{ $officer->term_end ? \Carbon\Carbon::parse($officer->term_end)->format('M d, Y') : '—' }}</td>
                            <td class="text-center">
                                @if($officer->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                No officers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-file-earmark-text me-1"></i>Recent Documents
        </h6>
        <a href="{{ route('villages.documents.index', $village) }}" class="btn btn-outline-primary btn-sm">
            View All <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-center">File Type</th>
                        <th scope="col">Uploaded By</th>
                        <th scope="col">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($village->documents->take(5) as $document)
                        <tr>
                            <td class="fw-semibold">{{ $document->title }}</td>
                            <td>{{ Str::limit($document->description ?? '', 50) ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION)) }}</span>
                            </td>
                            <td>{{ $document->uploader?->full_name ?? '—' }}</td>
                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                                No documents found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($village->latitude && $village->longitude)
    var map = L.map('village-map').setView([{{ $village->latitude }}, {{ $village->longitude }}], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap', maxZoom: 19 }).addTo(map);
    var icon = L.divIcon({
        className: 'gis-marker',
        html: '<div style="width:18px;height:18px;background:#1a4b8c;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.5);"></div>',
        iconSize: [24, 24], iconAnchor: [12, 12]
    });
    L.marker([{{ $village->latitude }}, {{ $village->longitude }}], { icon: icon })
        .bindPopup('<strong>{{ $village->village_name }}</strong>')
        .addTo(map);
    @endif
});
</script>
@endpush
