@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Notifications</h4>
            <p class="page-subtitle text-muted mb-0">
                @if($unreadCount > 0)
                    You have <strong>{{ $unreadCount }}</strong> unread notification{{ $unreadCount !== 1 ? 's' : '' }}.
                @else
                    All caught up!
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i>Mark All as Read
            </button>
        </form>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        <div class="notification-item d-flex align-items-start gap-3 p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}">
            <span class="badge bg-{{ $notification->color ?? 'secondary' }} rounded-circle p-2 mt-1 flex-shrink-0">
                <i class="bi {{ $notification->icon ?? 'bi-bell' }} text-white"></i>
            </span>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 {{ $notification->read_at ? '' : 'fw-bold' }}">{{ $notification->title }}</h6>
                        @if($notification->message)
                            <p class="text-muted small mb-0">{{ $notification->message }}</p>
                        @endif
                    </div>
                    <div class="text-end flex-shrink-0 ms-2">
                        <small class="text-muted" title="{{ $notification->created_at->format('F d, Y h:i A') }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-1">
                    @if(!$notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 small">Mark as read</button>
                    </form>
                    @endif
                    @if($notification->action_url)
                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-link text-decoration-none p-0 small">View details</a>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" style="display:inline;" onsubmit="return confirm('Delete this notification?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size:3rem;"></i>
            <p class="text-muted mt-2 mb-0">No notifications yet.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection
