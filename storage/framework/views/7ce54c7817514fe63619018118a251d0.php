<?php $__env->startSection('title', 'My Household'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">My Household</h4>
            <p class="page-subtitle text-muted mb-0">
                <?php echo e($household->household_number); ?> | <?php echo e($household->village->village_name ?? '—'); ?>

            </p>
        </div>
    </div>
</div>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-house-door-fill fs-2 text-primary d-block mb-2"></i>
                <h5 class="mb-1"><?php echo e($household->status === 'active' ? 'Active' : ucfirst($household->status)); ?></h5>
                <small class="text-muted">Household Status</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-people-fill fs-2 text-info d-block mb-2"></i>
                <h5 class="mb-1"><?php echo e($household->residents->count()); ?></h5>
                <small class="text-muted">Members</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack fs-2 text-success d-block mb-2"></i>
                <h5 class="mb-1">₱<?php echo e(number_format($balance['paid'], 2)); ?></h5>
                <small class="text-muted">Total Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-circle fs-2 <?php echo e(($balance['outstanding'] ?? 0) > 0 ? 'text-danger' : 'text-success'); ?> d-block mb-2"></i>
                <h5 class="mb-1 <?php echo e(($balance['outstanding'] ?? 0) > 0 ? 'text-danger' : 'text-success'); ?>">₱<?php echo e(number_format($balance['outstanding'], 2)); ?></h5>
                <small class="text-muted">Outstanding Balance</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-house-door me-2 text-primary"></i>Household Information</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Household Number</label>
                        <div class="fw-semibold"><?php echo e($household->household_number); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Village</label>
                        <div class="fw-semibold"><?php echo e($household->village->village_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Block Number</label>
                        <div class="fw-semibold"><?php echo e($household->block_number ?? '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Lot Number</label>
                        <div class="fw-semibold"><?php echo e($household->lot_number ?? '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Household Head</label>
                        <div class="fw-semibold"><?php echo e($household->household_head_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Contact Number</label>
                        <div class="fw-semibold"><?php echo e($household->contact_number ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Purok</label>
                        <div class="fw-semibold"><?php echo e($household->purok ?? '—'); ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Address</label>
                        <div class="fw-semibold"><?php echo e($household->address ?? '—'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-info"></i>Household Members (<?php echo e($household->residents->count()); ?>)</h6>
                <a href="<?php echo e(route('households.request-member-form')); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-person-plus me-1"></i>Request to Add Member
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Civil Status</th>
                                <th>Relationship</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $household->residents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?php echo e($resident->full_name ?? $resident->first_name . ' ' . $resident->last_name); ?>

                                </td>
                                <td>
                                    <?php if($resident->gender === 'Male'): ?>
                                        <span class="text-primary"><i class="bi bi-gender-male me-1"></i>Male</span>
                                    <?php elseif($resident->gender === 'Female'): ?>
                                        <span class="text-danger"><i class="bi bi-gender-female me-1"></i>Female</span>
                                    <?php else: ?>
                                        <span class="text-secondary"><?php echo e($resident->gender ?? '—'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($resident->age ?? '—'); ?></td>
                                <td><?php echo e($resident->civil_status ?? '—'); ?></td>
                                <td>
                                    <?php if($resident->relationship_to_head): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary"><?php echo e($resident->relationship_to_head); ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($resident->status) && $resident->status === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">No household members registered yet.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if($household->village && $household->village->isPaymentEnabled()): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-receipt me-2 text-primary"></i>Payment History
                </h6>
                <?php if($payments->count() > 0): ?>
                <a href="<?php echo e(route('household-payments.show', $household)); ?>" class="btn btn-outline-primary btn-sm">
                    View Full History
                </a>
                <?php endif; ?>
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
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $billings->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($billing->billing_number); ?></td>
                                <td><?php echo e($billing->paymentType->payment_name ?? '—'); ?></td>
                                <td><?php echo e($billing->billing_period); ?></td>
                                <td><?php echo e($billing->due_date->format('M d, Y')); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($billing->total_amount, 2)); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo e($billing->status_badge); ?>"><?php echo e($billing->status_label); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    <p class="mb-0">No billings found for your household.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-megaphone me-2 text-primary"></i>Village Announcements
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-megaphone fs-1 d-block mb-2"></i>
                    <p class="mb-0">No announcements yet. Check back later.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if($household->latitude && $household->longitude): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2 text-success"></i>Location</h6>
            </div>
            <div class="card-body p-0">
                <div id="household-map" style="height:250px;width:100%;"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($household->village && $household->village->isPaymentEnabled()): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-wallet2 me-2 text-success"></i>Payment Summary
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Paid</span>
                    <span class="fw-bold text-success">₱<?php echo e(number_format($balance['paid'], 2)); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Outstanding</span>
                    <span class="fw-bold <?php echo e(($balance['outstanding'] ?? 0) > 0 ? 'text-danger' : 'text-success'); ?>">
                        ₱<?php echo e(number_format($balance['outstanding'], 2)); ?>

                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Penalties</span>
                    <span class="fw-bold text-warning">₱<?php echo e(number_format($balance['penalties'], 2)); ?></span>
                </div>
                <hr>
                <a href="<?php echo e(route('household-payments.show', $household)); ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-receipt me-1"></i>View Full Payment History
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Quick Status
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Household</span>
                        <span class="badge bg-success rounded-pill"><?php echo e($household->status); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Village</span>
                        <span class="badge bg-primary rounded-pill"><?php echo e($household->village?->village_name ?? '—'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Members</span>
                        <span class="badge bg-info rounded-pill"><?php echo e($household->residents->count()); ?></span>
                    </li>
                    <?php if($household->village && $household->village->isPaymentEnabled()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Payment Status</span>
                        <?php if(($balance['outstanding'] ?? 0) > 0): ?>
                            <span class="badge bg-warning text-dark rounded-pill">Has Balance</span>
                        <?php else: ?>
                            <span class="badge bg-success rounded-pill">Up to Date</span>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if($household->latitude && $household->longitude): ?>
        var map = L.map('household-map').setView([<?php echo e($household->latitude); ?>, <?php echo e($household->longitude); ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap', maxZoom: 19 }).addTo(map);
        var icon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:16px;height:16px;background:#28a745;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.5);"></div>',
            iconSize: [22, 22], iconAnchor: [11, 11]
        });
        L.marker([<?php echo e($household->latitude); ?>, <?php echo e($household->longitude); ?>], { icon: icon }).addTo(map);
        <?php endif; ?>
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/households/my-household.blade.php ENDPATH**/ ?>