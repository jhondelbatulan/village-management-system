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
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Created By</label>
                    <select name="creator" class="form-select">
                        <option value="">Everyone</option>
                        <option value="super_admin" {{ request('creator') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('creator') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
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
                <div class="card shadow-sm h-100" data-event="{{ json_encode([
                    'title' => $event->title,
                    'category' => $event->category,
                    'status' => $event->status,
                    'date_display' => $event->event_date->format('M d, Y'),
                    'start' => $event->start_time?->format('h:i A'),
                    'end' => $event->end_time?->format('h:i A'),
                    'village' => $event->village?->village_name ?? 'All Villages',
                    'venue' => $event->venue,
                    'description' => $event->description,
                    'creator' => $event->creator?->full_name,
                    'creator_role' => $event->creator?->role?->role_slug,
                    'going' => $event->going_count,
                    'maybe' => $event->maybe_count,
                    'max' => $event->max_participants,
                    'cover_url' => $event->cover_image ? $event->cover_image_url : null,
                    'show_url' => route('events.show', $event),
                ]) }}">
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
                        <br><i class="bi bi-buildings me-1"></i> {{ $event->village?->village_name ?? 'All Villages' }}
                        @if($event->venue)
                            <br><i class="bi bi-geo-alt me-1"></i> {{ $event->venue }}
                        @endif
                    </p>
                    <span class="badge bg-light text-dark">{{ $event->category }}</span>
                    <div class="small text-muted mt-2">
                        <i class="bi bi-person me-1"></i>
                        @if($event->creator)
                            {{ $event->creator->full_name }}
                            @if($event->creator->role?->role_slug === 'super_admin')
                                <span class="badge bg-purple text-white ms-1">Super Admin</span>
                            @elseif($event->creator->role?->role_slug === 'admin')
                                <span class="badge bg-primary-subtle text-primary-emphasis border ms-1">Admin</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>
                        {{ $event->going_count }} Going &middot; {{ $event->maybe_count }} Maybe
                    </small>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#eventViewModal">View</button>
                        @if($event->isManagedBy(auth()->user()))
                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline"
                                  data-confirm="Delete this event? This cannot be undone.">
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

{{-- Quick event info modal --}}
<div class="modal fade" id="eventViewModal" tabindex="-1" aria-labelledby="eventViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="eventViewLabel">Event Details</h6>
                    <small class="text-muted" id="evViewCategory">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge" id="evViewStatus">—</span>
                    <span class="badge bg-light text-dark" id="evViewVillage">—</span>
                </div>
                <h5 class="fw-bold mb-3" id="evViewTitle">—</h5>
                <img id="evViewCover" src="" alt="Cover" class="img-fluid rounded mb-3" style="max-height: 220px; display:none">
                <div class="row g-2 small mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted"><i class="bi bi-people me-1"></i>Going</div>
                            <div class="fw-bold" id="evViewGoing">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted"><i class="bi bi-question-circle me-1"></i>Maybe</div>
                            <div class="fw-bold" id="evViewMaybe">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 small" id="evViewBody" style="white-space: pre-line"></div>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Date</dt>
                    <dd class="col-sm-8 fw-semibold" id="evViewDate">—</dd>
                    <dt class="col-sm-4 text-muted">Time</dt>
                    <dd class="col-sm-8 fw-semibold" id="evViewTime">—</dd>
                    <dt class="col-sm-4 text-muted">Venue</dt>
                    <dd class="col-sm-8 fw-semibold" id="evViewVenue">—</dd>
                    <dt class="col-sm-4 text-muted">Created By</dt>
                    <dd class="col-sm-8 fw-semibold" id="evViewCreator">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="evViewFull" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open full page
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = (value == null || value === '') ? '—' : value;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-bs-target="#eventViewModal"]');
        if (!trigger) return;

        var card = trigger.closest('[data-event]');
        if (!card) return;

        var d;
        try { d = JSON.parse(card.getAttribute('data-event')); }
        catch (error) { console.error('Event data could not be parsed:', error); return; }

        setText('evViewTitle', d.title);
        setText('evViewCategory', d.category);

        var status = document.getElementById('evViewStatus');
        if (status) {
            var labels = { draft: 'Draft', upcoming: 'Upcoming', ongoing: 'Ongoing', completed: 'Completed', cancelled: 'Cancelled' };
            var classes = { draft: 'bg-warning text-dark', upcoming: 'bg-primary', ongoing: 'bg-success', completed: 'bg-secondary', cancelled: 'bg-danger' };
            status.textContent = labels[d.status] || d.status || '—';
            status.className = 'badge ' + (classes[d.status] || 'bg-secondary');
        }

        setText('evViewVillage', d.village);
        setText('evViewGoing', d.going != null ? d.going : '');
        setText('evViewMaybe', d.maybe != null ? d.maybe : '');
        setText('evViewDate', d.date_display);
        setText('evViewTime', (d.start || d.end) ? ((d.start || '—') + (d.end ? ' – ' + d.end : '')) : '');
        setText('evViewVenue', d.venue);

        var role = d.creator_role ? ' (' + d.creator_role.replace('_', ' ') + ')' : '';
        setText('evViewCreator', d.creator ? d.creator + role : '');

        var body = document.getElementById('evViewBody');
        if (body) body.textContent = d.description || 'No description provided.';

        var cover = document.getElementById('evViewCover');
        if (cover) {
            if (d.cover_url) { cover.src = d.cover_url; cover.style.display = ''; }
            else { cover.removeAttribute('src'); cover.style.display = 'none'; }
        }

        var full = document.getElementById('evViewFull');
        if (full) full.href = d.show_url || '#';
    });
});
</script>
@endpush
