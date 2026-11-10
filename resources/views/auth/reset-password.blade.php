@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-wrapper">
    @include('partials.auth-background')

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset(config('app_settings.assets.logo')) }}"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">Set New Password</h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                Enter your new password below.
                            </p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger alert-sm py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('password.update') }}" method="POST" class="auth-form" novalidate>
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ $email ?? old('email') }}"
                                       required
                                       readonly>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-1"></i>New Password
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Enter new password"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-1"></i>Confirm Password
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Confirm new password"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                                <i class="bi bi-check-circle me-2"></i>Reset Password
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="{{ route('login') }}" class="small text-decoration-none forgot-link">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
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
