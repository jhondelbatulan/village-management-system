<?php $__env->startSection('title', $event->title); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><?php echo e($event->title); ?></h1>
        <p class="page-subtitle text-muted">
            <?php echo e($event->village?->village_name ?? ''); ?> &middot;
            <?php echo e($event->event_date->format('M d, Y')); ?>

        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if($event->isManagedBy(auth()->user())): ?>
            <a href="<?php echo e(route('events.edit', $event)); ?>" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        <?php endif; ?>
        <a href="<?php echo e(route('events.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if($event->status === 'upcoming'): ?>
                        <span class="badge bg-primary">Upcoming</span>
                    <?php elseif($event->status === 'ongoing'): ?>
                        <span class="badge bg-success">Ongoing</span>
                    <?php elseif($event->status === 'completed'): ?>
                        <span class="badge bg-secondary">Completed</span>
                    <?php elseif($event->status === 'cancelled'): ?>
                        <span class="badge bg-danger">Cancelled</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Draft</span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark"><?php echo e($event->category); ?></span>
                </div>

                <?php if($event->cover_image): ?>
                    <div class="mb-3">
                        <img src="<?php echo e($event->cover_image_url); ?>" alt="Cover" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                <?php endif; ?>

                <div class="event-body">
                    <?php echo nl2br(e($event->description)); ?>

                </div>

                <?php if($event->attachment): ?>
                    <div class="mt-3">
                        <a href="<?php echo e($event->attachment_url); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-paperclip me-1"></i> View Attachment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(auth()->user()->role?->role_slug === 'resident'): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Your Response</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('events.respond', $event)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="d-flex gap-2">
                            <button type="submit" name="response" value="going" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Going
                            </button>
                            <button type="submit" name="response" value="maybe" class="btn btn-warning">
                                <i class="bi bi-question-circle me-1"></i> Maybe
                            </button>
                            <button type="submit" name="response" value="not_going" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Not Going
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin', 'secretary'])): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Participations (<?php echo e($event->participations->count()); ?>)</h6>
                    <div class="d-flex gap-2">
                        <form action="<?php echo e(route('events.auto-generate', $event)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-magic me-1"></i> Auto-Generate from Responses
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipationModal">
                            <i class="bi bi-plus-lg me-1"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Block / Lot</th>
                                    <th>Household No.</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Recorded By</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $event->participations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <?php if($p->household): ?>
                                                Block <?php echo e($p->household->block_number ?? 'N/A'); ?> - Lot <?php echo e($p->household->lot_number ?? 'N/A'); ?>

                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($p->household?->household_number ?? '—'); ?></td>
                                        <td>
                                            <?php if($p->status === 'present'): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php elseif($p->status === 'absent'): ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Excused</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small"><?php echo e($p->remarks ?? '—'); ?></td>
                                        <td class="small"><?php echo e($p->recorder?->full_name ?? '—'); ?></td>
                                        <td class="text-end">
                                            <form action="<?php echo e(route('events.participations.destroy', [$event, $p])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this participation?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No participations recorded yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Event Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Village</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->village?->village_name ?? '—'); ?></dd>
                    <dt class="col-sm-5 text-muted">Category</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->category); ?></dd>
                    <dt class="col-sm-5 text-muted">Date</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->event_date->format('M d, Y')); ?></dd>
                    <dt class="col-sm-5 text-muted">Time</dt>
                    <dd class="col-sm-7 fw-semibold">
                        <?php echo e($event->start_time?->format('h:i A') ?? '—'); ?>

                        <?php if($event->end_time): ?> – <?php echo e($event->end_time->format('h:i A')); ?> <?php endif; ?>
                    </dd>
                    <dt class="col-sm-5 text-muted">Venue</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->venue ?? '—'); ?></dd>
                    <dt class="col-sm-5 text-muted">Max Participants</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->max_participants ?? 'Unlimited'); ?></dd>
                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7 fw-semibold"><?php echo e($event->creator?->full_name ?? '—'); ?></dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Responses</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Going</span>
                    <span class="badge bg-success rounded-pill"><?php echo e($event->going_count); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Maybe</span>
                    <span class="badge bg-warning text-dark rounded-pill"><?php echo e($event->maybe_count); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Not Going</span>
                    <span class="badge bg-danger rounded-pill"><?php echo e($event->not_going_count); ?></span>
                </div>

                <?php if($event->responses->count()): ?>
                    <hr>
                    <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none"
                       data-bs-toggle="modal" data-bs-target="#eventResponsesModal">
                        <span class="small fw-semibold"><?php echo e($event->responses->count()); ?> responded</span>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($event->isManagedBy(auth()->user())): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if(! in_array($event->status, ['completed', 'cancelled'])): ?>
                        <form action="<?php echo e(route('events.complete', $event)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Event Completed
                            </button>
                        </form>
                        <form action="<?php echo e(route('events.cancel', $event)); ?>" method="POST" onsubmit="return confirm('Cancel this event?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-1"></i> Cancel Event
                            </button>
                        </form>
                    <?php endif; ?>
                    <form action="<?php echo e(route('events.destroy', $event)); ?>" method="POST" onsubmit="return confirm('Delete this event?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Event
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin', 'secretary'])): ?>
    <div class="modal fade" id="addParticipationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('events.participations.store', $event)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Record Participation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
                            <select name="household_id" class="form-select" required>
                                <option value="">Select Household</option>
                                <?php $__currentLoopData = \App\Models\Household::where('village_id', $event->village_id)->where('status', 'active')->orderBy('household_number')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($household->id); ?>">Block <?php echo e($household->block_number ?? 'N/A'); ?> - Lot <?php echo e($household->lot_number ?? 'N/A'); ?> (<?php echo e($household->household_number); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">One participation is recorded per household per event.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional remarks...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="eventResponsesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Responding Households</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php $__empty_1 = true; $__currentLoopData = $event->responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">
                            Block <?php echo e($resp->household?->block_number ?? 'N/A'); ?>, Lot <?php echo e($resp->household?->lot_number ?? 'N/A'); ?>

                            <span class="text-muted">(<?php echo e($resp->household?->household_head_name ?? 'Unknown'); ?>)</span>
                        </span>
                        <?php if($resp->response === 'going'): ?>
                            <span class="badge bg-success">Going</span>
                        <?php elseif($resp->response === 'maybe'): ?>
                            <span class="badge bg-warning text-dark">Maybe</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Not Going</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted small mb-0">No responses yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/events/show.blade.php ENDPATH**/ ?>