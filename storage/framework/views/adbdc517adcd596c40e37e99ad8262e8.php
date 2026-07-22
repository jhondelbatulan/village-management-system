<?php if($villagePhotos->isNotEmpty()): ?>
<?php $carouselId = 'villagePhotoCarousel_' . uniqid(); ?>
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div id="<?php echo e($carouselId); ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-indicators">
            <?php $__currentLoopData = $villagePhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" data-bs-target="#<?php echo e($carouselId); ?>" data-bs-slide-to="<?php echo e($i); ?>" class="<?php echo e($i === 0 ? 'active' : ''); ?>" aria-label="Photo <?php echo e($i + 1); ?>"></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="carousel-inner" style="max-height:400px;">
            <?php $__currentLoopData = $villagePhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="carousel-item <?php echo e($i === 0 ? 'active' : ''); ?>">
                    <img src="<?php echo e($photo->photo_url); ?>" class="d-block w-100" alt="Village Photo" style="height:400px;object-fit:cover;">
                    <?php if($photo->caption): ?>
                        <div class="carousel-caption d-none d-md-block">
                            <p class="mb-0"><?php echo e($photo->caption); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($villagePhotos->count() > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo e($carouselId); ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo e($carouselId); ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/partials/village-photos-slideshow.blade.php ENDPATH**/ ?>