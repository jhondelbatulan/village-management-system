@extends('layouts.app')

@section('title', 'Household Map Data')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Household Map Data</h1>
        <p class="page-subtitle text-muted">List of all households with mapped coordinates</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gis.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-map me-1"></i> Map View
        </a>
        <a href="{{ route('gis.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary bg-opacity-10">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-list-ul me-2"></i> Mapped Households ({{ $households->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Head Name</th>
                        <th>Village</th>
                        <th>Block / Lot</th>
                        <th>Status</th>
                        <th>Lat</th>
                        <th>Lng</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($households as $h)
                        <tr>
                            <td class="fw-semibold">{{ $h->household_number }}</td>
                            <td>{{ $h->household_head_name }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $h->village->village_name ?? 'N/A' }}</span></td>
                            <td>{{ $h->block_number }} / {{ $h->lot_number }}</td>
                            <td>
                                <span class="badge bg-{{ $h->status === 'active' ? 'success' : ($h->status === 'inactive' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($h->status) }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $h->latitude }}</small></td>
                            <td><small class="text-muted">{{ $h->longitude }}</small></td>
                            <td class="text-center">
                                <a href="{{ route('households.show', $h) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-geo-alt" style="font-size: 2rem;"></i><br>
                                No households with coordinates found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
