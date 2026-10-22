<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['item', 'menuService', 'isChild' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['item', 'menuService', 'isChild' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $hasChildren = !empty($item['children']);
    $isActive = $menuService->isActive($item);
    $route = $item['route'] ?? '#';
    $routeParams = $item['route_params'] ?? [];
    $isUnresolved = str_contains($route, '__');
    $url = ($route !== '#' && ! $isUnresolved) ? route($route, $routeParams) : '#';
    $itemId = 'menu-' . \Illuminate\Support\Str::slug($item['label']);
?>

<li class="nav-item <?php echo e($hasChildren ? 'has-submenu' : ''); ?>">
    <?php if($hasChildren): ?>
        <a class="nav-link submenu-toggle <?php echo e($isActive ? 'active' : ''); ?>"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#<?php echo e($itemId); ?>"
           aria-expanded="<?php echo e($isActive ? 'true' : 'false'); ?>"
           aria-controls="<?php echo e($itemId); ?>">
            <i class="bi <?php echo e($item['icon'] ?? 'bi-circle'); ?> me-2"></i>
            <span><?php echo e($item['label']); ?></span>
            <i class="bi bi-chevron-down submenu-arrow ms-auto"></i>
        </a>
        <div class="collapse <?php echo e($isActive ? 'show' : ''); ?>" id="<?php echo e($itemId); ?>">
            <ul class="nav flex-column submenu">
                <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalce95f69c1ef890487f9ea684119db87d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce95f69c1ef890487f9ea684119db87d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.menu-item','data' => ['item' => $child,'menuService' => $menuService,'isChild' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('menu-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['item' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($child),'menuService' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menuService),'isChild' => true]); ?>
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
        </div>
    <?php else: ?>
        <a class="nav-link <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($isChild ? 'submenu-link' : ''); ?>"
           href="<?php echo e($url); ?>">
            <i class="bi <?php echo e($item['icon'] ?? 'bi-circle'); ?> me-2"></i>
            <span><?php echo e($item['label']); ?></span>
        </a>
    <?php endif; ?>
</li>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/components/menu-item.blade.php ENDPATH**/ ?>