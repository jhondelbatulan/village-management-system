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
</script>
@endpush
