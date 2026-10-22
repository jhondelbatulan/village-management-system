<?php $__env->startSection('title', 'Village Officers - ' . $village->village_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-1"></i><?php echo e($village->village_name); ?> — Officers
            </h4>
            <p class="page-subtitle text-muted mb-0">Manage village officers and their terms.</p>
        </div>
        <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

<?php $canManageOfficers = in_array(auth()->user()->role?->role_slug, ['admin', 'secretary'], true); ?>

<?php if($canManageOfficers): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" href="#addOfficerForm" role="button" aria-expanded="false" aria-controls="addOfficerForm">
            <i class="bi bi-plus-lg me-1"></i>Add New Officer
        </a>
    </div>
    <div class="collapse" id="addOfficerForm">
        <div class="card-body border-top">
            <form action="<?php echo e(route('villages.officers.store', $village)); ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?php echo csrf_field(); ?>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="position" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="position" name="position" value="<?php echo e(old('position')); ?>" required>
                        <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-3">
                        <label for="full_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="full_name" name="full_name" value="<?php echo e(old('full_name')); ?>" required>
                        <?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-2">
                        <label for="photo" class="form-label fw-semibold">Photo</label>
                        <input type="file" class="form-control <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="photo" name="photo" accept="image/*">
                        <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-2">
                        <label for="term_start" class="form-label fw-semibold">Term Start</label>
                        <input type="date" class="form-control <?php $__errorArgs = ['term_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="term_start" name="term_start" value="<?php echo e(old('term_start')); ?>">
                        <?php $__errorArgs = ['term_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-2">
                        <label for="term_end" class="form-label fw-semibold">Term End</label>
                        <input type="date" class="form-control <?php $__errorArgs = ['term_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="term_end" name="term_end" value="<?php echo e(old('term_end')); ?>">
                        <?php $__errorArgs = ['term_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="status" name="status" required>
                            <option value="active" <?php echo e(old('status', 'active') === 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="inactive" <?php echo e(old('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="reset" class="btn btn-outline-secondary btn-sm">Clear</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i>Add Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Position</th>
                        <th scope="col">Term Start</th>
                        <th scope="col">Term End</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $officers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $officer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if($officer->photo): ?>
                                    <img src="<?php echo e(asset('storage/' . $officer->photo)); ?>" alt="<?php echo e($officer->full_name); ?>"
                                         class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?php echo e($officer->full_name); ?></td>
                            <td><span class="badge bg-primary"><?php echo e($officer->position); ?></span></td>
                            <td><?php echo e($officer->term_start ? \Carbon\Carbon::parse($officer->term_start)->format('M d, Y') : '—'); ?></td>
                            <td><?php echo e($officer->term_end ? \Carbon\Carbon::parse($officer->term_end)->format('M d, Y') : '—'); ?></td>
                            <td class="text-center">
                                <?php if($officer->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if($canManageOfficers): ?>
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editOfficerModal<?php echo e($officer->id); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="<?php echo e(route('villages.officers.destroy', ['village' => $village, 'officer' => $officer])); ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to remove this officer?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                No officers found. Add an officer using the form above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($officers->hasPages()): ?>
        <div class="card-footer bg-white border-0 pt-3">
            <?php echo e($officers->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php if($canManageOfficers): ?>
<?php $__currentLoopData = $officers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $officer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editOfficerModal<?php echo e($officer->id); ?>" tabindex="-1" aria-labelledby="editOfficerModalLabel<?php echo e($officer->id); ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('villages.officers.update', ['village' => $village, 'officer' => $officer])); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="editOfficerModalLabel<?php echo e($officer->id); ?>">
                            <i class="bi bi-pencil me-1"></i>Edit Officer
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_position_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="edit_position_<?php echo e($officer->id); ?>" name="position" value="<?php echo e(old('position', $officer->position)); ?>" required>
                            <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="edit_full_name_<?php echo e($officer->id); ?>" name="full_name" value="<?php echo e(old('full_name', $officer->full_name)); ?>" required>
                            <?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Photo</label>
                            <?php if($officer->photo): ?>
                                <div class="mb-2">
                                    <img src="<?php echo e(asset('storage/' . $officer->photo)); ?>" alt="Current Photo" class="rounded" style="max-height: 60px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="edit_photo_<?php echo e($officer->id); ?>" name="photo" accept="image/*">
                            <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_term_start_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Term Start</label>
                                <input type="date" class="form-control <?php $__errorArgs = ['term_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit_term_start_<?php echo e($officer->id); ?>" name="term_start"
                                       value="<?php echo e(old('term_start', $officer->term_start ? \Carbon\Carbon::parse($officer->term_start)->format('Y-m-d') : '')); ?>">
                                <?php $__errorArgs = ['term_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_term_end_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Term End</label>
                                <input type="date" class="form-control <?php $__errorArgs = ['term_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="edit_term_end_<?php echo e($officer->id); ?>" name="term_end"
                                       value="<?php echo e(old('term_end', $officer->term_end ? \Carbon\Carbon::parse($officer->term_end)->format('Y-m-d') : '')); ?>">
                                <?php $__errorArgs = ['term_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label for="edit_status_<?php echo e($officer->id); ?>" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="edit_status_<?php echo e($officer->id); ?>" name="status" required>
                                <option value="active" <?php echo e(old('status', $officer->status) === 'active' ? 'selected' : ''); ?>>Active</option>
                                <option value="inactive" <?php echo e(old('status', $officer->status) === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Officer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/villages/officers.blade.php ENDPATH**/ ?>