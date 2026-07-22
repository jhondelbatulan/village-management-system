@extends('layouts.app')

@section('title', 'Password Security')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Password Security</h4>
            <p class="page-subtitle text-muted mb-0">Manage password policies and locked accounts.</p>
        </div>
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

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>Password Policy
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('system-admin.password-security.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Minimum Password Length</label>
                            <input type="number" name="password_min_length" class="form-control"
                                   value="{{ old('password_min_length', $settings['password_min_length'] ?? 8) }}" min="4" max="64">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password History Limit</label>
                            <input type="number" name="password_history_limit" class="form-control"
                                   value="{{ old('password_history_limit', $settings['password_history_limit'] ?? 5) }}" min="0" max="20">
                            <div class="form-text">Previous passwords that cannot be reused.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lockout After Failed Attempts</label>
                            <input type="number" name="account_lockout_attempts" class="form-control"
                                   value="{{ old('account_lockout_attempts', $settings['account_lockout_attempts'] ?? 5) }}" min="1" max="20">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lockout Duration (minutes)</label>
                            <input type="number" name="account_lockout_duration" class="form-control"
                                   value="{{ old('account_lockout_duration', $settings['account_lockout_duration'] ?? 30) }}" min="1" max="1440">
                        </div>

                        <div class="col-12"><hr><h6 class="fw-bold mb-3">Password Requirements</h6></div>

                        @php
                            $checks = [
                                'password_require_uppercase' => 'Uppercase Letter',
                                'password_require_lowercase' => 'Lowercase Letter',
                                'password_require_number' => 'Number',
                                'password_require_special' => 'Special Character',
                            ];
                        @endphp
                        @foreach($checks as $key => $label)
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" id="{{ $key }}" name="{{ $key }}" class="form-check-input" value="1"
                                       {{ old($key, $settings[$key] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="{{ $key }}">{{ $label }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Policy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-lock me-2 text-danger"></i>Locked Accounts
                </h6>
            </div>
            <div class="card-body p-0">
                @if($lockedUsers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lockedUsers as $lockedUser)
                            <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-3">
                                <div>
                                    <div class="fw-semibold">{{ $lockedUser->full_name }}</div>
                                    <small class="text-muted">
                                        {{ $lockedUser->username }} &middot; {{ $lockedUser->role?->role_name ?? 'N/A' }}
                                        @if($lockedUser->locked_at)
                                            &middot; Locked {{ $lockedUser->locked_at->diffForHumans() }}
                                        @endif
                                    </small>
                                </div>
                                <form action="{{ route('system-admin.password-security.unlock', $lockedUser->id) }}" method="POST"
                                      onsubmit="return confirm('Unlock this account?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-unlock me-1"></i>Unlock
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                        <p class="text-muted mb-0">No locked accounts.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Locked Accounts</p>
                        <h3 class="stat-value mb-0">{{ $securityStats['locked_accounts'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-lock"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Failed Logins (24h)</p>
                        <h3 class="stat-value mb-0">{{ $securityStats['failed_logins_today'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-shield-x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Failed Logins (Week)</p>
                        <h3 class="stat-value mb-0">{{ $securityStats['failed_logins_week'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-label text-muted mb-1">Flagged IPs (Week)</p>
                        <h3 class="stat-value mb-0">{{ $securityStats['flagged_attempts'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
