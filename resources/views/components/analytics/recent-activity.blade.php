@props([
    'activities' => collect(),
    'maxItems' => 10,
    'title' => 'Recent Activity',
    'icon' => 'bi-activity',
    'color' => 'primary',
])

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-{{ $color }}">
            <i class="bi {{ $icon }} me-2"></i>{{ $title }}
        </h6>
        <span class="badge bg-{{ $color }}">{{ $activities->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
            @forelse($activities->take($maxItems) as $activity)
                <div class="list-group-item list-group-item-action px-3 py-3 border-start border-{{ $activity['color'] }} border-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-{{ $activity['color'] }}-soft rounded-circle p-2">
                                <i class="bi {{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <p class="mb-1 small fw-semibold text-dark">{{ $activity['action'] }}</p>
                            @if($activity['description'])
                                <p class="mb-1 small text-muted text-truncate">{{ $activity['description'] }}</p>
                            @endif
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $activity['user'] }}
                                </small>
                                <small class="text-muted">·</small>
                                <small class="text-muted">{{ $activity['time_ago'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No recent activity</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
