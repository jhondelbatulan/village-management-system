@extends('layouts.app')

@section('title', 'Village Management')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Village Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage all villages in the system.</p>
        </div>
        @if(auth()->user()?->role?->role_slug === 'super_admin')
            <a href="{{ route('villages.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Create Village
            </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('villages.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="search" class="form-label fw-semibold small">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Search by name or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 col-lg-3">
                <label for="status" class="form-label fw-semibold small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Village Name</th>
                        <th scope="col" class="text-center">Households</th>
                        <th scope="col" class="text-center">Population</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($villages as $village)
                        @php
                            $villageData = [
                                'code' => $village->village_code,
                                'name' => $village->village_name,
                                'address' => $village->address,
                                'description' => $village->description,
                                'status' => $village->status,
                                'households' => $village->total_households ?? 0,
                                'population' => $village->total_population ?? 0,
                                'latitude' => $village->latitude,
                                'longitude' => $village->longitude,
                                'contact' => $village->contact_number,
                                'email' => $village->email,
                                'area' => $village->land_area_sq_km,
                                'established' => $village->established_date?->format('M d, Y'),
                                'show_url' => route('villages.show', $village),
                            ];
                        @endphp
                        <tr data-village="{{ json_encode($villageData) }}">
                            <td><span class="badge bg-light text-dark">{{ $village->village_code }}</span></td>
                            <td class="fw-semibold">{{ $village->village_name }}</td>
                            <td class="text-center">{{ $village->total_households ?? 0 }}</td>
                            <td class="text-center">{{ $village->total_population ?? 0 }}</td>
                            <td class="text-center">
                                @if($village->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="View"
                                            data-bs-toggle="modal" data-bs-target="#villageViewModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('villages.edit', $village) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($village->status === 'active')
                                        <form action="{{ route('villages.destroy', $village) }}" method="POST" class="d-inline"
                                              data-confirm="Are you sure you want to archive this village?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Archive">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-building fs-1 d-block mb-2"></i>
                                No villages found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($villages->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $villages->links() }}
        </div>
    @endif
</div>

{{-- Quick village info modal --}}
<div class="modal fade" id="villageViewModal" tabindex="-1" aria-labelledby="villageViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="villageViewLabel">Village Details</h6>
                    <small class="text-muted" id="vilViewCode">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <h5 class="mb-0 fw-bold" id="vilViewName">—</h5>
                    <span class="badge" id="vilViewStatus">—</span>
                </div>
                <p class="text-muted small mb-3" id="vilViewAddress">—</p>

                <div class="row g-2 small mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Households</div>
                            <div class="fw-bold" id="vilViewHouseholds">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Population</div>
                            <div class="fw-bold" id="vilViewPopulation">—</div>
                        </div>
                    </div>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Map Center</dt>
                    <dd class="col-sm-8 fw-semibold" id="vilViewCoords">—</dd>
                    <dt class="col-sm-4 text-muted">Land Area</dt>
                    <dd class="col-sm-8 fw-semibold" id="vilViewArea">—</dd>
                    <dt class="col-sm-4 text-muted">Established</dt>
                    <dd class="col-sm-8 fw-semibold" id="vilViewEstablished">—</dd>
                    <dt class="col-sm-4 text-muted">Contact</dt>
                    <dd class="col-sm-8 fw-semibold" id="vilViewContact">—</dd>
                    <dt class="col-sm-4 text-muted">Email</dt>
                    <dd class="col-sm-8 fw-semibold" id="vilViewEmail">—</dd>
                    <dt class="col-sm-4 text-muted">Description</dt>
                    <dd class="col-sm-8" id="vilViewDescription">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="vilViewFull" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open full page
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@if(isset($archivedVillages) && $archivedVillages->count() > 0)
    <div class="mt-4">
        <a class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse" href="#archivedSection" role="button" aria-expanded="false" aria-controls="archivedSection">
            <i class="bi bi-archive me-1"></i>Archived Villages ({{ $archivedVillages->count() }})
        </a>

        <div class="collapse" id="archivedSection">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Village Code</th>
                                    <th scope="col">Village Name</th>
                                    <th scope="col">Address</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archivedVillages as $village)
                                    <tr>
                                        <td><span class="badge bg-light text-dark">{{ $village->village_code }}</span></td>
                                        <td class="fw-semibold">{{ $village->village_name }}</td>
                                        <td>{{ $village->address ?? '—' }}</td>
                                        <td class="text-center"><span class="badge bg-secondary">Archived</span></td>
                                        <td class="text-end">
                                            <form action="{{ route('villages.restore', $village) }}" method="POST" class="d-inline"
                                                  data-confirm="Are you sure you want to restore this village?">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = (value == null || value === '') ? '—' : value;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-bs-target="#villageViewModal"]');
        if (!trigger) return;

        var row = trigger.closest('tr[data-village]');
        if (!row) return;

        var d;
        try { d = JSON.parse(row.getAttribute('data-village')); }
        catch (error) { console.error('Village row data could not be parsed:', error); return; }

        setText('vilViewName', d.name);
        setText('vilViewCode', 'Village code: ' + (d.code || ''));

        var status = document.getElementById('vilViewStatus');
        if (status) {
            status.textContent = d.status === 'active' ? 'Active' : 'Inactive';
            status.className = 'badge ' + (d.status === 'active' ? 'bg-success' : 'bg-secondary');
        }

        setText('vilViewAddress', d.address);
        setText('vilViewHouseholds', d.households != null ? Number(d.households).toLocaleString() : '—');
        setText('vilViewPopulation', d.population != null ? Number(d.population).toLocaleString() : '—');

        var coords = (d.latitude != null && d.latitude !== '' && d.longitude != null && d.longitude !== '')
            ? d.latitude + ', ' + d.longitude
            : '';
        setText('vilViewCoords', coords);
        setText('vilViewArea', d.area ? d.area + ' km²' : '');
        setText('vilViewEstablished', d.established);
        setText('vilViewContact', d.contact);
        setText('vilViewEmail', d.email);
        setText('vilViewDescription', d.description);

        var full = document.getElementById('vilViewFull');
        if (full) full.href = d.show_url || '#';
    });
});
</script>
@endpush
