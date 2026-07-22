<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-profile text-center">
        <div class="profile-avatar mx-auto mb-3">
            <img src="<?php echo e($authUser['profile_image'] ?? asset(config('app_settings.assets.default_profile'))); ?>"
                 alt="Profile Picture"
                 class="rounded-circle sidebar-logo">
        </div>
        <p class="profile-welcome mb-1">Welcome,</p>
        <h6 class="profile-name mb-1"><?php echo e($authUser['name'] ?? 'User'); ?></h6>
        <span class="badge profile-role"><?php echo e($roleLabel); ?></span>
        <a href="<?php echo e(route('profile.edit')); ?>" class="btn btn-sm btn-outline-light mt-3 w-100">
            <i class="bi bi-pencil-square me-1"></i> Edit Profile
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalce95f69c1ef890487f9ea684119db87d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce95f69c1ef890487f9ea684119db87d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.menu-item','data' => ['item' => $item,'menuService' => $menuService]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['item' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item),'menuService' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuService)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce95f69c1ef890487f9ea684119db87d)): ?>
<?php $attributes = $__attributesOriginalce95f69c1ef890487f9ea684119db87d; ?>
<?php unset($__attributesOriginalce95f69c1ef890487f9ea684119db87d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce95f69c1ef890487f9ea684119db87d)): ?>
<?php $component = $__componentOriginalce95f69c1ef890487f9ea684119db87d; ?>
<?php unset($__componentOriginalce95f69c1ef890487f9ea684119db87d); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </nav>
</aside>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>