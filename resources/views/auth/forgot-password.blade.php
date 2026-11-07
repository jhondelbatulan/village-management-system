@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-wrapper">
    <div class="auth-background" style="background-image: url('{{ asset(config('app_settings.assets.login_background')) }}');"></div>
    <div class="auth-overlay"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset(config('app_settings.assets.logo')) }}"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">Forgot Password</h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                Enter your account email and we'll send a verification code first, then your username and a new password.
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

                        <form action="{{ route('password.email') }}" method="POST" class="auth-form" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Enter your email"
                                       required
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                                <i class="bi bi-send me-2"></i>Send Verification Code
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
