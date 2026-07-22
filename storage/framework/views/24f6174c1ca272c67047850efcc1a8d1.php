<?php $__env->startSection('title', 'Edit Village'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Village</h4>
            <p class="page-subtitle text-muted mb-0">Update information for <?php echo e($village->village_name); ?>.</p>
        </div>
        <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

<form action="<?php echo e(route('villages.update', $village)); ?>" method="POST" enctype="multipart/form-data" novalidate>
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-building me-1"></i>Basic Information
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="village_name" class="form-label fw-semibold">Village Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php $__errorArgs = ['village_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="village_name" name="village_name" value="<?php echo e(old('village_name', $village->village_name)); ?>" required>
                    <?php $__errorArgs = ['village_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label for="village_code" class="form-label fw-semibold">Village Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?php $__errorArgs = ['village_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="village_code" name="village_code" value="<?php echo e(old('village_code', $village->village_code)); ?>" required>
                    <?php $__errorArgs = ['village_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                              id="description" name="description" rows="3"><?php echo e(old('description', $village->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control <?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="contact_number" name="contact_number" value="<?php echo e(old('contact_number', $village->contact_number)); ?>">
                    <?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="email" name="email" value="<?php echo e(old('email', $village->email)); ?>">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-6">
                    <label for="established_date" class="form-label fw-semibold">Established Date</label>
                    <input type="date" class="form-control <?php $__errorArgs = ['established_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="established_date" name="established_date" value="<?php echo e(old('established_date', $village->established_date ? \Carbon\Carbon::parse($village->established_date)->format('Y-m-d') : '')); ?>">
                    <?php $__errorArgs = ['established_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-geo-alt me-1"></i>Village Location
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-12">
                    <label for="address" class="form-label fw-semibold">Address</label>
                    <input type="text" class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="address" name="address" value="<?php echo e(old('address', $village->address)); ?>">
                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Pin Village Location <small class="text-muted">(click map to place marker, drag to adjust)</small></label>
                    <div id="village-map-picker" style="height:350px;width:100%;border:2px solid #dee2e6;border-radius:8px;position:relative;">
                        <div id="map-hint" style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);z-index:1000;background:rgba(26,75,140,.9);color:#fff;padding:4px 14px;border-radius:20px;font-size:0.8rem;pointer-events:none;">
                            Click to place village marker
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="latitude" class="form-label fw-semibold">Latitude <span class="text-danger">*</span></label>
                    <input type="text" step="any" class="form-control <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="latitude" name="latitude" value="<?php echo e(old('latitude', $village->latitude)); ?>" readonly required>
                    <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-4">
                    <label for="longitude" class="form-label fw-semibold">Longitude <span class="text-danger">*</span></label>
                    <input type="text" step="any" class="form-control <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="longitude" name="longitude" value="<?php echo e(old('longitude', $village->longitude)); ?>" readonly required>
                    <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="col-md-4">
                    <label for="total_area" class="form-label fw-semibold">Total Area</label>
                    <input type="number" step="any" class="form-control <?php $__errorArgs = ['total_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="total_area" name="total_area" value="<?php echo e(old('total_area', $village->total_area)); ?>">
                    <?php $__errorArgs = ['total_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <?php if(!$village->latitude || !$village->longitude): ?>
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <small><strong>Village location not configured.</strong> Please click on the map to set the village GPS coordinates.</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold text-primary mb-0">
                <i class="bi bi-toggle-on me-1"></i>Status
            </h6>
            <hr>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="status" name="status" required>
                        <option value="active" <?php echo e(old('status', $village->status) === 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="inactive" <?php echo e(old('status', $village->status) === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?php echo e(route('villages.show', $village)); ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Update Village
        </button>
    </div>
</form>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h6 class="fw-semibold text-primary mb-0">
            <i class="bi bi-images me-1"></i>Village Photos <span class="badge bg-secondary ms-1">Max 3</span>
        </h6>
        <hr>

        <?php if($village->photos->count() > 0): ?>
            <div class="row g-3 mb-3">
                <?php $__currentLoopData = $village->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4">
                        <div class="position-relative border rounded p-2 text-center">
                            <img src="<?php echo e($photo->photo_url); ?>" alt="Village Photo" class="img-fluid rounded" style="height:150px;object-fit:cover;width:100%;">
                            <form action="<?php echo e(route('villages.photos.delete', [$village, $photo])); ?>" method="POST" class="mt-2" onsubmit="return confirm('Delete this photo?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-3">No photos uploaded yet.</p>
        <?php endif; ?>

        <?php if($village->photos->count() < 3): ?>
            <form action="<?php echo e(route('villages.photos.upload', $village)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label for="photo" class="form-label fw-semibold">Add Photo</label>
                        <input type="file" class="form-control <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="photo" name="photo" accept="image/*" required>
                        <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info py-2 mb-0">
                <i class="bi bi-info-circle me-1"></i><small>Maximum of 3 photos reached. Remove an existing photo to upload a new one.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .map-picker-container { cursor: crosshair; }
    .map-picker-container:hover { border-color: var(--mvms-primary); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var latVal = <?php echo e($village->latitude ? $village->latitude : '9.2372'); ?>;
    var lngVal = <?php echo e($village->longitude ? $village->longitude : '125.5415'); ?>;
    var zoom = <?php echo e($village->latitude ? 15 : 12); ?>;

    var pickerMap = L.map('village-map-picker').setView([latVal, lngVal], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'OpenStreetMap', maxZoom: 19
    }).addTo(pickerMap);

    var pickerMarker = null;
    var latIn = document.getElementById('latitude');
    var lngIn = document.getElementById('longitude');
    var hint = document.getElementById('map-hint');

    if (latIn.value && lngIn.value) {
        pickerMarker = L.marker([parseFloat(latIn.value), parseFloat(lngIn.value)], { draggable: true }).addTo(pickerMap);
        if (hint) hint.style.display = 'none';

        pickerMarker.on('dragend', function(e) {
            var pos = e.target.getLatLng();
            latIn.value = pos.lat.toFixed(7);
            lngIn.value = pos.lng.toFixed(7);
        });
    }

    pickerMap.on('click', function(e) {
        latIn.value = e.latlng.lat.toFixed(7);
        lngIn.value = e.latlng.lng.toFixed(7);
        if (pickerMarker) pickerMap.removeLayer(pickerMarker);
        pickerMarker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(pickerMap);
        if (hint) hint.style.display = 'none';

        pickerMarker.on('dragend', function(ev) {
            var pos = ev.target.getLatLng();
            latIn.value = pos.lat.toFixed(7);
            lngIn.value = pos.lng.toFixed(7);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/villages/edit.blade.php ENDPATH**/ ?>