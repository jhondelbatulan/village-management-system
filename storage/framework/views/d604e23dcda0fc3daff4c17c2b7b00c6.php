<?php $__env->startSection('title', 'Village Payment Configuration'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Village Payment Configuration</h4>
            <p class="page-subtitle text-muted mb-0">Configure payment settings for each village.</p>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-center">Payment Module</th>
                        <th>Manager</th>
                        <th class="text-center">Grace Period</th>
                        <th>Billing Prefix</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $setting = $settings->firstWhere('village_id', $village->id);
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo e($village->village_name); ?></div>
                                <small class="text-muted"><?php echo e($village->village_code); ?></small>
                            </td>
                            <td class="text-center">
                                <?php if($setting && $setting->payment_enabled): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(ucfirst($setting->payment_manager_role ?? '—')); ?></td>
                            <td class="text-center"><?php echo e($setting->grace_period_days ?? 0); ?> days</td>
                            <td><?php echo e($setting->billing_prefix ?? 'BILL'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('payment-config.show', $village)); ?>" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if(auth()->user()->role?->role_slug === 'treasurer'): ?>
                                <a href="<?php echo e(route('payment-config.edit', $village)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo e(route('household-amounts.index', $village)); ?>" class="btn btn-sm btn-outline-success" title="Household Amounts">
                                    <i class="bi bi-house-door"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/config/index.blade.php ENDPATH**/ ?>