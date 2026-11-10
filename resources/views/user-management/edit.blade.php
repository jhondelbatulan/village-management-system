@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit User Account</h4>
            <p class="page-subtitle text-muted mb-0">Update account information for {{ $user->full_name }}.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Users
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" novalidate data-password-confirm data-password-confirm-message="Enter your password to save changes to this user account.">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-person-gear me-1"></i>Account Information
                    </h6>
                    <hr>
                </div>

                <div class="col-md-4">
                    <label for="role_id" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role_id') is-invalid @enderror"
                            id="role_id" name="role_id" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @if($isAdmin)
                    <input type="hidden" name="village_id" value="{{ auth()->user()->village_id }}">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Village</label>
                        <input type="text" class="form-control" value="{{ $user->village?->village_name ?? auth()->user()?->village?->village_name ?? 'N/A' }}" readonly>
                        <small class="text-muted">Village is restricted to your assigned village.</small>
                    </div>
                @else
                    <div class="col-md-4">
                        <label for="village_id" class="form-label fw-semibold">Village</label>
                        <select class="form-select @error('village_id') is-invalid @enderror"
                                id="village_id" name="village_id">
                            <option value="">Select Village</option>
                            @foreach($villages as $village)
                                <option value="{{ $village->id }}" {{ old('village_id', $user->village_id) == $village->id ? 'selected' : '' }}>
                                    {{ $village->village_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('village_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror"
                            id="status" name="status" required>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12" id="household-section" style="display: {{ old('role_id', $user->role_id) == $residentRoleId ? 'block' : 'none' }};">
                    <h6 class="fw-semibold text-primary mb-0 mt-3">
                        <i class="bi bi-house me-1"></i>Household Assignment
                    </h6>
                    <hr>
                </div>

                <div class="col-md-6" id="household-field" style="display: {{ old('role_id', $user->role_id) == $residentRoleId ? 'block' : 'none' }};">
                    <label for="household_id" class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
                    <select class="form-select @error('household_id') is-invalid @enderror"
                            id="household_id" name="household_id">
                        <option value="">Select Household</option>
                        @foreach($households as $household)
                            <option value="{{ $household->id }}" {{ old('household_id', $user->household_id) == $household->id ? 'selected' : '' }}>
                                Blk {{ $household->block_number ?? '—' }}, Lot {{ $household->lot_number ?? '—' }} - {{ $household->household_number }} ({{ $household->household_head_name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('household_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <h6 class="fw-semibold text-primary mb-0 mt-3">
                        <i class="bi bi-person me-1"></i>Personal Information
                    </h6>
                    <hr>
                </div>

                <div class="col-md-3">
                    <label for="first_name" class="form-label fw-semibold">First Name</label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                           id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}">
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                           id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="last_name" class="form-label fw-semibold">Last Name</label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                           id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="suffix" class="form-label fw-semibold">Suffix</label>
                    <input type="text" class="form-control @error('suffix') is-invalid @enderror"
                           id="suffix" name="suffix" value="{{ old('suffix', $user->suffix) }}">
                    @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <h6 class="fw-semibold text-primary mb-0 mt-3">
                        <i class="bi bi-envelope me-1"></i>Contact & Login Information
                    </h6>
                    <hr>
                </div>

                <div class="col-md-4">
                    <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                           id="username" name="username" value="{{ old('username', $user->username) }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email', $user->email) }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                           id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}">
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="password" class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="Leave blank to keep current">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control"
                           id="password_confirmation" name="password_confirmation"
                           placeholder="Confirm new password">
                </div>
                <div class="col-md-4">
                    <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                    <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                           id="profile_picture" name="profile_picture" accept="image/*">
                    @error('profile_picture') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Update Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const roleSelect = document.getElementById('role_id');
    const householdSection = document.getElementById('household-section');
    const householdField = document.getElementById('household-field');
    const householdSelect = document.getElementById('household_id');
    const residentRoleId = '{{ $residentRoleId }}';

    roleSelect.addEventListener('change', function() {
        if (this.value === residentRoleId) {
            householdSection.style.display = 'block';
            householdField.style.display = 'block';
            householdSelect.required = true;
        } else {
            householdSection.style.display = 'none';
            householdField.style.display = 'none';
            householdSelect.required = false;
            householdSelect.value = '';
        }
    });

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
