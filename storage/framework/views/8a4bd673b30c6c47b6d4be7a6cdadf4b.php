<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'height' => '250px',
    'id' => '',
    'canvasId' => '',
    'header' => null,
    'exportable' => false,
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
    'icon' => 'bi-bar-chart',
    'color' => 'primary',
    'height' => '250px',
    'id' => '',
    'canvasId' => '',
    'header' => null,
    'exportable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $chartId = $canvasId ?: ($id ?: 'chart-' . md5($title));
?>

<div class="card shadow-sm h-100 chart-card" id="card-<?php echo e($chartId); ?>">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-<?php echo e($color); ?>">
            <i class="bi <?php echo e($icon); ?> me-2"></i><?php echo e($title); ?>

        </h6>
        <div class="d-flex align-items-center gap-2">
            <?php if($header): ?>
                <?php echo e($header); ?>

            <?php endif; ?>
            <?php if($exportable && $canvasId): ?>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportChart('<?php echo e($chartId); ?>')" title="Export as PNG">
                <i class="bi bi-download"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body chart-body">
        <?php if($slot): ?>
            <?php echo e($slot); ?>

        <?php else: ?>
            <div style="position: relative; height: <?php echo e($height); ?>;">
                <canvas id="<?php echo e($chartId); ?>"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($exportable && $canvasId): ?>
<?php $__env->startPush('scripts'); ?>
<script>
function exportChart(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = canvasId + '-chart.png';
    link.href = canvas.toDataURL('image/png', 1.0);
    link.click();
}
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/analytics/chart-card.blade.php ENDPATH**/ ?>