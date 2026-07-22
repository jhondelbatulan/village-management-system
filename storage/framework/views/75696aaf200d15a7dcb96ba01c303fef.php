<?php $__env->startSection('title', 'Login History'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Login History</h4>
            <p class="page-subtitle text-muted mb-0">View all login and logout activity.</p>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('system-admin.login-history.index')); ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                <?php echo e($user->full_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="success" <?php echo e(request('status') == 'success' ? 'selected' : ''); ?>>Success</option>
                        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <a href="<?php echo e(route('system-admin.login-history.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Duration</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>OS</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $loginHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($entry->user): ?>
                                        <img src="<?php echo e($entry->user->profile_picture_url); ?>" alt="<?php echo e($entry->user->full_name); ?>" class="rounded-circle me-2" width="32" height="32">
                                    <?php endif; ?>
                                    <span class="fw-semibold"><?php echo e($entry->user->full_name ?? 'Unknown'); ?></span>
                                </div>
                            </td>
                            <td><small class="text-muted"><?php echo e($entry->username ?? '—'); ?></small></td>
                            <td>
                                <small class="text-muted"><?php echo e($entry->login_at?->format('M d, Y H:i:s') ?? '—'); ?></small>
                            </td>
                            <td>
                                <?php if($entry->logout_at): ?>
                                    <small class="text-muted"><?php echo e($entry->logout_at->format('M d, Y H:i:s')); ?></small>
                                <?php else: ?>
                                    <span class="badge bg-info">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($entry->logout_at && $entry->login_at): ?>
                                    <?php
                                        $minutes = $entry->login_at->diffInMinutes($entry->logout_at);
                                        $hours = floor($minutes / 60);
                                        $mins = $minutes % 60;
                                        $duration = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
                                    ?>
                                    <small class="text-muted"><?php echo e($duration); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?php echo e($entry->ip_address ?? '—'); ?></small></td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 120px;" title="<?php echo e($entry->browser); ?>">
                                    <?php echo e($entry->browser ?? '—'); ?>

                                </small>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 120px;" title="<?php echo e($entry->os); ?>">
                                    <?php echo e($entry->os ?? '—'); ?>

                                </small>
                            </td>
                            <td>
                                <?php if($entry->status === 'success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-box-arrow-in-right fs-1 d-block mb-2"></i>
                                No login history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($loginHistory->hasPages()): ?>
        <div class="card-footer bg-white border-0 pt-3">
            <?php echo e($loginHistory->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/system-admin/login-history.blade.php ENDPATH**/ ?>