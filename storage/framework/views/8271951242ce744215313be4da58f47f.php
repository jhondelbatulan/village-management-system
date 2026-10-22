<?php $__env->startSection('title', 'Monthly Collection Breakdown'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">Monthly Collection</h1>
        <p class="text-muted mb-0">Per-village collections for <?php echo e(now()->format('F Y')); ?> · Updated <?php echo e(now()->format('F d, Y')); ?></p>
    </div>
    <a href="<?php echo e(route('dashboard.super_admin')); ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Collected This Month','value' => '₱' . number_format($system['this_month_collected'] ?? 0, 2),'icon' => 'bi-cash-stack','color' => 'info','subtitle' => 'Across all villages','change' => $system['change'] ?? null,'large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collected This Month','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($system['this_month_collected'] ?? 0, 2)),'icon' => 'bi-cash-stack','color' => 'info','subtitle' => 'Across all villages','change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($system['change'] ?? null),'large' => true]); ?>
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
    <div class="col-xl-4 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Collected Last Month','value' => '₱' . number_format($system['previous_month_collected'] ?? 0, 2),'icon' => 'bi-clock-history','color' => 'secondary','subtitle' => 'Same period last month','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collected Last Month','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($system['previous_month_collected'] ?? 0, 2)),'icon' => 'bi-clock-history','color' => 'secondary','subtitle' => 'Same period last month','large' => true]); ?>
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
    <div class="col-xl-4 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'All-Time Collected','value' => '₱' . number_format($system['total_collected'] ?? 0, 2),'icon' => 'bi-safe','color' => 'primary','subtitle' => 'Since inception','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'All-Time Collected','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($system['total_collected'] ?? 0, 2)),'icon' => 'bi-safe','color' => 'primary','subtitle' => 'Since inception','large' => true]); ?>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart me-2 text-info"></i>Collections by Village</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-end">This Month</th>
                        <th class="text-end">Last Month</th>
                        <th class="text-end">Change</th>
                        <th class="text-end">All-Time Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e($row['village']->village_name); ?></td>
                        <td class="text-end">₱<?php echo e(number_format($row['this_month'], 2)); ?></td>
                        <td class="text-end">₱<?php echo e(number_format($row['previous_month'], 2)); ?></td>
                        <td class="text-end">
                            <?php if($row['change'] > 0): ?>
                                <span class="text-success fw-semibold">+<?php echo e($row['change']); ?>%</span>
                            <?php elseif($row['change'] < 0): ?>
                                <span class="text-danger fw-semibold"><?php echo e($row['change']); ?>%</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">₱<?php echo e(number_format($row['total_collected'], 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No active villages found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/dashboard/super-admin-monthly-collection.blade.php ENDPATH**/ ?>