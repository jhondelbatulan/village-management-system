<?php $__env->startSection('title', 'Super Admin Executive Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">System Executive Dashboard</h1>
        <p class="text-muted mb-0">Cross-village overview · <?php echo e(now()->format('F d, Y')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('villages.index')); ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-geo-alt me-1"></i>All Villages
        </a>
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i>Users
        </a>
    </div>
</div>

<?php echo $__env->make('partials.village-photos-slideshow', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Residents','value' => number_format($populationStats['total'] ?? 0),'icon' => 'bi-people','color' => 'primary','previousValue' => $populationStats['previous'] ?? null,'change' => $populationStats['change'] ?? null,'route' => ''.e(route('residents.index')).'','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Residents','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationStats['total'] ?? 0)),'icon' => 'bi-people','color' => 'primary','previousValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($populationStats['previous'] ?? null),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($populationStats['change'] ?? null),'route' => ''.e(route('residents.index')).'','large' => true]); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Active Households','value' => number_format($householdStats['active'] ?? 0),'icon' => 'bi-house-door','color' => 'success','previousValue' => $householdStats['previous'] ?? null,'change' => $householdStats['change'] ?? null,'route' => ''.e(route('households.index')).'','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Households','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdStats['active'] ?? 0)),'icon' => 'bi-house-door','color' => 'success','previousValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdStats['previous'] ?? null),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdStats['change'] ?? null),'route' => ''.e(route('households.index')).'','large' => true]); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Monthly Collection','value' => number_format($paymentStats['collected'] ?? 0),'icon' => 'bi-cash-stack','color' => 'info','previousValue' => $paymentStats['previous'] ?? null,'change' => $paymentStats['change'] ?? null,'subtitle' => 'Collected this month','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Monthly Collection','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($paymentStats['collected'] ?? 0)),'icon' => 'bi-cash-stack','color' => 'info','previousValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['previous'] ?? null),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['change'] ?? null),'subtitle' => 'Collected this month','large' => true]); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Pending Approvals','value' => number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0)),'icon' => 'bi-hourglass-split','color' => 'warning','subtitle' => 'Awaiting action','route' => ''.e(route('approvals.households')).'','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending Approvals','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0))),'icon' => 'bi-hourglass-split','color' => 'warning','subtitle' => 'Awaiting action','route' => ''.e(route('approvals.households')).'','large' => true]); ?>
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

<?php if($dashboardWidgets['analytics_summary'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Population by Village','icon' => 'bi-bar-chart','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Population by Village','icon' => 'bi-bar-chart','color' => 'primary']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginale0172d82feeb27b99b995a02e6ca0457 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0172d82feeb27b99b995a02e6ca0457 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['title' => 'System Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => $insights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insights)]); ?>
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
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Village Population Ranking','icon' => 'bi-trophy','color' => 'warning','valueLabel' => 'Residents','items' => $villageComparison ?? [],'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Village Population Ranking','icon' => 'bi-trophy','color' => 'warning','valueLabel' => 'Residents','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($villageComparison ?? []),'maxItems' => 5]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $attributes = $__attributesOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $component = $__componentOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__componentOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
    </div>
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Village Collection Ranking','icon' => 'bi-cash-coin','color' => 'success','valueLabel' => 'Amount','items' => $paymentComparison ?? [],'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Village Collection Ranking','icon' => 'bi-cash-coin','color' => 'success','valueLabel' => 'Amount','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentComparison ?? []),'maxItems' => 5]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $attributes = $__attributesOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $component = $__componentOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__componentOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if($dashboardWidgets['analytics_summary'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Monthly Payment Trend','icon' => 'bi-graph-up','color' => 'success','canvasId' => 'dashPaymentTrendChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Monthly Payment Trend','icon' => 'bi-graph-up','color' => 'success','canvasId' => 'dashPaymentTrendChart','height' => '250px']); ?>
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
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Participation by Village','icon' => 'bi-clipboard-check','color' => 'primary','canvasId' => 'dashParticipationChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Participation by Village','icon' => 'bi-clipboard-check','color' => 'primary','canvasId' => 'dashParticipationChart','height' => '250px']); ?>
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
<?php endif; ?>

<?php if($dashboardWidgets['recent_activities'] ?? true || $dashboardWidgets['system_notices'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.recent-activity','data' => ['title' => 'Recent System Activity','icon' => 'bi-activity','color' => 'primary','activities' => $recentActivity,'maxItems' => 10]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.recent-activity'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent System Activity','icon' => 'bi-activity','color' => 'primary','activities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentActivity),'maxItems' => 10]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d)): ?>
<?php $attributes = $__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d; ?>
<?php unset($__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d)): ?>
<?php $component = $__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d; ?>
<?php unset($__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d); ?>
<?php endif; ?>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-warning">
                    <i class="bi bi-hourglass-split me-2"></i>Pending Approvals
                </h6>
                <a href="<?php echo e(route('approvals.households')); ?>" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if($pendingHouseholds->count() > 0 || $pendingResidents->count() > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $pendingHouseholds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $household): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2 border-start border-warning border-3">
                                <div>
                                    <span class="fw-semibold small">Household: <?php echo e($household->lot_number); ?></span>
                                    <br><small class="text-muted">Block <?php echo e($household->block_number); ?> · <?php echo e($household->village?->village_name ?? 'N/A'); ?></small>
                                </div>
                                <span class="badge bg-<?php echo e($household->approval_status === 'admin_approved' ? 'info' : 'warning'); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $household->approval_status))); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $pendingResidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2 border-start border-info border-3">
                                <div>
                                    <span class="fw-semibold small">Resident: <?php echo e($resident->full_name); ?></span>
                                    <br><small class="text-muted">Household: <?php echo e($resident->household?->lot_number ?? 'N/A'); ?> · <?php echo e($resident->village?->village_name ?? 'N/A'); ?></small>
                                </div>
                                <span class="badge bg-<?php echo e($resident->approval_status === 'admin_approved' ? 'info' : 'warning'); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $resident->approval_status))); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                        <p class="text-muted small mt-2 mb-0">All approvals up to date</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($dashboardWidgets['participation_summary'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.progress-card','data' => ['title' => 'System Approval Rate','value' => $approvalStats['approval_rate'] ?? 0,'max' => 100,'color' => 'success','icon' => 'bi-check2-circle','label' => 'Households and residents approved']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.progress-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Approval Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($approvalStats['approval_rate'] ?? 0),'max' => 100,'color' => 'success','icon' => 'bi-check2-circle','label' => 'Households and residents approved']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5)): ?>
<?php $attributes = $__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5; ?>
<?php unset($__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5)): ?>
<?php $component = $__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5; ?>
<?php unset($__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5); ?>
<?php endif; ?>
    </div>
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal0ffcbd05bb9b09411b1f1d974598fa25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ffcbd05bb9b09411b1f1d974598fa25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.map-summary','data' => ['title' => 'GIS Coverage','mappedHouseholds' => $overview['mapped_households'] ?? 0,'totalHouseholds' => $overview['total_households'] ?? 0,'coveragePct' => $overview['coverage_pct'] ?? 0,'villagesMapped' => $overview['villages_mapped'] ?? 0,'totalVillages' => $overview['total_villages'] ?? 0,'route' => ''.e(route('gis.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.map-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'GIS Coverage','mappedHouseholds' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overview['mapped_households'] ?? 0),'totalHouseholds' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overview['total_households'] ?? 0),'coveragePct' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overview['coverage_pct'] ?? 0),'villagesMapped' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overview['villages_mapped'] ?? 0),'totalVillages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overview['total_villages'] ?? 0),'route' => ''.e(route('gis.index')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ffcbd05bb9b09411b1f1d974598fa25)): ?>
<?php $attributes = $__attributesOriginal0ffcbd05bb9b09411b1f1d974598fa25; ?>
<?php unset($__attributesOriginal0ffcbd05bb9b09411b1f1d974598fa25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ffcbd05bb9b09411b1f1d974598fa25)): ?>
<?php $component = $__componentOriginal0ffcbd05bb9b09411b1f1d974598fa25; ?>
<?php unset($__componentOriginal0ffcbd05bb9b09411b1f1d974598fa25); ?>
<?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if($dashboardWidgets['population_summary'] ?? true): ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-people me-2 text-primary"></i>Population Analytics
    </h5>
    <a href="<?php echo e(route('analytics.population.index')); ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Population','value' => number_format($populationSummary['total_population'] ?? 0),'icon' => 'bi-people-fill','color' => 'primary','change' => $populationSummary['population_growth'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Population','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationSummary['total_population'] ?? 0)),'icon' => 'bi-people-fill','color' => 'primary','change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($populationSummary['population_growth'] ?? null)]); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Approved','value' => number_format($populationSummary['approved'] ?? 0),'icon' => 'bi-check-circle','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approved','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationSummary['approved'] ?? 0)),'icon' => 'bi-check-circle','color' => 'success']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Pending','value' => number_format($populationSummary['pending'] ?? 0),'icon' => 'bi-hourglass-split','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationSummary['pending'] ?? 0)),'icon' => 'bi-hourglass-split','color' => 'warning']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Today','value' => number_format($populationSummary['registered_today'] ?? 0),'icon' => 'bi-calendar-check','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Today','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationSummary['registered_today'] ?? 0)),'icon' => 'bi-calendar-check','color' => 'info']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'This Month','value' => number_format($populationSummary['registered_this_month'] ?? 0),'icon' => 'bi-calendar-month','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'This Month','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($populationSummary['registered_this_month'] ?? 0)),'icon' => 'bi-calendar-month','color' => 'success']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Avg Age','value' => number_format($averageAge ?? 0, 1),'icon' => 'bi-person-lines-fill','color' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Avg Age','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($averageAge ?? 0, 1)),'icon' => 'bi-person-lines-fill','color' => 'secondary']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Population by Village','icon' => 'bi-bar-chart','color' => 'primary','canvasId' => 'dashVillageChart','height' => '280px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Population by Village','icon' => 'bi-bar-chart','color' => 'primary','canvasId' => 'dashVillageChart','height' => '280px']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginale0172d82feeb27b99b995a02e6ca0457 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0172d82feeb27b99b995a02e6ca0457 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['title' => 'Population Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => $populationInsights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Population Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($populationInsights)]); ?>
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
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Villages by Population','icon' => 'bi-trophy','color' => 'warning','valueLabel' => 'Residents','items' => collect($villageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Villages by Population','icon' => 'bi-trophy','color' => 'warning','valueLabel' => 'Residents','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($villageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray()),'maxItems' => 5]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $attributes = $__attributesOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $component = $__componentOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__componentOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
    </div>
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Gender Distribution','icon' => 'bi-gender-ambiguous','color' => 'info','canvasId' => 'dashGenderChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Gender Distribution','icon' => 'bi-gender-ambiguous','color' => 'info','canvasId' => 'dashGenderChart','height' => '250px']); ?>
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
<?php endif; ?>

<?php if($dashboardWidgets['household_summary'] ?? true): ?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-house-door me-2 text-success"></i>Household Analytics
    </h5>
    <a href="<?php echo e(route('analytics.household.index')); ?>" class="btn btn-sm btn-success">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Households','value' => number_format($householdSummary['total_households'] ?? 0),'icon' => 'bi-house-door-fill','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Households','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['total_households'] ?? 0)),'icon' => 'bi-house-door-fill','color' => 'success']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Approved','value' => number_format($householdSummary['approved'] ?? 0),'icon' => 'bi-check-circle','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approved','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['approved'] ?? 0)),'icon' => 'bi-check-circle','color' => 'success']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Pending','value' => number_format($householdSummary['pending'] ?? 0),'icon' => 'bi-hourglass-split','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['pending'] ?? 0)),'icon' => 'bi-hourglass-split','color' => 'warning']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Avg Size','value' => number_format($householdSummary['average_size'] ?? 0, 2),'icon' => 'bi-people','color' => 'info','subtitle' => 'members']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Avg Size','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['average_size'] ?? 0, 2)),'icon' => 'bi-people','color' => 'info','subtitle' => 'members']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Occupied Lots','value' => number_format($householdSummary['occupied_lots'] ?? 0),'icon' => 'bi-check2-square','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Occupied Lots','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['occupied_lots'] ?? 0)),'icon' => 'bi-check2-square','color' => 'primary']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Available','value' => number_format($householdSummary['available_lots'] ?? 0),'icon' => 'bi-plus-square','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Available','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($householdSummary['available_lots'] ?? 0)),'icon' => 'bi-plus-square','color' => 'warning']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Household Composition','icon' => 'bi-diagram-3','color' => 'warning','canvasId' => 'dashCompositionChart','height' => '260px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Household Composition','icon' => 'bi-diagram-3','color' => 'warning','canvasId' => 'dashCompositionChart','height' => '260px']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Household Size','icon' => 'bi-people','color' => 'success','canvasId' => 'dashHHSizeChart','height' => '260px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Household Size','icon' => 'bi-people','color' => 'success','canvasId' => 'dashHHSizeChart','height' => '260px']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginale0172d82feeb27b99b995a02e6ca0457 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0172d82feeb27b99b995a02e6ca0457 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['title' => 'Household Insights','icon' => 'bi-lightbulb','color' => 'success','insights' => $householdInsights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Household Insights','icon' => 'bi-lightbulb','color' => 'success','insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdInsights)]); ?>
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
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Households by Village','icon' => 'bi-trophy','color' => 'success','valueLabel' => 'Households','items' => collect($householdVillageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Households by Village','icon' => 'bi-trophy','color' => 'success','valueLabel' => 'Households','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($householdVillageComparison ?? [])->map(fn($v) => ['name' => $v['village']?->village_name ?? '', 'value' => number_format($v['total'] ?? 0)])->toArray()),'maxItems' => 5]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $attributes = $__attributesOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $component = $__componentOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__componentOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-success"><i class="bi bi-house-door me-2"></i>Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.demographic.index')); ?>" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-grid me-1"></i>Demographic Dashboard
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.household.index')); ?>" class="btn btn-outline-success w-100 py-2">
                            <i class="bi bi-house-door me-1"></i>Household Analytics
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.population.index')); ?>" class="btn btn-outline-info w-100 py-2">
                            <i class="bi bi-people me-1"></i>Population Analytics
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('gis.index')); ?>" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-map me-1"></i>GIS Map
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($dashboardWidgets['resident_summary'] ?? true): ?>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Education Rate','value' => ($educationSummary['education_rate'] ?? 0) . '%','icon' => 'bi-mortarboard-fill','color' => 'primary','subtitle' => ''.e(number_format($educationSummary['educated'] ?? 0)).' educated']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Education Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($educationSummary['education_rate'] ?? 0) . '%'),'icon' => 'bi-mortarboard-fill','color' => 'primary','subtitle' => ''.e(number_format($educationSummary['educated'] ?? 0)).' educated']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Currently Enrolled','value' => number_format($educationSummary['currently_enrolled'] ?? 0),'icon' => 'bi-book-fill','color' => 'info','subtitle' => ''.e($educationSummary['enrollment_rate'] ?? 0).'%']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Currently Enrolled','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($educationSummary['currently_enrolled'] ?? 0)),'icon' => 'bi-book-fill','color' => 'info','subtitle' => ''.e($educationSummary['enrollment_rate'] ?? 0).'%']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Employment Rate','value' => ($employmentSummary['employment_rate'] ?? 0) . '%','icon' => 'bi-briefcase-fill','color' => 'success','subtitle' => ''.e(number_format($employmentSummary['total_employed'] ?? 0)).' employed']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employment Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($employmentSummary['employment_rate'] ?? 0) . '%'),'icon' => 'bi-briefcase-fill','color' => 'success','subtitle' => ''.e(number_format($employmentSummary['total_employed'] ?? 0)).' employed']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Avg Monthly Income','value' => '₱' . number_format($employmentSummary['average_income'] ?? 0, 0),'icon' => 'bi-cash-stack','color' => 'warning','subtitle' => 'median: ₱'.e(number_format($employmentSummary['median_income'] ?? 0, 0)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Avg Monthly Income','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($employmentSummary['average_income'] ?? 0, 0)),'icon' => 'bi-cash-stack','color' => 'warning','subtitle' => 'median: ₱'.e(number_format($employmentSummary['median_income'] ?? 0, 0)).'']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Scholars','value' => number_format($educationSummary['scholars'] ?? 0),'icon' => 'bi-award-fill','color' => 'warning','subtitle' => ''.e($educationSummary['scholarship_rate'] ?? 0).'% of educated']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Scholars','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($educationSummary['scholars'] ?? 0)),'icon' => 'bi-award-fill','color' => 'warning','subtitle' => ''.e($educationSummary['scholarship_rate'] ?? 0).'% of educated']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'OFWs','value' => number_format($employmentSummary['ofw'] ?? 0),'icon' => 'bi-globe','color' => 'info','subtitle' => ''.e($employmentSummary['ofw_rate'] ?? 0).'% of residents']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'OFWs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($employmentSummary['ofw'] ?? 0)),'icon' => 'bi-globe','color' => 'info','subtitle' => ''.e($employmentSummary['ofw_rate'] ?? 0).'% of residents']); ?>
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
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-mortarboard me-2"></i>Quick Links</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.education.index')); ?>" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-mortarboard me-1"></i>Education
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.employment.index')); ?>" class="btn btn-outline-success w-100 py-2">
                            <i class="bi bi-briefcase me-1"></i>Employment
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.occupation.index')); ?>" class="btn btn-outline-info w-100 py-2">
                            <i class="bi bi-person-badge me-1"></i>Occupation
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('analytics.skills.index')); ?>" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-tools me-1"></i>Skills
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-person-badge me-2 text-info"></i>Occupation Analytics
    </h5>
    <a href="<?php echo e(route('analytics.occupation.index')); ?>" class="btn btn-sm btn-info">
        <i class="bi bi-bar-chart-line me-1"></i>Full Analytics
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Residents','value' => number_format($occupationSummary['total'] ?? 0),'icon' => 'bi-people-fill','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Residents','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($occupationSummary['total'] ?? 0)),'icon' => 'bi-people-fill','color' => 'primary']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'With Occupation','value' => number_format($occupationSummary['with_occupation'] ?? 0),'icon' => 'bi-briefcase-fill','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'With Occupation','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($occupationSummary['with_occupation'] ?? 0)),'icon' => 'bi-briefcase-fill','color' => 'info']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Coverage Rate','value' => ($occupationSummary['coverage_rate'] ?? 0) . '%','icon' => 'bi-check-circle','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Coverage Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($occupationSummary['coverage_rate'] ?? 0) . '%'),'icon' => 'bi-check-circle','color' => 'success']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Top Occupation','value' => $occupationSummary['top_occupation'] ?? '—','icon' => 'bi-trophy','color' => 'warning','subtitle' => ''.e($occupationSummary['top_count'] ?? 0).' residents']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Top Occupation','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($occupationSummary['top_occupation'] ?? '—'),'icon' => 'bi-trophy','color' => 'warning','subtitle' => ''.e($occupationSummary['top_count'] ?? 0).' residents']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Skill Coverage','value' => ($skillsSummary['skill_coverage'] ?? 0) . '%','icon' => 'bi-tools','color' => 'primary','subtitle' => ''.e(number_format($skillsSummary['with_any_skill'] ?? 0)).' skilled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Skill Coverage','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($skillsSummary['skill_coverage'] ?? 0) . '%'),'icon' => 'bi-tools','color' => 'primary','subtitle' => ''.e(number_format($skillsSummary['with_any_skill'] ?? 0)).' skilled']); ?>
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
    <div class="col-xl-2 col-md-4 col-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Avg Income','value' => '₱' . number_format($incomeStats['average'] ?? 0, 0),'icon' => 'bi-cash-stack','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Avg Income','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($incomeStats['average'] ?? 0, 0)),'icon' => 'bi-cash-stack','color' => 'success']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Top Occupations','icon' => 'bi-bar-chart','color' => 'info','canvasId' => 'dashOccChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Top Occupations','icon' => 'bi-bar-chart','color' => 'info','canvasId' => 'dashOccChart','height' => '250px']); ?>
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
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Skills Distribution','icon' => 'bi-tools','color' => 'warning','canvasId' => 'dashSkillChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Skills Distribution','icon' => 'bi-tools','color' => 'warning','canvasId' => 'dashSkillChart','height' => '250px']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Livelihood Distribution','icon' => 'bi-pie-chart','color' => 'success','canvasId' => 'dashLivelihoodChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Livelihood Distribution','icon' => 'bi-pie-chart','color' => 'success','canvasId' => 'dashLivelihoodChart','height' => '250px']); ?>
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
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Top Occupations','icon' => 'bi-trophy','color' => 'info','valueLabel' => 'Residents','items' => collect($occupationTop ?? [])->map(fn($count, $occ) => ['name' => $occ, 'value' => number_format($count)])->take(5)->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Top Occupations','icon' => 'bi-trophy','color' => 'info','valueLabel' => 'Residents','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($occupationTop ?? [])->map(fn($count, $occ) => ['name' => $occ, 'value' => number_format($count)])->take(5)->toArray()),'maxItems' => 5]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $attributes = $__attributesOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__attributesOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ec549346b1bac202822fb3c000f6e04)): ?>
<?php $component = $__componentOriginal7ec549346b1bac202822fb3c000f6e04; ?>
<?php unset($__componentOriginal7ec549346b1bac202822fb3c000f6e04); ?>
<?php endif; ?>
    </div>
</div>


<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h5 class="fw-bold text-gray-800 mb-0">
        <i class="bi bi-people me-2 text-secondary"></i>Population Pyramid
    </h5>
    <a href="<?php echo e(route('analytics.socio-economic.index')); ?>" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Socio-Economic View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Population','value' => number_format($pyramidStats['total'] ?? 0),'icon' => 'bi-people-fill','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Population','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($pyramidStats['total'] ?? 0)),'icon' => 'bi-people-fill','color' => 'primary']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Average Age','value' => number_format($pyramidStats['average_age'] ?? 0, 1),'icon' => 'bi-person-lines-fill','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Average Age','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($pyramidStats['average_age'] ?? 0, 1)),'icon' => 'bi-person-lines-fill','color' => 'info']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Male','value' => number_format($pyramidStats['total_male'] ?? 0),'icon' => 'bi-gender-male','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Male','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($pyramidStats['total_male'] ?? 0)),'icon' => 'bi-gender-male','color' => 'primary']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Female','value' => number_format($pyramidStats['total_female'] ?? 0),'icon' => 'bi-gender-female','color' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Female','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($pyramidStats['total_female'] ?? 0)),'icon' => 'bi-gender-female','color' => 'danger']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Population Pyramid','icon' => 'bi-people','color' => 'secondary','canvasId' => 'dashPyramidChart','height' => '400px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Population Pyramid','icon' => 'bi-people','color' => 'secondary','canvasId' => 'dashPyramidChart','height' => '400px']); ?>
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

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Government & Community Indicators</h5>
    <a href="<?php echo e(route('analytics.community-development.index')); ?>" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Community Development View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Gov. Assistance Beneficiaries','value' => number_format($govAssistanceSummary['with_assistance'] ?? 0),'icon' => 'bi-hands-helping','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Gov. Assistance Beneficiaries','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceSummary['with_assistance'] ?? 0)),'icon' => 'bi-hands-helping','color' => 'warning']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => '4PS Members','value' => number_format($govAssistanceProgramCounts['4Ps'] ?? 0),'icon' => 'bi-house-heart-fill','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => '4PS Members','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceProgramCounts['4Ps'] ?? 0)),'icon' => 'bi-house-heart-fill','color' => 'primary']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Healthcare Covered','value' => number_format($healthcareSummary['philhealth_members'] ?? 0),'icon' => 'bi-heart-pulse-fill','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Healthcare Covered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($healthcareSummary['philhealth_members'] ?? 0)),'icon' => 'bi-heart-pulse-fill','color' => 'success']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Village Development Score','value' => number_format($devScoreData['overall_score'] ?? 0, 1) . '/100','icon' => 'bi-speedometer2','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Village Development Score','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($devScoreData['overall_score'] ?? 0, 1) . '/100'),'icon' => 'bi-speedometer2','color' => 'info']); ?>
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

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'PhilHealth Members','value' => number_format($govMembershipCounts['PhilHealth'] ?? 0),'icon' => 'bi-heart-pulse-fill','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'PhilHealth Members','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govMembershipCounts['PhilHealth'] ?? 0)),'icon' => 'bi-heart-pulse-fill','color' => 'success']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'SSS Members','value' => number_format($govMembershipCounts['SSS'] ?? 0),'icon' => 'bi-person-badge-fill','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'SSS Members','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govMembershipCounts['SSS'] ?? 0)),'icon' => 'bi-person-badge-fill','color' => 'primary']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Pag-IBIG Members','value' => number_format($govMembershipCounts['Pag-IBIG'] ?? 0),'icon' => 'bi-bank-fill','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pag-IBIG Members','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govMembershipCounts['Pag-IBIG'] ?? 0)),'icon' => 'bi-bank-fill','color' => 'warning']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Voter Registration Rate','value' => number_format(($populationSummary['total_population'] ?? 0) > 0 ? (($govMembershipCounts['Registered Voter'] ?? 0) / ($populationSummary['total_population'] ?? 1) * 100) : 0, 1) . '%','icon' => 'bi-check2-square','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Voter Registration Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format(($populationSummary['total_population'] ?? 0) > 0 ? (($govMembershipCounts['Registered Voter'] ?? 0) / ($populationSummary['total_population'] ?? 1) * 100) : 0, 1) . '%'),'icon' => 'bi-check2-square','color' => 'info']); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="card-title mb-0"><i class="bi bi-clipboard-data me-2 text-info"></i>Community Development Indicators</h6>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $communityIndicators ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted"><?php echo e($indicator['label']); ?></span>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px; width: 120px;">
                                <div class="progress-bar bg-<?php echo e($indicator['status'] ?? 'info'); ?>" style="width: <?php echo e($indicator['value'] ?? 0); ?>%"></div>
                            </div>
                            <strong class="text-<?php echo e($indicator['status'] ?? 'info'); ?>"><?php echo e(number_format($indicator['value'] ?? 0, 1)); ?>%</strong>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No development indicators available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Executive Insights</h6>
            </div>
            <div class="card-body">
                <?php $__empty_1 = true; $__currentLoopData = $execInsights ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex align-items-start mb-3">
                        <div class="badge bg-<?php echo e($insight['priority'] ?? 'secondary'); ?> me-2 mt-1">
                            <i class="bi bi-<?php echo e($insight['icon'] ?? 'info-circle'); ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small"><?php echo e($insight['title'] ?? ''); ?></div>
                            <div class="text-muted small"><?php echo e($insight['description'] ?? ''); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No insights available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Government Assistance Summary</h5>
    <a href="<?php echo e(route('analytics.government-assistance.index')); ?>" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Gov. Assistance View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'TUPAD Beneficiaries','value' => number_format($govAssistanceProgramCounts['TUPAD'] ?? 0),'icon' => 'bi-tools','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'TUPAD Beneficiaries','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceProgramCounts['TUPAD'] ?? 0)),'icon' => 'bi-tools','color' => 'primary']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'AKAP Beneficiaries','value' => number_format($govAssistanceProgramCounts['AKAP'] ?? 0),'icon' => 'bi-hand-thumbs-up-fill','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AKAP Beneficiaries','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceProgramCounts['AKAP'] ?? 0)),'icon' => 'bi-hand-thumbs-up-fill','color' => 'success']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Educational Assistance','value' => number_format($govAssistanceProgramCounts['Educational Assistance'] ?? 0),'icon' => 'bi-book-fill','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Educational Assistance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceProgramCounts['Educational Assistance'] ?? 0)),'icon' => 'bi-book-fill','color' => 'info']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'SLP Beneficiaries','value' => number_format($govAssistanceProgramCounts['SLP'] ?? 0),'icon' => 'bi-graph-up-arrow','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'SLP Beneficiaries','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($govAssistanceProgramCounts['SLP'] ?? 0)),'icon' => 'bi-graph-up-arrow','color' => 'warning']); ?>
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

<div class="d-flex align-items-center justify-content-between mb-3 mt-4">
    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Planning & Demographic Indicators</h5>
    <a href="<?php echo e(route('analytics.planning.index')); ?>" class="btn btn-sm btn-secondary">
        <i class="bi bi-clipboard-data me-1"></i>Full Planning View
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Dependency Ratio','value' => number_format($depSummary['total_dependency_ratio'] ?? 0, 1) . '%','icon' => 'bi-people-fill','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dependency Ratio','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($depSummary['total_dependency_ratio'] ?? 0, 1) . '%'),'icon' => 'bi-people-fill','color' => 'warning']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Population Density','value' => number_format($densitySummary['population_density'] ?? 0, 1) . '/km²','icon' => 'bi-geo-alt-fill','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Population Density','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($densitySummary['population_density'] ?? 0, 1) . '/km²'),'icon' => 'bi-geo-alt-fill','color' => 'info']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Annual Growth','value' => number_format($growthSummary['annual_growth'] ?? 0, 1) . '%','icon' => 'bi-graph-up-arrow','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Annual Growth','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($growthSummary['annual_growth'] ?? 0, 1) . '%'),'icon' => 'bi-graph-up-arrow','color' => 'success']); ?>
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
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'HH Occupancy Rate','value' => (($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 0)) > 0 ? number_format(($householdSummary['occupied_lots'] ?? 0) / (($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 1)) * 100, 1) . '%' : '0%','icon' => 'bi-house-door-fill','color' => 'primary','subtitle' => ''.e(number_format($householdSummary['occupied_lots'] ?? 0)).' occupied']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'HH Occupancy Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 0)) > 0 ? number_format(($householdSummary['occupied_lots'] ?? 0) / (($householdSummary['occupied_lots'] ?? 0) + ($householdSummary['available_lots'] ?? 1)) * 100, 1) . '%' : '0%'),'icon' => 'bi-house-door-fill','color' => 'primary','subtitle' => ''.e(number_format($householdSummary['occupied_lots'] ?? 0)).' occupied']); ?>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0">
        <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Planning Insights</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php $__empty_1 = true; $__currentLoopData = $planningInsights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6">
                <div class="d-flex align-items-start">
                    <div class="badge bg-<?php echo e($insight['priority'] ?? 'secondary'); ?> me-2 mt-1"><i class="bi bi-<?php echo e($insight['icon'] ?? 'info-circle'); ?>"></i></div>
                    <div>
                        <div class="fw-semibold small"><?php echo e($insight['title']); ?></div>
                        <div class="text-muted small"><?php echo e($insight['description']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12 text-muted small">No planning insights available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = { primary: '#0d6efd', success: '#198754', danger: '#dc3545', warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d' };
    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, '#6f42c1', '#d63384', '#20c997', '#fd7e14'];

    const villageCtx = document.getElementById('dashVillageChart');
    if (villageCtx) {
        new Chart(villageCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']?->village_name ?? '')->toArray()); ?>,
                datasets: [{
                    label: 'Population',
                    data: <?php echo json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total'])->toArray()); ?>,
                    backgroundColor: palette.slice(0, count(<?php echo json_encode(collect($villageComparison ?? [])->toArray()); ?>)),
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const genderCtx = document.getElementById('dashGenderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($genderDist['labels'] ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($genderDist['values'] ?? []); ?>,
                    backgroundColor: [colors.primary, colors.danger, colors.secondary],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const compCtx = document.getElementById('dashCompositionChart');
    if (compCtx) {
        new Chart(compCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($householdComposition['labels'] ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($householdComposition['values'] ?? []); ?>,
                    backgroundColor: palette.slice(0, <?php echo json_encode(count($householdComposition['labels'] ?? [])); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const hhSizeCtx = document.getElementById('dashHHSizeChart');
    if (hhSizeCtx) {
        new Chart(hhSizeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($householdSizeDist['labels'] ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($householdSizeDist['values'] ?? []); ?>,
                    backgroundColor: palette.slice(0, <?php echo json_encode(count($householdSizeDist['labels'] ?? [])); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const occCtx = document.getElementById('dashOccChart');
    if (occCtx) {
        new Chart(occCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($occupationTop ?? [])); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_values($occupationTop ?? [])); ?>,
                    backgroundColor: '#0dcaf0',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const skillCtx = document.getElementById('dashSkillChart');
    if (skillCtx) {
        new Chart(skillCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($skillTop ?? [])); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_values($skillTop ?? [])); ?>,
                    backgroundColor: '#ffc107',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const liveCtx = document.getElementById('dashLivelihoodChart');
    if (liveCtx) {
        new Chart(liveCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($livelihoodDist ?? [])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($livelihoodDist ?? [], 'count')); ?>,
                    backgroundColor: palette.slice(0, <?php echo json_encode(count($livelihoodDist ?? [])); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const pyramidCtx = document.getElementById('dashPyramidChart');
    if (pyramidCtx) {
        new Chart(pyramidCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($pyramidData['labels'] ?? []); ?>,
                datasets: [
                    { label: 'Male', data: <?php echo json_encode($pyramidData['male'] ?? []); ?>, backgroundColor: '#0d6efd' },
                    { label: 'Female', data: <?php echo json_encode($pyramidData['female'] ?? []); ?>, backgroundColor: '#dc3545' }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { stacked: false } },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    const paymentTrendCtx = document.getElementById('dashPaymentTrendChart');
    if (paymentTrendCtx) {
        const paymentTrendData = <?php echo json_encode($monthlyPaymentTrend ?? []); ?>;
        new Chart(paymentTrendCtx, {
            type: 'line',
            data: {
                labels: Object.keys(paymentTrendData),
                datasets: [{
                    label: 'Collections',
                    data: Object.values(paymentTrendData),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    const participationCtx = document.getElementById('dashParticipationChart');
    if (participationCtx) {
        const participationData = <?php echo json_encode(collect($participationComparison ?? [])->map(fn($p) => ['name' => $p['village']?->village_name ?? '', 'events' => $p['total_events'] ?? 0, 'participants' => $p['total_participants'] ?? 0])->toArray()); ?>;
        new Chart(participationCtx, {
            type: 'bar',
            data: {
                labels: participationData.map(p => p.name),
                datasets: [
                    { label: 'Events', data: participationData.map(p => p.events), backgroundColor: '#0d6efd', borderRadius: 4 },
                    { label: 'Participants', data: participationData.map(p => p.participants), backgroundColor: '#198754', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/dashboard/super-admin.blade.php ENDPATH**/ ?>