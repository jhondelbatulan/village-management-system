<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'items' => [],
    'icon' => 'bi-trophy',
    'color' => 'warning',
    'valueLabel' => 'Value',
    'maxItems' => 5,
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
    'title' => '',
    'items' => [],
    'icon' => 'bi-trophy',
    'color' => 'warning',
    'valueLabel' => 'Value',
    'maxItems' => 5,
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
            <?php $__empty_1 = true; $__currentLoopData = array_slice($items, 0, $maxItems); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-<?php echo e($loop->iteration <= 3 ? $color : 'secondary'); ?> rounded-circle" style="width:24px;height:24px;line-height:18px;">
                            <?php echo e($loop->iteration); ?>

                        </span>
                        <span class="small fw-semibold"><?php echo e($item['name'] ?? ''); ?></span>
                    </div>
                    <span class="small fw-bold text-<?php echo e($color); ?>"><?php echo e($item['value'] ?? ''); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/ranking-card.blade.php ENDPATH**/ ?>