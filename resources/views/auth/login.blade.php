@extends('layouts.auth')

@section('title', 'Login')

@section('content')
@php $branding = app(\App\Services\BrandingService::class); @endphp
<div class="auth-wrapper">
    @include('partials.auth-background')

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ $branding->getLogoUrl() }}"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">
                                {{ $branding->get('system_name', config('app_settings.name')) }}
                            </h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                {{ $branding->get('system_tagline', config('app_settings.tagline')) }}
                            </p>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-sm py-2 small" role="alert">
                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger py-2 small mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if(($lockSeconds ?? 0) > 0)
                            <div class="alert alert-warning py-2 small mb-3" id="lockoutTimer"
                                 role="alert"
                                 data-until="{{ now()->addSeconds($lockSeconds)->timestamp }}">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Too many failed attempts. Please try again in
                                <strong><span id="lockoutSeconds">{{ $lockSeconds }}</span>s</strong>.
                            </div>
                        @endif

                        <form action="{{ route('login.submit') }}" method="POST" class="auth-form" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg @error('username') is-invalid @enderror"
                                       id="username"
                                       name="username"
                                       value="{{ old('username') }}"
                                       placeholder="Enter your username"
                                       required
                                       autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-1"></i>Password
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Enter your password"
                                           required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                    <label class="form-check-label small" for="remember">Remember Me</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="small text-decoration-none forgot-link">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn" id="loginBtn"
                                    {{ ($lockSeconds ?? 0) > 0 ? 'disabled' : '' }}>
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </form>
                    </div>
                </div>

                <footer class="auth-footer text-center mt-4">
                    <p class="text-white-50 small mb-1">{{ config('app_settings.footer_text') }}</p>
                    <p class="text-white-50 small mb-0">Authorized personnel only. No public registration.</p>
                </footer>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('.toggle-password')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
    });

    (function () {
        const timer = document.getElementById('lockoutTimer');
        const loginBtn = document.getElementById('loginBtn');
        if (!timer || !loginBtn) return;

        const until = parseInt(timer.dataset.until, 10) * 1000;

        function tick() {
            const remaining = Math.ceil((until - Date.now()) / 1000);
            const secondsEl = document.getElementById('lockoutSeconds');

            if (remaining > 0) {
                loginBtn.disabled = true;
                if (secondsEl) secondsEl.textContent = remaining;
                return;
            }

            loginBtn.disabled = false;
            timer.classList.remove('alert-warning');
            timer.classList.add('alert-success');
            timer.innerHTML = '<i class="bi bi-unlock me-1"></i>You can try logging in again.';
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
