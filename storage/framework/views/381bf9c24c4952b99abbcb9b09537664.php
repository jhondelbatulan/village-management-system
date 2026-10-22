<?php $__env->startSection('title', 'My Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">Welcome, <?php echo e($user->name ?? 'Resident'); ?></h1>
        <p class="text-muted mb-0"><?php echo e($village->village_name ?? 'Your Village'); ?> · <?php echo e(now()->format('F d, Y')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Reports
        </a>
        <a href="<?php echo e(route('announcements.index')); ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-megaphone me-1"></i>Announcements
        </a>
    </div>
</div>

<?php echo $__env->make('partials.village-photos-slideshow', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row g-4 mb-4">
    <div class="col-xl-6 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Outstanding Balance','value' => '₱'.number_format($balance['outstanding'] ?? 0, 2),'icon' => 'bi-wallet2','color' => 'danger','subtitle' => 'Unpaid amount','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Outstanding Balance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱'.number_format($balance['outstanding'] ?? 0, 2)),'icon' => 'bi-wallet2','color' => 'danger','subtitle' => 'Unpaid amount','large' => true]); ?>
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
    <div class="col-xl-6 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Paid','value' => '₱'.number_format($balance['paid'] ?? 0, 2),'icon' => 'bi-check-circle','color' => 'success','subtitle' => 'All time payments','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Paid','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱'.number_format($balance['paid'] ?? 0, 2)),'icon' => 'bi-check-circle','color' => 'success','subtitle' => 'All time payments','large' => true]); ?>
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

<?php if($dashboardWidgets['announcements'] ?? true || $dashboardWidgets['calendar'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal7ec549346b1bac202822fb3c000f6e04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ec549346b1bac202822fb3c000f6e04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Recent Announcements','icon' => 'bi-megaphone','color' => 'primary','valueLabel' => 'Date','items' => $recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d'), 'url' => route('announcements.show', $a)])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Announcements','icon' => 'bi-megaphone','color' => 'primary','valueLabel' => 'Date','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d'), 'url' => route('announcements.show', $a)])->toArray()),'maxItems' => 5]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.ranking-card','data' => ['title' => 'Upcoming Events','icon' => 'bi-calendar-event','color' => 'success','valueLabel' => 'Date','items' => $upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d'), 'url' => route('events.show', $e)])->toArray(),'maxItems' => 5]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.ranking-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Upcoming Events','icon' => 'bi-calendar-event','color' => 'success','valueLabel' => 'Date','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d'), 'url' => route('events.show', $e)])->toArray()),'maxItems' => 5]); ?>
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-house-door me-2"></i>My Household
                </h6>
                    <a href="<?php echo e(route('households.my-household')); ?>" class="btn btn-sm btn-outline-primary">View</a>
            </div>
            <div class="card-body">
                <?php if($household): ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-primary-soft rounded p-3 text-center">
                                <div class="small text-muted">Block / Lot</div>
                                <div class="fw-bold"><?php echo e($household->block_number); ?> / <?php echo e($household->lot_number); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-success-soft rounded p-3 text-center">
                                <div class="small text-muted">Family Count</div>
                                <div class="fw-bold text-capitalize"><?php echo e($household->household_type ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="small text-muted mb-1">Household Members</div>
                                <div class="fw-bold"><?php echo e($household->member_count); ?> residents</div>
                                <small class="text-muted">Head: <?php echo e($household->household_head_name ?? 'N/A'); ?></small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-house-add fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No household assigned yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-cash me-2"></i>Recent Payments
                </h6>
                <?php if(isset($household) && $household): ?>
                    <a href="<?php echo e(route('household-payments.show', $household)); ?>" class="btn btn-sm btn-outline-success">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if(isset($household) && $household): ?>
                    <?php
                        $recentPayments = \App\Models\HouseholdPayment::with('paymentType')
                            ->where('household_id', $household->id)
                            ->latest()->take(5)->get();
                    ?>
                    <?php if($recentPayments->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <div>
                                        <div class="small fw-semibold"><?php echo e($payment->paymentType?->payment_name ?? 'Payment'); ?></div>
                                        <small class="text-muted"><?php echo e($payment->payment_date->format('M d, Y')); ?></small>
                                    </div>
                                    <span class="fw-bold text-success">₱<?php echo e(number_format($payment->amount, 2)); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-inbox fs-1 text-gray-300"></i>
                            <p class="text-muted small mt-2 mb-0">No payments recorded</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-inbox fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No household payments</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if($dashboardWidgets['recent_activities'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <?php if (isset($component)) { $__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.recent-activity','data' => ['title' => 'My Recent Activity','icon' => 'bi-activity','color' => 'primary','activities' => $recentActivity,'maxItems' => 10]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.recent-activity'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Recent Activity','icon' => 'bi-activity','color' => 'primary','activities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentActivity),'maxItems' => 10]); ?>
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
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/dashboard/resident.blade.php ENDPATH**/ ?>