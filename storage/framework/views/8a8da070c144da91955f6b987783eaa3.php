<?php $__env->startSection('title', 'Household Payment Amounts - ' . $village->village_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Household Payment Amounts</h4>
            <p class="page-subtitle text-muted mb-0">
                <?php echo e($village->village_name); ?> — Customize billing amounts per household
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('payment-config.show', $village)); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Config
            </a>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Set custom billing amounts for individual households. Leave a field at <strong>0</strong> or blank to use the default amount from payment configuration.
    <br><small class="text-muted">Custom amounts apply to the next billing generation. Existing billings are not affected.</small>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Head Name</th>
                        <th>Block / Lot</th>
                        <?php $__currentLoopData = $paymentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-end"><?php echo e($type->payment_name); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $households; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary"><?php echo e($household->household_number); ?></span>
                            </td>
                            <td><?php echo e($household->household_head_name); ?></td>
                            <td>
                                <span class="text-muted">Block <?php echo e($household->block_number ?? '—'); ?> Lot <?php echo e($household->lot_number ?? '—'); ?></span>
                            </td>
                            <?php $__currentLoopData = $paymentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $customAmount = $household->paymentAmounts->firstWhere('payment_type_id', $type->id);
                                ?>
                                <td class="text-end">
                                    <?php if($customAmount): ?>
                                        <span class="fw-semibold text-success">₱<?php echo e(number_format($customAmount->custom_amount, 2)); ?></span>
                                        <br><small class="text-muted">Default: ₱<?php echo e(number_format($type->default_amount, 2)); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">₱<?php echo e(number_format($type->default_amount, 2)); ?></span>
                                        <br><small class="text-muted">Using default</small>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-end">
                                <a href="<?php echo e(route('household-amounts.edit', [$village, $household])); ?>"
                                   class="btn btn-sm btn-outline-warning" title="Edit Amounts">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(4 + $paymentTypes->count()); ?>" class="text-center py-4">
                                <i class="bi bi-house-door fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No approved active households found in this village.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/household-amounts/index.blade.php ENDPATH**/ ?>