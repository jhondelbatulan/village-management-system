@extends('layouts.app')

@section('title', 'Register Resident')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Register Resident</h4>
            <p class="page-subtitle text-muted mb-0">Add a new resident to the system with complete profile information.</p>
        </div>
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Residents
        </a>
    </div>
</div>

@if(!$hasHouseholds)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>No household records available. Please create a household first.</strong>
        <p class="mb-0 mt-1">A resident must belong to a household. Please register a household before adding residents.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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

<form action="{{ route('residents.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    {{-- Same member profile fields used by resident household edit and add-member forms. --}}
    @include('residents.partials.member-profile-fields', [
        'resident' => new \App\Models\Resident(),
        'households' => $households,
        'showHouseholdSelect' => true,
        'allowProfilePicture' => true,
    ])

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" {{ !$hasHouseholds ? 'disabled' : '' }}>
            <i class="bi bi-check-lg me-1"></i>Register Resident
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const contactNumberInput = document.getElementById('contact_number');
    if (contactNumberInput) contactNumberInput.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('picturePreview').src = ev.target.result;
                document.getElementById('picturePreviewWrapper').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('is_pwd').addEventListener('change', function() {
        document.getElementById('pwdTypeWrapper').style.display = this.checked ? 'block' : 'none';
    });

    const firstName = document.getElementById('first_name');
    const middleName = document.getElementById('middle_name');
    const lastName = document.getElementById('last_name');
    const suffix = document.getElementById('suffix');
    const birthdate = document.getElementById('birthdate');
    const ageField = document.getElementById('age');
    const residentForm = firstName.form;

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

    residentForm.addEventListener('submit', function (event) {
        firstName.value = titleCase(firstName.value);
        lastName.value = titleCase(lastName.value);
        middleName.value = middleName.value.trim().slice(0, 1);
        if (middleName.value) middleName.value = middleName.value.toUpperCase();
        suffix.value = normalizeSuffix(suffix.value);
        calculateAge();

        let firstInvalid = null;

        residentForm.querySelectorAll('[required]').forEach(function (el) {
            if (!el.value.trim()) {
                const label = el.id ? (residentForm.querySelector('label[for="' + el.id + '"]') || {}).textContent : null;
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
