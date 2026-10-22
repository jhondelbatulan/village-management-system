<?php $__env->startSection('title', 'Household Approvals'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Household Approvals</h1>
        <p class="page-subtitle text-muted">
            <?php if($role === 'admin' || $role === 'secretary'): ?>
                Review and approve household registrations in your village.
            <?php else: ?>
                Manage household approval status across all villages.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <?php if($role === 'admin' || $role === 'secretary'): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 <?php echo e(request('status') === 'pending' ? 'border-primary border-2' : ''); ?>">
            <a href="<?php echo e(route('approvals.households', ['status' => 'pending'])); ?>" class="text-decoration-none">
                <div class="fs-3 fw-bold text-warning"><?php echo e($counts['pendingCount']); ?></div>
                <div class="text-muted small">Pending Review</div>
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 <?php echo e(request('status') === 'admin_approved' ? 'border-primary border-2' : ''); ?>">
            <a href="<?php echo e(route('approvals.households', ['status' => 'admin_approved'])); ?>" class="text-decoration-none">
                <div class="fs-3 fw-bold text-info"><?php echo e($counts['adminApprovedCount']); ?></div>
                <div class="text-muted small">Awaiting Final Approval</div>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 <?php echo e(request('status') === 'approved' ? 'border-primary border-2' : ''); ?>">
            <a href="<?php echo e(route('approvals.households', ['status' => 'approved'])); ?>" class="text-decoration-none">
                <div class="fs-3 fw-bold text-success"><?php echo e($counts['approvedCount']); ?></div>
                <div class="text-muted small">Approved</div>
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 <?php echo e(request('status') === 'rejected' ? 'border-primary border-2' : ''); ?>">
            <a href="<?php echo e(route('approvals.households', ['status' => 'rejected'])); ?>" class="text-decoration-none">
                <div class="fs-3 fw-bold text-danger"><?php echo e($counts['rejectedCount']); ?></div>
                <div class="text-muted small">Rejected</div>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-list-check me-2 text-primary"></i>
            <?php if(request('status')): ?>
                <?php echo e(ucfirst(str_replace('_', ' ', request('status')))); ?> Records
            <?php else: ?>
                All Approval Records
            <?php endif; ?>
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <?php if(request('status')): ?>
                <a href="<?php echo e(route('approvals.households', array_filter(['search' => request('search'), 'village_id' => request('village_id')]))); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Clear Filter
                </a>
            <?php endif; ?>
            <?php if($role === 'super_admin'): ?>
            <form action="<?php echo e(route('approvals.households')); ?>" method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                <select name="village_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Villages</option>
                    <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($village->id); ?>" <?php echo e(request('village_id') == $village->id ? 'selected' : ''); ?>>
                            <?php echo e($village->village_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
            <?php endif; ?>
            <form action="<?php echo e(route('approvals.households')); ?>" method="GET" class="d-flex gap-1">
                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                <input type="hidden" name="village_id" value="<?php echo e(request('village_id')); ?>">
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search households..." value="<?php echo e(request('search')); ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Block / Lot</th>
                        <th>Head</th>
                        <th>Village</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $households; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('households.show', $household)); ?>" class="fw-semibold text-decoration-none">
                                    <?php echo e($household->household_number); ?>

                                </a>
                            </td>
                            <td>B<?php echo e($household->block_number ?? '—'); ?> / L<?php echo e($household->lot_number ?? '—'); ?></td>
                            <td><?php echo e($household->household_head_name ?? '—'); ?></td>
                            <td><?php echo e($household->village?->village_name ?? '—'); ?></td>
                            <td><?php echo e($household->submittedBy?->full_name ?? '—'); ?></td>
                            <td>
                                <?php if($household->approval_status === 'approved'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                <?php elseif($household->approval_status === 'admin_approved'): ?>
                                    <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>Admin Approved</span>
                                <?php elseif($household->approval_status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php elseif($household->approval_status === 'rejected'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="<?php echo e(route('households.show', $household)); ?>" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(($role === 'admin' && $household->approval_status === 'pending') ||
                                        ($role === 'super_admin' && $household->approval_status === 'admin_approved')): ?>
                                        <button type="button" class="btn btn-success btn-sm" title="Approve"
                                            data-bs-toggle="modal" data-bs-target="#approveHousehold<?php echo e($household->id); ?>">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" title="Reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectHousehold<?php echo e($household->id); ?>">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check-circle fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">
                                    <?php if(request('status')): ?>
                                        No <?php echo e(str_replace('_', ' ', request('status'))); ?> records found.
                                    <?php else: ?>
                                        No approval records found.
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($households->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($households->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__currentLoopData = $households; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(($role === 'admin' && $household->approval_status === 'pending') ||
        ($role === 'super_admin' && $household->approval_status === 'admin_approved')): ?>

    <div class="modal fade" id="approveHousehold<?php echo e($household->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('approvals.approve-household', $household)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Household</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Approve household <strong><?php echo e($household->household_number); ?></strong>?</p>
                        <p class="text-muted small">Block <?php echo e($household->block_number); ?>, Lot <?php echo e($household->lot_number); ?> — <?php echo e($household->village?->village_name); ?></p>
                        <?php if($role === 'admin'): ?>
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>This will mark the household as "Admin Approved". Super Admin will need to finalize the approval.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success small mb-0">
                                <i class="bi bi-info-circle me-1"></i>This is the final approval. The household will be officially registered.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectHousehold<?php echo e($household->id); ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('approvals.reject-household', $household)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Household</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Reject household <strong><?php echo e($household->household_number); ?></strong>?</p>
                        <p class="text-muted small">Block <?php echo e($household->block_number); ?>, Lot <?php echo e($household->lot_number); ?> — <?php echo e($household->village?->village_name); ?></p>
                        <div class="mb-3">
                            <label for="remarks_<?php echo e($household->id); ?>" class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="remarks_<?php echo e($household->id); ?>" name="remarks" rows="3" required
                                placeholder="Enter the reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/approvals/households.blade.php ENDPATH**/ ?>