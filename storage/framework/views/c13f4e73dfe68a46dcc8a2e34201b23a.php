<?php $__env->startSection('title', 'Online Payments'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Online Payments</h4>
            <p class="page-subtitle text-muted mb-0">GCash payment requests submitted by residents for approval.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('gcash-config.edit')); ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-phone me-1"></i>GCash Details
            </a>
            <?php if($pendingCount > 0): ?>
            <span class="btn btn-warning btn-sm disabled">
                <i class="bi bi-hourglass-split me-1"></i><?php echo e($pendingCount); ?> Pending
            </span>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-primary"></i>Payment Requests</h6>
            <div class="d-flex flex-wrap gap-2">
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo e(route('online-payments.index')); ?>"
                       class="btn btn-outline-secondary <?php echo e(request('status') ? '' : 'active'); ?>">All</a>
                    <a href="<?php echo e(route('online-payments.index', ['status' => 'pending'])); ?>"
                       class="btn btn-outline-warning text-dark <?php echo e(request('status') === 'pending' ? 'active' : ''); ?>">Pending</a>
                    <a href="<?php echo e(route('online-payments.index', ['status' => 'approved'])); ?>"
                       class="btn btn-outline-success <?php echo e(request('status') === 'approved' ? 'active' : ''); ?>">Approved</a>
                    <a href="<?php echo e(route('online-payments.index', ['status' => 'declined'])); ?>"
                       class="btn btn-outline-danger <?php echo e(request('status') === 'declined' ? 'active' : ''); ?>">Declined</a>
                </div>
                <form method="GET" action="<?php echo e(route('online-payments.index')); ?>" class="d-flex gap-2">
                    <?php if(request('status')): ?>
                        <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search billing, household, txn #..."
                           value="<?php echo e(request('search')); ?>" style="max-width: 240px;">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if($onlinePayments->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Billing #</th>
                        <th>Household</th>
                        <th class="text-end">Amount</th>
                        <th>Transaction #</th>
                        <th>Submitted</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $onlinePayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $onlinePayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <span class="fw-semibold text-primary"><?php echo e($onlinePayment->billing?->billing_number ?? '—'); ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo e($onlinePayment->household?->household_number ?? '—'); ?></div>
                            <small class="text-muted"><?php echo e($onlinePayment->household?->household_head_name ?? ''); ?></small>
                        </td>
                        <td class="text-end fw-semibold">₱<?php echo e(number_format($onlinePayment->amount, 2)); ?></td>
                        <td><span class="text-muted"><?php echo e($onlinePayment->transaction_number); ?></span></td>
                        <td><small class="text-muted"><?php echo e($onlinePayment->created_at->format('M d, Y h:i A')); ?></small></td>
                        <td class="text-center">
                            <span class="badge <?php echo e($onlinePayment->status_badge); ?>"><?php echo e($onlinePayment->status_label); ?></span>
                        </td>
                        <td class="text-end">
                            <a href="<?php echo e(route('online-payments.show', $onlinePayment)); ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?php echo e($onlinePayments->links()); ?>

        </div>
        <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <p class="mb-0">No online payment requests found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/online-payments/index.blade.php ENDPATH**/ ?>