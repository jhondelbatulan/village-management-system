<?php $__env->startSection('title', 'Admin Dashboard — ' . ($village->village_name ?? 'Village')); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800"><?php echo e($village->village_name ?? 'Village'); ?> Dashboard</h1>
        <p class="text-muted mb-0">Admin overview · <?php echo e(now()->format('F d, Y')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('households.index')); ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-house-door me-1"></i>Households
        </a>
        <a href="<?php echo e(route('residents.index')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i>Residents
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Collection This Month','value' => number_format($paymentStats['collected'] ?? 0),'icon' => 'bi-cash-stack','color' => 'info','previousValue' => $paymentStats['previous'] ?? null,'change' => $paymentStats['change'] ?? null,'subtitle' => 'Collected this month','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collection This Month','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($paymentStats['collected'] ?? 0)),'icon' => 'bi-cash-stack','color' => 'info','previousValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['previous'] ?? null),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['change'] ?? null),'subtitle' => 'Collected this month','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Pending Approvals','value' => number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0)),'icon' => 'bi-hourglass-split','color' => 'warning','subtitle' => 'Awaiting your action','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending Approvals','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format(($approvalStats['pending_households'] ?? 0) + ($approvalStats['pending_residents'] ?? 0))),'icon' => 'bi-hourglass-split','color' => 'warning','subtitle' => 'Awaiting your action','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Resident Age Distribution','icon' => 'bi-bar-chart','color' => 'primary','canvasId' => 'adminAgeDistChart','height' => '280px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Resident Age Distribution','icon' => 'bi-bar-chart','color' => 'primary','canvasId' => 'adminAgeDistChart','height' => '280px']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['title' => 'Village Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => $insights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Village Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insights)]); ?>
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
<?php endif; ?>

<?php if($dashboardWidgets['recent_activities'] ?? true || $dashboardWidgets['system_notices'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.recent-activity','data' => ['title' => 'Recent Village Activity','icon' => 'bi-activity','color' => 'primary','activities' => $recentActivity,'maxItems' => 10]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.recent-activity'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Village Activity','icon' => 'bi-activity','color' => 'primary','activities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentActivity),'maxItems' => 10]); ?>
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
                                    <br><small class="text-muted">Block <?php echo e($household->block_number); ?></small>
                                </div>
                                <span class="badge bg-warning">Pending</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $pendingResidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2 border-start border-info border-3">
                                <div>
                                    <span class="fw-semibold small">Resident: <?php echo e($resident->full_name); ?></span>
                                    <br><small class="text-muted">Household: <?php echo e($resident->household?->lot_number ?? 'N/A'); ?></small>
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

<?php if($dashboardWidgets['payment_summary'] ?? true || $dashboardWidgets['participation_summary'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.progress-card','data' => ['title' => 'Collection Rate','value' => $paymentStats['collection_rate'] ?? 0,'max' => 100,'color' => 'success','icon' => 'bi-graph-up-arrow','label' => 'of outstanding amount collected']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.progress-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collection Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['collection_rate'] ?? 0),'max' => 100,'color' => 'success','icon' => 'bi-graph-up-arrow','label' => 'of outstanding amount collected']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.progress-card','data' => ['title' => 'Approval Rate','value' => $approvalStats['approval_rate'] ?? 0,'max' => 100,'color' => 'info','icon' => 'bi-check2-circle','label' => 'households and residents approved']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.progress-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approval Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($approvalStats['approval_rate'] ?? 0),'max' => 100,'color' => 'info','icon' => 'bi-check2-circle','label' => 'households and residents approved']); ?>
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
    <div class="col-lg-4">
        <?php if (isset($component)) { $__componentOriginal0ffcbd05bb9b09411b1f1d974598fa25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ffcbd05bb9b09411b1f1d974598fa25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.map-summary','data' => ['title' => 'GIS Coverage','mappedHouseholds' => $householdStats['mapped_households'] ?? 0,'totalHouseholds' => $householdStats['active'] ?? 0,'coveragePct' => $householdStats['coverage_pct'] ?? 0,'villagesMapped' => 1,'totalVillages' => 1,'route' => ''.e(route('gis.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.map-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'GIS Coverage','mappedHouseholds' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdStats['mapped_households'] ?? 0),'totalHouseholds' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdStats['active'] ?? 0),'coveragePct' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($householdStats['coverage_pct'] ?? 0),'villagesMapped' => 1,'totalVillages' => 1,'route' => ''.e(route('gis.index')).'']); ?>
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

<?php if($dashboardWidgets['announcements'] ?? true || $dashboardWidgets['calendar'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Recent Announcements','icon' => 'bi-megaphone','color' => 'primary','valueLabel' => 'Date','items' => $recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d')])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Announcements','icon' => 'bi-megaphone','color' => 'primary','valueLabel' => 'Date','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d')])->toArray()),'maxItems' => 5]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Upcoming Events','icon' => 'bi-calendar-event','color' => 'success','valueLabel' => 'Date','items' => $upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d')])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Upcoming Events','icon' => 'bi-calendar-event','color' => 'success','valueLabel' => 'Date','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d')])->toArray()),'maxItems' => 5]); ?>
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

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ageCtx = document.getElementById('adminAgeDistChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($ageDistribution['labels'] ?? []); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode($ageDistribution['values'] ?? []); ?>,
                    backgroundColor: ['#dc3545', '#ffc107', '#198754', '#0d6efd', '#0dcaf0', '#6f42c1', '#6c757d'],
                    borderRadius: 6
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
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/dashboard/admin.blade.php ENDPATH**/ ?>