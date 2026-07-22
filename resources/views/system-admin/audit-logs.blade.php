@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Audit Logs</h4>
            <p class="page-subtitle text-muted mb-0">Track all system activity and user actions.</p>
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

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['total_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">This Week</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['total_week'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">This Month</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['total_month'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Unique Users Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['unique_users_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('system-admin.audit-logs.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">User ID</label>
                    <input type="number" name="user_id" class="form-control" placeholder="User ID" value="{{ request('user_id') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="secretary" {{ request('role') == 'secretary' ? 'selected' : '' }}>Secretary</option>
                        <option value="treasurer" {{ request('role') == 'treasurer' ? 'selected' : '' }}>Treasurer</option>
                        <option value="resident" {{ request('role') == 'resident' ? 'selected' : '' }}>Resident</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Village</label>
                    <select name="village_id" class="form-select">
                        <option value="">All Villages</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                                {{ $village->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($distinctActions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        @foreach($distinctModules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                {{ $module }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <a href="{{ route('system-admin.audit-logs.index') }}" class="btn btn-outline-secondary">
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
                        <th>Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Village</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td>
                                <small class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $log->user?->full_name ?? 'System' }}</span>
                            </td>
                            <td>
                                @php
                                    $roleColors = [
                                        'super_admin' => 'danger',
                                        'admin' => 'primary',
                                        'secretary' => 'info',
                                        'treasurer' => 'warning',
                                        'resident' => 'secondary',
                                    ];
                                    $roleColor = $roleColors[$log->user?->role?->role_slug ?? ''] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $roleColor }}">{{ $log->user?->role?->role_name ?? '—' }}</span>
                            </td>
                            <td>{{ $log->village?->village_name ?? '—' }}</td>
                            <td>
                                @php
                                    $actionColors = [
                                        'login' => 'success',
                                        'logout' => 'info',
                                        'create' => 'primary',
                                        'update' => 'warning',
                                        'delete' => 'danger',
                                        'failed_login' => 'danger',
                                        'unlock' => 'info',
                                    ];
                                    $actionColor = $actionColors[$log->action] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $actionColor }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                            </td>
                            <td>{{ $log->module ?? '—' }}</td>
                            <td><small class="text-muted">{{ $log->ip_address ?? '—' }}</small></td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 120px;" title="{{ $log->browser }}">
                                    {{ $log->browser ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->description }}">
                                    {{ $log->description ?? '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-journal fs-1 d-block mb-2"></i>
                                No audit logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($auditLogs->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $auditLogs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
