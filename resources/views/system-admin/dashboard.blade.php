@extends('layouts.app')

@section('title', 'Security Dashboard')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Security Dashboard</h4>
            <p class="page-subtitle text-muted mb-0">System security overview and monitoring &middot; {{ now()->format('F d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('system-admin.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i>Audit Logs
            </a>
            <a href="{{ route('system-admin.sessions.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-laptop me-1"></i>Sessions
            </a>
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
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Active Users</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['active_users'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Active Sessions</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['active_sessions'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-laptop"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Failed Logins Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['failed_logins_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-shield-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Locked Accounts</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['locked_accounts'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-lock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Audit Logs Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['audit_logs_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Total Audit Logs</p>
                        <h3 class="stat-value mb-0">{{ number_format($stats['total_audit_logs'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-secondary-subtle text-secondary">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-journal-text me-2 text-primary"></i>Recent Audit Logs
                </h6>
                <a href="{{ route('system-admin.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAuditLogs as $log)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $log->user?->full_name ?? 'System' }}</span>
                                    </td>
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
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $log->description }}">
                                            {{ $log->description ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-journal fs-1 d-block mb-2"></i>
                                        No recent audit logs.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-heart-pulse me-2 text-success"></i>System Health
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-database me-2"></i>Database</span>
                    @if(($health['database']['status'] ?? '') === 'healthy')
                        <span class="badge bg-success">Healthy</span>
                    @elseif(($health['database']['status'] ?? '') === 'warning')
                        <span class="badge bg-warning">Warning</span>
                    @else
                        <span class="badge bg-danger">Critical</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-hdd me-2"></i>Storage</span>
                    <span class="text-muted small">{{ $health['storage']['used'] ?? '—' }} / {{ $health['storage']['total'] ?? '—' }}</span>
                </div>
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-{{ ($health['storage']['usage_percent'] ?? 0) > 80 ? 'danger' : 'success' }}"
                         style="width: {{ $health['storage']['usage_percent'] ?? 0 }}%"></div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-speedometer me-2"></i>Cache</span>
                    @if(($health['cache']['status'] ?? '') === 'healthy')
                        <span class="badge bg-success">Operational</span>
                    @else
                        <span class="badge bg-warning">{{ $health['cache']['status'] ?? 'Unknown' }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted"><i class="bi bi-grid me-2"></i>Session Driver</span>
                    <span class="badge bg-info">{{ $health['session']['driver'] ?? 'file' }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-cloud-arrow-up me-2 text-info"></i>Backup Status
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-file-earmark-zip me-2"></i>Total Backups</span>
                    <span class="fw-semibold">{{ $backupStats['total_backups'] ?? 0 }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-hdd me-2"></i>Total Size</span>
                    <span class="fw-semibold">{{ $backupStats['total_size'] ?? '—' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><i class="bi bi-clock me-2"></i>Last Backup</span>
                    <span class="text-muted small">{{ $backupStats['last_backup'] ?? 'Never' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted"><i class="bi bi-check-circle me-2"></i>Status</span>
                    @if(($backupStats['last_status'] ?? '') === 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif(($backupStats['last_status'] ?? '') === 'failed')
                        <span class="badge bg-danger">Failed</span>
                    @else
                        <span class="badge bg-secondary">No Backups</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">
            <i class="bi bi-shield-check me-2 text-primary"></i>Security Overview
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-3 fw-bold text-primary">{{ number_format($securityStats['total_users'] ?? 0) }}</div>
                    <div class="text-muted small">Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-3 fw-bold text-success">{{ number_format($securityStats['active_users'] ?? 0) }}</div>
                    <div class="text-muted small">Active Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-3 fw-bold text-danger">{{ number_format($securityStats['locked_users'] ?? 0) }}</div>
                    <div class="text-muted small">Locked Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="fs-3 fw-bold text-warning">{{ number_format($securityStats['failed_logins_24h'] ?? 0) }}</div>
                    <div class="text-muted small">Failed Logins (24h)</div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-success-subtle text-success p-2"><i class="bi bi-shield-fill-check fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">Password Policy</div>
                        <div class="text-muted small">{{ $securityStats['password_policy'] ?? 'Standard' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-info-subtle text-info p-2"><i class="bi bi-clock-history fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">Session Timeout</div>
                        <div class="text-muted small">{{ $securityStats['session_timeout'] ?? '30 minutes' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-warning-subtle text-warning p-2"><i class="bi bi-key fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">2FA Enabled</div>
                        <div class="text-muted small">{{ $securityStats['two_factor_users'] ?? 0 }} users</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
