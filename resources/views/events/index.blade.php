@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Events</h1>
        <p class="page-subtitle text-muted">Manage village events and activities</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin']))
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Event
            </a>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('events.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, venue..." value="{{ request('search') }}">
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
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($events as $event)
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                @if($event->cover_image)
                    <img src="{{ $event->cover_image_url }}" class="card-img-top" alt="{{ $event->title }}" style="height: 160px; object-fit: cover;">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                        <i class="bi bi-calendar-event fs-1 text-muted"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title fw-semibold mb-0">{{ $event->title }}</h6>
                        @if($event->status === 'upcoming')
                            <span class="badge bg-primary">Upcoming</span>
                        @elseif($event->status === 'ongoing')
                            <span class="badge bg-success">Ongoing</span>
                        @elseif($event->status === 'completed')
                            <span class="badge bg-secondary">Completed</span>
                        @elseif($event->status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @else
                            <span class="badge bg-warning text-dark">Draft</span>
                        @endif
                    </div>
                    <p class="card-text small text-muted mb-2">
                        <i class="bi bi-calendar me-1"></i> {{ $event->event_date->format('M d, Y') }}
                        @if($event->start_time)
                            <br><i class="bi bi-clock me-1"></i> {{ $event->start_time->format('h:i A') }}
                            @if($event->end_time) – {{ $event->end_time->format('h:i A') }} @endif
                        @endif
                        @if($event->venue)
                            <br><i class="bi bi-geo-alt me-1"></i> {{ $event->venue }}
                        @endif
                    </p>
                    <span class="badge bg-light text-dark">{{ $event->category }}</span>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>
                        {{ $event->going_count }} Going &middot; {{ $event->maybe_count }} Maybe
                    </small>
                    <div class="d-flex gap-1">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @if($event->isManagedBy(auth()->user()))
                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this event? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Event">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-event fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">No events found.</p>
                    @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin']))
                        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Create First Event
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($events->hasPages())
    <div class="mt-3">
        {{ $events->withQueryString()->links() }}
    </div>
@endif
@endsection
