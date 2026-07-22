@if($pinnedSessions->count() > 0)
<div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 280px;">
    <li class="px-3 py-2">
        <small class="text-muted fw-semibold">QUICK SWITCH</small>
    </li>
    <li><hr class="dropdown-divider mt-0"></li>

    @if($isSwitched)
    <li>
        <form action="{{ route('sessions.back') }}" method="POST">
            @csrf
            <button type="submit" class="dropdown-item text-warning">
                <i class="bi bi-arrow-return-left me-2"></i>Switch Back to Original
            </button>
        </form>
    </li>
    <li><hr class="dropdown-divider"></li>
    @endif

    @foreach($pinnedSessions as $session)
        @if($session->targetUser && $session->target_user_id !== auth()->id())
        <li>
            <form action="{{ route('sessions.switch', $session->target_user_id) }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item d-flex align-items-center py-2">
                    <img src="{{ asset($session->targetUser->profile_picture ?? config('app_settings.assets.default_profile')) }}"
                         alt="" class="rounded-circle me-2" width="28" height="28">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $session->targetUser->full_name }}</div>
                        <small class="text-muted">
                            {{ ucfirst(str_replace('_', ' ', $session->targetUser->role?->role_slug ?? '')) }}
                            @if($session->targetUser->village)
                                — {{ $session->targetUser->village->village_name }}
                            @endif
                        </small>
                    </div>
                    <i class="bi bi-box-arrow-right text-muted"></i>
                </button>
            </form>
        </li>
        @endif
    @endforeach

    <li><hr class="dropdown-divider"></li>
    <li>
        <a class="dropdown-item text-center small" href="{{ route('sessions.index') }}">
            <i class="bi bi-gear me-1"></i>Manage Accounts
        </a>
    </li>
</div>
@endif
