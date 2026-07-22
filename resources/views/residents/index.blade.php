@extends('layouts.app')

@section('title', 'Resident Management')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Resident Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage and track all registered residents across villages.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->role?->role_slug === 'secretary')
                <a href="{{ route('residents.import') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-upload me-1"></i>Import
                </a>
            @endif
            <a href="{{ route('residents.export') }}" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            @if(auth()->user()->role?->role_slug === 'secretary')
                <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Create Resident
                </a>
            @endif
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('residents.index') }}" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search"
                           placeholder="Name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="village_id" class="form-label small fw-semibold">Village</label>
                    <select class="form-select form-select-sm" id="village_id" name="village_id">
                        <option value="">All Villages</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                                {{ $village->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="block_number" class="form-label small fw-semibold">Block</label>
                    <select class="form-select form-select-sm" id="block_number" name="block_number">
                        <option value="">All</option>
                        @foreach($blockNumbers as $block)
                            <option value="{{ $block }}" {{ request('block_number') == $block ? 'selected' : '' }}>{{ $block }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="lot_number" class="form-label small fw-semibold">Lot</label>
                    <select class="form-select form-select-sm" id="lot_number" name="lot_number">
                        <option value="">All</option>
                        @foreach($lotNumbers as $lot)
                            <option value="{{ $lot }}" {{ request('lot_number') == $lot ? 'selected' : '' }}>{{ $lot }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="gender" class="form-label small fw-semibold">Gender</label>
                    <select class="form-select form-select-sm" id="gender" name="gender">
                        <option value="">All</option>
                        <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ request('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small fw-semibold">Status</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="deceased" {{ request('status') === 'deceased' ? 'selected' : '' }}>Deceased</option>
                        <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="approval_status" class="form-label small fw-semibold">Approval</label>
                    <select class="form-select form-select-sm" id="approval_status" name="approval_status">
                        <option value="">All</option>
                        <option value="draft" {{ request('approval_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-2">
                    <label for="sort" class="form-label small fw-semibold">Sort</label>
                    <select class="form-select form-select-sm" id="sort" name="sort">
                        <option value="">Newest First</option>
                        <option value="az" {{ request('sort') === 'az' ? 'selected' : '' }}>A – Z (Last Name)</option>
                        <option value="za" {{ request('sort') === 'za' ? 'selected' : '' }}>Z – A (Last Name)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                    @if(request()->hasAny(['search', 'village_id', 'block_number', 'lot_number', 'gender', 'status', 'approval_status', 'sort']))
                        <span class="text-muted small align-self-center ms-1">
                            <i class="bi bi-info-circle me-1"></i>{{ $residents->total() }} result(s) found
                        </span>
                    @endif
                </div>
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
                        <th scope="col">Name</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Age</th>
                        <th scope="col">Village</th>
                        <th scope="col">Household</th>
                        <th scope="col">Status</th>
                        <th scope="col">Approval</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $resident)
                        <tr>
                            <td><span class="fw-semibold">{{ $resident->resident_code }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $resident->profile_picture_url }}"
                                         alt="{{ $resident->full_name }}"
                                         class="rounded-circle me-2" width="36" height="36">
                                    <div>
                                        <div class="fw-semibold">{{ $resident->full_name }}</div>
                                        <small class="text-muted">{{ $resident->email ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $resident->gender ?? '—' }}</td>
                            <td>{{ $resident->age ?? '—' }}</td>
                            <td>{{ $resident->village?->village_name ?? '—' }}</td>
                            <td>
                                @if($resident->household)
                                    <span class="text-muted small">Blk {{ $resident->household->block_number ?? '—' }}, Lot {{ $resident->household->lot_number ?? '—' }}</span><br>
                                    <span class="fw-semibold">{{ $resident->household?->household_number ?? '—' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($resident->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($resident->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($resident->status === 'deceased')
                                    <span class="badge bg-danger">Deceased</span>
                                @elseif($resident->status === 'transferred')
                                    <span class="badge bg-warning text-dark">Transferred</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($resident->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($resident->approval_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($resident->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($resident->approval_status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('residents.show', $resident) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->role?->role_slug === 'secretary' && in_array($resident->approval_status, ['draft', 'rejected']))
                                    <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if(auth()->user()->role?->role_slug === 'secretary' && $resident->status === 'active')
                                    <form action="{{ route('residents.destroy', $resident) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to archive this resident?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No residents found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($residents->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $residents->withQueryString()->links() }}
        </div>
    @endif
</div>

@if(isset($archivedResidents) && $archivedResidents->count())
    <div class="mt-4">
        <a class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse" href="#archivedSection" role="button" aria-expanded="false" aria-controls="archivedSection">
            <i class="bi bi-archive me-1"></i>Archived Residents ({{ $archivedResidents->total() }})
        </a>

        <div class="collapse" id="archivedSection">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Village</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($archivedResidents as $resident)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $resident->resident_code }}</span></td>
                                        <td>{{ $resident->full_name }}</td>
                                        <td>{{ $resident->village?->village_name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">Archived</span>
                                        </td>
                                        <td class="text-end">
                                            @if(auth()->user()->role?->role_slug === 'secretary')
                                                <form action="{{ route('residents.restore', $resident) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Restore this resident?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                    </button>
                                                </form>
                                            @endif
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
