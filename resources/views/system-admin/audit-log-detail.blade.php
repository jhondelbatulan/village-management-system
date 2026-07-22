@extends('layouts.app')

@section('title', 'Audit Log Detail')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Audit Log Detail</h4>
            <p class="page-subtitle text-muted mb-0">Details for log entry #{{ $auditLog->id }}</p>
        </div>
        <a href="{{ route('system-admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Audit Logs
        </a>
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

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-journal-text me-2 text-primary"></i>Log Entry Details
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Action</dt>
                    <dd class="col-sm-9">
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
                            $actionColor = $actionColors[$auditLog->action] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $actionColor }} fs-6">{{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}</span>
                    </dd>

                    <dt class="col-sm-3 text-muted">User</dt>
                    <dd class="col-sm-9 fw-semibold">{{ $auditLog->user?->full_name ?? 'System' }}</dd>

                    <dt class="col-sm-3 text-muted">Role</dt>
                    <dd class="col-sm-9">
                        @php
                            $roleColors = [
                                'super_admin' => 'danger',
                                'admin' => 'primary',
                                'secretary' => 'info',
                                'treasurer' => 'warning',
                                'resident' => 'secondary',
                            ];
                            $roleColor = $roleColors[$auditLog->user?->role?->role_slug ?? ''] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $roleColor }}">{{ $auditLog->user?->role?->role_name ?? '—' }}</span>
                    </dd>

                    <dt class="col-sm-3 text-muted">Village</dt>
                    <dd class="col-sm-9">{{ $auditLog->village?->village_name ?? 'System-wide' }}</dd>

                    <dt class="col-sm-3 text-muted">Module</dt>
                    <dd class="col-sm-9">{{ $auditLog->module ?? '—' }}</dd>

                    <dt class="col-sm-3 text-muted">IP Address</dt>
                    <dd class="col-sm-9"><code>{{ $auditLog->ip_address ?? '—' }}</code></dd>

                    <dt class="col-sm-3 text-muted">Browser</dt>
                    <dd class="col-sm-9">{{ $auditLog->browser ?? '—' }}</dd>

                    <dt class="col-sm-3 text-muted">User Agent</dt>
                    <dd class="col-sm-9">
                        <small class="text-muted">{{ $auditLog->user_agent ?? '—' }}</small>
                    </dd>

                    <dt class="col-sm-3 text-muted">Description</dt>
                    <dd class="col-sm-9">{{ $auditLog->description ?? '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Timestamp</dt>
                    <dd class="col-sm-9">{{ $auditLog->created_at->format('F d, Y H:i:s') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold">
                    <i class="bi bi-info-circle me-2 text-info"></i>Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        @if($auditLog->user)
                            <img src="{{ $auditLog->user->profile_picture_url }}" alt="{{ $auditLog->user->full_name }}" class="rounded-circle" width="48" height="48">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-gear text-white"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $auditLog->user?->full_name ?? 'System' }}</div>
                        <small class="text-muted">{{ $auditLog->user?->email ?? '' }}</small>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Log ID</span>
                    <span class="fw-semibold small">#{{ $auditLog->id }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Created</span>
                    <span class="small">{{ $auditLog->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($auditLog->old_values) || !empty($auditLog->new_values))
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold">
                <i class="bi bi-arrow-left-right me-2 text-warning"></i>Changes
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%;">Field</th>
                            <th style="width: 37.5%;">Old Value</th>
                            <th style="width: 37.5%;">New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $oldValues = is_array($auditLog->old_values) ? $auditLog->old_values : (json_decode($auditLog->old_values, true) ?? []);
                            $newValues = is_array($auditLog->new_values) ? $auditLog->new_values : (json_decode($auditLog->new_values, true) ?? []);
                            $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                        @endphp
                        @forelse($allKeys as $field)
                            <tr>
                                <td class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                <td>
                                    @if(isset($oldValues[$field]))
                                        @if(is_array($oldValues[$field]))
                                            <code>{{ json_encode($oldValues[$field]) }}</code>
                                        @else
                                            <span class="text-danger text-decoration-line-through">{{ $oldValues[$field] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($newValues[$field]))
                                        @if(is_array($newValues[$field]))
                                            <code>{{ json_encode($newValues[$field]) }}</code>
                                        @else
                                            <span class="text-success fw-semibold">{{ $newValues[$field] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    No changes recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
