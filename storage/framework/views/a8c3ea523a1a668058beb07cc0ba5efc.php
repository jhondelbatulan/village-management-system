<?php $__env->startSection('title', 'Participation - ' . $village->village_name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-geo-alt me-2"></i><?php echo e($village->village_name); ?> Participation
            </h1>
            <p class="text-muted mb-0"><?php echo e($village->village_code); ?> - Events &amp; Household Participation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('events.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-calendar me-1"></i>All Events
            </a>
            <a href="<?php echo e(route('participation.index')); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Participation Monitoring
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($participationStats['total_events']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-event fs-2 text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Attendance Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($participationStats['attendance_rate']); ?>%</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Household Participations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($participationStats['total_participants'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Residents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($participationStats['total_residents'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-lines-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-calendar3 me-1"></i>Events in <?php echo e($village->village_name); ?>


            </h6>
        </div>
        <div class="card-body">
            <?php if($events->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Participations</th>
                                <th>Responses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($event->title); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($event->category); ?></small>
                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo e(ucfirst($event->status)); ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo e($event->participations_count); ?> records</span></td>
                                    <td><span class="badge bg-info"><?php echo e($event->responses_count); ?> responses</span></td>
                                    <td>
                                        <a href="<?php echo e(route('events.show', $event)); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php if($events->hasPages()): ?>
                    <div class="mt-2"><?php echo e($events->links()); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                    <h5 class="mt-2 text-gray-600">No Events</h5>
                    <p class="text-muted">No events found for this village.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-house me-1"></i>Household Participation
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-success"><?php echo e($activeHouseholds->count()); ?> Active</span>
                <span class="badge bg-danger"><?php echo e($inactiveHouseholds->count()); ?> Inactive</span>
            </div>
        </div>
        <div class="card-body">
            <?php if(count($householdStats) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Block / Lot</th>
                                <th>Members</th>
                                <th>Events Attended</th>
                                <th>Participation %</th>
                                <th>Last Attendance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $householdStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('participation.household', $stat['household'])); ?>" class="fw-semibold text-decoration-none">
                                            Block <?php echo e($stat['household']->block_number ?? 'N/A'); ?> - Lot <?php echo e($stat['household']->lot_number ?? 'N/A'); ?>


                                        </a>
                                    </td>
                                    <td><?php echo e($stat['member_count']); ?></td>
                                    <td><?php echo e($stat['participated_count']); ?> / <?php echo e($stat['total_events']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px; width: 100px;">
                                                <div class="progress-bar bg-<?php echo e($stat['participation_pct'] >= 50 ? 'success' : ($stat['participation_pct'] >= 25 ? 'warning' : 'danger')); ?>"
                                                     style="width: <?php echo e($stat['participation_pct']); ?>%"></div>
                                            </div>
                                            <strong class="<?php echo e($stat['participation_pct'] >= 50 ? 'text-success' : ($stat['participation_pct'] >= 25 ? 'text-warning' : 'text-danger')); ?>">
                                                <?php echo e($stat['participation_pct']); ?>%
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($stat['last_attendance']): ?>
                                            <?php echo e($stat['last_attendance']->diffForHumans()); ?>


                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($stat['is_active']): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('participation.household', $stat['household'])); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-house fs-1 text-gray-300"></i>
                    <h5 class="mt-2 text-gray-600">No Household Data</h5>
                    <p class="text-muted">No household participation data available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/participation/village.blade.php ENDPATH**/ ?>