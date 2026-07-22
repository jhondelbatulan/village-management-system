<?php $__env->startSection('title', 'System Health'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">System Health</h4>
            <p class="page-subtitle text-muted mb-0">Monitor system components and performance.</p>
        </div>
        <div>
            <a href="<?php echo e(route('system-admin.dashboard')); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-database me-2 text-primary"></i>Database</h6>
                    <?php if(($health['database']['status'] ?? '') === 'healthy'): ?>
                        <span class="badge bg-success">Healthy</span>
                    <?php elseif(($health['database']['status'] ?? '') === 'warning'): ?>
                        <span class="badge bg-warning">Warning</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Critical</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Driver</small>
                    <div class="fw-semibold"><?php echo e($health['database']['driver'] ?? '—'); ?></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Latency</small>
                    <div class="fw-semibold"><?php echo e($health['database']['latency_ms'] ?? '—'); ?> ms</div>
                </div>
                <div>
                    <small class="text-muted">Size</small>
                    <div class="fw-semibold"><?php echo e($health['database']['size_formatted'] ?? '—'); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-hdd me-2 text-info"></i>Storage</h6>
                    <?php if(($health['storage']['status'] ?? '') === 'healthy'): ?>
                        <span class="badge bg-success">Healthy</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Warning</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Space</small>
                    <div class="fw-semibold"><?php echo e($health['storage']['total'] ?? '—'); ?></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Used / Free</small>
                    <div class="fw-semibold"><?php echo e($health['storage']['used'] ?? '—'); ?> / <?php echo e($health['storage']['free'] ?? '—'); ?></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">App Storage</small>
                    <div class="fw-semibold"><?php echo e($health['storage']['app_usage'] ?? '—'); ?></div>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-<?php echo e(($health['storage']['usage_percent'] ?? 0) > 80 ? 'danger' : 'success'); ?>"
                         style="width: <?php echo e($health['storage']['usage_percent'] ?? 0); ?>%"></div>
                </div>
                <small class="text-muted"><?php echo e($health['storage']['usage_percent'] ?? 0); ?>% used</small>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-speedometer me-2 text-success"></i>Cache</h6>
                    <?php if(($health['cache']['status'] ?? '') === 'healthy'): ?>
                        <span class="badge bg-success">Operational</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Error</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Driver</small>
                    <div class="fw-semibold"><?php echo e($health['cache']['driver'] ?? '—'); ?></div>
                </div>
                <div>
                    <small class="text-muted">Latency</small>
                    <div class="fw-semibold"><?php echo e($health['cache']['latency_ms'] ?? '—'); ?> ms</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-grid me-2 text-warning"></i>Session</h6>
                    <span class="badge bg-info">Info</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Driver</small>
                    <div class="fw-semibold"><?php echo e($health['session']['driver'] ?? '—'); ?></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Lifetime</small>
                    <div class="fw-semibold"><?php echo e($health['session']['lifetime'] ?? '—'); ?> min</div>
                </div>
                <div>
                    <small class="text-muted">Encrypted</small>
                    <div class="fw-semibold"><?php echo e(($health['session']['encrypt'] ?? false) ? 'Yes' : 'No'); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-task me-2 text-secondary"></i>Queue</h6>
                    <?php if(($health['queue']['status'] ?? '') === 'healthy'): ?>
                        <span class="badge bg-success">Healthy</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Error</span>
                    <?php endif; ?>
                </div>
                <div>
                    <small class="text-muted">Driver</small>
                    <div class="fw-semibold"><?php echo e($health['queue']['driver'] ?? '—'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Versions</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Laravel</span>
                    <span class="fw-semibold"><?php echo e($health['versions']['laravel'] ?? '—'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">PHP</span>
                    <span class="fw-semibold"><?php echo e($health['versions']['php'] ?? '—'); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Database</span>
                    <span class="fw-semibold"><?php echo e($health['versions']['database'] ?? '—'); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-pc-display me-2 text-info"></i>Server</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">OS</span>
                    <span class="fw-semibold"><?php echo e($health['server']['os'] ?? '—'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Max Execution Time</span>
                    <span class="fw-semibold"><?php echo e($health['server']['max_execution_time'] ?? '—'); ?>s</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Memory Limit</span>
                    <span class="fw-semibold"><?php echo e($health['server']['memory_limit'] ?? '—'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Upload Max Filesize</span>
                    <span class="fw-semibold"><?php echo e($health['server']['upload_max_filesize'] ?? '—'); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Post Max Size</span>
                    <span class="fw-semibold"><?php echo e($health['server']['post_max_size'] ?? '—'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/system-admin/health.blade.php ENDPATH**/ ?>