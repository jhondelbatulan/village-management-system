@extends('layouts.app')

@section('title', 'Login History')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Login History</h4>
            <p class="page-subtitle text-muted mb-0">View all login and logout activity.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('system-admin.login-history.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <a href="{{ route('system-admin.login-history.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Duration</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>OS</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loginHistory as $entry)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($entry->user)
                                        <img src="{{ $entry->user->profile_picture_url }}" alt="{{ $entry->user->full_name }}" class="rounded-circle me-2" width="32" height="32">
                                    @endif
                                    <span class="fw-semibold">{{ $entry->user?->full_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td><small class="text-muted">{{ $entry->username ?? '—' }}</small></td>
                            <td>
                                <small class="text-muted">{{ $entry->login_at?->format('M d, Y H:i:s') ?? '—' }}</small>
                            </td>
                            <td>
                                @if($entry->logout_at)
                                    <small class="text-muted">{{ $entry->logout_at->format('M d, Y H:i:s') }}</small>
                                @else
                                    <span class="badge bg-info">Active</span>
                                @endif
                            </td>
                            <td>
                                @if($entry->logout_at && $entry->login_at)
                                    @php
                                        $minutes = $entry->login_at->diffInMinutes($entry->logout_at);
                                        $hours = floor($minutes / 60);
                                        $mins = $minutes % 60;
                                        $duration = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
                                    @endphp
                                    <small class="text-muted">{{ $duration }}</small>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $entry->ip_address ?? '—' }}</small></td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 120px;" title="{{ $entry->browser }}">
                                    {{ $entry->browser ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 120px;" title="{{ $entry->os }}">
                                    {{ $entry->os ?? '—' }}
                                </small>
                            </td>
                            <td>
                                @if($entry->status === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-box-arrow-in-right fs-1 d-block mb-2"></i>
                                No login history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($loginHistory->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $loginHistory->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
