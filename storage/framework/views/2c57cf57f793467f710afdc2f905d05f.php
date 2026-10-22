<?php $__env->startSection('title', 'Create Announcement'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Create Announcement</h1>
        <p class="page-subtitle text-muted">Create a new announcement for <?php echo e($village->village_name ?? 'your village'); ?></p>
    </div>
    <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary">
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
        <form action="<?php echo e(route('announcements.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <?php if($role === 'super_admin'): ?>
                <input type="hidden" name="village_id" id="village_id_hidden" value="<?php echo e(old('village_id')); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Target Village <span class="text-danger">*</span></label>
                        <select name="village_id_select" id="village_id_select" class="form-select" required>
                            <option value="">Select Village</option>
                            <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v->id); ?>" <?php echo e(old('village_id') == $v->id ? 'selected' : ''); ?>>
                                    <?php echo e($v->village_name); ?> (<?php echo e($v->village_code); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Select the village this announcement is for.</small>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="village_id" value="<?php echo e($village->id); ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?php echo e(old('title')); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e(old('category') == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo e(old('description')); ?></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo e(old('start_date')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Expiration Date</label>
                    <input type="date" name="expiration_date" class="form-control" value="<?php echo e(old('expiration_date')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    <small class="text-muted">Max 2MB. JPG, PNG.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">Max 5MB. PDF, DOC, DOCX, XLS, XLSX.</small>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_pinned" value="1" <?php echo e(old('is_pinned') ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold">Pin this announcement</label>
                    </div>
                </div>

                <?php
                    $selectedRoles = old('visible_roles');
                    $isPublic = ! is_array($selectedRoles) || $selectedRoles === [] || in_array('public', $selectedRoles, true);
                ?>
                <div class="col-12">
                    <label class="form-label fw-semibold">Who Can View <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visible_roles[]" value="public" id="visible_public" <?php echo e($isPublic ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="visible_public"><i class="bi bi-globe2 me-1"></i>Public (all roles)</label>
                        </div>
                        <?php $__currentLoopData = ['admin', 'secretary', 'treasurer', 'resident']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleSlug): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="visible_roles[]" value="<?php echo e($roleSlug); ?>" id="visible_<?php echo e($roleSlug); ?>" <?php echo e(is_array($selectedRoles) && in_array($roleSlug, $selectedRoles, true) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="visible_<?php echo e($roleSlug); ?>"><?php echo e(ucfirst($roleSlug)); ?></label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <small class="text-muted">Leave "Public" checked to show this announcement to everyone. Uncheck it and tick only the roles allowed to view it.</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Announcement
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php if($role === 'super_admin'): ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('village_id_select');
    const hidden = document.getElementById('village_id_hidden');
    if (select && hidden) {
        select.addEventListener('change', function() { hidden.value = this.value; });
        if (select.value) hidden.value = select.value;
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/announcements/create.blade.php ENDPATH**/ ?>