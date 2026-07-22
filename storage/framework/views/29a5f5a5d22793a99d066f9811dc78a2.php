<?php $__env->startSection('title', 'Village Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Village Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage all villages in the system.</p>
        </div>
        <?php if(auth()->user()?->role?->role_slug === 'super_admin'): ?>
            <a href="<?php echo e(route('villages.create')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Create Village
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="<?php echo e(route('villages.index')); ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="search" class="form-label fw-semibold small">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Search by name or code..." value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-4 col-lg-3">
                <label for="status" class="form-label fw-semibold small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
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
                        <th scope="col">Village Code</th>
                        <th scope="col">Village Name</th>
                        <th scope="col">Address</th>
                        <th scope="col" class="text-center">Households</th>
                        <th scope="col" class="text-center">Population</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge bg-light text-dark"><?php echo e($village->village_code); ?></span></td>
                            <td class="fw-semibold"><?php echo e($village->village_name); ?></td>
                            <td><?php echo e($village->address ?? '—'); ?></td>
                            <td class="text-center"><?php echo e($village->total_households ?? 0); ?></td>
                            <td class="text-center"><?php echo e($village->total_population ?? 0); ?></td>
                            <td class="text-center">
                                <?php if($village->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('villages.edit', $village)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($village->status === 'active'): ?>
                                    <form action="<?php echo e(route('villages.destroy', $village)); ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to archive this village?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-building fs-1 d-block mb-2"></i>
                                No villages found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($villages->hasPages()): ?>
        <div class="card-footer bg-white border-0 pt-3">
            <?php echo e($villages->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php if(isset($archivedVillages) && $archivedVillages->count() > 0): ?>
    <div class="mt-4">
        <a class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse" href="#archivedSection" role="button" aria-expanded="false" aria-controls="archivedSection">
            <i class="bi bi-archive me-1"></i>Archived Villages (<?php echo e($archivedVillages->count()); ?>)
        </a>

        <div class="collapse" id="archivedSection">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Village Code</th>
                                    <th scope="col">Village Name</th>
                                    <th scope="col">Address</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $archivedVillages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark"><?php echo e($village->village_code); ?></span></td>
                                        <td class="fw-semibold"><?php echo e($village->village_name); ?></td>
                                        <td><?php echo e($village->address ?? '—'); ?></td>
                                        <td class="text-center"><span class="badge bg-secondary">Archived</span></td>
                                        <td class="text-end">
                                            <form action="<?php echo e(route('villages.restore', $village)); ?>" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to restore this village?')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/villages/index.blade.php ENDPATH**/ ?>