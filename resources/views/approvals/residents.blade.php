@extends('layouts.app')

@section('title', 'Resident Approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Resident Approvals</h1>
        <p class="page-subtitle text-muted">
            @if($role === 'admin' || $role === 'secretary')
                Review and approve resident registrations in your village.
            @else
                Manage resident approval status across all villages.
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
    @if($role === 'admin' || $role === 'secretary')
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'pending' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.residents', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-warning">{{ $counts['pendingCount'] }}</div>
                <div class="text-muted small">Pending Review</div>
            </a>
        </div>
    </div>
    @else
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'admin_approved' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.residents', ['status' => 'admin_approved']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-info">{{ $counts['adminApprovedCount'] }}</div>
                <div class="text-muted small">Awaiting Final Approval</div>
            </a>
        </div>
    </div>
    @endif
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 {{ $isHistory ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.residents', ['view' => 'history']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-success">{{ $counts['approvedCount'] }}</div>
                <div class="text-muted small">Approved History</div>
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'rejected' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.residents', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-danger">{{ $counts['rejectedCount'] }}</div>
                <div class="text-muted small">Rejected</div>
            </a>
        </div>
    </div>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $isHistory ? '' : 'active' }}"
           href="{{ route('approvals.residents', array_filter(['search' => request('search'), 'village_id' => request('village_id')])) }}">
            <i class="bi bi-list-check me-1"></i>All Approval Records
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $isHistory ? 'active' : '' }}"
           href="{{ route('approvals.residents', array_filter(['view' => 'history', 'search' => request('search'), 'village_id' => request('village_id')])) }}">
            <i class="bi bi-clock-history me-1"></i>Approved History
        </a>
    </li>
</ul>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi {{ $isHistory ? 'bi-clock-history' : 'bi-list-check' }} me-2 text-primary"></i>
            @if($isHistory)
                History of Approved Residents
            @elseif(request('status'))
                {{ ucfirst(str_replace('_', ' ', request('status'))) }} Records
            @else
                All Approval Records
            @endif
        </h6>
        <div class="d-flex gap-2 align-items-center">
            @if(request('status'))
                <a href="{{ route('approvals.residents', array_filter(['search' => request('search'), 'village_id' => request('village_id')])) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Clear Filter
                </a>
            @endif
            @if($role === 'super_admin')
            <form action="{{ route('approvals.residents') }}" method="GET" class="d-flex gap-2">
                <input type="hidden" name="view" value="{{ request('view') }}">
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
            <form action="{{ route('approvals.residents') }}" method="GET" class="d-flex gap-1">
                <input type="hidden" name="view" value="{{ request('view') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="village_id" value="{{ request('village_id') }}">
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Search residents..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Block / Lot</th>
                        <th>Village</th>
                        @if($isHistory)
                            <th>Approved By</th>
                            <th>Approved On</th>
                        @endif
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $resident)
                        <tr>
                            <td>
                                <a href="{{ route('residents.show', $resident) }}" class="fw-semibold text-decoration-none">
                                    {{ $resident->resident_code }}
                                </a>
                            </td>
                            <td>{{ $resident->full_name }}</td>
                            <td>
                                @if($resident->gender === 'Male')
                                    <span class="text-primary"><i class="bi bi-gender-male me-1"></i>Male</span>
                                @elseif($resident->gender === 'Female')
                                    <span class="text-danger"><i class="bi bi-gender-female me-1"></i>Female</span>
                                @else
                                    {{ $resident->gender ?? '—' }}
                                @endif
                            </td>
                            <td>{{ $resident->household ? 'Block ' . ($resident->household->block_number ?? '—') . ' - Lot ' . ($resident->household->lot_number ?? '—') : '—' }}</td>
                            <td>{{ $resident->village?->village_name ?? '—' }}</td>
                            @if($isHistory)
                                <td>{{ $resident->approvedBy?->full_name ?? '—' }}</td>
                                <td>{{ optional($resident->approved_at)->format('M d, Y h:i A') ?? '—' }}</td>
                            @endif
                            <td>
                                @if($resident->approval_status === 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                @elseif($resident->approval_status === 'admin_approved')
                                    <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>Admin Approved</span>
                                @elseif($resident->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                @elseif($resident->approval_status === 'rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('residents.show', $resident) }}" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(($role === 'admin' && $resident->approval_status === 'pending') ||
                                        ($role === 'super_admin' && $resident->approval_status === 'admin_approved'))
                                        <button type="button" class="btn btn-success btn-sm" title="Approve"
                                            data-bs-toggle="modal" data-bs-target="#approveResident{{ $resident->id }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" title="Reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectResident{{ $resident->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isHistory ? 9 : 7 }}" class="text-center py-5">
                                <i class="bi bi-check-circle fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">
                                    @if($isHistory)
                                        No approved residents in history yet.
                                    @elseif(request('status'))
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
    @if($residents->hasPages())
        <div class="card-footer bg-white">
            {{ $residents->links() }}
        </div>
    @endif
</div>

@foreach($residents as $resident)
    @if(($role === 'admin' && $resident->approval_status === 'pending') ||
        ($role === 'super_admin' && $resident->approval_status === 'admin_approved'))

    <div class="modal fade" id="approveResident{{ $resident->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('approvals.approve-resident', $resident) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Resident</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Approve resident <strong>{{ $resident->full_name }}</strong>?</p>
                        <p class="text-muted small">{{ $resident->resident_code }} — {{ $resident->village?->village_name }}</p>
                        @if($role === 'admin')
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>This will mark the resident as "Admin Approved". Super Admin will need to finalize the approval.
                            </div>
                        @else
                            <div class="alert alert-success small mb-0">
                                <i class="bi bi-info-circle me-1"></i>This is the final approval. The resident will be officially registered.
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

    <div class="modal fade" id="rejectResident{{ $resident->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('approvals.reject-resident', $resident) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Resident</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Reject resident <strong>{{ $resident->full_name }}</strong>?</p>
                        <p class="text-muted small">{{ $resident->resident_code }} — {{ $resident->village?->village_name }}</p>
                        <div class="mb-3">
                            <label for="remarks_{{ $resident->id }}" class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="remarks_{{ $resident->id }}" name="remarks" rows="3" required
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
