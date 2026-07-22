<?php
    $pinnedSessions = app(\App\Services\SessionManager::class)->getPinnedSessions(auth()->id());
    $isSwitched = app(\App\Services\SessionManager::class)->isSwitched();
?>

<header class="app-navbar">
    <div class="navbar-left">
        <button class="btn btn-link sidebar-toggle d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="navbar-brand-info d-flex align-items-center">
            <img src="<?php echo e($branding->getLogoUrl()); ?>" alt="Logo" class="navbar-logo me-2" style="height:32px;width:32px;object-fit:contain;border-radius:6px;">
            <div>
                <h5 class="navbar-system-name mb-0"><?php echo e($systemName); ?></h5>
                <small class="text-muted d-none d-md-inline"><?php echo e($branding->get('system_tagline', config('app_settings.tagline'))); ?></small>
            </div>
        </div>
    </div>

    <div class="navbar-right">
        <div class="navbar-date d-none d-md-flex align-items-center me-3">
            <i class="bi bi-calendar3 me-2"></i>
            <span id="currentDate"><?php echo e(now()->format('F d, Y')); ?></span>
            <span class="mx-2 text-muted">|</span>
            <i class="bi bi-clock me-1"></i>
            <span id="currentTime" class="fw-semibold"></span>
        </div>

        <?php if($isSwitched): ?>
        <form action="<?php echo e(route('sessions.back')); ?>" method="POST" class="me-2">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-warning btn-sm" title="Switch Back to Original Account">
                <i class="bi bi-arrow-return-left me-1"></i><span class="d-none d-md-inline">Back</span>
            </button>
        </form>
        <?php endif; ?>

        <?php if($pinnedSessions->count() > 0): ?>
        <div class="dropdown me-2">
            <button class="btn btn-link position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Quick Switch Account">
                <i class="bi bi-arrow-repeat fs-5"></i>
                <?php if($isSwitched): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning" style="font-size: 0.5rem;">
                        <i class="bi bi-person-check"></i>
                    </span>
                <?php endif; ?>
            </button>
            <?php echo $__env->make('sessions._quick-switch', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php endif; ?>

        <div class="dropdown me-2" id="notificationDropdown">
            <button class="btn btn-link position-relative notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell fs-5"></i>
                <span class="notification-badge badge rounded-pill bg-danger" id="notificationBadge" style="display:none;">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" id="notificationMenu" style="min-width:320px;max-height:400px;overflow-y:auto;">
                <li>
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <h6 class="mb-0">Notifications</h6>
                        <button class="btn btn-sm btn-link text-decoration-none" id="markAllRead" style="display:none;">Mark all read</button>
                    </div>
                </li>
                <li id="notificationList">
                    <span class="dropdown-item-text text-muted small px-3 py-2 d-block">No notifications</span>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item text-center small py-2" href="<?php echo e(route('notifications.index')); ?>">View All Notifications</a></li>
            </ul>
        </div>

        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle user-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo e(asset($authUser['profile_image'] ?? config('app_settings.assets.default_profile'))); ?>"
                     alt="User"
                     class="rounded-circle me-2"
                     width="32" height="32">
                <span class="d-none d-sm-inline"><?php echo e($authUser['name'] ?? 'User'); ?></span>
                <?php if($isSwitched): ?>
                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.6rem;">IMP</span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header"><?php echo e($roleLabel); ?></h6></li>
                <li><a class="dropdown-item" href="<?php echo e(route('profile.index')); ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('sessions.index')); ?>"><i class="bi bi-arrow-repeat me-2"></i>Switch Account</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('settings.personal')); ?>"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/partials/navbar.blade.php ENDPATH**/ ?>