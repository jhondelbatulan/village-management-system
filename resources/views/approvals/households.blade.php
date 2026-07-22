@extends('layouts.app')

@section('title', 'Household Approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Household Approvals</h1>
        <p class="page-subtitle text-muted">
            @if($role === 'admin' || $role === 'secretary')
                Review and approve household registrations in your village.
            @else
                Manage household approval status across all villages.
            @endif
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'pending' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.households', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-warning">{{ $counts['pendingCount'] }}</div>
                <div class="text-muted small">Pending Review</div>
            </a>
        </div>
    </div>
    @if($role === 'super_admin')
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'admin_approved' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.households', ['status' => 'admin_approved']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-info">{{ $counts['adminApprovedCount'] }}</div>
                <div class="text-muted small">Admin Approved</div>
            </a>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'approved' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.households', ['status' => 'approved']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-success">{{ $counts['approvedCount'] }}</div>
                <div class="text-muted small">Approved</div>
            </a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'rejected' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.households', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-danger">{{ $counts['rejectedCount'] }}</div>
                <div class="text-muted small">Rejected</div>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-list-check me-2 text-primary"></i>
            @if(request('status'))
                {{ ucfirst(str_replace('_', ' ', request('status'))) }} Records
            @else
                All Approval Records
            @endif
        </h6>
        <div class="d-flex gap-2 align-items-center">
            @if(request('status'))
                <a href="{{ route('approvals.households', array_filter(['search' => request('search'), 'village_id' => request('village_id')])) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Clear Filter
                </a>
            @endif
            @if($role === 'super_admin')
            <form action="{{ route('approvals.households') }}" method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="village_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Villages</option>
                    @foreach($villages as $village)
                        <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                            {{ $village->village_name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
            <form action="{{ route('approvals.households') }}" method="GET" class="d-flex gap-1">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="village_id" value="{{ request('village_id') }}">
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search households..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Block / Lot</th>
                        <th>Head</th>
                        <th>Village</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($households as $household)
                        <tr>
                            <td>
                                <a href="{{ route('households.show', $household) }}" class="fw-semibold text-decoration-none">
                                    {{ $household->household_number }}
                                </a>
                            </td>
                            <td>B{{ $household->block_number ?? '—' }} / L{{ $household->lot_number ?? '—' }}</td>
                            <td>{{ $household->household_head_name ?? '—' }}</td>
                            <td>{{ $household->village?->village_name ?? '—' }}</td>
                            <td>{{ $household->submittedBy?->full_name ?? '—' }}</td>
                            <td>
                                @if($household->approval_status === 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                @elseif($household->approval_status === 'admin_approved')
                                    <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>Admin Approved</span>
                                @elseif($household->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                @elseif($household->approval_status === 'rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('households.show', $household) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if((in_array($role, ['admin', 'secretary']) && $household->approval_status === 'pending') ||
                                        ($role === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved'])))
                                        <button type="button" class="btn btn-success btn-sm" title="Approve"
                                            data-bs-toggle="modal" data-bs-target="#approveHousehold{{ $household->id }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" title="Reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectHousehold{{ $household->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check-circle fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">
                                    @if(request('status'))
                                        No {{ str_replace('_', ' ', request('status')) }} records found.
                                    @else
                                        No approval records found.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($households->hasPages())
        <div class="card-footer bg-white">
            {{ $households->links() }}
        </div>
    @endif
</div>

@foreach($households as $household)
    @if((in_array($role, ['admin', 'secretary']) && $household->approval_status === 'pending') ||
        ($role === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved'])))

    <div class="modal fade" id="approveHousehold{{ $household->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('approvals.approve-household', $household) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Household</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Approve household <strong>{{ $household->household_number }}</strong>?</p>
                        <p class="text-muted small">Block {{ $household->block_number }}, Lot {{ $household->lot_number }} — {{ $household->village?->village_name }}</p>
                        @if(in_array($role, ['admin', 'secretary']))
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>This will mark the household as "Admin Approved". Super Admin will need to finalize the approval.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectHousehold{{ $household->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('approvals.reject-household', $household) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Household</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Reject household <strong>{{ $household->household_number }}</strong>?</p>
                        <p class="text-muted small">Block {{ $household->block_number }}, Lot {{ $household->lot_number }} — {{ $household->village?->village_name }}</p>
                        <div class="mb-3">
                            <label for="remarks_{{ $household->id }}" class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="remarks_{{ $household->id }}" name="remarks" rows="3" required
                                placeholder="Enter the reason for rejection..."></textarea>
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
@endforeach
@endsection
