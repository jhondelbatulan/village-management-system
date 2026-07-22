<?php $__env->startSection('title', 'Payment Records'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Payment Records</h4>
            <p class="page-subtitle text-muted mb-0">View all payment transactions.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if($canRecordPayment): ?>
            <a href="<?php echo e(route('payments.create')); ?>" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Record Payment
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('payments.index')); ?>">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Search receipt, billing, household..."
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
                    <select class="form-select form-select-sm" name="payment_status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('payment_status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="paid" <?php echo e(request('payment_status') === 'paid' ? 'selected' : ''); ?>>Paid</option>
                        <option value="partially_paid" <?php echo e(request('payment_status') === 'partially_paid' ? 'selected' : ''); ?>>Partially Paid</option>
                        <option value="overdue" <?php echo e(request('payment_status') === 'overdue' ? 'selected' : ''); ?>>Overdue</option>
                        <option value="cancelled" <?php echo e(request('payment_status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="month">
                        <option value="">Month</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                                <?php echo e(Carbon\Carbon::create()->month($m)->format('M')); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="year">
                        <option value="">Year</option>
                        <?php for($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select form-select-sm" name="payment_method">
                        <option value="">Method</option>
                        <option value="cash" <?php echo e(request('payment_method') === 'cash' ? 'selected' : ''); ?>>Cash</option>
                        <option value="gcash" <?php echo e(request('payment_method') === 'gcash' ? 'selected' : ''); ?>>GCash</option>
                        <option value="maya" <?php echo e(request('payment_method') === 'maya' ? 'selected' : ''); ?>>Maya</option>
                        <option value="bank_transfer" <?php echo e(request('payment_method') === 'bank_transfer' ? 'selected' : ''); ?>>Bank</option>
                    </select>
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
                        <th>Receipt #</th>
                        <th>Billing #</th>
                        <th>Household</th>
                        <th>Village</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th class="text-end">Total Paid</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary"><?php echo e($payment->receipt_number); ?></span>
                            </td>
                            <td>
                                <span class="text-muted"><?php echo e($payment->billing?->billing_number ?? '—'); ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo e($payment->household?->household_number ?? '—'); ?></div>
                                <small class="text-muted"><?php echo e($payment->household?->household_head_name ?? ''); ?></small>
                            </td>
                            <td><?php echo e($payment->village?->village_name ?? '—'); ?></td>
                            <td><?php echo e($payment->paymentType?->payment_name ?? '—'); ?></td>
                            <td><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                            <td class="text-end fw-semibold">₱<?php echo e(number_format($payment->total_paid, 2)); ?></td>
                            <td><?php echo e($payment->payment_method_label); ?></td>
                            <td class="text-center">
                                <span class="badge <?php echo e($payment->status_badge); ?>"><?php echo e($payment->status_label); ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('payments.show', $payment)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('payments.receipt', $payment)); ?>" class="btn btn-sm btn-outline-success" title="Receipt" target="_blank">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No payment records found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($payments->hasPages()): ?>
    <div class="card-footer bg-white border-0">
        <?php echo e($payments->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/index.blade.php ENDPATH**/ ?>