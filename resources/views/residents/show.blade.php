@extends('layouts.app')

@section('title', 'Resident Profile')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $resident->profile_picture_url }}"
                 alt="{{ $resident->full_name }}"
                 class="rounded-circle" width="64" height="64" style="object-fit: cover;">
            <div>
                <h4 class="page-title mb-1">{{ $resident->full_name }}</h4>
                <p class="page-subtitle text-muted mb-0">
                    <span class="me-2">{{ $resident->resident_code }}</span>
                    @if($resident->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($resident->status === 'inactive')
                        <span class="badge bg-secondary">Inactive</span>
                    @elseif($resident->status === 'deceased')
                        <span class="badge bg-danger">Deceased</span>
                    @elseif($resident->status === 'transferred')
                        <span class="badge bg-warning text-dark">Transferred</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($resident->status) }}</span>
                    @endif
                    @if($resident->approval_status === 'approved')
                        <span class="badge bg-success ms-1">Approved</span>
                    @elseif($resident->approval_status === 'admin_approved')
                        <span class="badge bg-info ms-1">Admin Approved</span>
                    @elseif($resident->approval_status === 'pending')
                        <span class="badge bg-warning text-dark ms-1">Pending Approval</span>
                    @elseif($resident->approval_status === 'rejected')
                        <span class="badge bg-danger ms-1">Rejected</span>
                    @else
                        <span class="badge bg-secondary ms-1">Draft</span>
                    @endif
                    @if(!empty($resident->special_population_types))
                        @foreach($resident->special_population_types as $type)
                            <span class="badge bg-info ms-1">{{ $type }}</span>
                        @endforeach
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
            @if(auth()->user()->hasRole('secretary') && in_array($resident->approval_status, ['draft', 'rejected']))
                <a href="{{ route('residents.edit', $resident) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square me-1"></i>Edit
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Personal Information
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">First Name</label>
                        <p class="fw-semibold mb-0">{{ $resident->first_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Middle Name</label>
                        <p class="fw-semibold mb-0">{{ $resident->middle_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <p class="fw-semibold mb-0">{{ $resident->last_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Suffix</label>
                        <p class="fw-semibold mb-0">{{ $resident->suffix ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Gender</label>
                        <p class="fw-semibold mb-0">{{ $resident->gender ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Birthdate</label>
                        <p class="fw-semibold mb-0">{{ $resident->birthdate?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Age</label>
                        <p class="fw-semibold mb-0">{{ $resident->age ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Civil Status</label>
                        <p class="fw-semibold mb-0">{{ $resident->civil_status ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Nationality</label>
                        <p class="fw-semibold mb-0">{{ $resident->nationality ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Religion</label>
                        <p class="fw-semibold mb-0">{{ $resident->religion ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Blood Type</label>
                        <p class="fw-semibold mb-0">{{ $resident->blood_type ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-envelope me-2 text-primary"></i>Contact Information
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Contact Number</label>
                        <p class="fw-semibold mb-0">{{ $resident->contact_number ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email</label>
                        <p class="fw-semibold mb-0">{{ $resident->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Voter Status</label>
                        <p class="mb-0">
                            @if($resident->voter_status)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Registered</span>
                            @else
                                <span class="badge bg-secondary">Not Registered</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-mortarboard me-2 text-primary"></i>Education
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Educational Attainment</label>
                        <p class="fw-semibold mb-0">{{ $resident->educational_attainment ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Current Education Level</label>
                        <p class="fw-semibold mb-0">{{ $resident->education_level ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">School Name</label>
                        <p class="fw-semibold mb-0">{{ $resident->school_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Grade/Year Level</label>
                        <p class="fw-semibold mb-0">{{ $resident->grade_level ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Scholarship</label>
                        <p class="mb-0">
                            @if($resident->is_scholarship)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ $resident->scholarship_type ?: 'Yes' }}</span>
                            @else
                                <span class="badge bg-secondary">None</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-briefcase me-2 text-primary"></i>Employment & Income
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Employment Status</label>
                        <p class="fw-semibold mb-0">{{ $resident->employment_status ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Occupation</label>
                        <p class="fw-semibold mb-0">{{ $resident->occupation ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Job Title</label>
                        <p class="fw-semibold mb-0">{{ $resident->job_title ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Employer</label>
                        <p class="fw-semibold mb-0">{{ $resident->employer_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Years of Experience</label>
                        <p class="fw-semibold mb-0">{{ $resident->years_of_experience ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Income Source</label>
                        <p class="fw-semibold mb-0">{{ $resident->income_source ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Monthly Income</label>
                        <p class="fw-semibold mb-0">{{ $resident->monthly_income_formatted ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Income Range</label>
                        <p class="fw-semibold mb-0">{{ $resident->monthly_income_range ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-check me-2 text-primary"></i>Government Memberships & Assistance
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted small">Memberships</label>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($resident->government_memberships as $member)
                                <span class="badge bg-primary">{{ $member }}</span>
                            @empty
                                <span class="text-muted">None</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Government Assistance</label>
                        <div class="d-flex flex-wrap gap-1">
                            @if($resident->is_4ps_member)
                                <span class="badge bg-warning text-dark">4Ps</span>
                            @endif
                            @if($resident->is_senior_pensioner)
                                <span class="badge bg-warning text-dark">Senior Pension</span>
                            @endif
                            @if($resident->other_gov_assistance)
                                <span class="badge bg-info">{{ $resident->other_gov_assistance }}</span>
                            @endif
                            @if(!$resident->is_4ps_member && !$resident->is_senior_pensioner && !$resident->other_gov_assistance)
                                <span class="text-muted">None</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-tools me-2 text-primary"></i>Skills & Community
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Skills</label>
                        <p class="fw-semibold mb-0">{{ $resident->skills ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Livelihood Programs</label>
                        <p class="fw-semibold mb-0">{{ $resident->livelihood_programs ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Special Qualifications</label>
                        <p class="fw-semibold mb-0">{{ $resident->special_qualifications ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Community Activities</label>
                        <p class="fw-semibold mb-0">{{ $resident->community_activities ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-telephone me-2 text-primary"></i>Emergency Contact
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Contact Person</label>
                        <p class="fw-semibold mb-0">{{ $resident->emergency_contact_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Contact Number</label>
                        <p class="fw-semibold mb-0">{{ $resident->emergency_contact_number ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Relationship</label>
                        <p class="fw-semibold mb-0">{{ $resident->emergency_contact_relationship ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <img src="{{ $resident->profile_picture_url }}"
                     alt="Profile Picture"
                     class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                <h5 class="mb-1">{{ $resident->full_name }}</h5>
                <span class="text-muted small">{{ $resident->resident_code }}</span>
                @if($resident->blood_type)
                    <div class="mt-2"><span class="badge bg-danger">{{ $resident->blood_type }}</span></div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-house me-2 text-primary"></i>Household
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label text-muted small">Household Number</label>
                    <p class="fw-semibold mb-0">{{ $resident->household?->household_number ?? '—' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Village</label>
                    <p class="fw-semibold mb-0">{{ $resident->village?->village_name ?? '—' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Relationship to Head</label>
                    <p class="fw-semibold mb-0">{{ $resident->relationship_to_head ?? '—' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Household Head</label>
                    <p class="mb-0">
                        @if($resident->is_household_head)
                            <span class="badge bg-primary"><i class="bi bi-person-check me-1"></i>Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="form-label text-muted small">Years of Residency</label>
                    <p class="fw-semibold mb-0">{{ $resident->years_of_residency ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>Special Population
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex flex-wrap gap-1">
                    @if($resident->is_pwd)
                        <span class="badge bg-info">PWD{{ $resident->pwd_type ? ': ' . $resident->pwd_type : '' }}</span>
                    @endif
                    @if($resident->is_solo_parent)
                        <span class="badge bg-info">Solo Parent</span>
                    @endif
                    @if($resident->is_senior_citizen)
                        <span class="badge bg-info">Senior Citizen</span>
                    @endif
                    @if($resident->is_youth)
                        <span class="badge bg-info">Youth</span>
                    @endif
                    @if($resident->is_student)
                        <span class="badge bg-info">Student</span>
                    @endif
                    @if($resident->is_ofw)
                        <span class="badge bg-info">OFW</span>
                    @endif
                    @if($resident->is_infant)
                        <span class="badge bg-info">Infant/Child</span>
                    @endif
                    @if(!count($resident->special_population_types))
                        <span class="text-muted">None</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Status
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label text-muted small">Status</label>
                    <p class="mb-0">
                        @if($resident->status === 'active')
                            <span class="badge bg-success fs-6">Active</span>
                        @elseif($resident->status === 'inactive')
                            <span class="badge bg-secondary fs-6">Inactive</span>
                        @elseif($resident->status === 'deceased')
                            <span class="badge bg-danger fs-6">Deceased</span>
                        @elseif($resident->status === 'transferred')
                            <span class="badge bg-warning text-dark fs-6">Transferred</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ ucfirst($resident->status) }}</span>
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Registered On</label>
                    <p class="fw-semibold mb-0">{{ $resident->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <label class="form-label text-muted small">Last Updated</label>
                    <p class="fw-semibold mb-0">{{ $resident->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-check me-2 text-primary"></i>Approval Workflow
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="mb-3">
                    <label class="form-label text-muted small">Approval Status</label>
                    <p class="mb-0">
                        @if($resident->approval_status === 'approved')
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Approved</span>
                        @elseif($resident->approval_status === 'admin_approved')
                            <span class="badge bg-info fs-6"><i class="bi bi-hourglass-split me-1"></i>Admin Approved (Pending Super Admin)</span>
                        @elseif($resident->approval_status === 'pending')
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock me-1"></i>Pending Review</span>
                        @elseif($resident->approval_status === 'rejected')
                            <span class="badge bg-danger fs-6"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                        @else
                            <span class="badge bg-secondary fs-6"><i class="bi bi-pencil me-1"></i>Draft</span>
                        @endif
                    </p>
                </div>
                @if($resident->submittedBy)
                <div class="mb-3">
                    <label class="form-label text-muted small">Submitted By</label>
                    <p class="fw-semibold mb-0">{{ $resident->submittedBy->full_name ?? '—' }}</p>
                </div>
                @endif
                @if($resident->admin_approved_by)
                <div class="mb-3">
                    <label class="form-label text-muted small">Admin Approved By</label>
                    <p class="fw-semibold mb-0">{{ $resident->adminApprovedBy?->full_name ?? '—' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Admin Approval Date</label>
                    <p class="fw-semibold mb-0">{{ $resident->admin_approved_at?->format('M d, Y h:i A') ?? '—' }}</p>
                </div>
                @endif
                @if($resident->approved_by && $resident->approval_status !== 'admin_approved')
                <div class="mb-3">
                    <label class="form-label text-muted small">Final Approved/Rejected By</label>
                    <p class="fw-semibold mb-0">{{ $resident->approvedBy?->full_name ?? '—' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Decision Date</label>
                    <p class="fw-semibold mb-0">{{ $resident->approved_at?->format('M d, Y h:i A') ?? '—' }}</p>
                </div>
                @endif
                @if($resident->remarks)
                <div class="mb-3">
                    <label class="form-label text-muted small">Remarks</label>
                    <p class="mb-0">{{ $resident->remarks }}</p>
                </div>
                @endif

                @if(auth()->user()->hasRole('secretary') && in_array($resident->approval_status, ['draft', 'rejected']))
                <form action="{{ route('residents.submit', $resident) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Submit this resident for admin review?')">
                        <i class="bi bi-send me-1"></i>Submit for Approval
                    </button>
                </form>
                @endif

                @if(auth()->user()->hasRole('admin') && $resident->approval_status === 'pending')
                <div class="mt-3">
                    <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-lg me-1"></i>Approve (Admin)
                    </button>
                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                </div>
                @endif

                @if(auth()->user()->hasRole('super_admin') && $resident->approval_status === 'admin_approved')
                <div class="mt-3">
                    <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-lg me-1"></i>Final Approve (Super Admin)
                    </button>
                    <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@if(
    (auth()->user()->hasRole('admin') && $resident->approval_status === 'pending') ||
    (auth()->user()->hasRole('super_admin') && $resident->approval_status === 'admin_approved')
)
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('residents.approve', $resident) }}" method="POST">
                @csrf
                <input type="hidden" name="approval_status" value="approved">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        @if(auth()->user()->hasRole('admin'))
                            <i class="bi bi-check-circle me-2"></i>Admin Approve Resident
                        @else
                            <i class="bi bi-check-circle me-2"></i>Final Approve Resident
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(auth()->user()->hasRole('admin'))
                        <p>Approve this resident? It will be queued for Super Admin final approval.</p>
                    @else
                        <p>Are you sure you want to <strong>finally approve</strong> this resident?</p>
                    @endif
                    <div class="mb-3">
                        <label for="approve_remarks" class="form-label fw-semibold">Remarks (optional)</label>
                        <textarea class="form-control" id="approve_remarks" name="remarks" rows="3" placeholder="Any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('residents.approve', $resident) }}" method="POST">
                @csrf
                <input type="hidden" name="approval_status" value="rejected">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Resident</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this resident.</p>
                    <div class="mb-3">
                        <label for="reject_remarks" class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_remarks" name="remarks" rows="3" placeholder="Reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
