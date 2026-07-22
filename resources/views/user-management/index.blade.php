@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">User Management</h4>
            <p class="page-subtitle text-muted mb-0">Manage system user accounts and roles.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Create User
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, username, email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->role_slug }}" {{ request('role') == $role->role_slug ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="">Newest First</option>
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A – Z</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z – A</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
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
                            <th scope="col">User</th>
                            <th scope="col">Username</th>
                            <th scope="col">Role</th>
                            <th scope="col">Village</th>
                            <th scope="col">Household</th>
                            <th scope="col">Status</th>
                            <th scope="col">Online</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr data-user-id="{{ $user->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->profile_picture_url }}"
                                             alt="{{ $user->full_name }}"
                                             class="rounded-circle me-2" width="36" height="36">
                                        <div>
                                            <div class="fw-semibold">{{ $user->full_name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td><span class="badge bg-primary">{{ $user->role?->role_name }}</span></td>
                                <td>{{ $user->village?->village_name ?? '—' }}</td>
                                <td>
                                    @if($user->household)
                                        <span class="text-muted">Blk {{ $user->household->block_number ?? '—' }}, Lot {{ $user->household->lot_number ?? '—' }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($user->status === 'inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @else
                                        <span class="badge bg-danger">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="online-badge badge bg-secondary">Offline</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $user->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
    @endsection

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateOnlineStatus() {
                fetch('{{ route('users.online-status') }}')
                    .then(function (r) { return r.json(); })
                    .then(function (onlineIds) {
                        document.querySelectorAll('[data-user-id]').forEach(function (row) {
                            var badge = row.querySelector('.online-badge');
                            if (!badge) return;
                            var userId = parseInt(row.getAttribute('data-user-id'), 10);
                            if (onlineIds.indexOf(userId) !== -1) {
                                badge.className = 'online-badge badge bg-success';
                                badge.textContent = 'Online';
                            } else {
                                badge.className = 'online-badge badge bg-secondary';
                                badge.textContent = 'Offline';
                            }
                        });
                    })
                    .catch(function () {});
            }
            updateOnlineStatus();
            setInterval(updateOnlineStatus, 30000);
        });
    </script>
    @endpush
