@extends('layouts.app')

@section('title', 'Active Sessions')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Active Sessions</h4>
            <p class="page-subtitle text-muted mb-0">Manage and monitor active user sessions.</p>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1"><i class="bi bi-laptop me-2 text-primary"></i>Active Sessions</h6>
                <p class="text-muted mb-0 small">{{ $sessions->count() }} active session(s) currently online.</p>
            </div>
            <form action="{{ route('system-admin.sessions.force-logout-all') }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to force logout ALL users? This will terminate every active session.')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Force Logout All
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>Last Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        @if($session->user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $session->user->profile_picture_url }}" alt="{{ $session->user->full_name }}" class="rounded-circle me-2" width="36" height="36">
                                    <div>
                                        <div class="fw-semibold">{{ $session->user->full_name }}</div>
                                        <small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                    </div>
                                </div>
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
                                    $roleColor = $roleColors[$session->user->role?->role_slug ?? ''] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $roleColor }}">{{ $session->user->role?->role_name ?? '—' }}</span>
                            </td>
                            <td><code>{{ $session->ip_address ?? '—' }}</code></td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;" title="{{ $session->browser }}">
                                    {{ $session->browser ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $session->last_activity ? $session->last_activity->diffForHumans() : '—' }}</small>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('system-admin.sessions.terminate', $session->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to terminate this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Terminate Session">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-laptop fs-1 d-block mb-2"></i>
                                No active sessions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold">
            <i class="bi bi-info-circle me-2 text-info"></i>Session Management Info
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-danger-subtle text-danger p-2"><i class="bi bi-box-arrow-right fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">Terminate Session</div>
                        <div class="text-muted small">End a specific user session immediately.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-warning-subtle text-warning p-2"><i class="bi bi-exclamation-triangle fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">Force Logout All</div>
                        <div class="text-muted small">Terminate all active sessions system-wide.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-info-subtle text-info p-2"><i class="bi bi-clock-history fs-5"></i></span>
                    </div>
                    <div>
                        <div class="fw-semibold small">Auto Timeout</div>
                        <div class="text-muted small">Sessions expire after inactivity.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
