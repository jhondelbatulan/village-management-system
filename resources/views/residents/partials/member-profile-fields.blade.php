@php
    $resident = $resident ?? new \App\Models\Resident();
    $showHouseholdSelect = $showHouseholdSelect ?? false;
    $allowProfilePicture = $allowProfilePicture ?? false;
@endphp

<div class="row g-3">
    @if($showHouseholdSelect)
        <div class="col-12">
            <label for="household_id" class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
            <select class="form-select @error('household_id') is-invalid @enderror" id="household_id" name="household_id" required>
                <option value="">Select household</option>
                @foreach($households as $household)
                    <option value="{{ $household->id }}" @selected((string) old('household_id', $resident->household_id) === (string) $household->id)>
                        {{ $household->household_number }} — {{ $household->household_head_name }}
                    </option>
                @endforeach
            </select>
            @error('household_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif

    <div class="col-md-4">
        <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" maxlength="255" autocomplete="given-name" value="{{ old('first_name', $resident->first_name) }}" required>
        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
        <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name" name="middle_name" maxlength="1" autocomplete="additional-name" value="{{ old('middle_name', $resident->middle_name) }}" placeholder="Single capital letter">
        <div class="form-text">Single capital letter only.</div>
        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" maxlength="255" autocomplete="family-name" value="{{ old('last_name', $resident->last_name) }}" required>
        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="suffix" class="form-label fw-semibold">Suffix</label>
        <input type="text" class="form-control @error('suffix') is-invalid @enderror" id="suffix" name="suffix" list="member-suffix-options" maxlength="10" value="{{ old('suffix', $resident->suffix) }}" placeholder="e.g. Jr., III" autocomplete="off">
        <datalist id="member-suffix-options">@foreach(['Jr.','Sr.','II','III','IV','V','VI','VII','VIII','IX','X'] as $suffix)<option value="{{ $suffix }}">@endforeach</datalist>
        @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
            <option value="">Select gender</option>
            @foreach(['Male','Female','Other'] as $option)<option value="{{ $option }}" @selected(old('gender', $resident->gender) === $option)>{{ $option }}</option>@endforeach
        </select>
        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="birthdate" class="form-label fw-semibold">Birthdate <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate', $resident->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}" required>
        @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="age" class="form-label fw-semibold">Age</label>
        <input type="number" class="form-control @error('age') is-invalid @enderror" id="age" name="age" value="{{ old('age', $resident->age) }}" readonly>
        <div class="form-text">Auto-calculated from birthdate.</div>
        @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="civil_status" class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
        <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" required>
            <option value="">Select civil status</option>
            @foreach(['Single','Married','Widowed','Separated','Divorced'] as $option)<option value="{{ $option }}" @selected(old('civil_status', $resident->civil_status) === $option)>{{ $option }}</option>@endforeach
        </select>
        @error('civil_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
        <input type="text" inputmode="numeric" pattern="[0-9]{7,20}" maxlength="20" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number', $resident->contact_number) }}" placeholder="Digits only">
        @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="email" class="form-label fw-semibold">Email Address</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $resident->email) }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="occupation" class="form-label fw-semibold">Occupation</label>
        <input type="text" class="form-control @error('occupation') is-invalid @enderror" id="occupation" name="occupation" value="{{ old('occupation', $resident->occupation) }}">
        @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="relationship_to_head" class="form-label fw-semibold">Relationship to Head</label>
        <input type="text" class="form-control @error('relationship_to_head') is-invalid @enderror" id="relationship_to_head" name="relationship_to_head" value="{{ old('relationship_to_head', $resident->relationship_to_head) }}">
        @error('relationship_to_head') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12"><hr><h6 class="fw-semibold text-primary mb-0">Additional Resident Information</h6></div>
    <div class="col-md-4">
        <label for="monthly_income_range" class="form-label fw-semibold">Income Range</label>
        <select class="form-select @error('monthly_income_range') is-invalid @enderror" id="monthly_income_range" name="monthly_income_range">
            <option value="">Select income range</option>
            @foreach(['Below ₱5,000','₱5,000 - ₱10,000','₱10,001 - ₱20,000','₱20,001 - ₱40,000','₱40,001 - ₱60,000','₱60,001 - ₱100,000','Above ₱100,000'] as $range)<option value="{{ $range }}" @selected(old('monthly_income_range', $resident->monthly_income_range) === $range)>{{ $range }}</option>@endforeach
        </select>
        @error('monthly_income_range') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="educational_attainment" class="form-label fw-semibold">Highest Educational Attainment</label>
        <select class="form-select @error('educational_attainment') is-invalid @enderror" id="educational_attainment" name="educational_attainment">
            <option value="">Select attainment</option>
            @foreach(['No Formal Education','Preschool','Elementary Level','Elementary Graduate','High School Level','High School Graduate','Vocational/Technical','Senior High Graduate','College Level','College Graduate','Post Graduate'] as $option)<option value="{{ $option }}" @selected(old('educational_attainment', $resident->educational_attainment) === $option)>{{ $option }}</option>@endforeach
        </select>
        @error('educational_attainment') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="employment_status" class="form-label fw-semibold">Employment Status</label>
        <select class="form-select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status">
            <option value="">Select employment status</option>
            @foreach(['Employed','Self-Employed','Unemployed','Retired','Student','Homemaker','Disabled'] as $option)<option value="{{ $option }}" @selected(old('employment_status', $resident->employment_status) === $option)>{{ $option }}</option>@endforeach
        </select>
        @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    @foreach([
        'special_population' => ['Special Population Classification', ['is_pwd'=>'PWD','is_solo_parent'=>'Solo Parent','is_senior_citizen'=>'Senior Citizen','is_youth'=>'Youth','is_student'=>'Student','is_ofw'=>'OFW','is_infant'=>'Infant/Child']],
        'government_memberships' => ['Government Memberships', ['is_pagibig_member'=>'Pag-IBIG','is_philhealth_member'=>'PhilHealth','is_sss_member'=>'SSS','is_gsis_member'=>'GSIS']],
        'government_assistance' => ['Government Assistance Applied', ['is_4ps_member'=>'4Ps','is_senior_pensioner'=>'Senior Pensioner']],
    ] as $group => [$label, $options])
        <div class="col-md-6">
            <label class="form-label fw-semibold d-block">{{ $label }}</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($options as $field => $option)
                    <label class="form-check"><input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field, $resident->{$field}))><span class="form-check-label">{{ $option }}</span></label>
                @endforeach
            </div>
        </div>
    @endforeach
    <div class="col-md-6" id="pwdTypeWrapper" style="display: {{ old('is_pwd', $resident->is_pwd) ? 'block' : 'none' }}">
        <label for="pwd_type" class="form-label fw-semibold">PWD Type</label>
        <input type="text" class="form-control @error('pwd_type') is-invalid @enderror" id="pwd_type" name="pwd_type" maxlength="255" value="{{ old('pwd_type', $resident->pwd_type) }}">
        @error('pwd_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="skills" class="form-label fw-semibold">Special Skills</label>
        <input type="text" class="form-control @error('skills') is-invalid @enderror" id="skills" name="skills" maxlength="1000" value="{{ old('skills', $resident->skills) }}" placeholder="e.g. Carpentry, Sewing, Cooking">
        @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="blood_type" class="form-label fw-semibold">Blood Type</label>
        <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
            <option value="">Select blood type</option>
            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown'] as $option)<option value="{{ $option }}" @selected(old('blood_type', $resident->blood_type) === $option)>{{ $option }}</option>@endforeach
        </select>
        @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    @if($showHouseholdSelect)
        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check mb-2"><input class="form-check-input" type="checkbox" id="is_household_head" name="is_household_head" value="1" @checked(old('is_household_head', $resident->is_household_head))><span class="form-check-label">Household Head</span></label>
        </div>
    @endif
    @if($allowProfilePicture)
        <div class="col-md-6">
            <label for="profile_picture" class="form-label fw-semibold">Profile Picture</label>
            <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
            @error('profile_picture') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6" id="picturePreviewWrapper" style="display:none">
            <label class="form-label fw-semibold">Preview</label><br>
            <img id="picturePreview" src="#" alt="Preview" class="rounded-circle" width="80" height="80" style="object-fit:cover">
        </div>
    @endif
</div>
