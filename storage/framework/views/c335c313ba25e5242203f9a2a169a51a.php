<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'activities' => collect(),
    'maxItems' => 10,
    'title' => 'Recent Activity',
    'icon' => 'bi-activity',
    'color' => 'primary',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'activities' => collect(),
    'maxItems' => 10,
    'title' => 'Recent Activity',
    'icon' => 'bi-activity',
    'color' => 'primary',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-<?php echo e($color); ?>">
            <i class="bi <?php echo e($icon); ?> me-2"></i><?php echo e($title); ?>

        </h6>
        <span class="badge bg-<?php echo e($color); ?>"><?php echo e($activities->count()); ?></span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
            <?php $__empty_1 = true; $__currentLoopData = $activities->take($maxItems); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item list-group-item-action px-3 py-3 border-start border-<?php echo e($activity['color']); ?> border-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?php echo e($activity['color']); ?>-soft rounded-circle p-2">
                                <i class="bi <?php echo e($activity['icon']); ?> text-<?php echo e($activity['color']); ?>"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <p class="mb-1 small fw-semibold text-dark"><?php echo e($activity['action']); ?></p>
                            <?php if($activity['description']): ?>
                                <p class="mb-1 small text-muted text-truncate"><?php echo e($activity['description']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i><?php echo e($activity['user']); ?>

                                </small>
                                <small class="text-muted">·</small>
                                <small class="text-muted"><?php echo e($activity['time_ago']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No recent activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/recent-activity.blade.php ENDPATH**/ ?>