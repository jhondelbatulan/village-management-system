@extends('layouts.app')

@section('title', 'Edit Resident')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Edit Resident</h4>
            <p class="page-subtitle text-muted mb-0">Update information for {{ $resident->full_name }}.</p>
        </div>
        <a href="{{ route('residents.show', $resident) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Profile
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('residents.update', $resident) }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="accordion" id="residentAccordion">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0 py-3" id="headingPersonal">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-decoration-none text-primary fw-semibold p-0" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapsePersonal" aria-expanded="true">
                                <i class="bi bi-person me-2"></i>Personal Information
                            </button>
                        </h6>
                    </div>
                    <div id="collapsePersonal" class="collapse show" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name" value="{{ old('first_name', $resident->first_name) }}" required>
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                           id="middle_name" name="middle_name" value="{{ old('middle_name', $resident->middle_name) }}">
                                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name" value="{{ old('last_name', $resident->last_name) }}" required>
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="suffix" class="form-label fw-semibold">Suffix</label>
                                    <input type="text" class="form-control @error('suffix') is-invalid @enderror"
                                           id="suffix" name="suffix" value="{{ old('suffix', $resident->suffix) }}" placeholder="e.g. Jr., Sr., III">
                                    @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male" {{ old('gender', $resident->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $resident->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $resident->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="birthdate" class="form-label fw-semibold">Birthdate <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('birthdate') is-invalid @enderror"
                                           id="birthdate" name="birthdate"
                                           value="{{ old('birthdate', $resident->birthdate?->format('Y-m-d')) }}" required>
                                    @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="civil_status" class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" required>
                                        <option value="">Select</option>
                                        <option value="Single" {{ old('civil_status', $resident->civil_status) === 'Single' ? 'selected' : '' }}>Single</option>
                                        <option value="Married" {{ old('civil_status', $resident->civil_status) === 'Married' ? 'selected' : '' }}>Married</option>
                                        <option value="Widowed" {{ old('civil_status', $resident->civil_status) === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        <option value="Separated" {{ old('civil_status', $resident->civil_status) === 'Separated' ? 'selected' : '' }}>Separated</option>
                                        <option value="Divorced" {{ old('civil_status', $resident->civil_status) === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                    </select>
                                    @error('civil_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="nationality" class="form-label fw-semibold">Nationality</label>
                                    <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                                           id="nationality" name="nationality"
                                           value="{{ old('nationality', $resident->nationality) }}">
                                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="religion" class="form-label fw-semibold">Religion</label>
                                    <input type="text" class="form-control @error('religion') is-invalid @enderror"
                                           id="religion" name="religion" value="{{ old('religion', $resident->religion) }}">
                                    @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="collapseContact" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                                           id="contact_number" name="contact_number"
                                           value="{{ old('contact_number', $resident->contact_number) }}">
                                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $resident->email) }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="voter_status" name="voter_status" value="1"
                                               {{ old('voter_status', $resident->voter_status) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="voter_status">Registered Voter</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
                                    <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                           id="profile_picture" name="profile_picture" accept="image/*">
                                    @error('profile_picture') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6" id="picturePreviewWrapper">
                                    <label class="form-label fw-semibold">Current Picture</label>
                                    <div>
                                        <img id="picturePreview" src="{{ $resident->profile_picture_url }}" alt="Profile"
                                             class="rounded-circle" width="80" height="80" style="object-fit: cover;">
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
                                <i class="bi bi-house me-2"></i>Household & Village
                            </button>
                        </h6>
                    </div>
                    <div id="collapseHousehold" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="household_id" class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
                                    <select class="form-select @error('household_id') is-invalid @enderror"
                                            id="household_id" name="household_id" required>
                                        <option value="">Select Household</option>
                                        @foreach($households as $household)
                                            <option value="{{ $household->id }}" {{ old('household_id', $resident->household_id) == $household->id ? 'selected' : '' }}>
                                                Blk {{ $household->block_number ?? '—' }}, Lot {{ $household->lot_number ?? '—' }} - {{ $household->household_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('household_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="relationship_to_head" class="form-label fw-semibold">Relationship to Head <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('relationship_to_head') is-invalid @enderror"
                                           id="relationship_to_head" name="relationship_to_head"
                                           value="{{ old('relationship_to_head', $resident->relationship_to_head) }}"
                                           placeholder="e.g. Self, Spouse, Child" required>
                                    @error('relationship_to_head') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_household_head" name="is_household_head" value="1"
                                               {{ old('is_household_head', $resident->is_household_head) ? 'checked' : '' }}>
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
                    <div id="collapseEducation" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="educational_attainment" class="form-label fw-semibold">Educational Attainment</label>
                                    <select class="form-select @error('educational_attainment') is-invalid @enderror"
                                            id="educational_attainment" name="educational_attainment">
                                        <option value="">Select</option>
                                        @foreach(['No Formal Education','Preschool','Elementary Level','Elementary Graduate','High School Level','High School Graduate','Vocational/Technical','Senior High Graduate','College Level','College Graduate','Post Graduate'] as $level)
                                            <option value="{{ $level }}" {{ old('educational_attainment', $resident->educational_attainment) === $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                    @error('educational_attainment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="education_level" class="form-label fw-semibold">Current Education Level</label>
                                    <select class="form-select @error('education_level') is-invalid @enderror"
                                            id="education_level" name="education_level">
                                        <option value="">Select</option>
                                        @foreach(['Not Enrolled','Elementary','Junior High School','Senior High School','Vocational/Technical','College/University','Post Graduate'] as $level)
                                            <option value="{{ $level }}" {{ old('education_level', $resident->education_level) === $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                    @error('education_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="school_name" class="form-label fw-semibold">School Name</label>
                                    <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                           id="school_name" name="school_name" value="{{ old('school_name', $resident->school_name) }}" placeholder="Current/most recent school">
                                    @error('school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="grade_level" class="form-label fw-semibold">Grade/Year Level</label>
                                    <input type="text" class="form-control @error('grade_level') is-invalid @enderror"
                                           id="grade_level" name="grade_level" value="{{ old('grade_level', $resident->grade_level) }}" placeholder="e.g. Grade 10, 3rd Year">
                                    @error('grade_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold d-block">&nbsp;</label>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="is_scholarship" name="is_scholarship" value="1"
                                               {{ old('is_scholarship', $resident->is_scholarship) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_scholarship">Under Scholarship</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="scholarship_type" class="form-label fw-semibold">Scholarship Type</label>
                                    <input type="text" class="form-control @error('scholarship_type') is-invalid @enderror"
                                           id="scholarship_type" name="scholarship_type" value="{{ old('scholarship_type', $resident->scholarship_type) }}" placeholder="e.g. DSWD, LGU, Private">
                                    @error('scholarship_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="collapseEmployment" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="employment_status" class="form-label fw-semibold">Employment Status</label>
                                    <select class="form-select @error('employment_status') is-invalid @enderror"
                                            id="employment_status" name="employment_status">
                                        <option value="">Select</option>
                                        @foreach(['Employed','Self-Employed','Unemployed','Retired','Student','Homemaker','Disabled'] as $status)
                                            <option value="{{ $status }}" {{ old('employment_status', $resident->employment_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="occupation" class="form-label fw-semibold">Occupation</label>
                                    <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                           id="occupation" name="occupation" value="{{ old('occupation', $resident->occupation) }}" placeholder="e.g. Teacher, Farmer">
                                    @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="job_title" class="form-label fw-semibold">Job Title</label>
                                    <input type="text" class="form-control @error('job_title') is-invalid @enderror"
                                           id="job_title" name="job_title" value="{{ old('job_title', $resident->job_title) }}" placeholder="e.g. Engineer, Cashier">
                                    @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="employer_name" class="form-label fw-semibold">Employer Name</label>
                                    <input type="text" class="form-control @error('employer_name') is-invalid @enderror"
                                           id="employer_name" name="employer_name" value="{{ old('employer_name', $resident->employer_name) }}">
                                    @error('employer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="years_of_experience" class="form-label fw-semibold">Years Exp.</label>
                                    <input type="number" class="form-control @error('years_of_experience') is-invalid @enderror"
                                           id="years_of_experience" name="years_of_experience" value="{{ old('years_of_experience', $resident->years_of_experience) }}" min="0" max="60">
                                    @error('years_of_experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="income_source" class="form-label fw-semibold">Income Source</label>
                                    <select class="form-select @error('income_source') is-invalid @enderror"
                                            id="income_source" name="income_source">
                                        <option value="">Select</option>
                                        @foreach(['Salary','Business','Farming/Agriculture','Fishing','Remittance','Pension','Freelance','Government Aid','Other'] as $source)
                                            <option value="{{ $source }}" {{ old('income_source', $resident->income_source) === $source ? 'selected' : '' }}>{{ $source }}</option>
                                        @endforeach
                                    </select>
                                    @error('income_source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income" class="form-label fw-semibold">Monthly Income (₱)</label>
                                    <input type="number" class="form-control @error('monthly_income') is-invalid @enderror"
                                           id="monthly_income" name="monthly_income" value="{{ old('monthly_income', $resident->monthly_income) }}" min="0" step="0.01">
                                    @error('monthly_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income_range" class="form-label fw-semibold">Income Range</label>
                                    <select class="form-select @error('monthly_income_range') is-invalid @enderror"
                                            id="monthly_income_range" name="monthly_income_range">
                                        <option value="">Select</option>
                                        @foreach(['Below ₱5,000','₱5,000 - ₱10,000','₱10,001 - ₱20,000','₱20,001 - ₱40,000','₱40,001 - ₱60,000','₱60,001 - ₱100,000','Above ₱100,000'] as $range)
                                            <option value="{{ $range }}" {{ old('monthly_income_range', $resident->monthly_income_range) === $range ? 'selected' : '' }}>{{ $range }}</option>
                                        @endforeach
                                    </select>
                                    @error('monthly_income_range') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="collapseSpecial" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pwd" name="is_pwd" value="1" {{ old('is_pwd', $resident->is_pwd) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_pwd">Person with Disability (PWD)</label>
                                    </div>
                                </div>
                                <div class="col-md-9" id="pwdTypeWrapper" style="display: {{ old('is_pwd', $resident->is_pwd) ? 'block' : 'none' }};">
                                    <label for="pwd_type" class="form-label fw-semibold">PWD Type</label>
                                    <input type="text" class="form-control @error('pwd_type') is-invalid @enderror"
                                           id="pwd_type" name="pwd_type" value="{{ old('pwd_type', $resident->pwd_type) }}" placeholder="e.g. Visual, Hearing, Mobility">
                                    @error('pwd_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_solo_parent" name="is_solo_parent" value="1" {{ old('is_solo_parent', $resident->is_solo_parent) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_solo_parent">Solo Parent</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_citizen" name="is_senior_citizen" value="1" {{ old('is_senior_citizen', $resident->is_senior_citizen) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_senior_citizen">Senior Citizen</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_youth" name="is_youth" value="1" {{ old('is_youth', $resident->is_youth) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_youth">Youth (15–30)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_student" name="is_student" value="1" {{ old('is_student', $resident->is_student) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_student">Student</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_ofw" name="is_ofw" value="1" {{ old('is_ofw', $resident->is_ofw) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_ofw">OFW</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_infant" name="is_infant" value="1" {{ old('is_infant', $resident->is_infant) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_infant">Infant/Child (0–5)</label>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <small class="text-muted">Note: Senior Citizen, Youth, and Infant auto-set from birthdate.</small>
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
                    <div id="collapseGovt" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT MEMBERSHIPS</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pagibig_member" name="is_pagibig_member" value="1" {{ old('is_pagibig_member', $resident->is_pagibig_member) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pagibig_member">Pag-IBIG</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_philhealth_member" name="is_philhealth_member" value="1" {{ old('is_philhealth_member', $resident->is_philhealth_member) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_philhealth_member">PhilHealth</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_sss_member" name="is_sss_member" value="1" {{ old('is_sss_member', $resident->is_sss_member) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_sss_member">SSS</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_gsis_member" name="is_gsis_member" value="1" {{ old('is_gsis_member', $resident->is_gsis_member) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_gsis_member">GSIS</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT ASSISTANCE</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_4ps_member" name="is_4ps_member" value="1" {{ old('is_4ps_member', $resident->is_4ps_member) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_4ps_member">4Ps Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_pensioner" name="is_senior_pensioner" value="1" {{ old('is_senior_pensioner', $resident->is_senior_pensioner) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_senior_pensioner">Senior Pensioner (SSP)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="other_gov_assistance" class="form-label fw-semibold">Other Government Assistance</label>
                                    <input type="text" class="form-control @error('other_gov_assistance') is-invalid @enderror"
                                           id="other_gov_assistance" name="other_gov_assistance" value="{{ old('other_gov_assistance', $resident->other_gov_assistance) }}" placeholder="e.g. DSWD, SAP, AICS">
                                    @error('other_gov_assistance') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="collapseSkills" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="skills" class="form-label fw-semibold">Skills</label>
                                    <textarea class="form-control @error('skills') is-invalid @enderror" id="skills" name="skills"
                                              rows="2" placeholder="e.g. Carpentry, Sewing, Cooking">{{ old('skills', $resident->skills) }}</textarea>
                                    @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="livelihood_programs" class="form-label fw-semibold">Livelihood Programs</label>
                                    <textarea class="form-control @error('livelihood_programs') is-invalid @enderror" id="livelihood_programs" name="livelihood_programs"
                                              rows="2" placeholder="e.g. TESDA, DTI">{{ old('livelihood_programs', $resident->livelihood_programs) }}</textarea>
                                    @error('livelihood_programs') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="special_qualifications" class="form-label fw-semibold">Special Qualifications</label>
                                    <textarea class="form-control @error('special_qualifications') is-invalid @enderror" id="special_qualifications" name="special_qualifications"
                                              rows="2" placeholder="e.g. Licensed Teacher">{{ old('special_qualifications', $resident->special_qualifications) }}</textarea>
                                    @error('special_qualifications') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="community_activities" class="form-label fw-semibold">Community Activities/Volunteer Work</label>
                                    <textarea class="form-control @error('community_activities') is-invalid @enderror" id="community_activities" name="community_activities"
                                              rows="2" placeholder="e.g. Barangay Tanod">{{ old('community_activities', $resident->community_activities) }}</textarea>
                                    @error('community_activities') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="collapseEmergency" class="collapse" data-bs-parent="#residentAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">EMERGENCY CONTACT</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_name" class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                           id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $resident->emergency_contact_name) }}">
                                    @error('emergency_contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('emergency_contact_number') is-invalid @enderror"
                                           id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number', $resident->emergency_contact_number) }}">
                                    @error('emergency_contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_relationship" class="form-label fw-semibold">Relationship</label>
                                    <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                           id="emergency_contact_relationship" name="emergency_contact_relationship"
                                           value="{{ old('emergency_contact_relationship', $resident->emergency_contact_relationship) }}" placeholder="e.g. Spouse, Parent">
                                    @error('emergency_contact_relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">ADDITIONAL INFORMATION</label>
                                </div>
                                <div class="col-md-3">
                                    <label for="blood_type" class="form-label fw-semibold">Blood Type</label>
                                    <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                        <option value="">Select</option>
                                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown'] as $type)
                                            <option value="{{ $type }}" {{ old('blood_type', $resident->blood_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="years_of_residency" class="form-label fw-semibold">Years of Residency</label>
                                    <input type="number" class="form-control @error('years_of_residency') is-invalid @enderror"
                                           id="years_of_residency" name="years_of_residency" value="{{ old('years_of_residency', $resident->years_of_residency) }}" min="0">
                                    @error('years_of_residency') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <i class="bi bi-person-badge me-1"></i>Resident Code
                    </h6>
                    <hr>
                    <div class="mb-3">
                        <input type="text" class="form-control" value="{{ $resident->resident_code }}" readonly>
                        <small class="text-muted">Resident code cannot be changed.</small>
                    </div>

                    <h6 class="fw-semibold text-primary mb-0 mt-4">
                        <i class="bi bi-info-circle me-1"></i>Status
                    </h6>
                    <hr>
                    <div>
                        <label for="status" class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $resident->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $resident->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="deceased" {{ old('status', $resident->status) === 'deceased' ? 'selected' : '' }}>Deceased</option>
                            <option value="transferred" {{ old('status', $resident->status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('residents.show', $resident) }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Update Resident
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('picturePreview').src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('is_pwd').addEventListener('change', function() {
        document.getElementById('pwdTypeWrapper').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush
