<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">My Profile</h4>
            <p class="page-subtitle text-muted mb-0">View and manage your account information.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('profile.edit')); ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i>Edit Profile
            </a>
            <a href="<?php echo e(route('profile.change-password')); ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-key me-1"></i>Change Password
            </a>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <img src="<?php echo e($user->profile_picture_url); ?>"
                     alt="Profile Picture"
                     class="rounded-circle mb-3 profile-display-avatar">
                <h5 class="mb-1"><?php echo e($user->full_name); ?></h5>
                <span class="badge bg-primary"><?php echo e($user->role?->role_name); ?></span>
                <?php if($user->village): ?>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="bi bi-geo-alt me-1"></i><?php echo e($user->village->village_name); ?>

                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Personal Information
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">First Name</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->first_name); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Middle Name</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->middle_name ?? '—'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->last_name); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Suffix</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->suffix ?? '—'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Username</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->username); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->email); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Contact Number</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->contact_number ?? '—'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status</label>
                        <p class="mb-0">
                            <span class="badge bg-<?php echo e($user->status === 'active' ? 'success' : 'danger'); ?>">
                                <?php echo e(ucfirst($user->status)); ?>

                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Login</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->last_login?->format('M d, Y h:i A') ?? 'Never'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Member Since</label>
                        <p class="fw-semibold mb-0"><?php echo e($user->created_at->format('M d, Y')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/profile/index.blade.php ENDPATH**/ ?>