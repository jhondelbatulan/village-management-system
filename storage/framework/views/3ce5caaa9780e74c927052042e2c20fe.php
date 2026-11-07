<?php $__env->startSection('title', 'Resident Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Resident Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage and track all registered residents across villages.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('residents.export')); ?>" class="btn btn-success btn-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
                <a href="<?php echo e(route('residents.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Create Resident
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?php echo e(route('residents.index')); ?>" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search"
                           placeholder="Name or code..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <label for="village_id" class="form-label small fw-semibold">Village</label>
                    <select class="form-select form-select-sm" id="village_id" name="village_id">
                        <option value="">All Villages</option>
                        <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($village->id); ?>" <?php echo e(request('village_id') == $village->id ? 'selected' : ''); ?>>
                                <?php echo e($village->village_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="block_number" class="form-label small fw-semibold">Block</label>
                    <select class="form-select form-select-sm" id="block_number" name="block_number">
                        <option value="">All</option>
                        <?php $__currentLoopData = $blockNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($block); ?>" <?php echo e(request('block_number') == $block ? 'selected' : ''); ?>><?php echo e($block); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="lot_number" class="form-label small fw-semibold">Lot</label>
                    <select class="form-select form-select-sm" id="lot_number" name="lot_number">
                        <option value="">All</option>
                        <?php $__currentLoopData = $lotNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($lot); ?>" <?php echo e(request('lot_number') == $lot ? 'selected' : ''); ?>><?php echo e($lot); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="gender" class="form-label small fw-semibold">Gender</label>
                    <select class="form-select form-select-sm" id="gender" name="gender">
                        <option value="">All</option>
                        <option value="Male" <?php echo e(request('gender') === 'Male' ? 'selected' : ''); ?>>Male</option>
                        <option value="Female" <?php echo e(request('gender') === 'Female' ? 'selected' : ''); ?>>Female</option>
                        <option value="Other" <?php echo e(request('gender') === 'Other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label small fw-semibold">Status</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">All</option>
                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                        <option value="deceased" <?php echo e(request('status') === 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                        <option value="transferred" <?php echo e(request('status') === 'transferred' ? 'selected' : ''); ?>>Transferred</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="approval_status" class="form-label small fw-semibold">Approval</label>
                    <select class="form-select form-select-sm" id="approval_status" name="approval_status">
                        <option value="">All</option>
                        <option value="draft" <?php echo e(request('approval_status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="pending" <?php echo e(request('approval_status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="approved" <?php echo e(request('approval_status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="rejected" <?php echo e(request('approval_status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-2">
                    <label for="sort" class="form-label small fw-semibold">Sort</label>
                    <select class="form-select form-select-sm" id="sort" name="sort">
                        <option value="">Newest First</option>
                        <option value="az" <?php echo e(request('sort') === 'az' ? 'selected' : ''); ?>>A – Z (Last Name)</option>
                        <option value="za" <?php echo e(request('sort') === 'za' ? 'selected' : ''); ?>>Z – A (Last Name)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="<?php echo e(route('residents.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                    <?php if(request()->hasAny(['search', 'village_id', 'block_number', 'lot_number', 'gender', 'status', 'approval_status', 'sort'])): ?>
                        <span class="text-muted small align-self-center ms-1">
                            <i class="bi bi-info-circle me-1"></i><?php echo e($residents->total()); ?> result(s) found
                        </span>
                    <?php endif; ?>
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
                        <th scope="col">Code</th>
                        <th scope="col">Name</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Age</th>
                        <th scope="col">Village</th>
                        <th scope="col">Household</th>
                        <th scope="col">Status</th>
                        <th scope="col">Approval</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $residents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="fw-semibold"><?php echo e($resident->resident_code); ?></span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo e($resident->profile_picture_url); ?>"
                                         alt="<?php echo e($resident->full_name); ?>"
                                         class="rounded-circle me-2" width="36" height="36">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($resident->full_name); ?></div>
                                        <small class="text-muted"><?php echo e($resident->email ?? '—'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($resident->gender ?? '—'); ?></td>
                            <td><?php echo e($resident->age ?? '—'); ?></td>
                            <td><?php echo e($resident->village?->village_name ?? '—'); ?></td>
                            <td>
                                <?php if($resident->household): ?>
                                    <span class="text-muted small">Blk <?php echo e($resident->household->block_number ?? '—'); ?>, Lot <?php echo e($resident->household->lot_number ?? '—'); ?></span><br>
                                    <span class="fw-semibold"><?php echo e($resident->household?->household_number ?? '—'); ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($resident->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif($resident->status === 'inactive'): ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php elseif($resident->status === 'deceased'): ?>
                                    <span class="badge bg-danger">Deceased</span>
                                <?php elseif($resident->status === 'transferred'): ?>
                                    <span class="badge bg-warning text-dark">Transferred</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(ucfirst($resident->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($resident->approval_status === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif($resident->approval_status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif($resident->approval_status === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('residents.show', $resident)); ?>" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if(auth()->user()->role?->role_slug === 'secretary' && in_array($resident->approval_status, ['draft', 'rejected'])): ?>
                                    <a href="<?php echo e(route('residents.edit', $resident)); ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(auth()->user()->role?->role_slug === 'secretary' && $resident->status === 'active'): ?>
                                    <form action="<?php echo e(route('residents.destroy', $resident)); ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to archive this resident?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No residents found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($residents->hasPages()): ?>
        <div class="card-footer bg-white border-0 pt-3">
            <?php echo e($residents->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php if(isset($archivedResidents) && $archivedResidents->count()): ?>
    <div class="mt-4">
        <a class="btn btn-outline-secondary btn-sm mb-3" data-bs-toggle="collapse" href="#archivedSection" role="button" aria-expanded="false" aria-controls="archivedSection">
            <i class="bi bi-archive me-1"></i>Archived Residents (<?php echo e($archivedResidents->total()); ?>)
        </a>

        <div class="collapse" id="archivedSection">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Village</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $archivedResidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><span class="fw-semibold"><?php echo e($resident->resident_code); ?></span></td>
                                        <td><?php echo e($resident->full_name); ?></td>
                                        <td><?php echo e($resident->village?->village_name ?? '—'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">Archived</span>
                                        </td>
                                        <td class="text-end">
                                            <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
                                                <form action="<?php echo e(route('residents.restore', $resident)); ?>" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Restore this resident?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/residents/index.blade.php ENDPATH**/ ?>