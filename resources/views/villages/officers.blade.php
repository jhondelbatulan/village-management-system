@extends('layouts.app')

@section('title', 'Village Officers - ' . $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-1"></i>{{ $village->village_name }} — Officers
            </h4>
            <p class="page-subtitle text-muted mb-0">Manage village officers and their terms.</p>
        </div>
        <a href="{{ route('villages.show', $village) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

@php $canManageOfficers = in_array(auth()->user()->role?->role_slug, ['admin', 'secretary'], true); @endphp

@if($canManageOfficers)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" href="#addOfficerForm" role="button" aria-expanded="false" aria-controls="addOfficerForm">
            <i class="bi bi-plus-lg me-1"></i>Add New Officer
        </a>
    </div>
    <div class="collapse" id="addOfficerForm">
        <div class="card-body border-top">
            <form action="{{ route('villages.officers.store', $village) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('position') is-invalid @enderror"
                               id="position" name="position" value="{{ old('position') }}" required>
                        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="full_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                               id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="photo" class="form-label fw-semibold">Photo</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror"
                               id="photo" name="photo" accept="image/*">
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="term_start" class="form-label fw-semibold">Term Start</label>
                        <input type="date" class="form-control @error('term_start') is-invalid @enderror"
                               id="term_start" name="term_start" value="{{ old('term_start') }}">
                        @error('term_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="term_end" class="form-label fw-semibold">Term End</label>
                        <input type="date" class="form-control @error('term_end') is-invalid @enderror"
                               id="term_end" name="term_end" value="{{ old('term_end') }}">
                        @error('term_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="reset" class="btn btn-outline-secondary btn-sm">Clear</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i>Add Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Position</th>
                        <th scope="col">Term Start</th>
                        <th scope="col">Term End</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officers as $officer)
                        <tr>
                            <td>
                                @if($officer->photo)
                                    <img src="{{ asset('storage/' . $officer->photo) }}" alt="{{ $officer->full_name }}"
                                         class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $officer->full_name }}</td>
                            <td><span class="badge bg-primary">{{ $officer->position }}</span></td>
                            <td>{{ $officer->term_start ? \Carbon\Carbon::parse($officer->term_start)->format('M d, Y') : '—' }}</td>
                            <td>{{ $officer->term_end ? \Carbon\Carbon::parse($officer->term_end)->format('M d, Y') : '—' }}</td>
                            <td class="text-center">
                                @if($officer->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($canManageOfficers)
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editOfficerModal{{ $officer->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('villages.officers.destroy', ['village' => $village, 'officer' => $officer]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to remove this officer?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                No officers found. Add an officer using the form above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($officers->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $officers->links() }}
        </div>
    @endif
</div>

@if($canManageOfficers)
@foreach($officers as $officer)
    <div class="modal fade" id="editOfficerModal{{ $officer->id }}" tabindex="-1" aria-labelledby="editOfficerModalLabel{{ $officer->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('villages.officers.update', ['village' => $village, 'officer' => $officer]) }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editOfficerModalLabel{{ $officer->id }}">
                            <i class="bi bi-pencil me-1"></i>Edit Officer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_position_{{ $officer->id }}" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('position') is-invalid @enderror"
                                   id="edit_position_{{ $officer->id }}" name="position" value="{{ old('position', $officer->position) }}" required>
                            @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name_{{ $officer->id }}" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                   id="edit_full_name_{{ $officer->id }}" name="full_name" value="{{ old('full_name', $officer->full_name) }}" required>
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo_{{ $officer->id }}" class="form-label fw-semibold">Photo</label>
                            @if($officer->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $officer->photo) }}" alt="Current Photo" class="rounded" style="max-height: 60px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                   id="edit_photo_{{ $officer->id }}" name="photo" accept="image/*">
                            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_term_start_{{ $officer->id }}" class="form-label fw-semibold">Term Start</label>
                                <input type="date" class="form-control @error('term_start') is-invalid @enderror"
                                       id="edit_term_start_{{ $officer->id }}" name="term_start"
                                       value="{{ old('term_start', $officer->term_start ? \Carbon\Carbon::parse($officer->term_start)->format('Y-m-d') : '') }}">
                                @error('term_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="edit_term_end_{{ $officer->id }}" class="form-label fw-semibold">Term End</label>
                                <input type="date" class="form-control @error('term_end') is-invalid @enderror"
                                       id="edit_term_end_{{ $officer->id }}" name="term_end"
                                       value="{{ old('term_end', $officer->term_end ? \Carbon\Carbon::parse($officer->term_end)->format('Y-m-d') : '') }}">
                                @error('term_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label for="edit_status_{{ $officer->id }}" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="edit_status_{{ $officer->id }}" name="status" required>
                                <option value="active" {{ old('status', $officer->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $officer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Officer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif
@endsection
