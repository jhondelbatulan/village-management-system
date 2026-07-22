<?php $__env->startSection('title', 'Request to Add Household Member'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Request to Add Household Member</h4>
            <p class="page-subtitle text-muted mb-0">Submit a request to add a new member to your household. A secretary will review and approve it.</p>
        </div>
        <a href="<?php echo e(route('households.my-household')); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to My Household
        </a>
    </div>
</div>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="<?php echo e(route('households.request-member')); ?>" method="POST" enctype="multipart/form-data" novalidate>
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="accordion" id="requestAccordion">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingPersonal">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapsePersonal" aria-expanded="true">
                                <i class="bi bi-person me-2"></i>Personal Information
                            </button>
                        </h6>
                    </div>
                    <div id="collapsePersonal" class="collapse show" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="first_name" name="first_name" value="<?php echo e(old('first_name')); ?>" required>
                                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['middle_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="middle_name" name="middle_name" value="<?php echo e(old('middle_name')); ?>">
                                    <?php $__errorArgs = ['middle_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="last_name" name="last_name" value="<?php echo e(old('last_name')); ?>" required>
                                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="suffix" class="form-label fw-semibold">Suffix</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['suffix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="suffix" name="suffix" value="<?php echo e(old('suffix')); ?>" placeholder="e.g. Jr., Sr., III">
                                    <?php $__errorArgs = ['suffix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="gender" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male" <?php echo e(old('gender') === 'Male' ? 'selected' : ''); ?>>Male</option>
                                        <option value="Female" <?php echo e(old('gender') === 'Female' ? 'selected' : ''); ?>>Female</option>
                                        <option value="Other" <?php echo e(old('gender') === 'Other' ? 'selected' : ''); ?>>Other</option>
                                    </select>
                                    <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="birthdate" class="form-label fw-semibold">Birthdate <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?php $__errorArgs = ['birthdate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="birthdate" name="birthdate" value="<?php echo e(old('birthdate')); ?>" required>
                                    <?php $__errorArgs = ['birthdate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="civil_status" class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['civil_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="civil_status" name="civil_status" required>
                                        <option value="">Select</option>
                                        <option value="Single" <?php echo e(old('civil_status') === 'Single' ? 'selected' : ''); ?>>Single</option>
                                        <option value="Married" <?php echo e(old('civil_status') === 'Married' ? 'selected' : ''); ?>>Married</option>
                                        <option value="Widowed" <?php echo e(old('civil_status') === 'Widowed' ? 'selected' : ''); ?>>Widowed</option>
                                        <option value="Separated" <?php echo e(old('civil_status') === 'Separated' ? 'selected' : ''); ?>>Separated</option>
                                        <option value="Divorced" <?php echo e(old('civil_status') === 'Divorced' ? 'selected' : ''); ?>>Divorced</option>
                                    </select>
                                    <?php $__errorArgs = ['civil_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="nationality" class="form-label fw-semibold">Nationality</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['nationality'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="nationality" name="nationality" value="<?php echo e(old('nationality', 'Filipino')); ?>">
                                    <?php $__errorArgs = ['nationality'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="religion" class="form-label fw-semibold">Religion</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['religion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="religion" name="religion" value="<?php echo e(old('religion')); ?>">
                                    <?php $__errorArgs = ['religion'];
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
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingContact">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseContact">
                                <i class="bi bi-envelope me-2"></i>Contact & Identity
                            </button>
                        </h6>
                    </div>
                    <div id="collapseContact" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="contact_number" name="contact_number" value="<?php echo e(old('contact_number')); ?>">
                                    <?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="email" name="email" value="<?php echo e(old('email')); ?>">
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="voter_status" name="voter_status" value="1"
                                               <?php echo e(old('voter_status') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="voter_status">Registered Voter</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingHousehold">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseHousehold">
                                <i class="bi bi-house me-2"></i>Household Relationship
                            </button>
                        </h6>
                    </div>
                    <div id="collapseHousehold" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="relationship_to_head" class="form-label fw-semibold">Relationship to Head <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['relationship_to_head'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="relationship_to_head" name="relationship_to_head"
                                           value="<?php echo e(old('relationship_to_head')); ?>" placeholder="e.g. Self, Spouse, Child" required>
                                    <?php $__errorArgs = ['relationship_to_head'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_household_head" name="is_household_head" value="1"
                                               <?php echo e(old('is_household_head') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_household_head">Household Head</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingEducation">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseEducation">
                                <i class="bi bi-mortarboard me-2"></i>Education
                            </button>
                        </h6>
                    </div>
                    <div id="collapseEducation" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="educational_attainment" class="form-label fw-semibold">Educational Attainment</label>
                                    <select class="form-select <?php $__errorArgs = ['educational_attainment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="educational_attainment" name="educational_attainment">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['No Formal Education','Preschool','Elementary Level','Elementary Graduate','High School Level','High School Graduate','Vocational/Technical','Senior High Graduate','College Level','College Graduate','Post Graduate']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($level); ?>" <?php echo e(old('educational_attainment') === $level ? 'selected' : ''); ?>><?php echo e($level); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['educational_attainment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="education_level" class="form-label fw-semibold">Current Education Level</label>
                                    <select class="form-select <?php $__errorArgs = ['education_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="education_level" name="education_level">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['Not Enrolled','Elementary','Junior High School','Senior High School','Vocational/Technical','College/University','Post Graduate']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($level); ?>" <?php echo e(old('education_level') === $level ? 'selected' : ''); ?>><?php echo e($level); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['education_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="school_name" class="form-label fw-semibold">School Name</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['school_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="school_name" name="school_name" value="<?php echo e(old('school_name')); ?>" placeholder="Current/most recent school">
                                    <?php $__errorArgs = ['school_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="grade_level" class="form-label fw-semibold">Grade/Year Level</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="grade_level" name="grade_level" value="<?php echo e(old('grade_level')); ?>" placeholder="e.g. Grade 10, 3rd Year">
                                    <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold d-block">&nbsp;</label>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="is_scholarship" name="is_scholarship" value="1"
                                               <?php echo e(old('is_scholarship') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_scholarship">Under Scholarship</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="scholarship_type" class="form-label fw-semibold">Scholarship Type</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['scholarship_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="scholarship_type" name="scholarship_type" value="<?php echo e(old('scholarship_type')); ?>" placeholder="e.g. DSWD, LGU, Private">
                                    <?php $__errorArgs = ['scholarship_type'];
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
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingEmployment">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseEmployment">
                                <i class="bi bi-briefcase me-2"></i>Employment & Income
                            </button>
                        </h6>
                    </div>
                    <div id="collapseEmployment" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="employment_status" class="form-label fw-semibold">Employment Status</label>
                                    <select class="form-select <?php $__errorArgs = ['employment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="employment_status" name="employment_status">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['Employed','Self-Employed','Unemployed','Retired','Student','Homemaker','Disabled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php echo e(old('employment_status') === $status ? 'selected' : ''); ?>><?php echo e($status); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['employment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="occupation" class="form-label fw-semibold">Occupation</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['occupation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="occupation" name="occupation" value="<?php echo e(old('occupation')); ?>" placeholder="e.g. Teacher, Farmer">
                                    <?php $__errorArgs = ['occupation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="job_title" class="form-label fw-semibold">Job Title</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['job_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="job_title" name="job_title" value="<?php echo e(old('job_title')); ?>" placeholder="e.g. Engineer, Cashier">
                                    <?php $__errorArgs = ['job_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="employer_name" class="form-label fw-semibold">Employer Name</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['employer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="employer_name" name="employer_name" value="<?php echo e(old('employer_name')); ?>">
                                    <?php $__errorArgs = ['employer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-2">
                                    <label for="years_of_experience" class="form-label fw-semibold">Years Exp.</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['years_of_experience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="years_of_experience" name="years_of_experience" value="<?php echo e(old('years_of_experience')); ?>" min="0" max="60">
                                    <?php $__errorArgs = ['years_of_experience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="income_source" class="form-label fw-semibold">Income Source</label>
                                    <select class="form-select <?php $__errorArgs = ['income_source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="income_source" name="income_source">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['Salary','Business','Farming/Agriculture','Fishing','Remittance','Pension','Freelance','Government Aid','Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($source); ?>" <?php echo e(old('income_source') === $source ? 'selected' : ''); ?>><?php echo e($source); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['income_source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income" class="form-label fw-semibold">Monthly Income (₱)</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['monthly_income'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="monthly_income" name="monthly_income" value="<?php echo e(old('monthly_income')); ?>" min="0" step="0.01">
                                    <?php $__errorArgs = ['monthly_income'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income_range" class="form-label fw-semibold">Income Range</label>
                                    <select class="form-select <?php $__errorArgs = ['monthly_income_range'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="monthly_income_range" name="monthly_income_range">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['Below ₱5,000','₱5,000 - ₱10,000','₱10,001 - ₱20,000','₱20,001 - ₱40,000','₱40,001 - ₱60,000','₱60,001 - ₱100,000','Above ₱100,000']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($range); ?>" <?php echo e(old('monthly_income_range') === $range ? 'selected' : ''); ?>><?php echo e($range); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['monthly_income_range'];
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
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingSpecial">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSpecial">
                                <i class="bi bi-people me-2"></i>Special Population Classification
                            </button>
                        </h6>
                    </div>
                    <div id="collapseSpecial" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pwd" name="is_pwd" value="1" <?php echo e(old('is_pwd') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_pwd">Person with Disability (PWD)</label>
                                    </div>
                                </div>
                                <div class="col-md-9" id="pwdTypeWrapper" style="display: <?php echo e(old('is_pwd') ? 'block' : 'none'); ?>;">
                                    <label for="pwd_type" class="form-label fw-semibold">PWD Type</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['pwd_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="pwd_type" name="pwd_type" value="<?php echo e(old('pwd_type')); ?>" placeholder="e.g. Visual, Hearing, Mobility">
                                    <?php $__errorArgs = ['pwd_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_solo_parent" name="is_solo_parent" value="1" <?php echo e(old('is_solo_parent') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_solo_parent">Solo Parent</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_citizen" name="is_senior_citizen" value="1" <?php echo e(old('is_senior_citizen') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_senior_citizen">Senior Citizen</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_youth" name="is_youth" value="1" <?php echo e(old('is_youth') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_youth">Youth (15–30)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_student" name="is_student" value="1" <?php echo e(old('is_student') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_student">Student</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_ofw" name="is_ofw" value="1" <?php echo e(old('is_ofw') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_ofw">OFW</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_infant" name="is_infant" value="1" <?php echo e(old('is_infant') ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-semibold" for="is_infant">Infant/Child (0–5)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingGovt">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseGovt">
                                <i class="bi bi-shield-check me-2"></i>Government Memberships & Assistance
                            </button>
                        </h6>
                    </div>
                    <div id="collapseGovt" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT MEMBERSHIPS</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pagibig_member" name="is_pagibig_member" value="1" <?php echo e(old('is_pagibig_member') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_pagibig_member">Pag-IBIG</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_philhealth_member" name="is_philhealth_member" value="1" <?php echo e(old('is_philhealth_member') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_philhealth_member">PhilHealth</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_sss_member" name="is_sss_member" value="1" <?php echo e(old('is_sss_member') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_sss_member">SSS</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_gsis_member" name="is_gsis_member" value="1" <?php echo e(old('is_gsis_member') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_gsis_member">GSIS</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT ASSISTANCE</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_4ps_member" name="is_4ps_member" value="1" <?php echo e(old('is_4ps_member') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_4ps_member">4Ps Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_pensioner" name="is_senior_pensioner" value="1" <?php echo e(old('is_senior_pensioner') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_senior_pensioner">Senior Pensioner (SSP)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_tupad_beneficiary" name="is_tupad_beneficiary" value="1" <?php echo e(old('is_tupad_beneficiary') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_tupad_beneficiary">TUPAD Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_akap_beneficiary" name="is_akap_beneficiary" value="1" <?php echo e(old('is_akap_beneficiary') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_akap_beneficiary">AKAP Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_educational_assistance" name="is_educational_assistance" value="1" <?php echo e(old('is_educational_assistance') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_educational_assistance">Educational Assistance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_emergency_assistance" name="is_emergency_assistance" value="1" <?php echo e(old('is_emergency_assistance') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_emergency_assistance">Emergency Assistance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_slp_beneficiary" name="is_slp_beneficiary" value="1" <?php echo e(old('is_slp_beneficiary') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_slp_beneficiary">SLP Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="other_gov_assistance" class="form-label fw-semibold">Other Government Assistance</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['other_gov_assistance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="other_gov_assistance" name="other_gov_assistance" value="<?php echo e(old('other_gov_assistance')); ?>" placeholder="e.g. DSWD, SAP, AICS">
                                    <?php $__errorArgs = ['other_gov_assistance'];
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
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingIds">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseIds">
                                <i class="bi bi-card-checklist me-2"></i>Government IDs & Social Categories
                            </button>
                        </h6>
                    </div>
                    <div id="collapseIds" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT IDS</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_national_id_holder" name="is_national_id_holder" value="1" <?php echo e(old('is_national_id_holder') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_national_id_holder">National ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_id_holder" name="is_senior_id_holder" value="1" <?php echo e(old('is_senior_id_holder') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_senior_id_holder">Senior Citizen ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pwd_id_holder" name="is_pwd_id_holder" value="1" <?php echo e(old('is_pwd_id_holder') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_pwd_id_holder">PWD ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_solo_parent_id_holder" name="is_solo_parent_id_holder" value="1" <?php echo e(old('is_solo_parent_id_holder') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_solo_parent_id_holder">Solo Parent ID</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">SOCIAL CATEGORIES</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_indigenous" name="is_indigenous" value="1" <?php echo e(old('is_indigenous') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_indigenous">Indigenous Person</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_veteran" name="is_veteran" value="1" <?php echo e(old('is_veteran') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_veteran">Veteran</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">COMMUNITY PARTICIPATION</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_disaster_volunteer" name="is_disaster_volunteer" value="1" <?php echo e(old('is_disaster_volunteer') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_disaster_volunteer">Disaster Volunteer</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_village_volunteer" name="is_village_volunteer" value="1" <?php echo e(old('is_village_volunteer') ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_village_volunteer">Village Volunteer</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingSkills">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSkills">
                                <i class="bi bi-tools me-2"></i>Skills, Livelihood & Qualifications
                            </button>
                        </h6>
                    </div>
                    <div id="collapseSkills" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="skills" class="form-label fw-semibold">Skills</label>
                                    <textarea class="form-control <?php $__errorArgs = ['skills'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="skills" name="skills"
                                              rows="2" placeholder="e.g. Carpentry, Sewing, Cooking, Driving"><?php echo e(old('skills')); ?></textarea>
                                    <?php $__errorArgs = ['skills'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="primary_skill" class="form-label fw-semibold">Primary Skill</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['primary_skill'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="primary_skill" name="primary_skill" value="<?php echo e(old('primary_skill')); ?>" placeholder="Main skill or specialization">
                                    <?php $__errorArgs = ['primary_skill'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="secondary_skill" class="form-label fw-semibold">Secondary Skill</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['secondary_skill'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="secondary_skill" name="secondary_skill" value="<?php echo e(old('secondary_skill')); ?>" placeholder="Additional skill">
                                    <?php $__errorArgs = ['secondary_skill'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="livelihood_programs" class="form-label fw-semibold">Livelihood Programs</label>
                                    <textarea class="form-control <?php $__errorArgs = ['livelihood_programs'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="livelihood_programs" name="livelihood_programs"
                                              rows="2" placeholder="e.g. TESDA, DTI, DSWD livelihood program"><?php echo e(old('livelihood_programs')); ?></textarea>
                                    <?php $__errorArgs = ['livelihood_programs'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="special_qualifications" class="form-label fw-semibold">Special Qualifications</label>
                                    <textarea class="form-control <?php $__errorArgs = ['special_qualifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="special_qualifications" name="special_qualifications"
                                              rows="2" placeholder="e.g. Licensed Teacher, Registered Nurse"><?php echo e(old('special_qualifications')); ?></textarea>
                                    <?php $__errorArgs = ['special_qualifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="community_activities" class="form-label fw-semibold">Community Activities/Volunteer Work</label>
                                    <textarea class="form-control <?php $__errorArgs = ['community_activities'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="community_activities" name="community_activities"
                                              rows="2" placeholder="e.g. Barangay Tanod, Health Volunteer"><?php echo e(old('community_activities')); ?></textarea>
                                    <?php $__errorArgs = ['community_activities'];
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
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingEmergency">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0 collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseEmergency">
                                <i class="bi bi-telephone me-2"></i>Emergency Contact & Additional Info
                            </button>
                        </h6>
                    </div>
                    <div id="collapseEmergency" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">EMERGENCY CONTACT</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_name" class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['emergency_contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="emergency_contact_name" name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name')); ?>">
                                    <?php $__errorArgs = ['emergency_contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['emergency_contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="emergency_contact_number" name="emergency_contact_number" value="<?php echo e(old('emergency_contact_number')); ?>">
                                    <?php $__errorArgs = ['emergency_contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_relationship" class="form-label fw-semibold">Relationship</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['emergency_contact_relationship'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="emergency_contact_relationship" name="emergency_contact_relationship" value="<?php echo e(old('emergency_contact_relationship')); ?>" placeholder="e.g. Spouse, Parent">
                                    <?php $__errorArgs = ['emergency_contact_relationship'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">ADDITIONAL INFORMATION</label>
                                </div>
                                <div class="col-md-3">
                                    <label for="blood_type" class="form-label fw-semibold">Blood Type</label>
                                    <select class="form-select <?php $__errorArgs = ['blood_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="blood_type" name="blood_type">
                                        <option value="">Select</option>
                                        <?php $__currentLoopData = ['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type); ?>" <?php echo e(old('blood_type') === $type ? 'selected' : ''); ?>><?php echo e($type); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['blood_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="years_of_residency" class="form-label fw-semibold">Years of Residency</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['years_of_residency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="years_of_residency" name="years_of_residency" value="<?php echo e(old('years_of_residency')); ?>" min="0">
                                    <?php $__errorArgs = ['years_of_residency'];
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
                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-info-circle me-1"></i>About This Request
                    </h6>
                    <hr>
                    <p class="text-muted small mb-3">
                        This request will be submitted to your village secretary for review and approval.
                        Once approved, the new member will be added to your household.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="<?php echo e(route('households.my-household')); ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Submit Request
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.getElementById('is_pwd').addEventListener('change', function() {
        document.getElementById('pwdTypeWrapper').style.display = this.checked ? 'block' : 'none';
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Dennis Casuco\Documents\multi-village-management-system\resources\views/households/request-member.blade.php ENDPATH**/ ?>