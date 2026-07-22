<?php $__env->startSection('title', 'Household - ' . $household->household_number); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><?php echo e($household->household_number); ?></h1>
        <p class="page-subtitle text-muted mb-0">
            <?php echo e($household->village->village_name ?? '—'); ?>

            <?php if($household->status === 'active'): ?>
                <span class="badge bg-success ms-2">Active</span>
            <?php elseif($household->status === 'inactive'): ?>
                <span class="badge bg-warning text-dark ms-2">Inactive</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Relocated</span>
            <?php endif; ?>
            <?php if($household->approval_status === 'approved'): ?>
                <span class="badge bg-success ms-1">Approved</span>
            <?php elseif($household->approval_status === 'admin_approved'): ?>
                <span class="badge bg-info ms-1">Admin Approved</span>
            <?php elseif($household->approval_status === 'pending'): ?>
                <span class="badge bg-warning text-dark ms-1">Pending Approval</span>
            <?php elseif($household->approval_status === 'rejected'): ?>
                <span class="badge bg-danger ms-1">Rejected</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-1">Draft</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('households.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <?php if(in_array(auth()->user()->role?->role_slug, ['secretary', 'admin', 'super_admin'])): ?>
            <form action="<?php echo e(route('households.toggle-status', $household)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn <?php echo e($household->status === 'active' ? 'btn-warning' : 'btn-success'); ?>"
                        onclick="return confirm('<?php echo e($household->status === 'active' ? 'Deactivate' : 'Activate'); ?> this household? <?php echo e($household->status === 'active' ? 'All linked user accounts will be deactivated.' : 'Linked user accounts will be reactivated.'); ?>')">
                    <i class="bi bi-<?php echo e($household->status === 'active' ? 'pause-circle' : 'play-circle'); ?> me-1"></i>
                    <?php echo e($household->status === 'active' ? 'Deactivate' : 'Activate'); ?>

                </button>
            </form>
        <?php endif; ?>
        <?php if(auth()->user()->role?->role_slug === 'secretary' && in_array($household->approval_status, ['draft', 'rejected'])): ?>
            <a href="<?php echo e(route('households.edit', $household)); ?>" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        <?php endif; ?>
        <?php if(auth()->user()->role?->role_slug === 'secretary' && $household->approval_status === 'draft'): ?>
            <form action="<?php echo e(route('households.submit', $household)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-info">
                    <i class="bi bi-send me-1"></i> Submit for Approval
                </button>
            </form>
        <?php endif; ?>
        <?php if(auth()->user()->role?->role_slug === 'admin' && $household->approval_status === 'pending'): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-lg me-1"></i> Review
            </button>
        <?php endif; ?>
        <?php if(auth()->user()->role?->role_slug === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved'])): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-lg me-1"></i> Final Approval
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-house-door me-2"></i> Household Information
                </h5>
            </div>
            <div class="card-body">
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
                    <div class="col-md-12">
                        <label class="form-label text-muted small">Address</label>
                        <div class="fw-semibold"><?php echo e($household->address ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Latitude</label>
                        <div class="fw-semibold"><?php echo e($household->latitude ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Longitude</label>
                        <div class="fw-semibold"><?php echo e($household->longitude ?? '—'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2"></i> House Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Household Type</label>
                        <div class="fw-semibold"><?php echo e($household->household_type ?? '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">House Ownership</label>
                        <div class="fw-semibold"><?php echo e($household->house_ownership ?? '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">House Status</label>
                        <div class="fw-semibold"><?php echo e(ucfirst($household->house_status ?? '—')); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Registration Date</label>
                        <div class="fw-semibold"><?php echo e($household->registration_date ? $household->registration_date->format('M d, Y') : '—'); ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            <?php if($household->status === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif($household->status === 'inactive'): ?>
                                <span class="badge bg-warning text-dark">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Relocated</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Member Count</label>
                        <div class="fw-semibold"><?php echo e($household->member_count); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2"></i> Approval Workflow
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Approval Status</label>
                        <div>
                            <?php if($household->approval_status === 'approved'): ?>
                                <span class="badge bg-success fs-6">Approved</span>
                            <?php elseif($household->approval_status === 'admin_approved'): ?>
                                <span class="badge bg-info fs-6">Admin Approved (Pending Super Admin)</span>
                            <?php elseif($household->approval_status === 'pending'): ?>
                                <span class="badge bg-warning text-dark fs-6">Pending</span>
                            <?php elseif($household->approval_status === 'rejected'): ?>
                                <span class="badge bg-danger fs-6">Rejected</span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">Draft</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Submitted By</label>
                        <div class="fw-semibold"><?php echo e($household->submittedBy?->full_name ?? '—'); ?></div>
                    </div>
                    <?php if($household->admin_approved_by): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Admin Approved By</label>
                        <div class="fw-semibold"><?php echo e($household->adminApprovedBy?->full_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Admin Approval Date</label>
                        <div class="fw-semibold"><?php echo e($household->admin_approved_at?->format('M d, Y h:i A') ?? '—'); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($household->approved_by && $household->approval_status !== 'admin_approved'): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Final Approved/Rejected By</label>
                        <div class="fw-semibold"><?php echo e($household->approvedBy?->full_name ?? '—'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Final Decision Date</label>
                        <div class="fw-semibold"><?php echo e($household->approved_at?->format('M d, Y h:i A') ?? '—'); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($household->remarks): ?>
                    <div class="col-12">
                        <label class="form-label text-muted small">Remarks</label>
                        <div class="fw-semibold"><?php echo e($household->remarks); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if($household->latitude && $household->longitude): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2"></i> Location</h6>
            </div>
            <div class="card-body p-0">
                <div id="household-map" style="height:250px;width:100%;"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2"></i> Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-12">
                        <div class="stat-card p-3">
                            <div class="stat-value fs-2 fw-bold text-primary"><?php echo e($household->member_count); ?></div>
                            <div class="text-muted small">Total Members</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if($household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-coin me-2"></i> Payment Summary
                </h6>
                <a href="<?php echo e(route('household-payments.show', $household)); ?>" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-receipt me-1"></i> View All
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-success">₱<?php echo e(number_format($household->paid_total, 2)); ?></div>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-danger">₱<?php echo e(number_format($household->outstanding_balance, 2)); ?></div>
                            <small class="text-muted">Outstanding</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-warning"><?php echo e($household->billings()->count()); ?></div>
                            <small class="text-muted">Total Billings</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($household->payment_behavior && $household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled): ?>
        <?php
            $pb = $household->payment_behavior;
            $pbColor = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$pb] ?? 'secondary';
            $pbIcon = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_ICONS[$pb] ?? 'bi-question-circle';
            $pbLabel = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$pb] ?? 'N/A';
            $reasons = json_decode($household->prediction_reasons, true) ?? [];
        ?>
        <div class="card shadow-sm mb-4 border-<?php echo e($pbColor); ?>">
            <div class="card-header bg-<?php echo e($pbColor); ?> bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-graph-up-arrow me-2"></i> Payment Behavior
                </h6>
                <a href="<?php echo e(route('analytics.predictive-payment.household', $household->id)); ?>" class="btn btn-outline-<?php echo e($pbColor); ?> btn-sm">
                    <i class="bi bi-bar-chart me-1"></i> Full Analysis
                </a>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="bi <?php echo e($pbIcon); ?> text-<?php echo e($pbColor); ?>" style="font-size: 2.5rem;"></i>
                    <div class="mt-2">
                        <span class="badge bg-<?php echo e($pbColor); ?> fs-6"><?php echo e($pbLabel); ?></span>
                    </div>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-<?php echo e($pbColor); ?>"><?php echo e($household->payment_reliability_score); ?></div>
                            <small class="text-muted">Score / 100</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-primary"><?php echo e($household->prediction_confidence); ?>%</div>
                            <small class="text-muted">Confidence</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-info"><?php echo e($household->last_prediction_at?->diffForHumans() ?? 'Never'); ?></div>
                            <small class="text-muted">Last Updated</small>
                        </div>
                    </div>
                </div>
                <?php if(count($reasons) > 0): ?>
                <div class="mt-3">
                    <small class="fw-semibold text-muted d-block mb-1">Reason:</small>
                    <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="small text-muted"><i class="bi bi-check2 text-<?php echo e($pbColor); ?> me-1"></i><?php echo e($reason); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-people me-2"></i> Household Members
            <span class="badge bg-primary ms-1"><?php echo e($household->member_count); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Resident Code</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Civil Status</th>
                        <th>Relationship to Head</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $household->residents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark"><?php echo e($resident->resident_code ?? '—'); ?></span>
                            </td>
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
                                    <span class="badge bg-outline-primary border"><?php echo e($resident->relationship_to_head); ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isset($resident->status) && $resident->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif(isset($resident->status) && $resident->status === 'inactive'): ?>
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-person-plus fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No household members registered yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(
    (auth()->user()->role?->role_slug === 'admin' && $household->approval_status === 'pending') ||
    (auth()->user()->role?->role_slug === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved']))
): ?>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('households.approve', $household)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">
                        <?php if(auth()->user()->role?->role_slug === 'admin'): ?>
                            Review Household (Admin Approval)
                        <?php else: ?>
                            Final Approval (Super Admin)
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Household: <?php echo e($household->household_number); ?></label>
                        <div class="text-muted small">Block <?php echo e($household->block_number); ?>, Lot <?php echo e($household->lot_number); ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action <span class="text-danger">*</span></label>
                        <select name="approval_status" id="approval_status" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3" id="remarksGroup">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"
                            placeholder="Enter remarks (required for rejection)"></textarea>
                        <small class="text-muted">Required when rejecting.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const approvalSelect = document.getElementById('approval_status');
        const remarksGroup = document.getElementById('remarksGroup');
        if (approvalSelect && remarksGroup) {
            approvalSelect.addEventListener('change', function() {
                if (this.value === 'rejected') {
                    remarksGroup.querySelector('textarea').required = true;
                } else {
                    remarksGroup.querySelector('textarea').required = false;
                }
            });
        }

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/households/show.blade.php ENDPATH**/ ?>