<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php $branding = app(\App\Services\BrandingService::class); ?>
    <title><?php echo $__env->yieldContent('title', 'Login'); ?> — <?php echo e($branding->get('system_name', config('app_settings.name'))); ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo e($branding->getFaviconUrl()); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/auth.css')); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="auth-body">
    <?php echo $__env->yieldContent('content'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/layouts/auth.blade.php ENDPATH**/ ?>