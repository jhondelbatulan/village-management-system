<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'GIS Summary',
    'mappedHouseholds' => 0,
    'totalHouseholds' => 0,
    'coveragePct' => 0,
    'villagesMapped' => 0,
    'totalVillages' => 0,
    'route' => '#',
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
    'title' => 'GIS Summary',
    'mappedHouseholds' => 0,
    'totalHouseholds' => 0,
    'coveragePct' => 0,
    'villagesMapped' => 0,
    'totalVillages' => 0,
    'route' => '#',
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
        <h6 class="m-0 fw-bold text-success">
            <i class="bi bi-geo-alt me-2"></i><?php echo e($title); ?>

        </h6>
        <a href="<?php echo e($route); ?>" class="btn btn-sm btn-outline-success">View Map</a>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col-6">
                <div class="bg-success-soft rounded p-3">
                    <div class="h4 mb-0 fw-bold text-success"><?php echo e($coveragePct); ?>%</div>
                    <small class="text-muted">Coverage</small>
                </div>
            </div>
            <div class="col-6">
                <div class="bg-info-soft rounded p-3">
                    <div class="h4 mb-0 fw-bold text-info"><?php echo e($villagesMapped); ?>/<?php echo e($totalVillages); ?></div>
                    <small class="text-muted">Villages Mapped</small>
                </div>
            </div>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: <?php echo e($coveragePct); ?>%"></div>
        </div>
        <small class="text-muted d-block mt-2"><?php echo e(number_format($mappedHouseholds)); ?> of <?php echo e(number_format($totalHouseholds)); ?> households mapped</small>
    </div>
</div>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/map-summary.blade.php ENDPATH**/ ?>