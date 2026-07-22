@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Announcements</h1>
        <p class="page-subtitle text-muted">Manage village announcements and notices</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin', 'secretary', 'treasurer']))
            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Announcement
            </a>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('announcements.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
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
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px"></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Village</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                @if($announcement->is_pinned)
                                    <i class="bi bi-pin-fill text-warning" title="Pinned"></i>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('announcements.show', $announcement) }}" class="fw-semibold text-decoration-none">
                                    {{ $announcement->title }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $announcement->category }}</span></td>
                            <td>{{ $announcement->village->village_name ?? '—' }}</td>
                            <td class="small">
                                @if($announcement->start_date)
                                    {{ $announcement->start_date->format('M d, Y') }}
                                @endif
                                @if($announcement->expiration_date)
                                    – {{ $announcement->expiration_date->format('M d, Y') }}
                                @endif
                            </td>
                            <td>
                                @if($announcement->status === 'published')
                                    <span class="badge bg-success">Published</span>
                                @elseif($announcement->status === 'archived')
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin', 'secretary', 'treasurer']))
                                        @if($announcement->status === 'draft' || $announcement->status === 'archived')
                                            <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($announcement->status === 'draft')
                                            <form action="{{ route('announcements.publish', $announcement) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Publish">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($announcement->status === 'published')
                                            <form action="{{ route('announcements.archive', $announcement) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-megaphone fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No announcements found.</p>
                                @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin', 'secretary', 'treasurer']))
                                    <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Create First Announcement
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($announcements->hasPages())
        <div class="card-footer bg-white">
            {{ $announcements->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
