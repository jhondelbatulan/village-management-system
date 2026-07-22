<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'value' => '',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => '',
    'previousValue' => null,
    'change' => null,
    'route' => null,
    'large' => false,
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
    'value' => '',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'subtitle' => '',
    'previousValue' => null,
    'change' => null,
    'route' => null,
    'large' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $cardClass = $large ? 'border-left-3 h-100 py-3' : 'border-left h-100 py-2';
    $colorClass = "border-{$color}";
    $iconBg = "bg-{$color}-soft";
?>

<div class="card shadow-sm <?php echo e($cardClass); ?> <?php echo e($colorClass); ?>" <?php if($route): ?> style="cursor:pointer;" <?php endif; ?>>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="text-xs fw-bold text-<?php echo e($color); ?> text-uppercase mb-1"><?php echo e($title); ?></div>
                <div class="<?php echo e($large ? 'h3' : 'h5'); ?> mb-0 fw-bold text-gray-800"><?php echo e($value); ?></div>
                <?php if($subtitle): ?>
                    <small class="text-muted"><?php echo e($subtitle); ?></small>
                <?php endif; ?>
                <?php if($change !== null): ?>
                    <div class="mt-1">
                        <?php if($change > 0): ?>
                            <span class="text-success small fw-semibold"><i class="bi bi-arrow-up me-1"></i>+<?php echo e($change); ?>%</span>
                            <span class="text-muted small">vs last month</span>
                        <?php elseif($change < 0): ?>
                            <span class="text-danger small fw-semibold"><i class="bi bi-arrow-down me-1"></i><?php echo e($change); ?>%</span>
                            <span class="text-muted small">vs last month</span>
                        <?php else: ?>
                            <span class="text-muted small"><i class="bi bi-dash me-1"></i>No change</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="<?php echo e($iconBg); ?> rounded-circle p-3">
                <i class="bi <?php echo e($icon); ?> fs-4 text-<?php echo e($color); ?>"></i>
            </div>
        </div>
    </div>
    <?php if($route): ?>
        <a href="<?php echo e($route); ?>" class="card-footer bg-<?php echo e($color); ?>-soft border-0 text-<?php echo e($color); ?> small fw-semibold py-2">
            View Details <i class="bi bi-arrow-right ms-1"></i>
        </a>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/summary-card.blade.php ENDPATH**/ ?>