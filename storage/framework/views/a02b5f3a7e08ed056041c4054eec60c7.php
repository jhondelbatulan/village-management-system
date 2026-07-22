<?php $__env->startSection('title', 'About System'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5 text-center">
                <?php
                    $branding = app(\App\Services\BrandingService::class);
                    $logo = $branding->getLogoUrl();
                ?>
                <img src="<?php echo e($logo); ?>" alt="Logo" class="mb-4" style="max-width:120px;max-height:120px;object-fit:contain;border-radius:16px;">

                <h2 class="fw-bold mb-1"><?php echo e($branding->get('system_name', config('app_settings.name'))); ?></h2>
                <p class="text-muted mb-4"><?php echo e($branding->get('system_tagline', config('app_settings.tagline'))); ?></p>

                <div class="row g-4 text-start mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-primary me-3 p-2"><i class="bi bi-building fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Organization</div>
                                <div class="text-muted"><?php echo e($branding->get('organization_name', 'N/A')); ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-info me-3 p-2"><i class="bi bi-geo-alt fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">CPDO Office</div>
                                <div class="text-muted"><?php echo e($branding->get('cpdo_office_name', 'N/A')); ?></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-success me-3 p-2"><i class="bi bi-code-slash fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Developer</div>
                                <div class="text-muted"><?php echo e($branding->get('developer_name', 'N/A')); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-warning me-3 p-2"><i class="bi bi-layers fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Capstone Team</div>
                                <div class="text-muted">BS Information Technology</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-secondary me-3 p-2"><i class="bi bi-mortarboard fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Adviser</div>
                                <div class="text-muted">Faculty Adviser</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-danger me-3 p-2"><i class="bi bi-calendar fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Academic Year</div>
                                <div class="text-muted"><?php echo e(date('Y')); ?>–<?php echo e(date('Y') + 1); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-3 text-start mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-primary">Laravel <?php echo e(app()->version()); ?></div>
                            <small class="text-muted">Framework</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-success">PHP <?php echo e(phpversion()); ?></div>
                            <small class="text-muted">Runtime</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-info"><?php echo e(config('database.default') === 'sqlite' ? 'SQLite' : config('database.default')); ?></div>
                            <small class="text-muted">Database</small>
                        </div>
                    </div>
                </div>

                <div class="text-muted small">
                    <?php echo e($branding->get('footer_copyright', config('app_settings.footer_text'))); ?>

                </div>
                <div class="text-muted small mt-1">Version <?php echo e($branding->get('system_version', config('app_settings.version'))); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/system-admin/about.blade.php ENDPATH**/ ?>