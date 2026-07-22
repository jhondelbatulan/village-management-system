@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Profile</h4>
            <p class="page-subtitle text-muted mb-0">Update your personal information.</p>
        </div>
        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Profile
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                   id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                            @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="suffix" class="form-label fw-semibold">Suffix</label>
                            <input type="text" class="form-control @error('suffix') is-invalid @enderror"
                                   id="suffix" name="suffix" value="{{ old('suffix', $user->suffix) }}">
                            @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                            <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                                   id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}">
                            @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            @error('profile_picture') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Accepted formats: JPG, PNG, WebP. Max size: 2MB.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <img src="{{ $user->profile_picture_url }}"
                     alt="Profile Picture"
                     class="rounded-circle mb-3 profile-display-avatar"
                     id="preview">
                <h6 class="mb-1">{{ $user->full_name }}</h6>
                <span class="badge bg-primary">{{ $user->role?->role_name }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('profile_picture')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('preview').src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
