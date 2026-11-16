@extends('layouts.app')

@section('title', 'Edit Household Member')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Edit Household Member</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="{{ route('households.my-household') }}">My Household</a></li><li class="breadcrumb-item active" aria-current="page">Edit Member</li></ol></nav>
        </div>
        <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to My Household</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <h6 class="fw-semibold mb-1"><i class="bi bi-person-lines-fill me-2 text-info"></i>{{ $resident->full_name }}</h6>
                <p class="text-muted small mb-0">Update this member's information. Changes are saved only after Admin and Super Admin approval.</p>
            </div>
            @if($errors->any())
                <div class="alert alert-danger"><strong>Please correct the following errors:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('households.members.update', $resident) }}" method="POST" id="memberForm" novalidate>
                @csrf
                @method('PUT')
                @include('residents.partials.member-profile-fields', ['resident' => $resident, 'showHouseholdSelect' => false, 'allowProfilePicture' => false])
                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                    <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('memberForm');
    const firstName = document.getElementById('first_name');
    const middleName = document.getElementById('middle_name');
    const lastName = document.getElementById('last_name');
    const birthdate = document.getElementById('birthdate');
    const age = document.getElementById('age');
    const phone = document.getElementById('contact_number');
    const pwd = document.getElementById('is_pwd');
    const pwdType = document.getElementById('pwdTypeWrapper');
    const titleCase = value => value.toLowerCase().replace(/(^|\s)(\p{L})/gu, (_, space, letter) => space + letter.toUpperCase());
    [firstName, lastName].forEach(field => field.addEventListener('input', function () {
        this.value = titleCase(this.value.replace(/[^\p{L} ]/gu, '').replace(/\s{2,}/g, ' '));
    }));
    middleName.addEventListener('input', function () { this.value = this.value.replace(/[^\p{L}]/gu, '').slice(0, 1).toUpperCase(); });
    phone.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
    pwd.addEventListener('change', function () { pwdType.style.display = this.checked ? 'block' : 'none'; });
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
        firstName.value = titleCase(firstName.value.trim());
        lastName.value = titleCase(lastName.value.trim());
        middleName.value = middleName.value.trim().slice(0, 1).toUpperCase();
        phone.value = phone.value.replace(/\D/g, '');
        calculateAge();
    });
    calculateAge();
});
</script>
@endpush
