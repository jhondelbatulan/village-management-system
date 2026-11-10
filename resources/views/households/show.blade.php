@extends('layouts.app')

@section('title', 'Household - ' . $household->household_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">{{ $household->household_number }}</h1>
        <p class="page-subtitle text-muted mb-0">
{{ $household->village?->village_name ?? '—' }}
            @if($household->status === 'active')
                <span class="badge bg-success ms-2">Active</span>
            @elseif($household->status === 'inactive')
                <span class="badge bg-warning text-dark ms-2">Inactive</span>
            @else
                <span class="badge bg-secondary ms-2">Relocated</span>
            @endif
            @if($household->approval_status === 'approved')
                <span class="badge bg-success ms-1">Approved</span>
            @elseif($household->approval_status === 'admin_approved')
                <span class="badge bg-info ms-1">Admin Approved</span>
            @elseif($household->approval_status === 'pending')
                <span class="badge bg-warning text-dark ms-1">Pending Approval</span>
            @elseif($household->approval_status === 'rejected')
                <span class="badge bg-danger ms-1">Rejected</span>
            @else
                <span class="badge bg-secondary ms-1">Draft</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        @if(in_array(auth()->user()->role?->role_slug, ['secretary', 'admin', 'super_admin']))
            <form action="{{ route('households.toggle-status', $household) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $household->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                        onclick="return confirm('{{ $household->status === 'active' ? 'Deactivate' : 'Activate' }} this household? {{ $household->status === 'active' ? 'All linked user accounts will be deactivated.' : 'Linked user accounts will be reactivated.' }}')">
                    <i class="bi bi-{{ $household->status === 'active' ? 'pause-circle' : 'play-circle' }} me-1"></i>
                    {{ $household->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        @endif
        @if(auth()->user()->role?->role_slug === 'secretary' && in_array($household->approval_status, ['draft', 'rejected']))
            <a href="{{ route('households.edit', $household) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endif
        @if(auth()->user()->role?->role_slug === 'secretary' && $household->approval_status === 'draft')
            <form action="{{ route('households.submit', $household) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">
                    <i class="bi bi-send me-1"></i> Submit for Approval
                </button>
            </form>
        @endif
        @if(auth()->user()->role?->role_slug === 'admin' && $household->approval_status === 'pending')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-lg me-1"></i> Review
            </button>
        @endif
        @if(auth()->user()->role?->role_slug === 'super_admin' && $household->approval_status === 'admin_approved')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-lg me-1"></i> Final Approval
            </button>
        @endif
        @if(auth()->user()->role?->role_slug === 'super_admin')
                                         <form action="{{ route('households.force-delete', $household) }}" method="POST" class="d-inline" data-password-confirm data-password-confirm-message="Enter your super admin password to permanently delete this household."
                  onsubmit="return confirm('Permanently delete household {{ $household->household_number }} and all of its residents? This cannot be undone and will remove all of its billing, payment, and participation records.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete Permanently
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-house-door me-2"></i> Household Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Household Number</label>
                        <div class="fw-semibold">{{ $household->household_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Village</label>
                        <div class="fw-semibold">{{ $household->village?->village_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Block Number</label>
                        <div class="fw-semibold">{{ $household->block_number ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Lot Number</label>
                        <div class="fw-semibold">{{ $household->lot_number ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Household Head</label>
                        <div class="fw-semibold">{{ $household->household_head_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Contact Number</label>
                        <div class="fw-semibold">{{ $household->contact_number ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Purok</label>
                        <div class="fw-semibold">{{ $household->purok ?? '—' }}</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted small">Address</label>
                        <div class="fw-semibold">{{ $household->address ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Latitude</label>
                        <div class="fw-semibold">{{ $household->latitude ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Longitude</label>
                        <div class="fw-semibold">{{ $household->longitude ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-building me-2"></i> House Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Family Count</label>
                        <div class="fw-semibold">{{ $household->household_type ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">House Ownership</label>
                        <div class="fw-semibold">{{ $household->house_ownership ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">House Material</label>
                        <div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $household->house_status ?? '')) ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Registration Date</label>
                        <div class="fw-semibold">{{ $household->registration_date ? $household->registration_date->format('M d, Y') : '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            @if($household->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($household->status === 'inactive')
                                <span class="badge bg-warning text-dark">Inactive</span>
                            @else
                                <span class="badge bg-secondary">Relocated</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Member Count</label>
                        <div class="fw-semibold">{{ $household->member_count }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history me-2"></i> Approval Workflow
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Approval Status</label>
                        <div>
                            @if($household->approval_status === 'approved')
                                <span class="badge bg-success fs-6">Approved</span>
                            @elseif($household->approval_status === 'admin_approved')
                                <span class="badge bg-info fs-6">Admin Approved (Pending Super Admin)</span>
                            @elseif($household->approval_status === 'pending')
                                <span class="badge bg-warning text-dark fs-6">Pending</span>
                            @elseif($household->approval_status === 'rejected')
                                <span class="badge bg-danger fs-6">Rejected</span>
                            @else
                                <span class="badge bg-secondary fs-6">Draft</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Submitted By</label>
                        <div class="fw-semibold">{{ $household->submittedBy?->full_name ?? '—' }}</div>
                    </div>
                    @if($household->admin_approved_by)
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Admin Approved By</label>
                        <div class="fw-semibold">{{ $household->adminApprovedBy?->full_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Admin Approval Date</label>
                        <div class="fw-semibold">{{ $household->admin_approved_at?->format('M d, Y h:i A') ?? '—' }}</div>
                    </div>
                    @endif
                    @if($household->approved_by && $household->approval_status !== 'admin_approved')
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Final Approved/Rejected By</label>
                        <div class="fw-semibold">{{ $household->approvedBy?->full_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Final Decision Date</label>
                        <div class="fw-semibold">{{ $household->approved_at?->format('M d, Y h:i A') ?? '—' }}</div>
                    </div>
                    @endif
                    @if($household->remarks)
                    <div class="col-12">
                        <label class="form-label text-muted small">Remarks</label>
                        <div class="fw-semibold">{{ $household->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if($household->latitude && $household->longitude)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success bg-opacity-10">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-map me-2"></i> Location</h6>
            </div>
            <div class="card-body p-0">
                <div id="household-map" style="height:250px;width:100%;"></div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info bg-opacity-10">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2"></i> Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-12">
                        <div class="stat-card p-3">
                            <div class="stat-value fs-2 fw-bold text-primary">{{ $household->member_count }}</div>
                            <div class="text-muted small">Total Members</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-cash-coin me-2"></i> Payment Summary
                </h6>
                <a href="{{ route('household-payments.show', $household) }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-receipt me-1"></i> View All
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-success">₱{{ number_format($household->paid_total, 2) }}</div>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-danger">₱{{ number_format($household->outstanding_balance, 2) }}</div>
                            <small class="text-muted">Outstanding</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-warning">{{ $household->billings()->count() }}</div>
                            <small class="text-muted">Total Billings</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($household->payment_behavior && $household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled)
        @php
            $pb = $household->payment_behavior;
            $pbColor = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$pb] ?? 'secondary';
            $pbIcon = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_ICONS[$pb] ?? 'bi-question-circle';
            $pbLabel = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$pb] ?? 'N/A';
            $reasons = json_decode($household->prediction_reasons, true) ?? [];
        @endphp
        <div class="card shadow-sm mb-4 border-{{ $pbColor }}">
            <div class="card-header bg-{{ $pbColor }} bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-graph-up-arrow me-2"></i> Payment Behavior
                </h6>
                <a href="{{ route('analytics.predictive-payment.household', $household->id) }}" class="btn btn-outline-{{ $pbColor }} btn-sm">
                    <i class="bi bi-bar-chart me-1"></i> Full Analysis
                </a>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="bi {{ $pbIcon }} text-{{ $pbColor }}" style="font-size: 2.5rem;"></i>
                    <div class="mt-2">
                        <span class="badge bg-{{ $pbColor }} fs-6">{{ $pbLabel }}</span>
                    </div>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-{{ $pbColor }}">{{ $household->payment_reliability_score }}</div>
                            <small class="text-muted">Score / 100</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-primary">{{ $household->prediction_confidence }}%</div>
                            <small class="text-muted">Confidence</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-2">
                            <div class="fs-5 fw-bold text-info">{{ $household->last_prediction_at?->diffForHumans() ?? 'Never' }}</div>
                            <small class="text-muted">Last Updated</small>
                        </div>
                    </div>
                </div>
                @if(count($reasons) > 0)
                <div class="mt-3">
                    <small class="fw-semibold text-muted d-block mb-1">Reason:</small>
                    @foreach($reasons as $reason)
                    <div class="small text-muted"><i class="bi bi-check2 text-{{ $pbColor }} me-1"></i>{{ $reason }}</div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-people me-2"></i> Household Members
            <span class="badge bg-primary ms-1">{{ $household->member_count }}</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Resident Code</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Civil Status</th>
                        <th>Relationship to Head</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($household->residents as $resident)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark">{{ $resident->resident_code ?? '—' }}</span>
                            </td>
                            <td class="fw-semibold">
                                {{ $resident->full_name ?? $resident->first_name . ' ' . $resident->last_name }}
                            </td>
                            <td>
                                @if($resident->gender === 'Male')
                                    <span class="text-primary"><i class="bi bi-gender-male me-1"></i>Male</span>
                                @elseif($resident->gender === 'Female')
                                    <span class="text-danger"><i class="bi bi-gender-female me-1"></i>Female</span>
                                @else
                                    <span class="text-secondary">{{ $resident->gender ?? '—' }}</span>
                                @endif
                            </td>
                            <td>{{ $resident->age ?? '—' }}</td>
                            <td>{{ $resident->civil_status ?? '—' }}</td>
                            <td>
                                @if($resident->relationship_to_head)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ $resident->relationship_to_head }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(isset($resident->status) && $resident->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif(isset($resident->status) && $resident->status === 'inactive')
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @else
                                    <span class="badge bg-light text-dark">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-person-plus fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No household members registered yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(
    (auth()->user()->role?->role_slug === 'admin' && $household->approval_status === 'pending') ||
    (auth()->user()->role?->role_slug === 'super_admin' && $household->approval_status === 'admin_approved')
)
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('households.approve', $household) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">
                        @if(auth()->user()->role?->role_slug === 'admin')
                            Review Household (Admin Approval)
                        @else
                            Final Approval (Super Admin)
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Household: {{ $household->household_number }}</label>
                        <div class="text-muted small">Block {{ $household->block_number }}, Lot {{ $household->lot_number }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action <span class="text-danger">*</span></label>
                        <select name="approval_status" id="approval_status" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3" id="remarksGroup">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"
                            placeholder="Enter remarks (required for rejection)"></textarea>
                        <small class="text-muted">Required when rejecting.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const approvalSelect = document.getElementById('approval_status');
        const remarksGroup = document.getElementById('remarksGroup');
        if (approvalSelect && remarksGroup) {
            approvalSelect.addEventListener('change', function() {
                if (this.value === 'rejected') {
                    remarksGroup.querySelector('textarea').required = true;
                } else {
                    remarksGroup.querySelector('textarea').required = false;
                }
            });
        }

        @if($household->latitude && $household->longitude)
        var map = L.map('household-map').setView([{{ $household->latitude }}, {{ $household->longitude }}], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: 'OpenStreetMap', maxZoom: 19 }).addTo(map);
        var icon = L.divIcon({
            className: 'gis-marker',
            html: '<div style="width:16px;height:16px;background:#28a745;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.5);"></div>',
            iconSize: [22, 22], iconAnchor: [11, 11]
        });
        L.marker([{{ $household->latitude }}, {{ $household->longitude }}], { icon: icon }).addTo(map);
        @endif
    });
</script>
@endpush
