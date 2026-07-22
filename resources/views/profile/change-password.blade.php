@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Change Password</h4>
            <p class="page-subtitle text-muted mb-0">Update your account password for security.</p>
        </div>
        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Profile
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>Password Update
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('profile.change-password.update') }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   placeholder="Enter current password"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Enter new password (min. 8 characters)"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               class="form-control"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="Confirm new password"
                               required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-info-circle text-primary me-1"></i>Password Requirements
                </h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Minimum 8 characters</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Mix of letters and numbers recommended</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Special characters encouraged</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Must not be a commonly used password</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>Must be confirmed correctly</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    });
</script>
@endpush
