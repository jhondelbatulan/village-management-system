<?php $__env->startSection('title', 'Household Billings'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Household Billings</h4>
            <p class="page-subtitle text-muted mb-0">Manage billing records for all households.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if($canGenerate): ?>
            <a href="<?php echo e(route('billings.generate')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-lightning me-1"></i>Generate Billings
            </a>
            <?php endif; ?>
        </div>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('billings.index')); ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Search billing #, household #, head name..."
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="village_id">
                        <option value="">All Villages</option>
                        <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($village->id); ?>" <?php echo e(request('village_id') == $village->id ? 'selected' : ''); ?>>
                                <?php echo e($village->village_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="payment_type_id">
                        <option value="">All Types</option>
                        <?php $__currentLoopData = $paymentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->id); ?>" <?php echo e(request('payment_type_id') == $type->id ? 'selected' : ''); ?>>
                                <?php echo e($type->payment_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>>Paid</option>
                        <option value="partially_paid" <?php echo e(request('status') === 'partially_paid' ? 'selected' : ''); ?>>Partially Paid</option>
                        <option value="overdue" <?php echo e(request('status') === 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                        <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" class="form-control form-control-sm" name="billing_period"
                           value="<?php echo e(request('billing_period')); ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-search"></i>
                    </button>
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
                        <th>Billing #</th>
                        <th>Household</th>
                        <th>Village</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $billings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary"><?php echo e($billing->billing_number); ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo e($billing->household?->household_number ?? '—'); ?></div>
                                <small class="text-muted"><?php echo e($billing->household?->household_head_name ?? ''); ?></small>
                            </td>
                            <td><?php echo e($billing->village?->village_name ?? '—'); ?></td>
                            <td><?php echo e($billing->paymentType?->payment_name ?? '—'); ?></td>
                            <td><?php echo e($billing->billing_period); ?></td>
                            <td><?php echo e($billing->due_date->format('M d, Y')); ?></td>
                            <td class="text-end">₱<?php echo e(number_format($billing->total_amount, 2)); ?></td>
                            <td class="text-center">
                                <span class="badge <?php echo e($billing->status_badge); ?>"><?php echo e($billing->status_label); ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('billings.show', $billing)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No billing records found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($billings->hasPages()): ?>
    <div class="card-footer bg-white border-0">
        <?php echo e($billings->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/billings/index.blade.php ENDPATH**/ ?>