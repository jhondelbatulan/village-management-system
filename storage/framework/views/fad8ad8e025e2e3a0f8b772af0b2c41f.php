<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
<?php $branding = app(\App\Services\BrandingService::class); ?>
<div class="auth-wrapper">
    <div class="auth-background" style="background-image: url('<?php echo e(asset(config('app_settings.assets.login_background'))); ?>');"></div>
    <div class="auth-overlay"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo e($branding->getLogoUrl()); ?>"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">
                                <?php echo e($branding->get('system_name', config('app_settings.name'))); ?>

                            </h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                <?php echo e($branding->get('system_tagline', config('app_settings.tagline'))); ?>

                            </p>
                        </div>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success alert-sm py-2 small" role="alert">
                                <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger py-2 small mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <?php echo e($errors->first()); ?>

                            </div>
                        <?php endif; ?>

                        <?php if(($lockSeconds ?? 0) > 0): ?>
                            <div class="alert alert-warning py-2 small mb-3" id="lockoutTimer"
                                 role="alert"
                                 data-until="<?php echo e(now()->addSeconds($lockSeconds)->timestamp); ?>">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Too many failed attempts. Please try again in
                                <strong><span id="lockoutSeconds"><?php echo e($lockSeconds); ?></span>s</strong>.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('login.submit')); ?>" method="POST" class="auth-form" novalidate>
                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="username"
                                       name="username"
                                       value="<?php echo e(old('username')); ?>"
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
                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
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
                                <a href="<?php echo e(route('password.request')); ?>" class="small text-decoration-none forgot-link">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn" id="loginBtn"
                                    <?php echo e(($lockSeconds ?? 0) > 0 ? 'disabled' : ''); ?>>
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </form>
                    </div>
                </div>

                <footer class="auth-footer text-center mt-4">
                    <p class="text-white-50 small mb-1"><?php echo e(config('app_settings.footer_text')); ?></p>
                    <p class="text-white-50 small mb-0">Authorized personnel only. No public registration.</p>
                </footer>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/auth/login.blade.php ENDPATH**/ ?>