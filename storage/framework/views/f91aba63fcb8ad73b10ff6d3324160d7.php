<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($reportTitle ?? 'Report'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .report-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2e59d9; }
        .report-header h1 { font-size: 20px; color: #2e59d9; margin-bottom: 5px; }
        .report-header .subtitle { font-size: 11px; color: #666; }
        .report-header .logo-section { text-align: right; }
        .report-header .logo-section strong { font-size: 13px; color: #2e59d9; display: block; }
        .report-header .logo-section small { color: #888; font-size: 10px; }
        .report-meta { background: #f8f9fc; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; display: flex; gap: 30px; font-size: 10px; color: #555; }
        .report-meta span { display: inline-block; }
        .report-meta strong { color: #333; }
        h2 { font-size: 14px; color: #2e59d9; margin: 18px 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #e0e0e0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 15px 0; font-size: 10px; }
        th { background-color: #2e59d9; color: white; padding: 6px 8px; text-align: left; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background-color: #f8f9fc; }
        .summary-grid { display: flex; gap: 12px; margin: 10px 0 15px 0; flex-wrap: wrap; }
        .summary-box { background: #f8f9fc; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px 15px; min-width: 120px; text-align: center; flex: 1; }
        .summary-box .value { font-size: 20px; font-weight: bold; color: #2e59d9; }
        .summary-box .label { font-size: 9px; color: #888; margin-top: 2px; }
        .report-footer { margin-top: 30px; padding-top: 10px; border-top: 2px solid #e0e0e0; text-align: center; font-size: 9px; color: #999; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no-data { text-align: center; padding: 30px; color: #999; font-style: italic; }
    </style>
</head>
<body>
    <div class="report-header">
        <div>
            <h1><?php echo e($reportTitle ?? 'Report'); ?></h1>
            <div class="subtitle"><?php echo e($villageName ?? 'All Villages'); ?></div>
        </div>
        <div class="logo-section">
            <strong>Multi-Village Management System</strong>
            <small>GIS-Enabled Analytics Platform</small>
        </div>
    </div>

    <div class="report-meta">
        <span><strong>Village:</strong> <?php echo e($villageName ?? 'All Villages'); ?></span>
        <span><strong>Generated:</strong> <?php echo e($generatedAt ?? now()->format('F d, Y h:i A')); ?></span>
        <span><strong>By:</strong> <?php echo e($generatedBy ?? 'System'); ?></span>
    </div>

    <?php if(isset($total)): ?>
    <div class="summary-grid">
        <div class="summary-box">
            <div class="value"><?php echo e(number_format($total)); ?></div>
            <div class="label">Total Records</div>
        </div>
        <?php if(isset($totalOutstanding)): ?>
        <div class="summary-box">
            <div class="value">₱<?php echo e(number_format($totalOutstanding, 2)); ?></div>
            <div class="label">Total Outstanding</div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if(isset($summary) && is_array($summary) && !empty($summary)): ?>
    <h2>Summary</h2>
    <div class="summary-grid">
        <?php $__currentLoopData = array_slice($summary, 0, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!is_array($value)): ?>
            <div class="summary-box">
                <div class="value"><?php echo e(is_numeric($value) ? number_format($value, $value == (int)$value ? 0 : 1) : $value); ?></div>
                <div class="label"><?php echo e(ucwords(str_replace('_', ' ', $key))); ?></div>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if(isset($rows) && is_array($rows) && count($rows) > 0): ?>
    <h2>Details</h2>
    <table>
        <thead>
            <tr>
                <?php $__currentLoopData = array_keys($rows[0]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($header); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($cell ?? ''); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">No data available for this report.</div>
    <?php endif; ?>

    <?php if(isset($insights) && is_array($insights) && count($insights) > 0): ?>
    <h2>Insights</h2>
    <ul>
        <?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><strong><?php echo e(ucfirst($insight['type'] ?? 'Info')); ?>:</strong> <?php echo e($insight['message'] ?? $insight); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <?php endif; ?>

    <div class="report-footer">
        Multi-Village Management System · GIS-Enabled Multi-Village Management ·
        Generated <?php echo e($generatedAt ?? now()->format('F d, Y h:i A')); ?>

    </div>
</body>
</html>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/reports/exports/generic-pdf.blade.php ENDPATH**/ ?>