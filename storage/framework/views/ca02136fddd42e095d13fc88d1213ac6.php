<?php $__env->startSection('title', 'Payment Config - ' . $village->village_name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1"><?php echo e($village->village_name); ?> — Payment Configuration</h4>
            <p class="page-subtitle text-muted mb-0">Village payment settings and payment types.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('payment-config.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <?php if(auth()->user()->role?->role_slug === 'treasurer'): ?>
            <a href="<?php echo e(route('payment-config.edit', $village)); ?>" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
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

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-gear me-2 text-primary"></i>Payment Settings
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if($setting): ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Module</label>
                        <p class="mb-0">
                            <?php if($setting->payment_enabled): ?>
                                <span class="badge bg-success fs-6">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">Disabled</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Payment Manager</label>
                        <p class="fw-semibold mb-0"><?php echo e(ucfirst($setting->payment_manager_role ?? 'None')); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Grace Period</label>
                        <p class="fw-semibold mb-0"><?php echo e($setting->grace_period_days); ?> days</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Receipt Prefix</label>
                        <p class="fw-semibold mb-0"><?php echo e($setting->receipt_prefix); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Billing Prefix</label>
                        <p class="fw-semibold mb-0"><?php echo e($setting->billing_prefix); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Permit Prefix</label>
                        <p class="fw-semibold mb-0"><?php echo e($setting->permit_prefix); ?></p>
                    </div>
                    <?php if($setting->monthly_due_day): ?>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Monthly Due Day</label>
                        <p class="fw-semibold mb-0">Day <?php echo e($setting->monthly_due_day); ?> of each month</p>
                    </div>
                    <?php endif; ?>
                    <?php if($setting->annual_due_month): ?>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Annual Due Month</label>
                        <p class="fw-semibold mb-0"><?php echo e(Carbon\Carbon::create()->month($setting->annual_due_month)->format('F')); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($setting->annual_due_day): ?>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Annual Due Day</label>
                        <p class="fw-semibold mb-0">Day <?php echo e($setting->annual_due_day); ?> of the due month</p>
                    </div>
                    <?php endif; ?>
                    <?php if($setting->payment_notes): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">Payment Notes</label>
                        <p class="mb-0"><?php echo e($setting->payment_notes); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-gear fs-1 d-block mb-2"></i>
                    <p>No payment configuration set for this village.</p>
                    <?php if(auth()->user()->role?->role_slug === 'treasurer'): ?>
                    <a href="<?php echo e(route('payment-config.edit', $village)); ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i>Configure Now
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-primary"></i>Payment Types (<?php echo e($paymentTypes->count()); ?>)
                </h6>
                <div class="d-flex gap-2">
                    <?php if(auth()->user()->role?->role_slug === 'treasurer'): ?>
                    <a href="<?php echo e(route('household-amounts.index', $village)); ?>" class="btn btn-outline-success btn-sm" title="Manage per-household amounts">
                        <i class="bi bi-house-door me-1"></i>Household Amounts
                    </a>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentTypeModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Type
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if($paymentTypes->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Frequency</th>
                                <th class="text-end">Default Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $paymentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($type->payment_name); ?></div>
                                        <?php if($type->description): ?>
                                            <small class="text-muted"><?php echo e(Str::limit($type->description, 50)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?php echo e(ucfirst($type->frequency)); ?></span></td>
                                    <td class="text-end">₱<?php echo e(number_format($type->default_amount, 2)); ?></td>
                                    <td class="text-center">
                                        <?php if($type->is_active): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <p>No payment types configured.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('payment-config.store-type')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="village_id" value="<?php echo e($village->id); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Payment Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payment_name" name="payment_name" required
                               placeholder="e.g. Monthly House Rental">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="frequency" class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="frequency" name="frequency" required>
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                                <option value="one_time">One-Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="default_amount" class="form-label fw-semibold">Default Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="default_amount"
                                       name="default_amount" value="0.00" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/config/show.blade.php ENDPATH**/ ?>