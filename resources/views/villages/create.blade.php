@extends('layouts.app')

@section('title', 'Create Village')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Create Village</h4>
            <p class="page-subtitle text-muted mb-0">Register a new village in the system.</p>
        </div>
        <a href="{{ route('villages.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Villages
        </a>
    </div>
</div>

<form action="{{ route('villages.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

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
                           id="village_name" name="village_name" value="{{ old('village_name') }}" required>
                    @error('village_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="village_code" class="form-label fw-semibold">Village Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('village_code') is-invalid @enderror"
                           id="village_code" name="village_code" value="{{ old('village_code') }}" required>
                    @error('village_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="established_date" class="form-label fw-semibold">Established Date</label>
                    <input type="date" class="form-control @error('established_date') is-invalid @enderror"
                           id="established_date" name="established_date" value="{{ old('established_date') }}">
                    @error('established_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-geo-alt me-1"></i>Location
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                           id="address" name="address" value="{{ old('address') }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="latitude" class="form-label fw-semibold">Latitude</label>
                    <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                           id="latitude" name="latitude" value="{{ old('latitude') }}">
                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="longitude" class="form-label fw-semibold">Longitude</label>
                    <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror"
                           id="longitude" name="longitude" value="{{ old('longitude') }}">
                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="total_area" class="form-label fw-semibold">Total Area</label>
                    <input type="number" step="any" class="form-control @error('total_area') is-invalid @enderror"
                           id="total_area" name="total_area" value="{{ old('total_area') }}">
                    @error('total_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-image me-1"></i>Media
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="logo" class="form-label fw-semibold">Logo</label>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror"
                           id="logo" name="logo" accept="image/*">
                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="cover_photo" class="form-label fw-semibold">Cover Photo</label>
                    <input type="file" class="form-control @error('cover_photo') is-invalid @enderror"
                           id="cover_photo" name="cover_photo" accept="image/*">
                    @error('cover_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
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
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('villages.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Create Village
        </button>
    </div>
</form>
@endsection
