<?php $__env->startSection('title', 'Events'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Events</h1>
        <p class="page-subtitle text-muted">Manage village events and activities</p>
    </div>
    <div class="d-flex gap-2">
        <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin'])): ?>
            <a href="<?php echo e(route('events.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Event
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('events.index')); ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, venue..." value="<?php echo e(request('search')); ?>">
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
                        <option value="upcoming" <?php echo e(request('status') == 'upcoming' ? 'selected' : ''); ?>>Upcoming</option>
                        <option value="ongoing" <?php echo e(request('status') == 'ongoing' ? 'selected' : ''); ?>>Ongoing</option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
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
                    <a href="<?php echo e(route('events.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <?php if($event->cover_image): ?>
                    <img src="<?php echo e($event->cover_image_url); ?>" class="card-img-top" alt="<?php echo e($event->title); ?>" style="height: 160px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                        <i class="bi bi-calendar-event fs-1 text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title fw-semibold mb-0"><?php echo e($event->title); ?></h6>
                        <?php if($event->status === 'upcoming'): ?>
                            <span class="badge bg-primary">Upcoming</span>
                        <?php elseif($event->status === 'ongoing'): ?>
                            <span class="badge bg-success">Ongoing</span>
                        <?php elseif($event->status === 'completed'): ?>
                            <span class="badge bg-secondary">Completed</span>
                        <?php elseif($event->status === 'cancelled'): ?>
                            <span class="badge bg-danger">Cancelled</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Draft</span>
                        <?php endif; ?>
                    </div>
                    <p class="card-text small text-muted mb-2">
                        <i class="bi bi-calendar me-1"></i> <?php echo e($event->event_date->format('M d, Y')); ?>

                        <?php if($event->start_time): ?>
                            <br><i class="bi bi-clock me-1"></i> <?php echo e($event->start_time->format('h:i A')); ?>

                            <?php if($event->end_time): ?> – <?php echo e($event->end_time->format('h:i A')); ?> <?php endif; ?>
                        <?php endif; ?>
                        <?php if($event->venue): ?>
                            <br><i class="bi bi-geo-alt me-1"></i> <?php echo e($event->venue); ?>

                        <?php endif; ?>
                    </p>
                    <span class="badge bg-light text-dark"><?php echo e($event->category); ?></span>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>
                        <?php echo e($event->going_count); ?> Going &middot; <?php echo e($event->maybe_count); ?> Maybe
                    </small>
                    <div class="d-flex gap-1">
                        <a href="<?php echo e(route('events.show', $event)); ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <?php if($event->isManagedBy(auth()->user())): ?>
                            <form action="<?php echo e(route('events.destroy', $event)); ?>" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this event? This cannot be undone.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Event">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-event fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">No events found.</p>
                    <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin'])): ?>
                        <a href="<?php echo e(route('events.create')); ?>" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Create First Event
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if($events->hasPages()): ?>
    <div class="mt-3">
        <?php echo e($events->withQueryString()->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/events/index.blade.php ENDPATH**/ ?>