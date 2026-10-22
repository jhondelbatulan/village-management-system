<?php $__env->startSection('title', 'Participation History - ' . $resident->full_name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person me-2"></i><?php echo e($resident->full_name); ?>

            </h1>
            <p class="text-muted mb-0">Participation History - <?php echo e($resident->village?->village_name ?? 'N/A'); ?></p>
        </div>
        <a href="<?php echo e(route('participation.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Resident Info -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">Block / Lot</small>
                    <strong><?php echo e($resident->household ? 'Block ' . ($resident->household->block_number ?? 'N/A') . ' - Lot ' . ($resident->household->lot_number ?? 'N/A') : 'N/A'); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Village</small>
                    <strong><?php echo e($resident->village?->village_name ?? 'N/A'); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge bg-<?php echo e($resident->status === 'active' ? 'success' : 'danger'); ?>">
                        <?php echo e(ucfirst($resident->status)); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Participation Records -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-1"></i>Participation Records
            </h6>
        </div>
        <div class="card-body">
            <?php if($participations->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="25%">Event</th>
                            <th width="15%">Date</th>
                            <th width="12%">Status</th>
                            <th width="20%">Remarks</th>
                            <th width="15%">Recorded By</th>
                            <th width="13%">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $participations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('events.show', $participation->event)); ?>">
                                    <?php echo e($participation->event->title ?? 'N/A'); ?>

                                </a>
                                <br>
                                <small class="text-muted"><?php echo e($participation->event->category ?? ''); ?></small>
                            </td>
                            <td><?php echo e($participation->event?->event_date ? \Carbon\Carbon::parse($participation->event->event_date)->format('M d, Y') : 'N/A'); ?></td>
                            <td>
                                <?php if($participation->status === 'present'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                                <?php elseif($participation->status === 'absent'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                <?php elseif($participation->status === 'excused'): ?>
                                    <span class="badge bg-info"><i class="bi bi-info-circle me-1"></i>Excused</span>
                                <?php elseif($participation->status === 'late'): ?>
                                    <span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Late</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(ucfirst($participation->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($participation->remarks ?? '-'); ?></td>
                            <td><?php echo e($participation->recorder?->name ?? 'System'); ?></td>
                            <td><?php echo e($participation->created_at->format('M d, Y')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($participations->links()); ?>

            <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                <h5 class="mt-2 text-gray-600">No Participation Records</h5>
                <p class="text-muted">This resident has no recorded participation yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/participation/history.blade.php ENDPATH**/ ?>