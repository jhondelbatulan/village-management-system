<?php $__env->startSection('title', 'Participation Monitoring'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-people me-2"></i>Participation Monitoring
            </h1>
            <p class="text-muted mb-0">
                <?php if($role === 'super_admin'): ?>
                    Village Comparison Overview
                <?php elseif($role === 'admin'): ?>
                    <?php echo e($village->village_name ?? 'Village'); ?> - President Dashboard
                <?php else: ?>
                    <?php echo e($village->village_name ?? 'Village'); ?> - Secretary Dashboard
                <?php endif; ?>
            </p>
        </div>
        <a href="<?php echo e(route('events.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-calendar me-1"></i>View Events
        </a>
    </div>

    <?php if($role === 'super_admin'): ?>
        
        <?php
            $totalVillages = count($stats);
            $totalEvents = collect($stats)->sum('total_events');
            $totalParticipants = collect($stats)->sum('total_participants');
            $avgAttendance = $totalVillages > 0 ? round(collect($stats)->avg('attendance_rate'), 1) : 0;
        ?>
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Villages</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($totalVillages); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-house-door fs-2 text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Events</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($totalEvents); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-event fs-2 text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Participants</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($totalParticipants)); ?></div>
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
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Attendance Rate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($avgAttendance); ?>%</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php $__empty_1 = true; $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $villageStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?php echo e($villageStat['village']->village_name); ?> (<?php echo e($villageStat['village']->village_code); ?>)
                        </h6>
                        <span class="badge bg-<?php echo e($villageStat['attendance_rate'] >= 50 ? 'success' : ($villageStat['attendance_rate'] >= 25 ? 'warning' : 'danger')); ?>">
                            <?php echo e($villageStat['attendance_rate']); ?>%
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Events</small>
                                <strong><?php echo e($villageStat['total_events']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Residents</small>
                                <strong><?php echo e($villageStat['total_residents']); ?></strong>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Present</small>
                                <strong class="text-success"><?php echo e($villageStat['present']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Absent</small>
                                <strong class="text-danger"><?php echo e($villageStat['absent']); ?></strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Late</small>
                                <strong class="text-warning"><?php echo e($villageStat['late']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Excused</small>
                                <strong class="text-info"><?php echo e($villageStat['excused']); ?></strong>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Upcoming: <?php echo e($villageStat['upcoming_events']); ?></span>
                            <span class="text-muted small">Completed: <?php echo e($villageStat['completed_events']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people fs-1 text-gray-300"></i>
                        <h5 class="mt-3 text-gray-600">No Village Data</h5>
                        <p class="text-muted">No active villages found with participation data.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Participants</div>
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

        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-pie-chart me-1"></i>Participation Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                            $totalP = $participationStats['total_participants'] ?: 1;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                                <strong><?php echo e($participationStats['present']); ?></strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo e(($participationStats['present'] / $totalP) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                <strong><?php echo e($participationStats['absent']); ?></strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo e(($participationStats['absent'] / $totalP) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-warning"><i class="bi bi-clock me-1"></i>Late</span>
                                <strong><?php echo e($participationStats['late']); ?></strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo e(($participationStats['late'] / $totalP) * 100); ?>%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-info"><i class="bi bi-info-circle me-1"></i>Excused</span>
                                <strong><?php echo e($participationStats['excused']); ?></strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: <?php echo e(($participationStats['excused'] / $totalP) * 100); ?>%"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted d-block">Upcoming</small>
                                <strong class="text-primary"><?php echo e($participationStats['upcoming_events']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Completed</small>
                                <strong class="text-success"><?php echo e($participationStats['completed_events']); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-calendar3 me-1"></i>Recent Events
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if($recentEvents->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Participations</th>
                                        <th>Responses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($event->title); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($event->category); ?></small>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                        <td><span class="badge bg-primary"><?php echo e($event->participations_count); ?> records</span></td>
                                        <td><span class="badge bg-info"><?php echo e($event->responses_count); ?> responses</span></td>
                                        <td>
                                            <a href="<?php echo e(route('events.show', $event)); ?>" class="btn btn-sm btn-outline-primary">
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
                            <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                            <h5 class="mt-2 text-gray-600">No Recent Events</h5>
                            <p class="text-muted">No events found for this village.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                                <th>Household</th>
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
                                    <a href="<?php echo e(route('participation.household', $stat['household'])); ?>">
                                        <?php echo e($stat['household']->family_name); ?>

                                    </a>
                                    <br>
                                    <small class="text-muted">Block <?php echo e($stat['household']->block_number ?? 'N/A'); ?> — Lot <?php echo e($stat['household']->lot_number ?? 'N/A'); ?></small>
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
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/participation/index.blade.php ENDPATH**/ ?>