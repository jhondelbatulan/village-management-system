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

                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror"
                            id="status" name="status" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

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
                        <i class="bi bi-person me-1"></i>Personal Information
                    </h6>
                    <hr>
                </div>

                <div class="col-md-3">
                    <label for="first_name" class="form-label fw-semibold">First Name</label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                           id="first_name" name="first_name" value="{{ old('first_name') }}"
                           maxlength="255" placeholder="e.g., Juan" autocomplete="off">
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                           id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                           maxlength="1" placeholder="e.g., M" autocomplete="off">
                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Single capital letter only.</small>
                </div>
                <div class="col-md-3">
                    <label for="last_name" class="form-label fw-semibold">Last Name</label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                           id="last_name" name="last_name" value="{{ old('last_name') }}"
                           maxlength="255" placeholder="e.g., Dela Cruz" autocomplete="off">
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="suffix" class="form-label fw-semibold">Suffix</label>
                    <input type="text" class="form-control @error('suffix') is-invalid @enderror"
                           id="suffix" name="suffix" value="{{ old('suffix') }}"
                           list="suffix-options" maxlength="10" placeholder="e.g., Jr." autocomplete="off">
                    <datalist id="suffix-options">
                        <option value="Jr."></option>
                        <option value="Sr."></option>
                        <option value="II"></option>
                        <option value="III"></option>
                        <option value="IV"></option>
                        <option value="V"></option>
                        <option value="VI"></option>
                        <option value="VII"></option>
                        <option value="VIII"></option>
                        <option value="IX"></option>
                        <option value="X"></option>
                    </datalist>
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
                           id="username" name="username" value="{{ old('username') }}" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

    const firstName = document.getElementById('first_name');
    const middleName = document.getElementById('middle_name');
    const lastName = document.getElementById('last_name');
    const suffix = document.getElementById('suffix');
    const email = document.getElementById('email');
    const userForm = firstName.form;

    const NAME_PATTERN = /^[\p{L}]+(?: [\p{L}]+)*$/u;
    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const SUFFIX_PATTERN = /^(?:Jr\.|Sr\.|II|III|IV|V|VI|VII|VIII|IX|X)$/;

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

    // Capitalize the first letter of every word while typing (never changes the length).
    function autoCapitalize(value) {
        return value.replace(/(^|\s)(\p{L})/gu, function (match, space, letter) {
            return space + letter.toUpperCase();
        });
    }

    function titleCase(value) {
        value = value.replace(/\s+/g, ' ').trim();
        if (!value) return '';
        if (value === value.toUpperCase() || value === value.toLowerCase()) {
            value = value.toLowerCase();
        }
        return value.split(' ').map(function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function normalizeSuffix(value) {
        const key = value.trim().replace(/\.$/, '').toLowerCase();
        const map = {
            jr: 'Jr.', sr: 'Sr.', ii: 'II', iii: 'III', iv: 'IV', v: 'V',
            vi: 'VI', vii: 'VII', viii: 'VIII', ix: 'IX', x: 'X'
        };
        return map[key] || value.trim();
    }

    function checkName(el, label) {
        const value = el.value.trim();
        if (!value) return el.required ? label + ' is required.' : null;
        if (!NAME_PATTERN.test(value)) return label + ' must only contain letters — no numbers or special characters.';
        return null;
    }

    function checkMiddleName(el) {
        const value = el.value.trim();
        if (!value) return null;
        if (!/^\p{Lu}$/u.test(value)) return 'Middle name must be a single capital letter (e.g., M).';
        return null;
    }

    function checkSuffix(el) {
        const value = el.value.trim();
        if (!value) return null;
        if (!SUFFIX_PATTERN.test(value)) return 'Suffix must only be one of: Jr., Sr., II, III, IV, V, VI, VII, VIII, IX, X.';
        return null;
    }

    function checkEmail(el) {
        const value = el.value.trim();
        if (!value) return null;
        if (!EMAIL_PATTERN.test(value)) return 'Please enter a valid email address.';
        return null;
    }

    function validateField(el, checker, label) {
        const message = checker(el, label);
        if (message) {
            setFieldError(el, message);
            return false;
        }
        clearFieldError(el);
        return true;
    }

    [[firstName, 'First Name'], [lastName, 'Last Name']].forEach(function (pair) {
        const el = pair[0];
        const label = pair[1];

        el.addEventListener('input', function () {
            const caret = el.selectionStart;

            const cleaned = el.value.replace(/[^\p{L} ]/gu, '').replace(/ {2,}/g, ' ');
            if (cleaned !== el.value) {
                el.value = cleaned;
                el.setSelectionRange(Math.min(caret, cleaned.length), Math.min(caret, cleaned.length));
            }

            const capitalized = autoCapitalize(el.value);
            if (capitalized !== el.value) {
                el.value = capitalized;
                el.setSelectionRange(Math.min(caret, capitalized.length), Math.min(caret, capitalized.length));
            }

            clearFieldError(el);
        });

        el.addEventListener('blur', function () {
            el.value = titleCase(el.value);
            validateField(el, checkName, label);
        });
    });

    middleName.addEventListener('input', function () {
        const cleaned = middleName.value.replace(/[^\p{L}]/gu, '').slice(0, 1);
        middleName.value = cleaned ? cleaned.toUpperCase() : '';
        clearFieldError(middleName);
    });

    middleName.addEventListener('blur', function () {
        validateField(middleName, checkMiddleName);
    });

    suffix.addEventListener('input', function () {
        suffix.value = suffix.value.replace(/[^\p{L}.]/gu, '');
        clearFieldError(suffix);
    });

    suffix.addEventListener('blur', function () {
        suffix.value = normalizeSuffix(suffix.value);
        validateField(suffix, checkSuffix);
    });

    email.addEventListener('input', function () {
        clearFieldError(email);
    });

    email.addEventListener('blur', function () {
        validateField(email, checkEmail);
    });

    userForm.addEventListener('submit', function (event) {
        firstName.value = titleCase(firstName.value);
        lastName.value = titleCase(lastName.value);
        suffix.value = normalizeSuffix(suffix.value);

        let firstInvalid = null;

        userForm.querySelectorAll('[required]').forEach(function (el) {
            if (!el.value.trim()) {
                const label = el.id ? (userForm.querySelector('label[for="' + el.id + '"]') || {}).textContent : null;
                setFieldError(el, (label ? label.replace('*', '').trim() : 'This field') + ' is required.');
                if (!firstInvalid) firstInvalid = el;
            }
        });

        [[firstName, checkName, 'First Name'], [lastName, checkName, 'Last Name'],
         [middleName, checkMiddleName], [suffix, checkSuffix], [email, checkEmail]].forEach(function (entry) {
            if (!validateField(entry[0], entry[1], entry[2]) && !firstInvalid) {
                firstInvalid = entry[0];
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
        }
    });
</script>
@endpush
