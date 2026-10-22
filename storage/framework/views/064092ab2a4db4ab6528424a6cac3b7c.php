<?php $__env->startSection('title', 'Record Payment'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Record Payment</h4>
            <p class="page-subtitle text-muted mb-0">Record a payment against an existing billing.</p>
        </div>
        <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<?php if(!empty($onlinePayment)): ?>
    <div class="alert alert-success border-start border-4 border-success" role="alert">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-check-circle"></i>
            <strong>Recording approved online payment</strong>
            <span class="badge bg-success">Approved</span>
        </div>
        <small>
            <?php echo e($onlinePayment->household?->household_number ?? '—'); ?> —
            <?php echo e($onlinePayment->household?->household_head_name ?? ''); ?> |
            Bill <strong><?php echo e($onlinePayment->billing?->billing_number ?? '—'); ?></strong> |
            Amount <strong>₱<?php echo e(number_format($onlinePayment->amount, 2)); ?></strong> |
            GCash Txn #<strong><?php echo e($onlinePayment->transaction_number); ?></strong>
        </small>
    </div>
<?php endif; ?>

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

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="<?php echo e(route('payments.store')); ?>" method="POST" novalidate id="paymentForm">
                    <?php echo csrf_field(); ?>
                    <?php if(!empty($onlinePayment)): ?>
                        <input type="hidden" name="online_payment_id" value="<?php echo e($onlinePayment->id); ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="billing_id" class="form-label fw-semibold">Select Billing <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 mb-2">
                            <select class="form-select form-select-sm" id="sortSelect" style="max-width: 220px;">
                                <option value="default">Default Order</option>
                                <option value="name-asc">Household Name (A-Z)</option>
                                <option value="name-desc">Household Name (Z-A)</option>
                                <option value="block-asc">Block Number (Low-High)</option>
                                <option value="block-desc">Block Number (High-Low)</option>
                                <option value="due-asc">Due Date (Earliest)</option>
                                <option value="due-desc">Due Date (Latest)</option>
                                <option value="amount-desc">Balance (Highest)</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="billingSearch"
                                   placeholder="Search by name, block, billing #..." style="max-width: 300px;">
                        </div>
                        <select class="form-select" id="billing_id" name="billing_id" required>
                            <option value="">-- Select an unpaid/partially paid billing --</option>
                            <?php $__currentLoopData = $billings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $totalPaid = (float) $billing->payments->sum('total_paid');
                                    $balance = (float) $billing->total_amount - $totalPaid;
                                ?>
                                <option value="<?php echo e($billing->id); ?>"
                                    data-amount="<?php echo e($billing->total_amount); ?>"
                                    data-household="<?php echo e($billing->household?->household_number ?? ''); ?>"
                                    data-head="<?php echo e($billing->household?->household_head_name ?? ''); ?>"
                                    data-type="<?php echo e($billing->paymentType?->payment_name ?? ''); ?>"
                                    data-period="<?php echo e($billing->billing_period); ?>"
                                    data-due="<?php echo e($billing->due_date->format('M d, Y')); ?>"
                                    data-due-sort="<?php echo e($billing->due_date->format('Y-m-d')); ?>"
                                    data-village="<?php echo e($billing->village?->village_name ?? ''); ?>"
                                    data-balance="<?php echo e(number_format($balance, 2, '.', '')); ?>"
                                    data-balance-sort="<?php echo e($balance); ?>"
                                    data-block="<?php echo e($billing->household?->block_number ?? 999); ?>"
                                    data-head-sort="<?php echo e(strtolower($billing->household?->household_head_name ?? 'zzz')); ?>"
                                    data-billing-id="<?php echo e($billing->id); ?>"
                                    data-search="<?php echo e(strtolower(($billing->household?->household_head_name ?? '') . ' ' . ($billing->household?->household_number ?? '') . ' ' . $billing->billing_number . ' Block ' . ($billing->household?->block_number ?? ''))); ?>"
                                    <?php echo e(request('billing_id') == $billing->id || (!empty($onlinePayment) && $onlinePayment->billing_id == $billing->id) ? 'selected' : ''); ?>>
                                    <?php echo e($billing->household?->household_head_name ?? 'Unknown'); ?> — <?php echo e($billing->billing_number); ?> | <?php echo e($billing->paymentType?->payment_name ?? ''); ?> | ₱<?php echo e(number_format($balance, 2)); ?> remaining | Due: <?php echo e($billing->due_date->format('M d, Y')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted" id="billingCount"></small>
                    </div>

                    <div id="billingInfo" class="card bg-light border-0 mb-4" style="display:none;">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Household</label>
                                    <div class="fw-semibold" id="infoHousehold">—</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Village</label>
                                    <div class="fw-semibold" id="infoVillage">—</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Payment Type</label>
                                    <div class="fw-semibold" id="infoType">—</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Period</label>
                                    <div class="fw-semibold" id="infoPeriod">—</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Due Date</label>
                                    <div class="fw-semibold" id="infoDue">—</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Total Amount Due</label>
                                    <div class="fw-bold text-primary" id="infoTotal">₱0.00</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Remaining Balance</label>
                                    <div class="fw-bold text-danger" id="infoBalance">₱0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="amount"
                                       name="amount" value="<?php echo e(old('amount', !empty($onlinePayment) ? number_format($onlinePayment->amount, 2, '.', '') : '')); ?>"
                                       min="0.01" <?php echo e(!empty($onlinePayment) ? 'readonly' : 'required'); ?>

                                       placeholder="Enter payment amount">
                            </div>
                            <div id="amountError" class="text-danger small mt-1" style="display:none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="discount" class="form-label fw-semibold">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control" id="discount"
                                       name="discount" value="<?php echo e(old('discount', '0.00')); ?>" min="0"
                                       <?php echo e(!empty($onlinePayment) ? 'readonly' : ''); ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <?php if(!empty($onlinePayment)): ?>
                                <input type="hidden" name="payment_method" value="gcash">
                                <select class="form-select" id="payment_method" disabled>
                                    <option value="gcash" selected>GCash (approved online payment)</option>
                                </select>
                            <?php else: ?>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="cash" <?php echo e(old('payment_method') === 'cash' ? 'selected' : ''); ?>>Cash</option>
                                    <option value="gcash" <?php echo e(old('payment_method') === 'gcash' ? 'selected' : ''); ?>>GCash</option>
                                    <option value="maya" <?php echo e(old('payment_method') === 'maya' ? 'selected' : ''); ?>>Maya</option>
                                    <option value="bank_transfer" <?php echo e(old('payment_method') === 'bank_transfer' ? 'selected' : ''); ?>>Bank Transfer</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="reference_number" class="form-label fw-semibold">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number"
                                   name="reference_number"
                                   value="<?php echo e(old('reference_number', !empty($onlinePayment) ? $onlinePayment->transaction_number : '')); ?>"
                                   <?php echo e(!empty($onlinePayment) ? 'readonly' : ''); ?>

                                   placeholder="For non-cash payments">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Net Amount to Pay</label>
                            <div class="form-control bg-light fw-bold text-primary" id="totalDisplay">₱0.00</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Remaining After Payment</label>
                            <div class="form-control bg-light fw-bold" id="remainingDisplay">₱0.00</div>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label fw-semibold">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2"
                                      placeholder="Optional notes..."><?php echo e(old('remarks')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-lg me-1"></i>Record Payment
                        </button>
                        <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingSelect = document.getElementById('billing_id');
    const sortSelect = document.getElementById('sortSelect');
    const billingSearch = document.getElementById('billingSearch');
    const billingCount = document.getElementById('billingCount');
    const billingInfo = document.getElementById('billingInfo');
    const amountInput = document.getElementById('amount');
    const discountInput = document.getElementById('discount');
    const totalDisplay = document.getElementById('totalDisplay');
    const remainingDisplay = document.getElementById('remainingDisplay');
    const amountError = document.getElementById('amountError');
    const submitBtn = document.getElementById('submitBtn');
    let currentBalance = 0;

    billingSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (!this.value) {
            billingInfo.style.display = 'none';
            currentBalance = 0;
            amountInput.max = '';
            amountInput.value = '';
            updateTotal();
            return;
        }

        document.getElementById('infoHousehold').textContent = selected.dataset.household + (selected.dataset.head ? ' (' + selected.dataset.head + ')' : '');
        document.getElementById('infoVillage').textContent = selected.dataset.village;
        document.getElementById('infoType').textContent = selected.dataset.type;
        document.getElementById('infoPeriod').textContent = selected.dataset.period;
        document.getElementById('infoDue').textContent = selected.dataset.due;
        document.getElementById('infoTotal').textContent = '₱' + parseFloat(selected.dataset.amount).toFixed(2);

        currentBalance = parseFloat(selected.dataset.balance);
        document.getElementById('infoBalance').textContent = '₱' + currentBalance.toFixed(2);

        amountInput.value = currentBalance.toFixed(2);
        amountInput.max = currentBalance;
        amountInput.min = 0.01;
        billingInfo.style.display = 'block';
        updateTotal();
    });

    function updateTotal() {
        const amount = parseFloat(amountInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const net = Math.max(0, amount - discount);
        const remaining = Math.max(0, currentBalance - net);

        totalDisplay.textContent = '₱' + net.toFixed(2);
        remainingDisplay.textContent = '₱' + remaining.toFixed(2);

        if (net > currentBalance + 0.01) {
            amountError.textContent = 'Amount exceeds remaining balance of ₱' + currentBalance.toFixed(2);
            amountError.style.display = 'block';
            amountInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        } else if (amount <= 0) {
            amountError.textContent = 'Payment amount must be greater than zero.';
            amountError.style.display = 'block';
            amountInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        } else {
            amountError.style.display = 'none';
            amountInput.classList.remove('is-invalid');
            submitBtn.disabled = false;
        }

        if (net > 0) {
            totalDisplay.classList.remove('text-muted');
            totalDisplay.classList.add('text-primary');
        }
    }

    amountInput.addEventListener('input', updateTotal);
    discountInput.addEventListener('input', updateTotal);

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const net = amount - discount;

        if (net <= 0) {
            e.preventDefault();
            alert('Net payment amount must be greater than zero.');
            return;
        }

        if (net > currentBalance + 0.01) {
            e.preventDefault();
            alert('Payment amount of ₱' + net.toFixed(2) + ' exceeds the remaining balance of ₱' + currentBalance.toFixed(2) + '.');
            return;
        }
    });

    if (billingSelect.value) {
        billingSelect.dispatchEvent(new Event('change'));
    }

    <?php if(!empty($onlinePayment)): ?>
    // Online payment: only the matching billing can be selected, and the
    // amount is restored to the resident's submitted GCash amount after the
    // change handler resets it to the full balance.
    Array.from(billingSelect.options).forEach(function(o) {
        if (o.value && o.value !== '<?php echo e($onlinePayment->billing_id); ?>') {
            o.remove();
        }
    });
    amountInput.value = '<?php echo e(number_format($onlinePayment->amount, 2, '.', '')); ?>';
    updateTotal();
    <?php endif; ?>

    function sortBillings() {
        const selectedValue = billingSelect.value;
        const options = Array.from(billingSelect.options).filter(o => o.value !== '');
        const placeholder = billingSelect.options[0];

        options.sort((a, b) => {
            switch (sortSelect.value) {
                case 'name-asc': return a.dataset.headSort.localeCompare(b.dataset.headSort);
                case 'name-desc': return b.dataset.headSort.localeCompare(a.dataset.headSort);
                case 'block-asc': return parseInt(a.dataset.block) - parseInt(b.dataset.block);
                case 'block-desc': return parseInt(b.dataset.block) - parseInt(a.dataset.block);
                case 'due-asc': return a.dataset.dueSort.localeCompare(b.dataset.dueSort);
                case 'due-desc': return b.dataset.dueSort.localeCompare(a.dataset.dueSort);
                case 'amount-desc': return parseFloat(b.dataset.balanceSort) - parseFloat(a.dataset.balanceSort);
                default: return 0;
            }
        });

        billingSelect.innerHTML = '';
        billingSelect.appendChild(placeholder);
        options.forEach(o => billingSelect.appendChild(o));
        billingSelect.value = selectedValue;
    }

    function filterBillings() {
        const query = billingSearch.value.toLowerCase().trim();
        const options = Array.from(billingSelect.options).filter(o => o.value !== '');
        let visible = 0;

        options.forEach(o => {
            const match = !query || (o.dataset.search && o.dataset.search.includes(query));
            o.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        billingCount.textContent = query ? visible + ' billing(s) found' : '';
    }

    sortSelect.addEventListener('change', sortBillings);
    billingSearch.addEventListener('input', filterBillings);
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/payments/create.blade.php ENDPATH**/ ?>