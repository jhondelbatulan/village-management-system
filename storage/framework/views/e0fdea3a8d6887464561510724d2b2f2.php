<?php $__env->startSection('title', 'Report Center'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>Report Center
        </h1>
        <p class="text-muted mb-0">Generate, export, and download comprehensive system reports</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('reports.history')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i>History
        </a>
        <?php if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin'])): ?>
        <a href="<?php echo e(route('reports.schedule')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar-check me-1"></i>Schedules
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary-soft rounded-circle p-3 me-3"><i class="bi bi-file-earmark-text fs-4 text-primary"></i></div>
                <div><div class="fw-bold fs-5"><?php echo e(count($allowedTypes)); ?></div><div class="text-muted small">Available Reports</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success-soft rounded-circle p-3 me-3"><i class="bi bi-clock-history fs-4 text-success"></i></div>
                <div><div class="fw-bold fs-5"><?php echo e($recentReports->count()); ?></div><div class="text-muted small">Recent Reports</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="bg-info-soft rounded-circle p-3 me-3"><i class="bi bi-geo-alt fs-4 text-info"></i></div>
                <div><div class="fw-bold fs-5"><?php echo e($villages->count()); ?></div><div class="text-muted small">Villages</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning-soft rounded-circle p-3 me-3"><i class="bi bi-download fs-4 text-warning"></i></div>
                <div><div class="fw-bold fs-5">3</div><div class="text-muted small">Export Formats</div></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-gear me-2"></i>Quick Generate</h6>
    </div>
    <div class="card-body">
        <form action="<?php echo e(route('reports.generate')); ?>" method="POST" id="reportForm">
            <?php echo csrf_field(); ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Report Type *</label>
                    <select name="report_type" class="form-select" required id="reportType">
                        <option value="">Select report type...</option>
                        <?php $__currentLoopData = $filteredCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <optgroup label="<?php echo e($cat['label']); ?>">
                                <?php $__currentLoopData = $cat['reports']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($type['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Village</label>
                    <select name="village_id" class="form-select" id="villageSelect">
                        <option value="">All Villages</option>
                        <?php $__currentLoopData = $villages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $village): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($village->id); ?>"><?php echo e($village->village_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold">Format *</label>
                    <div class="d-flex flex-column gap-1">
                        <div class="form-check"><input class="form-check-input" type="radio" name="format" value="pdf" checked id="fmtPdf"><label class="form-check-label small" for="fmtPdf"><i class="bi bi-filetype-pdf text-danger"></i> PDF</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="format" value="csv" id="fmtCsv"><label class="form-check-label small" for="fmtCsv"><i class="bi bi-filetype-csv text-success"></i> CSV</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="format" value="excel" id="fmtExcel"><label class="form-check-label small" for="fmtExcel"><i class="bi bi-file-earmark-excel text-success"></i> Excel</label></div>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-download me-1"></i>Generate</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="openPrintView()" title="Print Preview"><i class="bi bi-printer"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $__currentLoopData = $filteredCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-4">
    <h5 class="fw-bold text-gray-800 mb-3">
        <?php switch($catKey):
            case ('resident'): ?> <i class="bi bi-people me-2 text-primary"></i> <?php break; ?>
            <?php case ('household'): ?> <i class="bi bi-house-door me-2 text-success"></i> <?php break; ?>
            <?php case ('payment'): ?> <i class="bi bi-cash-stack me-2 text-warning"></i> <?php break; ?>
            <?php case ('participation'): ?> <i class="bi bi-people-fill me-2 text-info"></i> <?php break; ?>
            <?php case ('announcement'): ?> <i class="bi bi-megaphone me-2 text-secondary"></i> <?php break; ?>
            <?php case ('gis'): ?> <i class="bi bi-map me-2 text-info"></i> <?php break; ?>
            <?php case ('analytics'): ?> <i class="bi bi-graph-up me-2 text-primary"></i> <?php break; ?>
            <?php case ('executive'): ?> <i class="bi bi-file-earmark-text me-2 text-danger"></i> <?php break; ?>
        <?php endswitch; ?>
        <?php echo e($cat['label']); ?>

    </h5>
    <div class="row g-3">
        <?php $__currentLoopData = $cat['reports']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-left border-<?php echo e($type['color']); ?>" style="border-left-width: 4px !important;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-<?php echo e($type['color']); ?>-soft rounded p-2 me-2">
                            <i class="bi <?php echo e($type['icon']); ?> text-<?php echo e($type['color']); ?>"></i>
                        </div>
                        <h6 class="mb-0 fw-bold small"><?php echo e($type['name']); ?></h6>
                    </div>
                    <p class="text-muted small mb-2" style="font-size: 11px;"><?php echo e($type['description']); ?></p>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-<?php echo e($type['color']); ?> flex-grow-1" onclick="generateReport('<?php echo e($key); ?>', 'pdf')">
                            <i class="bi bi-filetype-pdf me-1"></i>PDF
                        </button>
                        <button class="btn btn-sm btn-outline-<?php echo e($type['color']); ?>" onclick="generateReport('<?php echo e($key); ?>', 'csv')" title="CSV">
                            <i class="bi bi-filetype-csv"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-<?php echo e($type['color']); ?>" onclick="generateReport('<?php echo e($key); ?>', 'excel')" title="Excel">
                            <i class="bi bi-file-earmark-excel"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="printReport('<?php echo e($key); ?>')" title="Print Preview">
                            <i class="bi bi-printer"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($recentReports->count() > 0): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Recent Reports</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Report</th><th>Format</th><th>Village</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $recentReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><i class="bi bi-file-earmark-text me-1 text-primary"></i><?php echo e($report->report_name); ?></td>
                    <td><span class="badge bg-<?php echo e($report->format === 'pdf' ? 'danger' : ($report->format === 'csv' ? 'success' : 'info')); ?>"><?php echo e(strtoupper($report->format)); ?></span></td>
                    <td><?php echo e($report->village?->village_name ?? 'All'); ?></td>
                    <td class="small text-muted"><?php echo e($report->created_at->format('M d, Y h:i A')); ?></td>
                    <td>
                        <?php if($report->file_path && file_exists($report->file_path)): ?>
                        <a href="<?php echo e(route('reports.generate', ['report_type' => $report->report_type, 'format' => $report->format, 'village_id' => $report->village_id])); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function generateReport(type, format) {
    const form = document.getElementById('reportForm');
    document.getElementById('reportType').value = type;
    document.querySelector('input[name="format"][value="' + format + '"]').checked = true;
    form.submit();
}

function printReport(type) {
    const villageId = document.getElementById('villageSelect').value;
    let url = '<?php echo e(route("reports.print")); ?>?report_type=' + type;
    if (villageId) url += '&village_id=' + villageId;
    window.open(url, '_blank');
}

function openPrintView() {
    const type = document.getElementById('reportType').value;
    if (!type) { alert('Please select a report type first.'); return; }
    printReport(type);
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/reports/index.blade.php ENDPATH**/ ?>