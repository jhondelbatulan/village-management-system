@extends('layouts.auth')

@section('title', 'Set Up Your Account')

@section('content')
<div class="auth-wrapper">
    @include('partials.auth-background')

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset(config('app_settings.assets.logo')) }}"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">Set Up Your Account</h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                Welcome, <strong>{{ $user->username }}</strong>! Complete your personal information
                                and set your own password before continuing to your dashboard.
                            </p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger alert-sm py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-sm py-2 small mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Please fix the highlighted fields below.
                            </div>
                        @endif

                        <form action="{{ route('account.setup.store') }}" method="POST" class="auth-form" novalidate>
                            @csrf

                            <h6 class="fw-semibold text-muted text-uppercase small mb-3">
                                <i class="bi bi-person me-1"></i>Personal Information
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name"
                                           name="first_name"
                                           value="{{ old('first_name', $user->first_name) }}"
                                           required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="middle_name" class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>Middle Name
                                    </label>
                                    <input type="text"
                                           class="form-control @error('middle_name') is-invalid @enderror"
                                           id="middle_name"
                                           name="middle_name"
                                           value="{{ old('middle_name', $user->middle_name) }}"
                                           placeholder="e.g., M">
                                    @error('middle_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label for="last_name" class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name"
                                           name="last_name"
                                           value="{{ old('last_name', $user->last_name) }}"
                                           required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="suffix" class="form-label fw-semibold">
                                        <i class="bi bi-hash me-1"></i>Suffix
                                    </label>
                                    <select class="form-select @error('suffix') is-invalid @enderror" id="suffix" name="suffix">
                                        <option value="">—</option>
                                        @foreach(\App\Http\Requests\AccountSetupRequest::SUFFIXES as $suffixOption)
                                            <option value="{{ $suffixOption }}"
                                                @selected(old('suffix', $user->suffix) === $suffixOption)>{{ $suffixOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('suffix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h6 class="fw-semibold text-muted text-uppercase small mt-2 mb-3">
                                <i class="bi bi-envelope me-1"></i>Contact Details
                            </h6>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="you@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_number" class="form-label fw-semibold">
                                    <i class="bi bi-telephone me-1"></i>Contact Number <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('contact_number') is-invalid @enderror"
                                       id="contact_number"
                                       name="contact_number"
                                       value="{{ old('contact_number', $user->contact_number) }}"
                                       placeholder="e.g., 0917 123 4567"
                                       required>
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <h6 class="fw-semibold text-muted text-uppercase small mt-2 mb-3">
                                <i class="bi bi-key me-1"></i>Your New Password
                            </h6>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-1"></i>New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Choose your new password"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if(count($requirements))
                                    <div class="small text-muted mt-2">
                                        <span class="fw-semibold">Password must have:</span>
                                        <ul class="mb-0 ps-3">
                                            @foreach($requirements as $requirement)
                                                <li>{{ $requirement }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-1"></i>Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Re-enter your new password"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                                <i class="bi bi-check-circle me-2"></i>Set Up My Account &amp; Continue
                            </button>
                        </form>

                        <form action="{{ route('logout') }}" method="POST" class="text-center mt-3">
                            @csrf
                            <button type="submit" class="btn btn-link text-decoration-none small p-0">
                                <i class="bi bi-box-arrow-right me-1"></i>Sign out and use a different account
                            </button>
                        </form>
                    </div>
                </div>

                <footer class="auth-footer text-center mt-4">
                    <p class="text-white-50 small mb-1">{{ config('app_settings.footer_text') }}</p>
                </footer>
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

    // Real-time form validation: names are auto-capitalized as they are typed,
    // the middle name stays a single capital letter, and every field reports
    // problems the moment it loses focus instead of after a full page round-trip.
    (function () {
        const setupForm = document.querySelector('form.auth-form');
        if (!setupForm) return;

        const firstName = document.getElementById('first_name');
        const middleName = document.getElementById('middle_name');
        const lastName = document.getElementById('last_name');
        const email = document.getElementById('email');
        const contactNumber = document.getElementById('contact_number');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');

        const NAME_PATTERN = /^[\p{L}]+(?: [\p{L}]+)*$/u;
        const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        const CONTACT_PATTERN = /^[0-9+\-\s()]{7,20}$/;

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
            el.classList.remove('is-valid');
            getFeedback(el).textContent = message;
        }

        function clearFieldError(el) {
            el.classList.remove('is-invalid');
            const feedback = getFeedback(el);
            feedback.textContent = '';
        }

        function markValid(el) {
            el.classList.remove('is-invalid');
            el.classList.add('is-valid');
            getFeedback(el).textContent = '';
        }

        // Capitalize the first letter of every word while typing.
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

        function checkName(el, label) {
            const value = el.value.trim();
            if (!value) return label + ' is required.';
            if (!NAME_PATTERN.test(value)) return label + ' must only contain letters — no numbers or special characters.';
            return null;
        }

        function checkMiddleName(el) {
            const value = el.value.trim();
            if (!value) return null;
            if (!/^\p{Lu}$/u.test(value)) return 'Middle name must be a single capital letter (e.g., M).';
            return null;
        }

        function checkEmail(el) {
            const value = el.value.trim();
            if (!value) return 'Email address is required.';
            if (!EMAIL_PATTERN.test(value)) return 'Please enter a valid email address.';
            return null;
        }

        function checkContact(el) {
            const value = el.value.trim();
            if (!value) return 'Contact number is required.';
            if (!CONTACT_PATTERN.test(value)) return 'Contact number must be a valid phone number (7–20 digits, may include +, -, spaces or parentheses).';
            return null;
        }

        function validateField(el, checker, label) {
            const message = checker(el, label);
            if (message) {
                setFieldError(el, message);
                return false;
            }
            markValid(el);
            return true;
        }

        [[firstName, 'First Name'], [lastName, 'Last Name']].forEach(function (pair) {
            const el = pair[0];
            const label = pair[1];
            if (!el) return;

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

        if (middleName) {
            middleName.addEventListener('input', function () {
                const cleaned = middleName.value.replace(/[^\p{L}]/gu, '').slice(0, 1);
                middleName.value = cleaned ? cleaned.toUpperCase() : '';
                clearFieldError(middleName);
            });

            middleName.addEventListener('blur', function () {
                validateField(middleName, checkMiddleName);
            });
        }

        if (email) {
            email.addEventListener('input', function () { clearFieldError(email); });
            email.addEventListener('blur', function () { validateField(email, checkEmail); });
        }

        if (contactNumber) {
            contactNumber.addEventListener('input', function () { clearFieldError(contactNumber); });
            contactNumber.addEventListener('blur', function () { validateField(contactNumber, checkContact); });
        }

        function checkPasswordLength() {
            if (!password || password.classList.contains('is-invalid')) return;
            const value = password.value;
            if (value && value.length < 8) {
                setFieldError(password, 'Password must be at least 8 characters.');
            } else {
                clearFieldError(password);
            }
        }

        function checkPasswordMatch() {
            if (!passwordConfirmation) return;
            const value = passwordConfirmation.value;
            if (value && value !== password.value) {
                setFieldError(passwordConfirmation, 'Password confirmation does not match.');
            } else if (value) {
                markValid(passwordConfirmation);
            } else {
                clearFieldError(passwordConfirmation);
            }
        }

        if (password) {
            password.addEventListener('input', checkPasswordLength);
            password.addEventListener('input', checkPasswordMatch);
            password.addEventListener('blur', checkPasswordLength);
        }

        if (passwordConfirmation) {
            passwordConfirmation.addEventListener('input', function () {
                clearFieldError(passwordConfirmation);
                checkPasswordMatch();
            });
            passwordConfirmation.addEventListener('blur', checkPasswordMatch);
        }

        setupForm.addEventListener('submit', function (event) {
            if (firstName) firstName.value = titleCase(firstName.value);
            if (lastName) lastName.value = titleCase(lastName.value);

            let firstInvalid = null;

            setupForm.querySelectorAll('[required]').forEach(function (el) {
                if (el.type === 'hidden' || el.type === 'submit' || el.type === 'button') return;
                if (!el.value.trim()) {
                    const labelEl = el.id ? setupForm.querySelector('label[for="' + el.id + '"]') : null;
                    const label = labelEl ? labelEl.textContent.replace('*', '').trim() : 'This field';
                    setFieldError(el, label + ' is required.');
                    if (!firstInvalid) firstInvalid = el;
                }
            });

            const checks = [];
            if (firstName) checks.push([firstName, checkName, 'First Name']);
            if (lastName) checks.push([lastName, checkName, 'Last Name']);
            if (middleName) checks.push([middleName, checkMiddleName]);
            if (email) checks.push([email, checkEmail]);
            if (contactNumber) checks.push([contactNumber, checkContact]);

            checks.forEach(function (entry) {
                if (!validateField(entry[0], entry[1], entry[2]) && !firstInvalid) {
                    firstInvalid = entry[0];
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
    })();
</script>
@endpush
