<?php $__env->startSection('title', 'Billing - ' . $billing->billing_number); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Billing <?php echo e($billing->billing_number); ?></h4>
            <p class="page-subtitle text-muted mb-0">
                <?php echo e($billing->village?->village_name ?? '—'); ?> | <?php echo e($billing->billing_period); ?>

                <span class="badge <?php echo e($billing->status_badge); ?> ms-2"><?php echo e($billing->status_label); ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('billings.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <?php if($canPayOnline): ?>
            <a href="<?php echo e(route('billings.pay-online', $billing)); ?>" class="btn btn-success btn-sm">
                <i class="bi bi-phone me-1"></i>Pay Online (GCash)
            </a>
            <?php endif; ?>
            <?php if($canRecordPayment && in_array($billing->status, ['pending', 'overdue', 'partially_paid'])): ?>
            <a href="<?php echo e(route('payments.create')); ?>?billing_id=<?php echo e($billing->id); ?><?php if($activeOnlinePayment && $activeOnlinePayment->status === 'approved'): ?>&online_payment=<?php echo e($activeOnlinePayment->id); ?><?php endif; ?>"
               class="btn btn-success btn-sm">
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

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($activeOnlinePayment): ?>
    <div class="alert alert-info border-start border-4 border-info" role="alert">
        <?php if($activeOnlinePayment->status === 'pending'): ?>
            <i class="bi bi-hourglass-split me-2"></i>
            An online GCash payment request of <strong>₱<?php echo e(number_format($activeOnlinePayment->amount, 2)); ?></strong>
            (txn #<?php echo e($activeOnlinePayment->transaction_number); ?>) submitted on
            <?php echo e($activeOnlinePayment->created_at->format('M d, Y')); ?> is <strong>awaiting treasurer approval</strong>.
        <?php else: ?>
            <i class="bi bi-check-circle me-2"></i>
            An approved online GCash payment of <strong>₱<?php echo e(number_format($activeOnlinePayment->amount, 2)); ?></strong>
            is <strong>awaiting recording</strong> by the treasurer.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Billing Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Number</label>
                        <div class="fw-semibold"><?php echo e($billing->billing_number); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Household</label>
                        <div class="fw-semibold"><?php echo e($billing->household?->household_number ?? '—'); ?></div>
                        <small class="text-muted"><?php echo e($billing->household?->household_head_name ?? ''); ?></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Village</label>
                        <div class="fw-semibold"><?php echo e($billing->village?->village_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Type</label>
                        <div class="fw-semibold"><?php echo e($billing->paymentType?->payment_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Billing Period</label>
                        <div class="fw-semibold"><?php echo e($billing->billing_period); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Due Date</label>
                        <div class="fw-semibold"><?php echo e($billing->due_date->format('M d, Y')); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created By</label>
                        <div class="fw-semibold"><?php echo e($billing->creator?->full_name ?? 'System'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created Date</label>
                        <div class="fw-semibold"><?php echo e($billing->created_at->format('M d, Y h:i A')); ?></div>
                    </div>
                    <?php if($billing->remarks): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">Remarks</label>
                        <div><?php echo e($billing->remarks); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-primary"></i>Payment Records</h6>
            </div>
            <div class="card-body p-0">
                <?php if($billing->payments->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction #</th>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total Paid</th>
                                <th>Status</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $billing->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <span class="text-muted">TXN-<?php echo e(str_pad($payment->id, 6, '0', STR_PAD_LEFT)); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('payments.show', $payment)); ?>" class="text-primary fw-semibold">
                                        <?php echo e($payment->receipt_number); ?>

                                    </a>
                                </td>
                                <td><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                                <td><?php echo e($payment->payment_method_label); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($payment->discount, 2)); ?></td>
                                <td class="text-end fw-semibold">₱<?php echo e(number_format($payment->total_paid, 2)); ?></td>
                                <td><span class="badge <?php echo e($payment->status_badge); ?>"><?php echo e($payment->status_label); ?></span></td>
                                <td><?php echo e($payment->receivedByUser?->full_name ?? '—'); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <p class="mb-0">No payments recorded yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2 text-primary"></i>Amount Summary</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Base Amount</span>
                    <span class="fw-semibold">₱<?php echo e(number_format($billing->amount, 2)); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Discount</span>
                    <span class="fw-semibold text-success">-₱<?php echo e(number_format($billing->discount, 2)); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Due</span>
                    <span class="fw-bold fs-5">₱<?php echo e(number_format($billing->total_amount, 2)); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Paid</span>
                    <span class="fw-semibold text-success">₱<?php echo e(number_format($totalPaid, 2)); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted fw-semibold">Balance</span>
                    <span class="fw-bold fs-5 <?php echo e($balance > 0 ? 'text-danger' : 'text-success'); ?>">
                        ₱<?php echo e(number_format($balance, 2)); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/billings/show.blade.php ENDPATH**/ ?>