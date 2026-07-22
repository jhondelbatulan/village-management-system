<?php $__env->startSection('title', 'Government Assistance Analytics'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-building-check me-2"></i>Government Assistance Analytics
        </h1>
        <p class="text-muted mb-0">Overview of government assistance programs across all villages</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter Village
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?php echo e(route('analytics.government-assistance.index')); ?>" <?php if(!$villageId): ?> <?php endif; ?>>All Villages</a></li>
                <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><a class="dropdown-item <?php if($villageId == $village->id): ?> active <?php endif; ?>" href="<?php echo e(route('analytics.government-assistance.village', $village->id)); ?>"><?php echo e($village->village_name); ?></a></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?php echo e(route('analytics.government-assistance.export', 'csv')); ?>"><i class="bi bi-filetype-csv me-2"></i>Export CSV</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('analytics.government-assistance.export', 'pdf')); ?>"><i class="bi bi-file-pdf me-2"></i>Export PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Residents','value' => $summary['total'],'icon' => 'bi-people','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Residents','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['total']),'icon' => 'bi-people','color' => 'primary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $attributes = $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $component = $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'With Assistance','value' => $summary['with_assistance'],'icon' => 'bi-building-check','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'With Assistance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['with_assistance']),'icon' => 'bi-building-check','color' => 'success']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $attributes = $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $component = $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Coverage Rate','value' => $summary['coverage_rate'] . '%','icon' => 'bi-graph-up','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Coverage Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['coverage_rate'] . '%'),'icon' => 'bi-graph-up','color' => 'info']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $attributes = $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c)): ?>
<?php $component = $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c; ?>
<?php unset($__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c); ?>
<?php endif; ?>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6 mb-4">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Program Distribution','canvasId' => 'programChart']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Program Distribution','canvasId' => 'programChart']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c4118fba45727bba6046c46969dd6d8)): ?>
<?php $attributes = $__attributesOriginal3c4118fba45727bba6046c46969dd6d8; ?>
<?php unset($__attributesOriginal3c4118fba45727bba6046c46969dd6d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c4118fba45727bba6046c46969dd6d8)): ?>
<?php $component = $__componentOriginal3c4118fba45727bba6046c46969dd6d8; ?>
<?php unset($__componentOriginal3c4118fba45727bba6046c46969dd6d8); ?>
<?php endif; ?>
    </div>
    <div class="col-xl-6 col-lg-6 mb-4">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Beneficiary Distribution','canvasId' => 'beneficiaryChart']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Beneficiary Distribution','canvasId' => 'beneficiaryChart']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c4118fba45727bba6046c46969dd6d8)): ?>
<?php $attributes = $__attributesOriginal3c4118fba45727bba6046c46969dd6d8; ?>
<?php unset($__attributesOriginal3c4118fba45727bba6046c46969dd6d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c4118fba45727bba6046c46969dd6d8)): ?>
<?php $component = $__componentOriginal3c4118fba45727bba6046c46969dd6d8; ?>
<?php unset($__componentOriginal3c4118fba45727bba6046c46969dd6d8); ?>
<?php endif; ?>
    </div>
</div>

<!-- Program Counts Table -->
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-table me-1"></i>Program Counts
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Program</th>
                        <th class="text-center">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $programCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($program); ?></td>
                        <td class="text-center"><?php echo e($count); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Village Comparison -->
<?php if(!$villageId && count($villageComparison) > 0): ?>
<div class="card mb-4 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-bar-chart me-1"></i>Village Comparison
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-center">Total Residents</th>
                        <th class="text-center">With Assistance</th>
                        <th class="text-center">Coverage Rate</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $villageComparison; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($name); ?></td>
                        <td class="text-center"><?php echo e($village['total']); ?></td>
                        <td class="text-center"><?php echo e($village['with_assistance']); ?></td>
                        <td class="text-center"><?php echo e($village['rate']); ?>%</td>
                        <td class="text-center">
                            <a href="<?php echo e(route('analytics.government-assistance.village', $village['village_id'])); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Insights -->
<?php if (isset($component)) { $__componentOriginale0172d82feeb27b99b995a02e6ca0457 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0172d82feeb27b99b995a02e6ca0457 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['insights' => $insights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insights)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0172d82feeb27b99b995a02e6ca0457)): ?>
<?php $attributes = $__attributesOriginale0172d82feeb27b99b995a02e6ca0457; ?>
<?php unset($__attributesOriginale0172d82feeb27b99b995a02e6ca0457); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0172d82feeb27b99b995a02e6ca0457)): ?>
<?php $component = $__componentOriginale0172d82feeb27b99b995a02e6ca0457; ?>
<?php unset($__componentOriginale0172d82feeb27b99b995a02e6ca0457); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];
const programLabels = <?php echo json_encode(array_keys($programDistribution), 15, 512) ?>;
const programData = <?php echo json_encode(array_values(array_map(function($item) { return $item['count']; }, $programDistribution)), 512) ?>;

new Chart(document.getElementById('programChart'), {
    type: 'bar',
    data: {
        labels: programLabels,
        datasets: [{
            label: 'Residents',
            data: programData,
            backgroundColor: colors.slice(0, programLabels.length),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { maxRotation: 45 } }
        }
    }
});

const beneficiaryLabels = <?php echo json_encode(array_keys($beneficiaryDistribution), 15, 512) ?>;
const beneficiaryData = <?php echo json_encode(array_values(array_map(function($item) { return $item['count']; }, $beneficiaryDistribution)), 512) ?>;

new Chart(document.getElementById('beneficiaryChart'), {
    type: 'doughnut',
    data: {
        labels: beneficiaryLabels,
        datasets: [{
            data: beneficiaryData,
            backgroundColor: colors.slice(0, beneficiaryLabels.length),
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 15 } }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/analytics/government-assistance/index.blade.php ENDPATH**/ ?>