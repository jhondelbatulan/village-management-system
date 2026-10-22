<?php $__env->startSection('title', 'Household Payments - ' . $household->household_number); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Payment History</h4>
            <p class="page-subtitle text-muted mb-0">
                <?php echo e($household->household_number); ?> | <?php echo e($household->village?->village_name ?? '—'); ?>

            </p>
        </div>
        <a href="<?php echo e(route('households.show', $household)); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Household
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack fs-2 text-success d-block mb-2"></i>
                <h4 class="mb-1">₱<?php echo e(number_format($balance['paid'], 2)); ?></h4>
                <small class="text-muted">Total Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-circle fs-2 text-danger d-block mb-2"></i>
                <h4 class="mb-1">₱<?php echo e(number_format($balance['outstanding'], 2)); ?></h4>
                <small class="text-muted">Outstanding Balance</small>
            </div>
        </div>
    </div>
</div>

<?php if($gcashConfig && ($canPayOnline ?? false)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-success"></i>GCash Payment Guide</h6>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="bg-light rounded p-3 text-center">
            <div class="small text-muted mb-1">Send your GCash payment to</div>
            <div class="fs-5 fw-bold text-success"><?php echo e($gcashConfig->gcash_number); ?></div>
            <div class="fw-semibold"><?php echo e($gcashConfig->gcash_name); ?></div>
        </div>
        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>Use <strong>Pay Online</strong> on any unpaid bill below to submit your payment with a screenshot for approval.
        </small>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Billings</h6>
    </div>
    <div class="card-body p-0">
        <?php if($billings->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Billing #</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $billings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $rowBalance = (float) $billing->total_amount - (float) $billing->payments->sum('total_paid');
                        $hasActiveRequest = in_array($billing->id, $activeRequestBillingIds ?? []);
                        $payableStatus = in_array($billing->status, ['pending', 'overdue', 'partially_paid']);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo e(route('billings.show', $billing)); ?>" class="text-primary fw-semibold">
                                <?php echo e($billing->billing_number); ?>

                            </a>
                        </td>
                        <td><?php echo e($billing->paymentType?->payment_name ?? '—'); ?></td>
                        <td><?php echo e($billing->billing_period); ?></td>
                        <td><?php echo e($billing->due_date->format('M d, Y')); ?></td>
                        <td class="text-end">₱<?php echo e(number_format($billing->amount, 2)); ?></td>
                        <td class="text-end fw-semibold">₱<?php echo e(number_format($billing->total_amount, 2)); ?></td>
                        <td class="text-center">
                            <span class="badge <?php echo e($billing->status_badge); ?>"><?php echo e($billing->status_label); ?></span>
                        </td>
                        <td class="text-end">
                            <?php if($hasActiveRequest): ?>
                                <span class="badge bg-warning text-dark">Awaiting Approval</span>
                            <?php elseif(($canPayOnline ?? false) && $payableStatus && $rowBalance > 0.009): ?>
                                <a href="<?php echo e(route('billings.pay-online', $billing)); ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-phone me-1"></i>Pay Online
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
            <p class="mb-0">No billings found for this household.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Payment History</h6>
    </div>
    <div class="card-body p-0">
        <?php if($payments->count() > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction #</th>
                        <th>Receipt #</th>
                        <th>Billing #</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-center">Status</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <span class="text-muted">TXN-<?php echo e(str_pad($payment->id, 6, '0', STR_PAD_LEFT)); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo e(route('payments.receipt', $payment)); ?>" class="text-primary fw-semibold" target="_blank">
                                <?php echo e($payment->receipt_number); ?>

                            </a>
                        </td>
                        <td><?php echo e($payment->billing?->billing_number ?? '—'); ?></td>
                        <td><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                        <td><?php echo e($payment->payment_method_label); ?></td>
                        <td class="text-end fw-semibold">₱<?php echo e(number_format($payment->total_paid, 2)); ?></td>
                        <td class="text-center">
                            <span class="badge <?php echo e($payment->status_badge); ?>"><?php echo e($payment->status_label); ?></span>
                        </td>
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

<?php if(($onlinePayments ?? collect())->count() > 0): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-warning"></i>Online Payment Requests</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Billing #</th>
                        <th class="text-end">Amount</th>
                        <th>Transaction #</th>
                        <th>Submitted</th>
                        <th class="text-center">Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $onlinePayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $onlinePayment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <a href="<?php echo e(route('billings.show', $onlinePayment->billing_id)); ?>" class="text-primary fw-semibold">
                                <?php echo e($onlinePayment->billing?->billing_number ?? '—'); ?>

                            </a>
                        </td>
                        <td class="text-end fw-semibold">₱<?php echo e(number_format($onlinePayment->amount, 2)); ?></td>
                        <td><span class="text-muted"><?php echo e($onlinePayment->transaction_number); ?></span></td>
                        <td><small class="text-muted"><?php echo e($onlinePayment->created_at->format('M d, Y h:i A')); ?></small></td>
                        <td class="text-center">
                            <span class="badge <?php echo e($onlinePayment->status_badge); ?>"><?php echo e($onlinePayment->status_label); ?></span>
                        </td>
                        <td>
                            <?php if($onlinePayment->status === 'declined' && $onlinePayment->declined_reason): ?>
                                <small class="text-danger"><?php echo e($onlinePayment->declined_reason); ?></small>
                            <?php elseif($onlinePayment->status === 'approved' && $onlinePayment->payment_id): ?>
                                <a href="<?php echo e(route('payments.receipt', $onlinePayment->payment_id)); ?>" class="small text-primary" target="_blank">
                                    View Receipt
                                </a>
                            <?php elseif($onlinePayment->status === 'approved'): ?>
                                <small class="text-success">Being recorded</small>
                            <?php else: ?>
                                <small class="text-muted">Awaiting treasurer approval</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/household.blade.php ENDPATH**/ ?>