@extends('layouts.auth')

@section('title', 'Verify Code')

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
                            <h4 class="auth-title fw-bold text-primary mb-1">Verify Your Email</h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                We sent a 6-digit code to <strong>{{ $maskedEmail }}</strong>.
                                Enter it below to receive your login credentials.
                            </p>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-sm py-2 small" role="alert">
                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-sm py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-sm py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('password.otp.verify') }}" method="POST" class="auth-form" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="otp" class="form-label fw-semibold">
                                    <i class="bi bi-shield-lock me-1"></i>Verification Code
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                                       id="otp"
                                       name="otp"
                                       value="{{ old('otp') }}"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       pattern="[0-9]{6}"
                                       maxlength="6"
                                       placeholder="000000"
                                       required
                                       autofocus>
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The code expires in {{ $minutes }} minutes.</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                                <i class="bi bi-check-circle me-2"></i>Verify &amp; Send Credentials
                            </button>
                        </form>

                        <form action="{{ route('password.otp.resend') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm w-100 text-decoration-none forgot-link">
                                <i class="bi bi-arrow-clockwise me-1"></i>Resend verification code
                            </button>
                        </form>

                        <div class="text-center mt-3">
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
