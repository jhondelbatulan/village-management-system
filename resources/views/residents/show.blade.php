@extends('layouts.app')

@section('title', 'Resident Profile')

@section('content')
@php
    $population = $resident->special_population_types ?? [];
    $memberships = $resident->government_memberships ?? [];
    $assistance = collect(['4Ps' => $resident->is_4ps_member, 'Senior Pensioner' => $resident->is_senior_pensioner])->filter()->keys()->values()->all();
    $sections = [
        'Personal Information' => [
            'First Name' => $resident->first_name,
            'Middle Name' => $resident->middle_name,
            'Last Name' => $resident->last_name,
            'Suffix' => $resident->suffix,
            'Gender' => $resident->gender,
            'Birthdate' => $resident->birthdate?->format('M d, Y'),
            'Age' => $resident->age,
            'Civil Status' => $resident->civil_status,
            'Nationality' => $resident->nationality,
            'Religion' => $resident->religion,
            'Blood Type' => $resident->blood_type,
            'Household Head' => $resident->is_household_head ? 'Yes' : null,
        ],
        'Contact Information' => [
            'Contact Number' => $resident->contact_number,
            'Email' => $resident->email,
            'Registered Voter' => $resident->voter_status ? 'Yes' : null,
        ],
        'Education' => [
            'Highest Educational Attainment' => $resident->educational_attainment,
            'Current Education Level' => $resident->education_level,
            'School Name' => $resident->school_name,
            'Grade/Year Level' => $resident->grade_level,
            'Scholarship' => $resident->is_scholarship ? ($resident->scholarship_type ?: 'Yes') : null,
        ],
        'Employment and Income' => [
            'Employment Status' => $resident->employment_status,
            'Occupation' => $resident->occupation,
            'Job Title' => $resident->job_title,
            'Employer' => $resident->employer_name,
            'Years of Experience' => $resident->years_of_experience,
            'Income Source' => $resident->income_source,
            'Monthly Income' => $resident->monthly_income_formatted,
            'Income Range' => $resident->monthly_income_range,
        ],
        'Special Population' => [
            'Classification' => $population,
            'PWD Type' => $resident->pwd_type,
        ],
        'Government Memberships and Assistance' => [
            'Memberships' => $memberships,
            'Assistance Applied' => $assistance,
            'Other Assistance' => $resident->other_gov_assistance,
        ],
        'Skills and Community' => [
            'Special Skills' => $resident->skills,
            'Primary Skill' => $resident->primary_skill,
            'Secondary Skill' => $resident->secondary_skill,
            'Livelihood Programs' => $resident->livelihood_programs,
            'Special Qualifications' => $resident->special_qualifications,
            'Community Activities' => $resident->community_activities,
        ],
        'Emergency Contact' => [
            'Name' => $resident->emergency_contact_name,
            'Contact Number' => $resident->emergency_contact_number,
            'Relationship' => $resident->emergency_contact_relationship,
        ],
        'Household and Residency' => [
            'Relationship to Head' => $resident->relationship_to_head,
            'Years of Residency' => $resident->years_of_residency,
            'Household' => $resident->household?->household_number,
            'Village' => $resident->village?->village_name,
        ],
    ];
@endphp

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $resident->profile_picture_url }}" alt="{{ $resident->full_name }}" class="rounded-circle" width="64" height="64" style="object-fit:cover">
            <div>
                <h4 class="page-title mb-1">{{ $resident->full_name }}</h4>
                <p class="page-subtitle text-muted mb-0">
                    {{ $resident->resident_code }}
                    <span class="badge bg-{{ $resident->status === 'active' ? 'success' : 'secondary' }} ms-1">{{ ucfirst($resident->status) }}</span>
                    @if($resident->approval_status === 'approved') <span class="badge bg-success ms-1">Approved</span>
                    @elseif($resident->approval_status === 'admin_approved') <span class="badge bg-info ms-1">Admin Approved</span>
                    @elseif($resident->approval_status === 'pending') <span class="badge bg-warning text-dark ms-1">Pending Approval</span>
                    @elseif($resident->approval_status === 'rejected') <span class="badge bg-danger ms-1">Rejected</span>
                    @else <span class="badge bg-secondary ms-1">Draft</span> @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
            @if(auth()->user()->hasRole('secretary'))
                <a href="{{ route('residents.edit', $resident) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Edit</a>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    @foreach($sections as $title => $pairs)
        @php $visiblePairs = collect($pairs)->filter(fn ($value) => is_array($value) ? count($value) > 0 : filled($value)); @endphp
        @if($visiblePairs->isNotEmpty())
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="mb-0 fw-semibold">{{ $title }}</h6></div>
                    <div class="card-body px-4 pb-4">
                        <dl class="row mb-0">
                            @foreach($visiblePairs as $label => $value)
                                <dt class="col-sm-5 text-muted small fw-normal mb-2">{{ $label }}</dt>
                                <dd class="col-sm-7 fw-semibold mb-2">
                                    @if(is_array($value))
                                        <div class="d-flex flex-wrap gap-1">@foreach($value as $item)<span class="badge bg-primary">{{ $item }}</span>@endforeach</div>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="mb-0 fw-semibold"><i class="bi bi-shield-check me-2 text-primary"></i>Approval Workflow</h6></div>
    <div class="card-body px-4 pb-4">
        <p class="mb-3"><span class="text-muted me-2">Approval status</span>
            @if($resident->approval_status === 'approved') <span class="badge bg-success">Approved</span>
            @elseif($resident->approval_status === 'admin_approved') <span class="badge bg-info">Admin Approved — Awaiting Super Admin</span>
            @elseif($resident->approval_status === 'pending') <span class="badge bg-warning text-dark">Pending Review</span>
            @elseif($resident->approval_status === 'rejected') <span class="badge bg-danger">Rejected</span>
            @else <span class="badge bg-secondary">Draft</span> @endif
        </p>
        @if($resident->submittedBy)
            <p><span class="text-muted me-2">Submitted by</span>{{ $resident->submittedBy->full_name }}</p>
        @endif
        @if($resident->admin_approved_by)
            <p><span class="text-muted me-2">Admin approved by</span>{{ $resident->adminApprovedBy?->full_name }}</p>
            @if($resident->admin_approved_at)<p><span class="text-muted me-2">Admin approval date</span>{{ $resident->admin_approved_at->format('M d, Y h:i A') }}</p>@endif
        @endif
        @if($resident->approved_by && $resident->approval_status !== 'admin_approved')
            <p><span class="text-muted me-2">Final decision by</span>{{ $resident->approvedBy?->full_name }}</p>
            @if($resident->approved_at)<p><span class="text-muted me-2">Decision date</span>{{ $resident->approved_at->format('M d, Y h:i A') }}</p>@endif
        @endif
        @if($resident->remarks)<p><span class="text-muted me-2">Remarks</span>{{ $resident->remarks }}</p>@endif
        @if(auth()->user()->hasRole('secretary') && in_array($resident->approval_status, ['draft', 'rejected']))
            <form action="{{ route('residents.submit', $resident) }}" method="POST" data-confirm="Submit this resident for admin review?">
                @csrf
                <button type="submit" class="btn btn-warning"><i class="bi bi-send me-1"></i>Submit for Approval</button>
            </form>
        @endif
        @if((auth()->user()->hasRole('admin') && $resident->approval_status === 'pending') || (auth()->user()->hasRole('super_admin') && $resident->approval_status === 'admin_approved'))
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="bi bi-check-lg me-1"></i>{{ auth()->user()->hasRole('admin') ? 'Approve (Admin)' : 'Final Approve' }}</button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Reject</button>
            </div>
        @endif
    </div>
</div>

@if((auth()->user()->hasRole('admin') && $resident->approval_status === 'pending') || (auth()->user()->hasRole('super_admin') && $resident->approval_status === 'admin_approved'))
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form action="{{ route('residents.approve', $resident) }}" method="POST">
                @csrf <input type="hidden" name="approval_status" value="approved">
                <div class="modal-header"><h5 class="modal-title">Approve Resident</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><label for="approve_remarks" class="form-label">Remarks (optional)</label><textarea class="form-control" id="approve_remarks" name="remarks" rows="3"></textarea></div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Approve</button></div>
            </form>
        </div></div>
    </div>
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form action="{{ route('residents.approve', $resident) }}" method="POST">
                @csrf <input type="hidden" name="approval_status" value="rejected">
                <div class="modal-header"><h5 class="modal-title">Reject Resident</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body"><label for="reject_remarks" class="form-label">Reason <span class="text-danger">*</span></label><textarea class="form-control" id="reject_remarks" name="remarks" rows="3" required></textarea></div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
            </form>
        </div></div>
    </div>
@endif
@endsection
