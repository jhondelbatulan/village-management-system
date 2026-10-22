<?php $__env->startSection('title', 'Forgot Password'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-wrapper">
    <div class="auth-background" style="background-image: url('<?php echo e(asset(config('app_settings.assets.login_background'))); ?>');"></div>
    <div class="auth-overlay"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo e(asset(config('app_settings.assets.logo'))); ?>"
                                 alt="System Logo"
                                 class="auth-logo mb-3">
                            <h4 class="auth-title fw-bold text-primary mb-1">Forgot Password</h4>
                            <p class="auth-subtitle text-muted small mb-0">
                                Enter your account email and we'll send your username and a new password to sign in with.
                            </p>
                        </div>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success alert-sm py-2 small" role="alert">
                                <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?>

                        <?php if(session('error')): ?>
                            <div class="alert alert-danger alert-sm py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i><?php echo e(session('error')); ?>

                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('password.email')); ?>" method="POST" class="auth-form" novalidate>
                            <?php echo csrf_field(); ?>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-1"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="email"
                                       name="email"
                                       value="<?php echo e(old('email')); ?>"
                                       placeholder="Enter your email"
                                       required
                                       autofocus>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
                                <i class="bi bi-send me-2"></i>Send Login Credentials
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="<?php echo e(route('login')); ?>" class="small text-decoration-none forgot-link">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>

                <footer class="auth-footer text-center mt-4">
                    <p class="text-white-50 small mb-1"><?php echo e(config('app_settings.footer_text')); ?></p>
                </footer>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>