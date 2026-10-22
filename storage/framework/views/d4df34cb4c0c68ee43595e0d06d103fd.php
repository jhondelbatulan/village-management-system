<?php $__env->startSection('title', 'Pay Online - ' . $billing->billing_number); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Pay Online via GCash</h4>
            <p class="page-subtitle text-muted mb-0">
                <?php echo e($billing->billing_number); ?> | <?php echo e($billing->paymentType?->payment_name ?? '—'); ?> | <?php echo e($billing->billing_period); ?>

            </p>
        </div>
        <a href="<?php echo e(route('billings.show', $billing)); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Billing
        </a>
    </div>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-phone me-2 text-success"></i>GCash Payment Guide</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="bg-light rounded p-3 text-center mb-3">
                    <div class="small text-muted mb-1">Send your GCash payment to</div>
                    <div class="fs-4 fw-bold text-success"><?php echo e($gcashConfig->gcash_number); ?></div>
                    <div class="fw-semibold"><?php echo e($gcashConfig->gcash_name); ?></div>
                </div>
                <ol class="mb-0 ps-3 small">
                    <li class="mb-2">Open your GCash app and send the payment amount to the number above.</li>
                    <li class="mb-2">Take a <strong>screenshot</strong> of your successful transaction.</li>
                    <li class="mb-2">Fill in the amount you paid and the GCash transaction number below.</li>
                    <li class="mb-2">Attach the screenshot and submit — your treasurer will review and approve it.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Bill Details</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Billing Number</label>
                        <div class="fw-semibold"><?php echo e($billing->billing_number); ?></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Payment Type</label>
                        <div class="fw-semibold"><?php echo e($billing->paymentType?->payment_name ?? '—'); ?></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Billing Period</label>
                        <div class="fw-semibold"><?php echo e($billing->billing_period); ?></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Due Date</label>
                        <div class="fw-semibold"><?php echo e($billing->due_date->format('M d, Y')); ?></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small">Total Amount</label>
                        <div class="fw-semibold">₱<?php echo e(number_format($billing->total_amount, 2)); ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Remaining Balance</label>
                        <div class="fw-bold text-danger fs-5">₱<?php echo e(number_format($balance, 2)); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cloud-upload me-2 text-primary"></i>Submit Your Payment</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="<?php echo e(route('billings.pay-online.store', $billing)); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Amount You Paid <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0.01" max="<?php echo e($balance); ?>"
                                   class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="amount" name="amount"
                                   value="<?php echo e(old('amount', number_format($balance, 2, '.', ''))); ?>"
                                   required placeholder="Enter the amount you sent via GCash">
                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <small class="text-muted">Maximum payable balance: ₱<?php echo e(number_format($balance, 2)); ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_number" class="form-label fw-semibold">GCash Transaction Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['transaction_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="transaction_number" name="transaction_number"
                               value="<?php echo e(old('transaction_number')); ?>"
                               maxlength="100" required placeholder="e.g. 123456789012">
                        <?php $__errorArgs = ['transaction_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Found on your GCash confirmation/receipt.</small>
                    </div>

                    <div class="mb-4">
                        <label for="proof" class="form-label fw-semibold">Payment Screenshot <span class="text-danger">*</span></label>
                        <input type="file" class="form-control <?php $__errorArgs = ['proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="proof" name="proof" accept="image/*" required>
                        <?php $__errorArgs = ['proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
                        <small class="text-muted">Screenshot of your GCash transaction as proof of payment. JPG, PNG, or WebP. Max 2MB.</small>
                        <div class="mt-2" id="proofPreview" style="display:none;">
                            <img id="proofPreviewImg" src="" alt="Proof preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>

                    <div class="alert alert-light border mb-4">
                        <small class="text-muted">
                            <i class="bi bi-hourglass-split me-1"></i>Your submission will be sent to the treasurer for review.
                            You will be notified once it is approved or declined.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send me-1"></i>Submit for Approval
                        </button>
                        <a href="<?php echo e(route('billings.show', $billing)); ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const proof = document.getElementById('proof');
    const preview = document.getElementById('proofPreview');
    const previewImg = document.getElementById('proofPreviewImg');

    proof.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            previewImg.src = URL.createObjectURL(this.files[0]);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/billings/pay-online.blade.php ENDPATH**/ ?>