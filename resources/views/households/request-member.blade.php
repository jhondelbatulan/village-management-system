@extends('layouts.app')

@section('title', 'Request to Add Household Member')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Request to Add Household Member</h4>
            <p class="page-subtitle text-muted mb-0">Submit a request to add a new member to your household. It will be reviewed by your village admin and finalized by the Super Admin.</p>
        </div>
        <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to My Household
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

<form action="{{ route('households.request-member') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

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
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name" value="{{ old('first_name') }}"
                                           maxlength="255" placeholder="e.g. Juan" autocomplete="off" required>
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                           id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                                           maxlength="1" placeholder="e.g. D" autocomplete="off">
                                    <div class="form-text">Single capital letter only.</div>
                                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name" value="{{ old('last_name') }}"
                                           maxlength="255" placeholder="e.g. Dela Cruz" autocomplete="off" required>
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="suffix" class="form-label fw-semibold">Suffix</label>
                                    <input type="text" class="form-control @error('suffix') is-invalid @enderror"
                                           id="suffix" name="suffix" value="{{ old('suffix') }}"
                                           list="member-suffix-options" maxlength="10" placeholder="e.g. Jr., Sr., III" autocomplete="off">
                                    <datalist id="member-suffix-options">
                                        <option value="Jr."></option>
                                        <option value="Sr."></option>
                                        <option value="II"></option>
                                        <option value="III"></option>
                                        <option value="IV"></option>
                                        <option value="V"></option>
                                        <option value="VI"></option>
                                        <option value="VII"></option>
                                        <option value="VIII"></option>
                                        <option value="IX"></option>
                                        <option value="X"></option>
                                    </datalist>
                                    @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="birthdate" class="form-label fw-semibold">Birthdate <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('birthdate') is-invalid @enderror"
                                           id="birthdate" name="birthdate" value="{{ old('birthdate') }}" required>
                                    @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="age" class="form-label fw-semibold">Age</label>
                                    <input type="number" class="form-control @error('age') is-invalid @enderror"
                                           id="age" name="age" value="{{ old('age') }}"
                                           min="0" max="130" placeholder="Auto-calculated" readonly>
                                    <div class="form-text">Auto-calculated from birthdate.</div>
                                    @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="civil_status" class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" required>
                                        <option value="">Select</option>
                                        <option value="Single" {{ old('civil_status') === 'Single' ? 'selected' : '' }}>Single</option>
                                        <option value="Married" {{ old('civil_status') === 'Married' ? 'selected' : '' }}>Married</option>
                                        <option value="Widowed" {{ old('civil_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        <option value="Separated" {{ old('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                                        <option value="Divorced" {{ old('civil_status') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                    </select>
                                    @error('civil_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="nationality" class="form-label fw-semibold">Nationality</label>
                                    <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                                           id="nationality" name="nationality" value="{{ old('nationality', 'Filipino') }}">
                                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="religion" class="form-label fw-semibold">Religion</label>
                                    <input type="text" class="form-control @error('religion') is-invalid @enderror"
                                           id="religion" name="religion" value="{{ old('religion') }}">
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
                    <div id="collapseContact" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="voter_status" name="voter_status" value="1"
                                               {{ old('voter_status') ? 'checked' : '' }}>
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
                                    <input type="text" class="form-control @error('relationship_to_head') is-invalid @enderror"
                                           id="relationship_to_head" name="relationship_to_head"
                                           value="{{ old('relationship_to_head') }}" placeholder="e.g. Self, Spouse, Child" required>
                                    @error('relationship_to_head') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_household_head" name="is_household_head" value="1"
                                               {{ old('is_household_head') ? 'checked' : '' }}>
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
                                    <select class="form-select @error('educational_attainment') is-invalid @enderror"
                                            id="educational_attainment" name="educational_attainment">
                                        <option value="">Select</option>
                                        @foreach(['No Formal Education','Preschool','Elementary Level','Elementary Graduate','High School Level','High School Graduate','Vocational/Technical','Senior High Graduate','College Level','College Graduate','Post Graduate'] as $level)
                                            <option value="{{ $level }}" {{ old('educational_attainment') === $level ? 'selected' : '' }}>{{ $level }}</option>
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
                                            <option value="{{ $level }}" {{ old('education_level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                    @error('education_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="school_name" class="form-label fw-semibold">School Name</label>
                                    <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                           id="school_name" name="school_name" value="{{ old('school_name') }}" placeholder="Current/most recent school">
                                    @error('school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="grade_level" class="form-label fw-semibold">Grade/Year Level</label>
                                    <input type="text" class="form-control @error('grade_level') is-invalid @enderror"
                                           id="grade_level" name="grade_level" value="{{ old('grade_level') }}" placeholder="e.g. Grade 10, 3rd Year">
                                    @error('grade_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold d-block">&nbsp;</label>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="is_scholarship" name="is_scholarship" value="1"
                                               {{ old('is_scholarship') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_scholarship">Under Scholarship</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="scholarship_type" class="form-label fw-semibold">Scholarship Type</label>
                                    <input type="text" class="form-control @error('scholarship_type') is-invalid @enderror"
                                           id="scholarship_type" name="scholarship_type" value="{{ old('scholarship_type') }}" placeholder="e.g. DSWD, LGU, Private">
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
                    <div id="collapseEmployment" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="employment_status" class="form-label fw-semibold">Employment Status</label>
                                    <select class="form-select @error('employment_status') is-invalid @enderror"
                                            id="employment_status" name="employment_status">
                                        <option value="">Select</option>
                                        @foreach(['Employed','Self-Employed','Unemployed','Retired','Student','Homemaker','Disabled'] as $status)
                                            <option value="{{ $status }}" {{ old('employment_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="occupation" class="form-label fw-semibold">Occupation</label>
                                    <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                           id="occupation" name="occupation" value="{{ old('occupation') }}" placeholder="e.g. Teacher, Farmer">
                                    @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="job_title" class="form-label fw-semibold">Job Title</label>
                                    <input type="text" class="form-control @error('job_title') is-invalid @enderror"
                                           id="job_title" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Engineer, Cashier">
                                    @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="employer_name" class="form-label fw-semibold">Employer Name</label>
                                    <input type="text" class="form-control @error('employer_name') is-invalid @enderror"
                                           id="employer_name" name="employer_name" value="{{ old('employer_name') }}">
                                    @error('employer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="years_of_experience" class="form-label fw-semibold">Years Exp.</label>
                                    <input type="number" class="form-control @error('years_of_experience') is-invalid @enderror"
                                           id="years_of_experience" name="years_of_experience" value="{{ old('years_of_experience') }}" min="0" max="60">
                                    @error('years_of_experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="income_source" class="form-label fw-semibold">Income Source</label>
                                    <select class="form-select @error('income_source') is-invalid @enderror"
                                            id="income_source" name="income_source">
                                        <option value="">Select</option>
                                        @foreach(['Salary','Business','Farming/Agriculture','Fishing','Remittance','Pension','Freelance','Government Aid','Other'] as $source)
                                            <option value="{{ $source }}" {{ old('income_source') === $source ? 'selected' : '' }}>{{ $source }}</option>
                                        @endforeach
                                    </select>
                                    @error('income_source') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income" class="form-label fw-semibold">Monthly Income (₱)</label>
                                    <input type="number" class="form-control @error('monthly_income') is-invalid @enderror"
                                           id="monthly_income" name="monthly_income" value="{{ old('monthly_income') }}" min="0" step="0.01">
                                    @error('monthly_income') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="monthly_income_range" class="form-label fw-semibold">Income Range</label>
                                    <select class="form-select @error('monthly_income_range') is-invalid @enderror"
                                            id="monthly_income_range" name="monthly_income_range">
                                        <option value="">Select</option>
                                        @foreach(['Below ₱5,000','₱5,000 - ₱10,000','₱10,001 - ₱20,000','₱20,001 - ₱40,000','₱40,001 - ₱60,000','₱60,001 - ₱100,000','Above ₱100,000'] as $range)
                                            <option value="{{ $range }}" {{ old('monthly_income_range') === $range ? 'selected' : '' }}>{{ $range }}</option>
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
                    <div id="collapseSpecial" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pwd" name="is_pwd" value="1" {{ old('is_pwd') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_pwd">Person with Disability (PWD)</label>
                                    </div>
                                </div>
                                <div class="col-md-9" id="pwdTypeWrapper" style="display: {{ old('is_pwd') ? 'block' : 'none' }};">
                                    <label for="pwd_type" class="form-label fw-semibold">PWD Type</label>
                                    <input type="text" class="form-control @error('pwd_type') is-invalid @enderror"
                                           id="pwd_type" name="pwd_type" value="{{ old('pwd_type') }}" placeholder="e.g. Visual, Hearing, Mobility">
                                    @error('pwd_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_solo_parent" name="is_solo_parent" value="1" {{ old('is_solo_parent') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_solo_parent">Solo Parent</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_citizen" name="is_senior_citizen" value="1" {{ old('is_senior_citizen') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_senior_citizen">Senior Citizen</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_youth" name="is_youth" value="1" {{ old('is_youth') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_youth">Youth (15–30)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_student" name="is_student" value="1" {{ old('is_student') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_student">Student</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_ofw" name="is_ofw" value="1" {{ old('is_ofw') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_ofw">OFW</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_infant" name="is_infant" value="1" {{ old('is_infant') ? 'checked' : '' }}>
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
                                        <input type="checkbox" class="form-check-input" id="is_pagibig_member" name="is_pagibig_member" value="1" {{ old('is_pagibig_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pagibig_member">Pag-IBIG</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_philhealth_member" name="is_philhealth_member" value="1" {{ old('is_philhealth_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_philhealth_member">PhilHealth</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_sss_member" name="is_sss_member" value="1" {{ old('is_sss_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_sss_member">SSS</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_gsis_member" name="is_gsis_member" value="1" {{ old('is_gsis_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_gsis_member">GSIS</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">GOVERNMENT ASSISTANCE</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_4ps_member" name="is_4ps_member" value="1" {{ old('is_4ps_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_4ps_member">4Ps Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_pensioner" name="is_senior_pensioner" value="1" {{ old('is_senior_pensioner') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_senior_pensioner">Senior Pensioner (SSP)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_tupad_beneficiary" name="is_tupad_beneficiary" value="1" {{ old('is_tupad_beneficiary') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_tupad_beneficiary">TUPAD Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_akap_beneficiary" name="is_akap_beneficiary" value="1" {{ old('is_akap_beneficiary') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_akap_beneficiary">AKAP Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_educational_assistance" name="is_educational_assistance" value="1" {{ old('is_educational_assistance') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_educational_assistance">Educational Assistance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_emergency_assistance" name="is_emergency_assistance" value="1" {{ old('is_emergency_assistance') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_emergency_assistance">Emergency Assistance</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_slp_beneficiary" name="is_slp_beneficiary" value="1" {{ old('is_slp_beneficiary') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_slp_beneficiary">SLP Beneficiary</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="other_gov_assistance" class="form-label fw-semibold">Other Government Assistance</label>
                                    <input type="text" class="form-control @error('other_gov_assistance') is-invalid @enderror"
                                           id="other_gov_assistance" name="other_gov_assistance" value="{{ old('other_gov_assistance') }}" placeholder="e.g. DSWD, SAP, AICS">
                                    @error('other_gov_assistance') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <input type="checkbox" class="form-check-input" id="is_national_id_holder" name="is_national_id_holder" value="1" {{ old('is_national_id_holder') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_national_id_holder">National ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_senior_id_holder" name="is_senior_id_holder" value="1" {{ old('is_senior_id_holder') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_senior_id_holder">Senior Citizen ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_pwd_id_holder" name="is_pwd_id_holder" value="1" {{ old('is_pwd_id_holder') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pwd_id_holder">PWD ID</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_solo_parent_id_holder" name="is_solo_parent_id_holder" value="1" {{ old('is_solo_parent_id_holder') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_solo_parent_id_holder">Solo Parent ID</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">SOCIAL CATEGORIES</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_indigenous" name="is_indigenous" value="1" {{ old('is_indigenous') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_indigenous">Indigenous Person</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_veteran" name="is_veteran" value="1" {{ old('is_veteran') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_veteran">Veteran</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold text-muted small">COMMUNITY PARTICIPATION</label>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_disaster_volunteer" name="is_disaster_volunteer" value="1" {{ old('is_disaster_volunteer') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_disaster_volunteer">Disaster Volunteer</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_village_volunteer" name="is_village_volunteer" value="1" {{ old('is_village_volunteer') ? 'checked' : '' }}>
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
                                    <textarea class="form-control @error('skills') is-invalid @enderror" id="skills" name="skills"
                                              rows="2" placeholder="e.g. Carpentry, Sewing, Cooking, Driving">{{ old('skills') }}</textarea>
                                    @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="primary_skill" class="form-label fw-semibold">Primary Skill</label>
                                    <input type="text" class="form-control @error('primary_skill') is-invalid @enderror"
                                           id="primary_skill" name="primary_skill" value="{{ old('primary_skill') }}" placeholder="Main skill or specialization">
                                    @error('primary_skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="secondary_skill" class="form-label fw-semibold">Secondary Skill</label>
                                    <input type="text" class="form-control @error('secondary_skill') is-invalid @enderror"
                                           id="secondary_skill" name="secondary_skill" value="{{ old('secondary_skill') }}" placeholder="Additional skill">
                                    @error('secondary_skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="livelihood_programs" class="form-label fw-semibold">Livelihood Programs</label>
                                    <textarea class="form-control @error('livelihood_programs') is-invalid @enderror" id="livelihood_programs" name="livelihood_programs"
                                              rows="2" placeholder="e.g. TESDA, DTI, DSWD livelihood program">{{ old('livelihood_programs') }}</textarea>
                                    @error('livelihood_programs') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="special_qualifications" class="form-label fw-semibold">Special Qualifications</label>
                                    <textarea class="form-control @error('special_qualifications') is-invalid @enderror" id="special_qualifications" name="special_qualifications"
                                              rows="2" placeholder="e.g. Licensed Teacher, Registered Nurse">{{ old('special_qualifications') }}</textarea>
                                    @error('special_qualifications') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="community_activities" class="form-label fw-semibold">Community Activities/Volunteer Work</label>
                                    <textarea class="form-control @error('community_activities') is-invalid @enderror" id="community_activities" name="community_activities"
                                              rows="2" placeholder="e.g. Barangay Tanod, Health Volunteer">{{ old('community_activities') }}</textarea>
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
                    <div id="collapseEmergency" class="collapse" data-bs-parent="#requestAccordion">
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted small">EMERGENCY CONTACT</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_name" class="form-label fw-semibold">Contact Person</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                           id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
                                    @error('emergency_contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_number" class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" class="form-control @error('emergency_contact_number') is-invalid @enderror"
                                           id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number') }}">
                                    @error('emergency_contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="emergency_contact_relationship" class="form-label fw-semibold">Relationship</label>
                                    <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                           id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" placeholder="e.g. Spouse, Parent">
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
                                            <option value="{{ $type }}" {{ old('blood_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="years_of_residency" class="form-label fw-semibold">Years of Residency</label>
                                    <input type="number" class="form-control @error('years_of_residency') is-invalid @enderror"
                                           id="years_of_residency" name="years_of_residency" value="{{ old('years_of_residency') }}" min="0">
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
                        <i class="bi bi-info-circle me-1"></i>About This Request
                    </h6>
                    <hr>
                    <p class="text-muted small mb-3">
                        This request will be submitted to your village admin for review, then to the Super Admin for final approval.
                        Once approved, the new member will be added to your household.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Submit Request
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('is_pwd').addEventListener('change', function() {
        document.getElementById('pwdTypeWrapper').style.display = this.checked ? 'block' : 'none';
    });

    const firstName = document.getElementById('first_name');
    const middleName = document.getElementById('middle_name');
    const lastName = document.getElementById('last_name');
    const suffix = document.getElementById('suffix');
    const birthdate = document.getElementById('birthdate');
    const ageField = document.getElementById('age');
    const memberForm = firstName.form;

    const NAME_PATTERN = /^[\p{L}]+(?: [\p{L}]+)*$/u;
    const SUFFIX_PATTERN = /^(?:Jr\.|Sr\.|II|III|IV|V|VI|VII|VIII|IX|X)$/;

    function getFeedback(el) {
        let node = el.nextElementSibling;
        while (node) {
            if (node.classList && node.classList.contains('invalid-feedback')) return node;
            node = node.nextElementSibling;
        }
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        el.insertAdjacentElement('afterend', feedback);
        return feedback;
    }

    function setFieldError(el, message) {
        el.classList.add('is-invalid');
        getFeedback(el).textContent = message;
    }

    function clearFieldError(el) {
        el.classList.remove('is-invalid');
        getFeedback(el).textContent = '';
    }

    function autoCapitalize(value) {
        return value.replace(/(^|\s)(\p{L})/gu, function (match, space, letter) {
            return space + letter.toUpperCase();
        });
    }

    function titleCase(value) {
        value = value.replace(/\s+/g, ' ').trim();
        if (!value) return '';
        if (value === value.toUpperCase() || value === value.toLowerCase()) {
            value = value.toLowerCase();
        }
        return value.split(' ').map(function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function normalizeSuffix(value) {
        const key = value.trim().replace(/\.$/, '').toLowerCase();
        const map = {
            jr: 'Jr.', sr: 'Sr.', ii: 'II', iii: 'III', iv: 'IV', v: 'V',
            vi: 'VI', vii: 'VII', viii: 'VIII', ix: 'IX', x: 'X'
        };
        return map[key] || value.trim();
    }

    function checkName(el, label) {
        const value = el.value.trim();
        if (!value) return el.required ? label + ' is required.' : null;
        if (!NAME_PATTERN.test(value)) return label + ' must only contain letters - no numbers or special characters.';
        return null;
    }

    function checkMiddleName(el) {
        const value = el.value.trim();
        if (!value) return null;
        if (!/^\p{Lu}$/u.test(value)) return 'Middle name must be a single capital letter (e.g., M).';
        return null;
    }

    function checkSuffix(el) {
        const value = el.value.trim();
        if (!value) return null;
        if (!SUFFIX_PATTERN.test(value)) return 'Suffix must only be one of: Jr., Sr., II, III, IV, V, VI, VII, VIII, IX, X.';
        return null;
    }

    function validateField(el, checker, label) {
        const message = checker(el, label);
        if (message) {
            setFieldError(el, message);
            return false;
        }
        clearFieldError(el);
        return true;
    }

    function calculateAge() {
        const value = birthdate.value;
        if (!value) {
            ageField.value = '';
            return;
        }

        const today = new Date();
        const birth = new Date(value + 'T00:00:00');
        if (isNaN(birth.getTime()) || birth > today) {
            ageField.value = '';
            return;
        }

        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        ageField.value = Math.max(age, 0);
    }

    [firstName, lastName].forEach(function (el) {
        const label = el.id === 'first_name' ? 'First Name' : 'Last Name';

        el.addEventListener('input', function () {
            const caret = el.selectionStart;

            const cleaned = el.value.replace(/[^\p{L} ]/gu, '').replace(/ {2,}/g, ' ');
            if (cleaned !== el.value) {
                el.value = cleaned;
                el.setSelectionRange(Math.min(caret, cleaned.length), Math.min(caret, cleaned.length));
            }

            const capitalized = autoCapitalize(el.value);
            if (capitalized !== el.value) {
                el.value = capitalized;
                el.setSelectionRange(Math.min(caret, capitalized.length), Math.min(caret, capitalized.length));
            }

            clearFieldError(el);
        });

        el.addEventListener('blur', function () {
            el.value = titleCase(el.value);
            validateField(el, checkName, label);
        });
    });

    middleName.addEventListener('input', function () {
        const cleaned = middleName.value.replace(/[^\p{L}]/gu, '').slice(0, 1);
        middleName.value = cleaned ? cleaned.toUpperCase() : '';
        clearFieldError(middleName);
    });

    middleName.addEventListener('blur', function () {
        validateField(middleName, checkMiddleName);
    });

    suffix.addEventListener('input', function () {
        suffix.value = suffix.value.replace(/[^\p{L}.]/gu, '');
        clearFieldError(suffix);
    });

    suffix.addEventListener('blur', function () {
        suffix.value = normalizeSuffix(suffix.value);
        validateField(suffix, checkSuffix);
    });

    birthdate.addEventListener('change', calculateAge);
    birthdate.addEventListener('input', calculateAge);

    memberForm.addEventListener('submit', function (event) {
        firstName.value = titleCase(firstName.value);
        lastName.value = titleCase(lastName.value);
        middleName.value = middleName.value.trim().slice(0, 1);
        if (middleName.value) middleName.value = middleName.value.toUpperCase();
        suffix.value = normalizeSuffix(suffix.value);
        calculateAge();

        let firstInvalid = null;

        memberForm.querySelectorAll('[required]').forEach(function (el) {
            if (!el.value.trim()) {
                const label = el.id ? (memberForm.querySelector('label[for="' + el.id + '"]') || {}).textContent : null;
                setFieldError(el, (label ? label.replace('*', '').trim() : 'This field') + ' is required.');
                if (!firstInvalid) firstInvalid = el;
            }
        });

        [[firstName, checkName, 'First Name'], [lastName, checkName, 'Last Name'],
         [middleName, checkMiddleName], [suffix, checkSuffix]].forEach(function (entry) {
            if (!validateField(entry[0], entry[1], entry[2]) && !firstInvalid) {
                firstInvalid = entry[0];
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
        }
    });

    calculateAge();
</script>
@endpush