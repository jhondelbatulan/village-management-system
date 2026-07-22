<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'insights' => [],
    'title' => 'Quick Insights',
    'icon' => 'bi-lightbulb',
    'color' => 'info',
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
    'insights' => [],
    'title' => 'Quick Insights',
    'icon' => 'bi-lightbulb',
    'color' => 'info',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center">
        <h6 class="m-0 fw-bold text-<?php echo e($color); ?>">
            <i class="bi <?php echo e($icon); ?> me-2"></i><?php echo e($title); ?>

        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item border-start border-<?php echo e($insight['type'] ?? 'info'); ?> border-3 px-3 py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?php echo e($insight['type'] ?? 'info'); ?>-soft rounded-circle p-2">
                                <i class="bi <?php echo e($insight['icon'] ?? 'bi-info-circle'); ?> text-<?php echo e($insight['type'] ?? 'info'); ?>"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 small text-dark"><?php echo e($insight['message']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4">
                    <i class="bi bi-lightbulb fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No insights available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/quick-insights.blade.php ENDPATH**/ ?>