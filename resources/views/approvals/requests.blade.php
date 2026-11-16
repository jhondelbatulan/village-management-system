@extends('layouts.app')

@section('title', $action === 'delete' ? 'Delete Requests' : 'Update Approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">{{ $action === 'delete' ? 'Delete Request Approvals' : 'Household & Resident Update Approvals' }}</h1>
        <p class="page-subtitle text-muted">
            @if($role === 'admin')
                Review staged {{ $action === 'delete' ? 'delete requests' : 'updates' }} in your village (Stage 1). Changes are saved only after Super Admin final approval.
            @else
                Give final approval on admin-approved {{ $action === 'delete' ? 'delete requests' : 'updates' }} (Stage 2). This is the step that saves the changes or deletes the record.
            @endif
        </p>
    </div>
    <a href="{{ $isHistory ? route('approvals.'.$action.'s') : route('approvals.'.$action.'s', ['view' => 'history']) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clock-history me-1"></i>{{ $isHistory ? 'Back to Queue' : 'History' }}
    </a>
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
    @if($role === 'admin')
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'pending' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.'.$action.'s', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-warning">{{ $counts['pendingCount'] }}</div>
                <div class="text-muted small">Awaiting My Approval</div>
            </a>
        </div>
    </div>
    @else
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'admin_approved' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.'.$action.'s', ['status' => 'admin_approved']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-info">{{ $counts['adminApprovedCount'] }}</div>
                <div class="text-muted small">Awaiting Final Approval</div>
            </a>
        </div>
    </div>
    @endif
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ $isHistory ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.'.$action.'s', ['view' => 'history']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-success">{{ $counts['approvedCount'] }}</div>
                <div class="text-muted small">Approved History</div>
            </a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 {{ request('status') === 'rejected' ? 'border-primary border-2' : '' }}">
            <a href="{{ route('approvals.'.$action.'s', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="fs-3 fw-bold text-danger">{{ $counts['rejectedCount'] }}</div>
                <div class="text-muted small">Rejected</div>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi {{ $action === 'delete' ? 'bi-trash' : 'bi-pencil-square' }} me-2 text-primary"></i>
            @if($isHistory)
                Processed {{ $action === 'delete' ? 'Delete Requests' : 'Update Requests' }}
            @elseif(request('status'))
                {{ ucfirst(str_replace('_', ' ', request('status'))) }} Records
            @else
                {{ $action === 'delete' ? 'Delete Request Queue' : 'Update Request Queue' }}
            @endif
        </h6>
        <form action="{{ route('approvals.'.$action.'s') }}" method="GET" class="d-flex gap-1">
            <input type="hidden" name="view" value="{{ request('view') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search records..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Record</th>
                        <th>{{ $action === 'delete' ? 'Requested Action' : 'Proposed Changes' }}</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($changes as $change)
                        @php
                            $target = $change->resolveTarget();
                            $fieldKeys = $action === 'update' ? array_keys($change->payload ?? []) : [];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $change->target_type === 'household' ? 'bg-primary' : 'bg-info text-dark' }} me-2">
                                        {{ $change->targetTypeLabel }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $change->target_label ?? '—' }}</div>
                                        <small class="text-muted">{{ $change->village?->village_name ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($action === 'delete')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                        <i class="bi bi-trash me-1"></i>Archive record
                                    </span>
                                @else
                                    <small class="text-muted">
                                        {{ count($fieldKeys) }} field(s) changed:
                                        <span class="fw-semibold text-dark">
                                            {{ collect($fieldKeys)->map(fn ($k) => ucwords(str_replace('_', ' ', $k)))->take(6)->implode(', ') }}{{ count($fieldKeys) > 6 ? '…' : '' }}
                                        </span>
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $change->submittedBy?->full_name ?? '—' }}</div>
                                <small class="text-muted text-capitalize">{{ $change->submitter_role ?? '—' }} &middot; {{ optional($change->created_at)->format('M d, Y h:i A') }}</small>
                            </td>
                            <td>
                                @if($change->approval_status === 'approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                @elseif($change->approval_status === 'superseded')
                                    <span class="badge bg-secondary"><i class="bi bi-arrow-repeat me-1"></i>Superseded</span>
                                @elseif($change->approval_status === 'admin_approved')
                                    <span class="badge bg-info"><i class="bi bi-hourglass-split me-1"></i>Admin Approved</span>
                                @elseif($change->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                @endif
                                @if(in_array($change->approval_status, ['approved', 'rejected', 'superseded'], true) && $change->remarks)
                                    <div><small class="text-muted" title="{{ $change->remarks }}">Remarks: {{ \Illuminate\Support\Str::limit($change->remarks, 40) }}</small></div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="View details"
                                            data-bs-toggle="modal" data-bs-target="#details{{ $change->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(($role === 'admin' && $change->approval_status === 'pending') ||
                                        ($role === 'super_admin' && $change->approval_status === 'admin_approved'))
                                        <button type="button" class="btn btn-success btn-sm" title="Approve"
                                                data-bs-toggle="modal" data-bs-target="#approve{{ $change->id }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" title="Reject"
                                                data-bs-toggle="modal" data-bs-target="#reject{{ $change->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Details modal --}}
                        <div class="modal fade" id="details{{ $change->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi {{ $action === 'delete' ? 'bi-trash' : 'bi-pencil-square' }} me-2"></i>
                                            {{ $change->actionLabel }} — {{ $change->targetTypeLabel }} {{ $change->target_label ?? '' }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3 d-flex flex-wrap gap-2">
                                            <span class="badge bg-light text-dark border">Submitted by: {{ $change->submittedBy?->full_name ?? '—' }} ({{ $change->submitter_role ?? '—' }})</span>
                                            <span class="badge bg-light text-dark border">Stage: {{ $change->stageStatusLabel }}</span>
                                            <span class="badge bg-light text-dark border">{{ optional($change->created_at)->format('M d, Y h:i A') }}</span>
                                        </div>

                                        @if($action === 'delete')
                                            <div class="alert alert-warning small mb-0">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                This will archive the {{ strtolower($change->targetTypeLabel) }}
                                                <strong>{{ $change->target_label }}</strong>
                                                @if($change->target_type === 'household')
                                                    together with all of its residents
                                                @endif
                                                — but only after the Super Admin gives final approval. If rejected, nothing is deleted.
                                            </div>
                                        @elseif($target)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 30%;">Field</th>
                                                            <th style="width: 35%;">Current Value</th>
                                                            <th style="width: 35%;">Proposed Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($change->payload ?? [] as $field => $value)
                                                            @php
                                                                $current = $target->{$field} ?? null;
                                                                $label = ucwords(str_replace('_', ' ', $field));
                                                                $isFlag = str_starts_with($field, 'is_');
                                                            @endphp
                                                            <tr>
                                                                <td class="fw-semibold">{{ $label }}</td>
                                                                <td>
                                                                    @if($field === 'profile_picture')
                                                                        <em class="text-muted">Existing picture</em>
                                                                    @elseif($isFlag)
                                                                        {{ $current ? 'Yes' : 'No' }}
                                                                    @else
                                                                        {{ $current === null || $current === '' ? '—' : $current }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($field === 'profile_picture')
                                                                        <em class="text-muted">New picture uploaded</em>
                                                                    @elseif($isFlag)
                                                                        {{ $value ? 'Yes' : 'No' }}
                                                                    @else
                                                                        {{ $value === null || $value === '' ? '—' : $value }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-secondary small mb-0">The original record no longer exists.</div>
                                        @endif

                                        @if(in_array($change->approval_status, ['approved', 'rejected'], true) && $change->remarks)
                                            <div class="alert alert-light border small mt-3 mb-0">
                                                <strong>Remarks:</strong> {{ $change->remarks }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(($role === 'admin' && $change->approval_status === 'pending') ||
                            ($role === 'super_admin' && $change->approval_status === 'admin_approved'))
                        {{-- Approve modal --}}
                        <div class="modal fade" id="approve{{ $change->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('approvals.approve-'.$action, $change) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve {{ $change->actionLabel }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>
                                                Approve the {{ strtolower($change->actionLabel) }}
                                                for <strong>{{ $change->target_label }}</strong>?
                                            </p>
                                            @if($role === 'admin')
                                                <div class="alert alert-info small mb-0">
                                                    <i class="bi bi-info-circle me-1"></i>This marks the request as "Admin Approved". The Super Admin must give final approval before anything is saved.
                                                </div>
                                            @else
                                                <div class="alert alert-success small mb-0">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    This is the final approval —
                                                    @if($action === 'delete')
                                                        the record will be archived immediately.
                                                    @else
                                                        the changes will be saved immediately.
                                                    @endif
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

                        {{-- Reject modal --}}
                        <div class="modal fade" id="reject{{ $change->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('approvals.reject-'.$action, $change) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject {{ $change->actionLabel }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Reject the {{ strtolower($change->actionLabel) }} for <strong>{{ $change->target_label }}</strong>?</p>
                                            <div class="alert alert-warning small">
                                                <i class="bi bi-info-circle me-1"></i>
                                                @if($action === 'delete')
                                                    The delete will be canceled — the record stays untouched.
                                                @else
                                                    The changes will not be saved — the record keeps its current values.
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="remarks_{{ $change->id }}" class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="remarks_{{ $change->id }}" name="remarks" rows="3" required
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
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">
                                    @if($isHistory)
                                        No processed {{ $action === 'delete' ? 'delete requests' : 'update requests' }} yet.
                                    @elseif(request('status'))
                                        No {{ str_replace('_', ' ', request('status')) }} records found.
                                    @else
                                        No {{ $action === 'delete' ? 'delete requests' : 'update requests' }} need your action.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($changes->hasPages())
        <div class="card-footer bg-white">
            {{ $changes->links() }}
        </div>
    @endif
</div>
@endsection
