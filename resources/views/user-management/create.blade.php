@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Create User Account</h4>
            <p class="page-subtitle text-muted mb-0">Register a new user account in the system.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Users
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

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
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" value="{{ auth()->user()?->village?->village_name ?? 'N/A' }}" readonly>
                        <small class="text-muted">Village is auto-assigned based on your account.</small>
                    </div>
                @else
                    <div class="col-md-4">
                        <label for="village_id" class="form-label fw-semibold">Village</label>
                        <select class="form-select @error('village_id') is-invalid @enderror"
                                id="village_id" name="village_id">
                            <option value="">Select Village</option>
                            @foreach($villages as $village)
                                <option value="{{ $village->id }}" {{ old('village_id') == $village->id ? 'selected' : '' }}>
                                    {{ $village->village_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('village_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div class="col-12" id="household-section" style="display: {{ old('role_id') == $residentRoleId ? 'block' : 'none' }};">
                    <h6 class="fw-semibold text-primary mb-0 mt-3">
                        <i class="bi bi-house me-1"></i>Household Assignment
                    </h6>
                    <hr>
                </div>

                <div class="col-md-6" id="household-field" style="display: {{ old('role_id') == $residentRoleId ? 'block' : 'none' }};">
                    <label for="household_id" class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
                    <select class="form-select @error('household_id') is-invalid @enderror"
                            id="household_id" name="household_id">
                        <option value="">Select Household</option>
                        @foreach($households as $household)
                            <option value="{{ $household->id }}" {{ old('household_id') == $household->id ? 'selected' : '' }}>
                                Blk {{ $household->block_number ?? '—' }}, Lot {{ $household->lot_number ?? '—' }} - {{ $household->household_number }} ({{ $household->household_head_name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('household_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <h6 class="fw-semibold text-primary mb-0 mt-3">
                        <i class="bi bi-shield-lock me-1"></i>Login Information
                    </h6>
                    <hr>
                </div>

                <div class="col-md-4">
                    <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                           id="username" name="username" value="{{ old('username') }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="Min. 8 characters"
                               required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control"
                           id="password_confirmation" name="password_confirmation"
                           placeholder="Confirm password" required>
                </div>
                <div class="col-12">
                    <div class="alert alert-info py-2 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Personal information (name, email and contact number) and the profile picture are filled in by
                        the user themselves the first time they sign in, through the account setup step.
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Create Account
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

    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const userForm = password ? password.form : null;

    function getFeedback(el) {
        let node = el.nextElementSibling;
        while (node) {
            if (node.classList && node.classList.contains('invalid-feedback')) return node;
            node = node.nextElementSibling;
        }
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        el.insertAdjacentElement('afterend', feedback);
        return feedback;
    }

    function setFieldError(el, message) {
        el.classList.add('is-invalid');
        getFeedback(el).textContent = message;
    }

    function clearFieldError(el) {
        el.classList.remove('is-invalid');
        const feedback = getFeedback(el);
        feedback.textContent = '';
    }

    function checkPasswordMatch() {
        if (!password || !passwordConfirmation || !passwordConfirmation.value) return;
        if (passwordConfirmation.value !== password.value) {
            setFieldError(passwordConfirmation, 'Password confirmation does not match.');
        } else {
            clearFieldError(passwordConfirmation);
        }
    }

    if (passwordConfirmation) {
        passwordConfirmation.addEventListener('input', function () {
            clearFieldError(passwordConfirmation);
            checkPasswordMatch();
        });
        passwordConfirmation.addEventListener('blur', checkPasswordMatch);
    }

    if (userForm) {
        userForm.addEventListener('submit', function (event) {
            let firstInvalid = null;

            userForm.querySelectorAll('[required]').forEach(function (el) {
                if (el.type === 'hidden' || el.type === 'submit' || el.type === 'button') return;
                if (!el.value.trim()) {
                    const label = el.id ? (userForm.querySelector('label[for="' + el.id + '"]') || {}).textContent : null;
                    setFieldError(el, (label ? label.replace('*', '').trim() : 'This field') + ' is required.');
                    if (!firstInvalid) firstInvalid = el;
                }
            });

            if (password && password.value && password.value.length < 8) {
                setFieldError(password, 'Password must be at least 8 characters.');
                if (!firstInvalid) firstInvalid = password;
            }

            if (passwordConfirmation && passwordConfirmation.value !== password.value) {
                setFieldError(passwordConfirmation, 'Password confirmation does not match.');
                if (!firstInvalid) firstInvalid = passwordConfirmation;
            }

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus();
            }
        });
    }
</script>
@endpush
