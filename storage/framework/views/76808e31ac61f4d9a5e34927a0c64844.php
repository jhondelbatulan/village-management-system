<?php $__env->startSection('title', 'Household Participation - Block ' . ($household->block_number ?? 'N/A') . ' - Lot ' . ($household->lot_number ?? 'N/A')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-house me-2"></i>Block <?php echo e($household->block_number ?? 'N/A'); ?> - Lot <?php echo e($household->lot_number ?? 'N/A'); ?>

            </h1>
            <p class="text-muted mb-0"><?php echo e($household->village->village_name); ?> - Participation History</p>
        </div>
        <a href="<?php echo e(route('participation.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Household Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['member_count']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Events Attended</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['attended']); ?> / <?php echo e($stats['total_events']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo e($stats['participation_pct'] >= 50 ? 'success' : 'danger'); ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?php echo e($stats['participation_pct'] >= 50 ? 'success' : 'danger'); ?> text-uppercase mb-1">Participation Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['participation_pct']); ?>%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge bg-<?php echo e($stats['participation_pct'] >= 50 ? 'success' : 'danger'); ?>">
                                    <?php echo e($stats['participation_pct'] >= 50 ? 'Active' : 'Inactive'); ?>

                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-award fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Participation History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-1"></i>Participation History
            </h6>
        </div>
        <div class="card-body">
            <?php if($participations->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="20%">Event</th>
                            <th width="15%">Date</th>
                            <th width="15%">Block / Lot</th>
                            <th width="12%">Status</th>
                            <th width="18%">Remarks</th>
                            <th width="20%">Recorded By</th>
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
                            <td>Block <?php echo e($household->block_number ?? 'N/A'); ?> - Lot <?php echo e($household->lot_number ?? 'N/A'); ?></td>
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
                <p class="text-muted">This household has no recorded participation yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/participation/household.blade.php ENDPATH**/ ?>