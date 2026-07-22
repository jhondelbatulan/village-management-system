<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'value' => '—',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => null,
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
    'title',
    'value' => '—',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="col-sm-6 col-xl-4 col-xxl-3">
    <div class="card stat-card shadow-sm h-100 border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label text-muted mb-1"><?php echo e($title); ?></p>
                    <h3 class="stat-value mb-0"><?php echo e($value); ?></h3>
                    <?php if($subtitle): ?>
                        <small class="text-muted"><?php echo e($subtitle); ?></small>
                    <?php endif; ?>
                </div>
                <div class="stat-icon bg-<?php echo e($color); ?>-subtle text-<?php echo e($color); ?>">
                    <i class="bi <?php echo e($icon); ?>"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/stat-card.blade.php ENDPATH**/ ?>