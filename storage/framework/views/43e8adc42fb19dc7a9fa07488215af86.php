<?php $__env->startSection('title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">User Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage system user accounts and roles.</p>
        </div>
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Create User
        </a>
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
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('users.index')); ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, username, email..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->role_slug); ?>" <?php echo e(request('role') == $role->role_slug ? 'selected' : ''); ?>>
                                <?php echo e($role->role_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="">Newest First</option>
                        <option value="az" <?php echo e(request('sort') == 'az' ? 'selected' : ''); ?>>A – Z</option>
                        <option value="za" <?php echo e(request('sort') == 'za' ? 'selected' : ''); ?>>Z – A</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
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
                            <th scope="col">User</th>
                            <th scope="col">Username</th>
                            <th scope="col">Role</th>
                            <th scope="col">Village</th>
                            <th scope="col">Household</th>
                            <th scope="col">Status</th>
                            <th scope="col">Online</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr data-user-id="<?php echo e($user->id); ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo e($user->profile_picture_url); ?>"
                                             alt="<?php echo e($user->full_name); ?>"
                                             class="rounded-circle me-2" width="36" height="36">
                                        <div>
                                            <div class="fw-semibold"><?php echo e($user->full_name); ?></div>
                                            <small class="text-muted"><?php echo e($user->email); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($user->username); ?></td>
                                <td><span class="badge bg-primary"><?php echo e($user->role?->role_name); ?></span></td>
                                <td><?php echo e($user->village?->village_name ?? '—'); ?></td>
                                <td>
                                    <?php if($user->household): ?>
                                        <span class="text-muted">Blk <?php echo e($user->household->block_number ?? '—'); ?>, Lot <?php echo e($user->household->lot_number ?? '—'); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->status === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif($user->status === 'inactive'): ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="online-badge badge bg-secondary">Offline</span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if(auth()->id() !== $user->id): ?>
                                        <form action="<?php echo e(route('users.toggle-status', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-sm <?php echo e($user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success'); ?>" title="<?php echo e($user->status === 'active' ? 'Deactivate' : 'Activate'); ?>">
                                                <i class="bi bi-<?php echo e($user->status === 'active' ? 'pause-circle' : 'play-circle'); ?>"></i>
                                            </button>
                                        </form>
                                        <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($users->hasPages()): ?>
            <div class="card-footer bg-white border-0 pt-3">
                <?php echo e($users->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>
    </div>
    <?php $__env->stopSection(); ?>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateOnlineStatus() {
                fetch('<?php echo e(route('users.online-status')); ?>')
                    .then(function (r) { return r.json(); })
                    .then(function (onlineIds) {
                        document.querySelectorAll('[data-user-id]').forEach(function (row) {
                            var badge = row.querySelector('.online-badge');
                            if (!badge) return;
                            var userId = parseInt(row.getAttribute('data-user-id'), 10);
                            if (onlineIds.indexOf(userId) !== -1) {
                                badge.className = 'online-badge badge bg-success';
                                badge.textContent = 'Online';
                            } else {
                                badge.className = 'online-badge badge bg-secondary';
                                badge.textContent = 'Offline';
                            }
                        });
                    })
                    .catch(function () {});
            }
            updateOnlineStatus();
            setInterval(updateOnlineStatus, 30000);
        });
    </script>
    <?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/user-management/index.blade.php ENDPATH**/ ?>