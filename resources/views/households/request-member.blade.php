@extends('layouts.app')

@section('title', 'Request to Add Household Member')

@section('content')
<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="page-title mb-1">Request to Add Household Member</h4>
        <p class="page-subtitle text-muted mb-0">Complete the member information. The request will be reviewed by your Admin and finalized by the Super Admin.</p>
    </div>
    <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to My Household</a>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('households.request-member') }}" method="POST" id="memberForm" novalidate>
            @csrf
            @include('residents.partials.member-profile-fields', ['resident' => new \App\Models\Resident(), 'showHouseholdSelect' => false, 'allowProfilePicture' => false])
            <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
            </div>
        </form>
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
