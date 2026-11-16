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

    {{-- Keep registration and editing on the same resident information form. --}}
    @include('residents.partials.member-profile-fields', [
        'resident' => $resident,
        'households' => $households,
        'showHouseholdSelect' => true,
        'allowProfilePicture' => true,
    ])

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
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('residents.update', $resident) }}"]');
    const first = document.getElementById('first_name');
    const middle = document.getElementById('middle_name');
    const last = document.getElementById('last_name');
    const birthdate = document.getElementById('birthdate');
    const age = document.getElementById('age');
    const phone = document.getElementById('contact_number');
    const titleCase = value => value.toLowerCase().replace(/(^|\s)(\p{L})/gu, (_, space, letter) => space + letter.toUpperCase());
    [first, last].forEach(field => field.addEventListener('input', function () {
        this.value = titleCase(this.value.replace(/[^\p{L} ]/gu, '').replace(/\s{2,}/g, ' '));
    }));
    middle.addEventListener('input', function () { this.value = this.value.replace(/[^\p{L}]/gu, '').slice(0, 1).toUpperCase(); });
    phone.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
    document.getElementById('profile_picture').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = event => { document.getElementById('picturePreview').src = event.target.result; };
        reader.readAsDataURL(file);
    });
    const pwd = document.getElementById('is_pwd');
    pwd.addEventListener('change', function () { document.getElementById('pwdTypeWrapper').style.display = this.checked ? 'block' : 'none'; });
    function calculateAge() {
        if (!birthdate.value) { age.value = ''; return; }
        const birth = new Date(birthdate.value + 'T00:00:00');
        if (Number.isNaN(birth.getTime()) || birth >= new Date()) { age.value = ''; return; }
        const today = new Date();
        let years = today.getFullYear() - birth.getFullYear();
        if (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) years--;
        age.value = years;
    }
    birthdate.addEventListener('change', calculateAge);
    form.addEventListener('submit', function () {
        first.value = titleCase(first.value.trim());
        last.value = titleCase(last.value.trim());
        middle.value = middle.value.trim().slice(0, 1).toUpperCase();
        phone.value = phone.value.replace(/\D/g, '');
        calculateAge();
    });
    calculateAge();
});
</script>
@endpush
