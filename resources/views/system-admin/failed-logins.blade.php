@extends('layouts.app')

@section('title', 'Failed Login Monitoring')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Failed Login Monitoring</h4>
            <p class="page-subtitle text-muted mb-0">Track and monitor failed authentication attempts.</p>
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
                        <p class="stat-label text-muted mb-1">Failed Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($securityStats['failed_logins_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-shield-x"></i>
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
                        <p class="stat-label text-muted mb-1">Failed This Week</p>
                        <h3 class="stat-value mb-0">{{ number_format($securityStats['failed_logins_week'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
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
                        <p class="stat-label text-muted mb-1">Flagged Attempts</p>
                        <h3 class="stat-value mb-0">{{ number_format($securityStats['flagged_attempts'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-flag"></i>
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
                        <p class="stat-label text-muted mb-1">Unique IPs Today</p>
                        <h3 class="stat-value mb-0">{{ number_format($securityStats['unique_ips_today'] ?? 0) }}</h3>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-globe"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">
            <i class="bi bi-shield-exclamation me-2 text-danger"></i>Failed Login Attempts
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>Time</th>
                        <th>Browser</th>
                        <th>OS</th>
                        <th>Reason</th>
                        <th>Flagged</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedLogins as $entry)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $entry->username ?? '—' }}</span>
                            </td>
                            <td><code>{{ $entry->ip_address ?? '—' }}</code></td>
                            <td>
                                <small class="text-muted">{{ $entry->created_at->format('M d, Y H:i:s') }}</small>
                            </td>
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
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $entry->reason }}">
                                    {{ $entry->reason ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @if($entry->flagged)
                                    <span class="badge bg-danger">Flagged</span>
                                @else
                                    <span class="badge bg-secondary">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                                No failed login attempts recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($failedLogins->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $failedLogins->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
