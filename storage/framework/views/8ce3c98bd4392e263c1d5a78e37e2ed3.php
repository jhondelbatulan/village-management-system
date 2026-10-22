<?php $__env->startSection('title', 'Village Documents - ' . $village->village_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-earmark-text me-1"></i><?php echo e($village->village_name); ?> — Documents
            </h4>
            <p class="page-subtitle text-muted mb-0">Manage village documents and files.</p>
        </div>
        <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

<?php $canManageDocuments = in_array(auth()->user()->role?->role_slug, ['admin', 'secretary'], true); ?>

<?php if($canManageDocuments): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" href="#uploadForm" role="button" aria-expanded="false" aria-controls="uploadForm">
            <i class="bi bi-cloud-upload me-1"></i>Upload Document
        </a>
    </div>
    <div class="collapse" id="uploadForm">
        <div class="card-body border-top">
            <form action="<?php echo e(route('villages.documents.store', $village)); ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?php echo csrf_field(); ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="title" name="title" value="<?php echo e(old('title')); ?>" required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-5">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                  id="description" name="description" rows="1"><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-3">
                        <label for="file" class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">PDF, DOC, DOCX, JPG, PNG</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="reset" class="btn btn-outline-secondary btn-sm">Clear</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload
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
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-center">File Type</th>
                        <th scope="col">Uploaded By</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="fw-semibold"><?php echo e($document->title); ?></td>
                            <td><?php echo e(Str::limit($document->description ?? '', 60) ?: '—'); ?></td>
                            <td class="text-center">
                                <?php $ext = strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION)); ?>
                                <?php if($ext === 'PDF'): ?>
                                    <span class="badge bg-danger"><?php echo e($ext); ?></span>
                                <?php elseif(in_array($ext, ['DOC', 'DOCX'])): ?>
                                    <span class="badge bg-primary"><?php echo e($ext); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo e($ext); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($document->uploader?->full_name ?? '—'); ?></td>
                            <td><?php echo e($document->created_at->format('M d, Y')); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('villages.documents.download', ['village' => $village, 'document' => $document])); ?>"
                                   class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <?php if($canManageDocuments): ?>
                                <form action="<?php echo e(route('villages.documents.destroy', ['village' => $village, 'document' => $document])); ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
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
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                                No documents found. Upload a document using the form above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($documents->hasPages()): ?>
        <div class="card-footer bg-white border-0 pt-3">
            <?php echo e($documents->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/villages/documents.blade.php ENDPATH**/ ?>