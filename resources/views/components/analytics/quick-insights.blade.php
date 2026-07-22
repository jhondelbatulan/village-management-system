@props([
    'insights' => [],
    'title' => 'Quick Insights',
    'icon' => 'bi-lightbulb',
    'color' => 'info',
])

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center">
        <h6 class="m-0 fw-bold text-{{ $color }}">
            <i class="bi {{ $icon }} me-2"></i>{{ $title }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($insights as $insight)
                <div class="list-group-item border-start border-{{ $insight['type'] ?? 'info' }} border-3 px-3 py-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="bg-{{ $insight['type'] ?? 'info' }}-soft rounded-circle p-2">
                                <i class="bi {{ $insight['icon'] ?? 'bi-info-circle' }} text-{{ $insight['type'] ?? 'info' }}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 small text-dark">{{ $insight['message'] }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="bi bi-lightbulb fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No insights available</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
