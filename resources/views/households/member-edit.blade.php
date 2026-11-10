@extends('layouts.app')

@section('title', 'Edit Household Member')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Edit Household Member</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('households.my-household') }}">My Household</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Member</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to My Household
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <h6 class="fw-semibold mb-1">
                    <i class="bi bi-person-lines-fill me-2 text-info"></i>
                    {{ $resident->full_name ?? ($resident->first_name . ' ' . $resident->last_name) }}
                </h6>
                <p class="text-muted small mb-0">
                    Update this member's personal details. The household and village assignment cannot be changed here.
                </p>
            </div>

            <form action="{{ route('households.members.update', $resident) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('first_name') is-invalid @enderror"
                               id="first_name"
                               name="first_name"
                               value="{{ old('first_name', $resident->first_name) }}"
                               required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                        <input type="text"
                               class="form-control @error('middle_name') is-invalid @enderror"
                               id="middle_name"
                               name="middle_name"
                               value="{{ old('middle_name', $resident->middle_name) }}"
                               placeholder="e.g., M">
                        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('last_name') is-invalid @enderror"
                               id="last_name"
                               name="last_name"
                               value="{{ old('last_name', $resident->last_name) }}"
                               required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="suffix" class="form-label fw-semibold">Suffix</label>
                        <select class="form-select @error('suffix') is-invalid @enderror" id="suffix" name="suffix">
                            <option value="">—</option>
                            @foreach(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'] as $suffixOption)
                                <option value="{{ $suffixOption }}" @selected(old('suffix', $resident->suffix) === $suffixOption)>
                                    {{ $suffixOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                            <option value="">Select gender</option>
                            @foreach(['Male', 'Female', 'Other'] as $genderOption)
                                <option value="{{ $genderOption }}" @selected(old('gender', $resident->gender) === $genderOption)>
                                    {{ $genderOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="birthdate" class="form-label fw-semibold">Birthdate <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('birthdate') is-invalid @enderror"
                               id="birthdate"
                               name="birthdate"
                               value="{{ old('birthdate', $resident->birthdate ? \Illuminate\Support\Carbon::parse($resident->birthdate)->format('Y-m-d') : '') }}"
                               max="{{ now()->subDay()->format('Y-m-d') }}"
                               required>
                        @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="civil_status" class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" required>
                            <option value="">Select civil status</option>
                            @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $statusOption)
                                <option value="{{ $statusOption }}" @selected(old('civil_status', $resident->civil_status) === $statusOption)>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('civil_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                        <input type="text"
                               class="form-control @error('contact_number') is-invalid @enderror"
                               id="contact_number"
                               name="contact_number"
                               value="{{ old('contact_number', $resident->contact_number) }}"
                               placeholder="e.g., 0917 123 4567">
                        @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', $resident->email) }}"
                               placeholder="member@example.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="occupation" class="form-label fw-semibold">Occupation</label>
                        <input type="text"
                               class="form-control @error('occupation') is-invalid @enderror"
                               id="occupation"
                               name="occupation"
                               value="{{ old('occupation', $resident->occupation) }}">
                        @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="relationship_to_head" class="form-label fw-semibold">Relationship to Head</label>
                        <input type="text"
                               class="form-control @error('relationship_to_head') is-invalid @enderror"
                               id="relationship_to_head"
                               name="relationship_to_head"
                               value="{{ old('relationship_to_head', $resident->relationship_to_head) }}"
                               placeholder="e.g., Spouse, Child">
                        @error('relationship_to_head') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                    <a href="{{ route('households.my-household') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
