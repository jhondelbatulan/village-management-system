<?php $__env->startSection('title', $announcement->title); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><?php echo e($announcement->title); ?></h1>
        <p class="page-subtitle text-muted">
            <?php echo e($announcement->village?->village_name ?? ''); ?> &middot;
            Created <?php echo e($announcement->created_at->format('M d, Y')); ?>

        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if($announcement->isManagedBy(auth()->user())): ?>
            <?php if($announcement->status === 'draft' || $announcement->status === 'archived'): ?>
                <a href="<?php echo e(route('announcements.edit', $announcement)); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if($announcement->is_pinned): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-pin me-1"></i> Pinned</span>
                    <?php endif; ?>
                    <?php if($announcement->status === 'published'): ?>
                        <span class="badge bg-success">Published</span>
                    <?php elseif($announcement->status === 'archived'): ?>
                        <span class="badge bg-secondary">Archived</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Draft</span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark"><?php echo e($announcement->category); ?></span>
                </div>

                <?php if($announcement->cover_image): ?>
                    <div class="mb-3">
                        <img src="<?php echo e($announcement->cover_image_url); ?>" alt="Cover" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                <?php endif; ?>

                <div class="announcement-body">
                    <?php echo nl2br(e($announcement->description)); ?>

                </div>

                <?php if($announcement->attachment): ?>
                    <div class="mt-3">
                        <a href="<?php echo e($announcement->attachment_url); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-paperclip me-1"></i> View Attachment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Village</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($announcement->village?->village_name ?? '—'); ?></dd>
                    <dt class="col-sm-5 text-muted">Category</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($announcement->category); ?></dd>
                    <dt class="col-sm-5 text-muted">Status</dt>
                    <dd class="col-sm-7">
                        <?php if($announcement->status === 'published'): ?>
                            <span class="badge bg-success">Published</span>
                        <?php elseif($announcement->status === 'archived'): ?>
                            <span class="badge bg-secondary">Archived</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Draft</span>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-5 text-muted">Visible To</dt>
                    <dd class="col-sm-7">
                        <?php if(empty($announcement->visible_roles)): ?>
                            <span class="badge bg-info-subtle text-info-emphasis border"><i class="bi bi-globe2 me-1"></i>Public</span>
                        <?php else: ?>
                            <?php $__currentLoopData = $announcement->visible_roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visibleRole): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-primary-subtle text-primary-emphasis border"><?php echo e(ucfirst($visibleRole)); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-5 text-muted">Start Date</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($announcement->start_date?->format('M d, Y') ?? '—'); ?></dd>
                    <dt class="col-sm-5 text-muted">Expires</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($announcement->expiration_date?->format('M d, Y') ?? '—'); ?></dd>
                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($announcement->creator?->full_name ?? '—'); ?></dd>
                </dl>
            </div>
        </div>

        <?php if($announcement->isManagedBy(auth()->user())): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if($announcement->status === 'draft'): ?>
                        <form action="<?php echo e(route('announcements.publish', $announcement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-lg me-1"></i> Publish
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($announcement->status === 'published'): ?>
                        <form action="<?php echo e(route('announcements.archive', $announcement)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-archive me-1"></i> Archive
                            </button>
                        </form>
                    <?php endif; ?>
                    <form action="<?php echo e(route('announcements.destroy', $announcement)); ?>" method="POST" onsubmit="return confirm('Delete this announcement?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/announcements/show.blade.php ENDPATH**/ ?>