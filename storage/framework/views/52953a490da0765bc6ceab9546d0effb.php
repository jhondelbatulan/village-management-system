<?php $__env->startSection('title', 'Edit Event'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Edit Event</h1>
        <p class="page-subtitle text-muted">Update event: <?php echo e($event->title); ?></p>
    </div>
    <a href="<?php echo e(route('events.index')); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?php echo e(route('events.update', $event)); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?php echo e(old('title', $event->title)); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e(old('category', $event->category) == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo e(old('description', $event->description)); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" value="<?php echo e(old('event_date', $event->event_date?->format('Y-m-d'))); ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="<?php echo e(old('start_time', $event->start_time?->format('H:i'))); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="<?php echo e(old('end_time', $event->end_time?->format('H:i'))); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Venue</label>
                    <input type="text" name="venue" class="form-control" value="<?php echo e(old('venue', $event->venue)); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Participants</label>
                    <input type="number" name="max_participants" class="form-control" value="<?php echo e(old('max_participants', $event->max_participants)); ?>" min="1">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    <?php if($event->cover_image): ?>
                        <small class="text-muted">Current: <?php echo e(basename($event->cover_image)); ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <?php if($event->attachment): ?>
                        <small class="text-muted">Current: <?php echo e(basename($event->attachment)); ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo e(route('events.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Event
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/events/edit.blade.php ENDPATH**/ ?>