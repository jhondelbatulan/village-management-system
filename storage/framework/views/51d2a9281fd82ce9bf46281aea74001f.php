<?php $__env->startSection('title', 'Household Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Household Management</h1>
        <p class="page-subtitle text-muted">Manage household registrations and records</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('households.export')); ?>" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Export
        </a>
        <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
            <a href="<?php echo e(route('households.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Household
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('households.index')); ?>">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="HH#, Head name..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Village</label>
                    <select name="village_id" class="form-select">
                        <option value="">All Villages</option>
                        <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($village->id); ?>" <?php echo e(request('village_id') == $village->id ? 'selected' : ''); ?>>
                                <?php echo e($village->village_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Block</label>
                    <select name="block_number" class="form-select">
                        <option value="">All</option>
                        <?php $__currentLoopData = $blockNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($block); ?>" <?php echo e(request('block_number') == $block ? 'selected' : ''); ?>><?php echo e($block); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Lot</label>
                    <select name="lot_number" class="form-select">
                        <option value="">All</option>
                        <?php $__currentLoopData = $lotNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($lot); ?>" <?php echo e(request('lot_number') == $lot ? 'selected' : ''); ?>><?php echo e($lot); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                        <option value="relocated" <?php echo e(request('status') == 'relocated' ? 'selected' : ''); ?>>Relocated</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Approval</label>
                    <select name="approval_status" class="form-select">
                        <option value="">All</option>
                        <option value="draft" <?php echo e(request('approval_status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="pending" <?php echo e(request('approval_status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="approved" <?php echo e(request('approval_status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="rejected" <?php echo e(request('approval_status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="">Newest First</option>
                        <option value="az" <?php echo e(request('sort') == 'az' ? 'selected' : ''); ?>>A – Z (Head Name)</option>
                        <option value="za" <?php echo e(request('sort') == 'za' ? 'selected' : ''); ?>>Z – A (Head Name)</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="<?php echo e(route('households.index')); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                    <?php if(request()->hasAny(['search', 'village_id', 'block_number', 'lot_number', 'status', 'approval_status', 'sort'])): ?>
                        <span class="text-muted small align-self-center ms-1">
                            <i class="bi bi-info-circle me-1"></i><?php echo e($households->total()); ?> result(s) found
                        </span>
                    <?php endif; ?>
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
                        <th>Household #</th>
                        <th>Block / Lot</th>
                        <th>Village</th>
                        <th>Head</th>
                        <th>Purok</th>
                        <th class="text-center">Members</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Payment Behavior</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $households; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?php echo e($household->household_number); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">Blk <?php echo e($household->block_number ?? '—'); ?> Lot <?php echo e($household->lot_number ?? '—'); ?></span>
                            </td>
                            <td><?php echo e($household->village?->village_name ?? '—'); ?></td>
                            <td><?php echo e($household->household_head_name ?? '—'); ?></td>
                            <td><?php echo e($household->purok ?? '—'); ?></td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill">
                                    <?php echo e($household->member_count); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($household->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif($household->status === 'inactive'): ?>
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Relocated</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($household->approval_status === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif($household->approval_status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif($household->approval_status === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $pb = $household->payment_behavior;
                                    $pbColor = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$pb] ?? 'secondary';
                                    $pbIcon = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_ICONS[$pb] ?? 'bi-question-circle';
                                    $pbLabel = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$pb] ?? 'N/A';
                                    $pbScore = $household->payment_reliability_score;
                                ?>
                                <?php if($pb && $household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled): ?>
                                <div class="d-flex align-items-center">
                                    <i class="bi <?php echo e($pbIcon); ?> text-<?php echo e($pbColor); ?> me-1"></i>
                                    <div>
                                        <span class="badge bg-<?php echo e($pbColor); ?>"><?php echo e($pbLabel); ?></span>
                                        <div class="small text-muted">Score: <?php echo e($pbScore); ?>/100</div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('households.show', $household)); ?>" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
                                        <?php if($household->approval_status === 'draft' || $household->approval_status === 'rejected'): ?>
                                            <a href="<?php echo e(route('households.edit', $household)); ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($household->approval_status === 'active'): ?>
                                            <form action="<?php echo e(route('households.destroy', $household)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Archive this household?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-house-door fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No households found.</p>
                                <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
                                    <a href="<?php echo e(route('households.create')); ?>" class="btn btn-primary btn-sm mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Register First Household
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($households->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($households->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php if(isset($archivedHouseholds) && $archivedHouseholds->count() > 0): ?>
    <div class="card shadow-sm mt-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-archive me-2"></i> Archived Households (<?php echo e($archivedHouseholds->count()); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Household #</th>
                            <th>Village</th>
                            <th>Head</th>
                            <th>Purok</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $archivedHouseholds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $archived): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="table-light">
                                <td class="fw-semibold"><?php echo e($archived->household_number); ?></td>
                                <td><?php echo e($archived->village?->village_name ?? '—'); ?></td>
                                <td><?php echo e($archived->household_head_name ?? '—'); ?></td>
                                <td><?php echo e($archived->purok ?? '—'); ?></td>
                                <td>
                                    <span class="badge bg-secondary">Archived</span>
                                </td>
                                <td class="text-end">
                                    <?php if(auth()->user()->role?->role_slug === 'secretary'): ?>
                                        <form action="<?php echo e(route('households.restore', $archived)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
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
        <?php if($archivedHouseholds->hasPages()): ?>
            <div class="card-footer bg-white">
                <?php echo e($archivedHouseholds->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/households/index.blade.php ENDPATH**/ ?>