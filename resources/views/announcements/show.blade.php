@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">{{ $announcement->title }}</h1>
        <p class="page-subtitle text-muted">
            {{ $announcement->village?->village_name ?? '' }} &middot;
            Created {{ $announcement->created_at->format('M d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->role?->role_slug === 'secretary')
            @if($announcement->status === 'draft' || $announcement->status === 'archived')
                <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endif
        @endif
        <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($announcement->is_pinned)
                        <span class="badge bg-warning text-dark"><i class="bi bi-pin me-1"></i> Pinned</span>
                    @endif
                    @if($announcement->status === 'published')
                        <span class="badge bg-success">Published</span>
                    @elseif($announcement->status === 'archived')
                        <span class="badge bg-secondary">Archived</span>
                    @else
                        <span class="badge bg-warning text-dark">Draft</span>
                    @endif
                    <span class="badge bg-light text-dark">{{ $announcement->category }}</span>
                </div>

                @if($announcement->cover_image)
                    <div class="mb-3">
                        <img src="{{ $announcement->cover_image_url }}" alt="Cover" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                @endif

                <div class="announcement-body">
                    {!! nl2br(e($announcement->description)) !!}
                </div>

                @if($announcement->attachment)
                    <div class="mt-3">
                        <a href="{{ $announcement->attachment_url }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-paperclip me-1"></i> View Attachment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Village</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $announcement->village?->village_name ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Category</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $announcement->category }}</dd>
                    <dt class="col-sm-5 text-muted">Status</dt>
                    <dd class="col-sm-7">
                        @if($announcement->status === 'published')
                            <span class="badge bg-success">Published</span>
                        @elseif($announcement->status === 'archived')
                            <span class="badge bg-secondary">Archived</span>
                        @else
                            <span class="badge bg-warning text-dark">Draft</span>
                        @endif
                    </dd>
                    <dt class="col-sm-5 text-muted">Start Date</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $announcement->start_date?->format('M d, Y') ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Expires</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $announcement->expiration_date?->format('M d, Y') ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $announcement->creator?->full_name ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        @if(auth()->user()->role?->role_slug === 'secretary')
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    @if($announcement->status === 'draft')
                        <form action="{{ route('announcements.publish', $announcement) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-lg me-1"></i> Publish
                            </button>
                        </form>
                    @endif
                    @if($announcement->status === 'published')
                        <form action="{{ route('announcements.archive', $announcement) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-archive me-1"></i> Archive
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
