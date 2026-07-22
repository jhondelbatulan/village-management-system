<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'label' => '',
    'icon' => 'bi-graph-up',
    'suffix' => '%',
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
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'label' => '',
    'icon' => 'bi-graph-up',
    'suffix' => '%',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $percentage = $max > 0 ? min(round(($value / $max) * 100, 1), 100) : 0;
?>

<div class="card shadow-sm h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="m-0 fw-bold text-<?php echo e($color); ?>">
                <i class="bi <?php echo e($icon); ?> me-2"></i><?php echo e($title); ?>

            </h6>
            <span class="badge bg-<?php echo e($color); ?>"><?php echo e($value); ?><?php echo e($suffix); ?></span>
        </div>
        <div class="progress" style="height: 12px;">
            <div class="progress-bar bg-<?php echo e($color); ?>" role="progressbar"
                 style="width: <?php echo e($percentage); ?>%;"
                 aria-valuenow="<?php echo e($value); ?>" aria-valuemin="0" aria-valuemax="<?php echo e($max); ?>">
            </div>
        </div>
        <?php if($label): ?>
            <small class="text-muted mt-2 d-block"><?php echo e($label); ?></small>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/progress-card.blade.php ENDPATH**/ ?>