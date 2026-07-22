<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Notifications</h4>
            <p class="page-subtitle text-muted mb-0">
                <?php if($unreadCount > 0): ?>
                    You have <strong><?php echo e($unreadCount); ?></strong> unread notification<?php echo e($unreadCount !== 1 ? 's' : ''); ?>.
                <?php else: ?>
                    All caught up!
                <?php endif; ?>
            </p>
        </div>
        <?php if($unreadCount > 0): ?>
        <form method="POST" action="<?php echo e(route('notifications.mark-all-read')); ?>" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i>Mark All as Read
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="notification-item d-flex align-items-start gap-3 p-3 border-bottom <?php echo e($notification->read_at ? '' : 'bg-light'); ?>">
            <span class="badge bg-<?php echo e($notification->color ?? 'secondary'); ?> rounded-circle p-2 mt-1 flex-shrink-0">
                <i class="bi <?php echo e($notification->icon ?? 'bi-bell'); ?> text-white"></i>
            </span>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 <?php echo e($notification->read_at ? '' : 'fw-bold'); ?>"><?php echo e($notification->title); ?></h6>
                        <?php if($notification->message): ?>
                            <p class="text-muted small mb-0"><?php echo e($notification->message); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-end flex-shrink-0 ms-2">
                        <small class="text-muted" title="<?php echo e($notification->created_at->format('F d, Y h:i A')); ?>">
                            <?php echo e($notification->created_at->diffForHumans()); ?>

                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-1">
                    <?php if(!$notification->read_at): ?>
                    <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 small">Mark as read</button>
                    </form>
                    <?php endif; ?>
                    <?php if($notification->action_url): ?>
                    <a href="<?php echo e($notification->action_url); ?>" class="btn btn-sm btn-link text-decoration-none p-0 small">View details</a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('notifications.destroy', $notification->id)); ?>" style="display:inline;" onsubmit="return confirm('Delete this notification?');">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size:3rem;"></i>
            <p class="text-muted mt-2 mb-0">No notifications yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification-item form').forEach(function(form) {
        form.addEventListener('submit', function() {
            setTimeout(function() { location.reload(); }, 300);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/notifications/index.blade.php ENDPATH**/ ?>