<?php $__env->startSection('title', 'Announcements'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Announcements</h1>
        <p class="page-subtitle text-muted">Manage village announcements and notices</p>
    </div>
    <div class="d-flex gap-2">
        <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin'])): ?>
            <a href="<?php echo e(route('announcements.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Announcement
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('announcements.index')); ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, description..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e(request('category') == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                        <option value="archived" <?php echo e(request('status') == 'archived' ? 'selected' : ''); ?>>Archived</option>
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
                    <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px"></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Village</th>
                        <th>Visible To</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if($announcement->is_pinned): ?>
                                    <i class="bi bi-pin-fill text-warning" title="Pinned"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('announcements.show', $announcement)); ?>" class="fw-semibold text-decoration-none">
                                    <?php echo e($announcement->title); ?>

                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark"><?php echo e($announcement->category); ?></span></td>
                            <td><?php echo e($announcement->village->village_name ?? '—'); ?></td>
                            <td>
                                <?php if(empty($announcement->visible_roles)): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis border"><i class="bi bi-globe2 me-1"></i>Public</span>
                                <?php else: ?>
                                    <?php $__currentLoopData = $announcement->visible_roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visibleRole): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-primary-subtle text-primary-emphasis border"><?php echo e(ucfirst($visibleRole)); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?php if($announcement->start_date): ?>
                                    <?php echo e($announcement->start_date->format('M d, Y')); ?>

                                <?php endif; ?>
                                <?php if($announcement->expiration_date): ?>
                                    – <?php echo e($announcement->expiration_date->format('M d, Y')); ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($announcement->status === 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php elseif($announcement->status === 'archived'): ?>
                                    <span class="badge bg-secondary">Archived</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('announcements.show', $announcement)); ?>" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if($announcement->isManagedBy(auth()->user())): ?>
                                        <?php if($announcement->status === 'draft' || $announcement->status === 'archived'): ?>
                                            <a href="<?php echo e(route('announcements.edit', $announcement)); ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($announcement->status === 'draft'): ?>
                                            <form action="<?php echo e(route('announcements.publish', $announcement)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Publish">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if($announcement->status === 'published'): ?>
                                            <form action="<?php echo e(route('announcements.archive', $announcement)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('announcements.destroy', $announcement)); ?>" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-megaphone fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No announcements found.</p>
                                <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin'])): ?>
                                    <a href="<?php echo e(route('announcements.create')); ?>" class="btn btn-primary btn-sm mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Create First Announcement
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($announcements->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($announcements->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/announcements/index.blade.php ENDPATH**/ ?>