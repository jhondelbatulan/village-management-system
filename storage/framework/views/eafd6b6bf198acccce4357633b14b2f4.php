<?php $__env->startSection('title', 'Treasurer Dashboard — ' . ($village->village_name ?? 'Village')); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800"><?php echo e($village->village_name ?? 'Village'); ?> Treasurer Dashboard</h1>
        <p class="text-muted mb-0">Financial overview · <?php echo e(now()->format('F d, Y')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('billings.index')); ?>" class="btn btn-sm btn-outline-success">
            <i class="bi bi-receipt me-1"></i>Billings
        </a>
        <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-sm btn-outline-info">
            <i class="bi bi-cash me-1"></i>Payments
        </a>
    </div>
</div>

<?php echo $__env->make('partials.village-photos-slideshow', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <?php if (isset($component)) { $__componentOriginal81cd247fb7bb4bcc092b56aeeb65006c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81cd247fb7bb4bcc092b56aeeb65006c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Collected This Month','value' => '₱'.number_format($paymentStats['collected'] ?? 0, 2),'icon' => 'bi-cash-stack','color' => 'success','previousValue' => $paymentStats['previous'] ?? null,'change' => $paymentStats['change'] ?? null,'subtitle' => 'Total collections','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collected This Month','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱'.number_format($paymentStats['collected'] ?? 0, 2)),'icon' => 'bi-cash-stack','color' => 'success','previousValue' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['previous'] ?? null),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['change'] ?? null),'subtitle' => 'Total collections','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Outstanding Balance','value' => '₱'.number_format($paymentStats['outstanding'] ?? 0, 2),'icon' => 'bi-exclamation-triangle','color' => 'danger','subtitle' => 'Unpaid billings','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Outstanding Balance','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱'.number_format($paymentStats['outstanding'] ?? 0, 2)),'icon' => 'bi-exclamation-triangle','color' => 'danger','subtitle' => 'Unpaid billings','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Total Billings','value' => number_format($paymentStats['total_billings'] ?? 0),'icon' => 'bi-receipt','color' => 'primary','subtitle' => 'All billing records','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Billings','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($paymentStats['total_billings'] ?? 0)),'icon' => 'bi-receipt','color' => 'primary','subtitle' => 'All billing records','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.summary-card','data' => ['title' => 'Collection Rate','value' => ($paymentStats['collection_rate'] ?? 0).'%','icon' => 'bi-graph-up-arrow','color' => 'info','subtitle' => 'Of total billings','large' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.summary-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collection Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($paymentStats['collection_rate'] ?? 0).'%'),'icon' => 'bi-graph-up-arrow','color' => 'info','subtitle' => 'Of total billings','large' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Monthly Collection Trend','icon' => 'bi-graph-up','color' => 'success','canvasId' => 'treasPaymentTrendChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Monthly Collection Trend','icon' => 'bi-graph-up','color' => 'success','canvasId' => 'treasPaymentTrendChart','height' => '250px']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.quick-insights','data' => ['title' => 'Financial Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => $insights]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.quick-insights'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Financial Insights','icon' => 'bi-lightbulb','color' => 'info','insights' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insights)]); ?>
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

<?php if($dashboardWidgets['recent_activities'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal00ee5f815e4606cfdb60d04f3d02a82d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00ee5f815e4606cfdb60d04f3d02a82d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.recent-activity','data' => ['title' => 'Recent Activity','icon' => 'bi-activity','color' => 'success','activities' => $recentActivity,'maxItems' => 10]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.recent-activity'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Activity','icon' => 'bi-activity','color' => 'success','activities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recentActivity),'maxItems' => 10]); ?>
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
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-cash-stack me-2"></i>Recent Payments
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if($recentPayments->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Household</th>
                                    <th class="small">Amount</th>
                                    <th class="small">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="small"><?php echo e($payment->household->household_head_name ?? 'N/A'); ?></td>
                                        <td class="small fw-bold text-success">₱<?php echo e(number_format($payment->amount, 2)); ?></td>
                                        <td class="small text-muted"><?php echo e($payment->payment_date->format('M d')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No recent payments</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($dashboardWidgets['analytics_summary'] ?? true): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php if (isset($component)) { $__componentOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a20514bdbc5bce8d21b8bc5f257bfd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.progress-card','data' => ['title' => 'Collection Rate','value' => $paymentStats['collection_rate'] ?? 0,'max' => 100,'color' => 'success','icon' => 'bi-graph-up-arrow','label' => 'of total billings collected this month']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.progress-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Collection Rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paymentStats['collection_rate'] ?? 0),'max' => 100,'color' => 'success','icon' => 'bi-graph-up-arrow','label' => 'of total billings collected this month']); ?>
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
        <?php if (isset($component)) { $__componentOriginal3c4118fba45727bba6046c46969dd6d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c4118fba45727bba6046c46969dd6d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.analytics.chart-card','data' => ['title' => 'Revenue by Payment Type','icon' => 'bi-pie-chart','color' => 'info','canvasId' => 'treasRevenueTypeChart','height' => '250px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('analytics.chart-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Revenue by Payment Type','icon' => 'bi-pie-chart','color' => 'info','canvasId' => 'treasRevenueTypeChart','height' => '250px']); ?>
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

<?php
    $predictionStats = app(\App\Services\Analytics\PredictionAnalyticsService::class)->getVillageStats($village->id);
    $behaviorService = app(\App\Services\Analytics\PaymentBehaviorService::class);
?>

<?php if($dashboardWidgets['analytics_summary'] ?? true && $predictionStats['total_households'] > 0): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary bg-opacity-10">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-graph-up-arrow me-2"></i>Payment Prediction Analytics
                </h6>
                <a href="<?php echo e(route('analytics.predictive-payment.index', ['village_id' => $village->id])); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-bar-chart me-1"></i>Full Analysis
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-success"><?php echo e($predictionStats['distribution']['excellent']['count'] ?? 0); ?></div>
                        <div class="small text-muted">Excellent Payers</div>
                        <div class="small text-success"><i class="bi bi-check-circle-fill me-1"></i>Score 85+</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-primary"><?php echo e($predictionStats['distribution']['good']['count'] ?? 0); ?></div>
                        <div class="small text-muted">Good Payers</div>
                        <div class="small text-primary"><i class="bi bi-check-circle me-1"></i>Score 70-84</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-warning"><?php echo e($predictionStats['distribution']['moderate']['count'] ?? 0); ?></div>
                        <div class="small text-muted">Moderate Payers</div>
                        <div class="small text-warning"><i class="bi bi-exclamation-circle me-1"></i>Score 50-69</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-danger"><?php echo e($predictionStats['distribution']['at_risk']['count'] ?? 0); ?></div>
                        <div class="small text-muted">At-Risk Payers</div>
                        <div class="small text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Score 30-49</div>
                    </div>
                    <div class="col text-center p-2 rounded border">
                        <div class="fs-4 fw-bold text-dark"><?php echo e($predictionStats['distribution']['high_risk']['count'] ?? 0); ?></div>
                        <div class="small text-muted">High-Risk Payers</div>
                        <div class="small text-dark"><i class="bi bi-x-circle-fill me-1"></i>Score &lt;30</div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-speedometer2 text-primary me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">Avg. Score: <?php echo e($predictionStats['avg_score']); ?>/100</div>
                                <small class="text-muted">Village reliability average</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-clock-history text-warning me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">On-Time: <?php echo e($predictionStats['on_time_rate']); ?>%</div>
                                <small class="text-muted">Payment punctuality rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 rounded bg-light">
                            <i class="bi bi-exclamation-triangle text-danger me-2 fs-5"></i>
                            <div>
                                <div class="fw-bold">Follow-Up: <?php echo e(($predictionStats['high_risk_count'] + $predictionStats['at_risk_count'])); ?> households</div>
                                <small class="text-muted">Requires Treasurer attention</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentTrendCtx = document.getElementById('treasPaymentTrendChart');
    if (paymentTrendCtx) {
        const trendData = <?php echo json_encode($monthlyPaymentTrend ?? []); ?>;
        new Chart(paymentTrendCtx, {
            type: 'line',
            data: {
                labels: Object.keys(trendData),
                datasets: [{
                    label: 'Collections',
                    data: Object.values(trendData),
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

    const revenueCtx = document.getElementById('treasRevenueTypeChart');
    if (revenueCtx) {
        const revenueData = <?php echo json_encode($revenueByType ?? []); ?>;
        const palette = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', '#6c757d', '#6f42c1', '#d63384', '#20c997', '#fd7e14'];
        new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(revenueData),
                datasets: [{
                    data: Object.values(revenueData),
                    backgroundColor: palette.slice(0, Object.keys(revenueData).length),
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
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/dashboard/treasurer.blade.php ENDPATH**/ ?>