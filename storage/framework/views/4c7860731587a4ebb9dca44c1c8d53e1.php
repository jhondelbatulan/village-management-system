<?php $__env->startSection('title', 'Switch Account'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Switch Account</h4>
            <p class="page-subtitle text-muted mb-0">Quickly switch between different user accounts.</p>
        </div>
        <?php if($isSwitched): ?>
        <form action="<?php echo e(route('sessions.back')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="bi bi-arrow-return-left me-1"></i>Switch Back to Original
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($isSwitched): ?>
<div class="alert alert-warning mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Session Impersonation Active</strong> — You are currently viewing as another user.
    <form action="<?php echo e(route('sessions.back')); ?>" method="POST" class="d-inline">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-sm btn-warning ms-2">Switch Back</button>
    </form>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pin-angle me-2 text-primary"></i>Pinned Accounts (<?php echo e($pinnedSessions->count()); ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if($pinnedSessions->count() > 0): ?>
                <div class="list-group list-group-flush">
                    <?php $__currentLoopData = $pinnedSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo e(asset($session->targetUser->profile_picture ?? config('app_settings.assets.default_profile'))); ?>"
                                 alt="" class="rounded-circle me-3" width="40" height="40">
                            <div>
                                <div class="fw-semibold"><?php echo e($session->targetUser->full_name); ?></div>
                                <small class="text-muted">
                                    <?php echo e($session->targetUser->username); ?> |
                                    <span class="badge bg-<?php echo e($session->targetUser->role?->role_slug === 'super_admin' ? 'danger' : ($session->targetUser->role?->role_slug === 'admin' ? 'primary' : ($session->targetUser->role?->role_slug === 'treasurer' ? 'success' : ($session->targetUser->role?->role_slug === 'secretary' ? 'info' : 'secondary')))); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $session->targetUser->role?->role_slug ?? ''))); ?>

                                    </span>
                                    <?php if($session->targetUser->village): ?>
                                        | <?php echo e($session->targetUser->village->village_name); ?>

                                    <?php endif; ?>
                                </small>
                                <?php if($session->last_used_at): ?>
                                    <br><small class="text-muted">Last used: <?php echo e($session->last_used_at->diffForHumans()); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if($session->target_user_id !== auth()->id()): ?>
                            <form action="<?php echo e(route('sessions.switch', $session->target_user_id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Switch
                                </button>
                            </form>
                            <?php endif; ?>
                            <form action="<?php echo e(route('sessions.unpin', $session->target_user_id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Unpin">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-pin-angle fs-1 d-block mb-2"></i>
                    <p class="mb-2">No pinned accounts yet.</p>
                    <small>Pin accounts below for quick switching.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-success"></i>All Available Accounts (<?php echo e($availableAccounts->count()); ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php $__currentLoopData = $availableAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo e(asset($account->profile_picture ?? config('app_settings.assets.default_profile'))); ?>"
                                 alt="" class="rounded-circle me-3" width="40" height="40">
                            <div>
                                <div class="fw-semibold"><?php echo e($account->full_name); ?></div>
                                <small class="text-muted">
                                    <?php echo e($account->username); ?> |
                                    <span class="badge bg-<?php echo e($account->role?->role_slug === 'super_admin' ? 'danger' : ($account->role?->role_slug === 'admin' ? 'primary' : ($account->role?->role_slug === 'treasurer' ? 'success' : ($account->role?->role_slug === 'secretary' ? 'info' : 'secondary')))); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $account->role?->role_slug ?? ''))); ?>

                                    </span>
                                    <?php if($account->village): ?>
                                        | <?php echo e($account->village->village_name); ?>

                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="<?php echo e(route('sessions.switch', $account->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Switch
                                </button>
                            </form>
                            <form action="<?php echo e(route('sessions.pin')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo e($account->id); ?>">
                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Pin">
                                    <i class="bi bi-pin-angle"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>How It Works
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <h6 class="fw-semibold"><i class="bi bi-pin-angle text-primary me-2"></i>Pin Accounts</h6>
                    <p class="text-muted small mb-0">Pin frequently used accounts for quick access. Pinned accounts appear in the navbar dropdown.</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-semibold"><i class="bi bi-box-arrow-right text-success me-2"></i>Switch Accounts</h6>
                    <p class="text-muted small mb-0">Click "Switch" to instantly log in as another user. Your original session is preserved.</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-semibold"><i class="bi bi-arrow-return-left text-warning me-2"></i>Switch Back</h6>
                    <p class="text-muted small mb-0">Use the "Switch Back" button or navbar dropdown to return to your original account.</p>
                </div>
                <div>
                    <h6 class="fw-semibold"><i class="bi bi-shield-check text-danger me-2"></i>Security Note</h6>
                    <p class="text-muted small mb-0">This feature is for development/testing. Account switching is logged for audit purposes.</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lightning me-2 text-warning"></i>Quick Tips
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Pin one account per role for fastest testing</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Use the navbar dropdown for one-click switching</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Pinned accounts show role badges and village names</li>
                    <li><i class="bi bi-check text-success me-2"></i>Your current impersonation is shown in the navbar</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/sessions/index.blade.php ENDPATH**/ ?>