<?php if($pinnedSessions->count() > 0): ?>
<div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 280px;">
    <li class="px-3 py-2">
        <small class="text-muted fw-semibold">QUICK SWITCH</small>
    </li>
    <li><hr class="dropdown-divider mt-0"></li>

    <?php if($isSwitched): ?>
    <li>
        <form action="<?php echo e(route('sessions.back')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="dropdown-item text-warning">
                <i class="bi bi-arrow-return-left me-2"></i>Switch Back to Original
            </button>
        </form>
    </li>
    <li><hr class="dropdown-divider"></li>
    <?php endif; ?>

    <?php $__currentLoopData = $pinnedSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($session->targetUser && $session->target_user_id !== auth()->id()): ?>
        <li>
            <form action="<?php echo e(route('sessions.switch', $session->target_user_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="dropdown-item d-flex align-items-center py-2">
                    <img src="<?php echo e(asset($session->targetUser->profile_picture ?? config('app_settings.assets.default_profile'))); ?>"
                         alt="" class="rounded-circle me-2" width="28" height="28">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?php echo e($session->targetUser->full_name); ?></div>
                        <small class="text-muted">
                            <?php echo e(ucfirst(str_replace('_', ' ', $session->targetUser->role?->role_slug ?? ''))); ?>

                            <?php if($session->targetUser->village): ?>
                                — <?php echo e($session->targetUser->village->village_name); ?>

                            <?php endif; ?>
                        </small>
                    </div>
                    <i class="bi bi-box-arrow-right text-muted"></i>
                </button>
            </form>
        </li>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <li><hr class="dropdown-divider"></li>
    <li>
        <a class="dropdown-item text-center small" href="<?php echo e(route('sessions.index')); ?>">
            <i class="bi bi-gear me-1"></i>Manage Accounts
        </a>
    </li>
</div>
<?php endif; ?>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/sessions/_quick-switch.blade.php ENDPATH**/ ?>