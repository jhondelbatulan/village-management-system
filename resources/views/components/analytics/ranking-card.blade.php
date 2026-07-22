@props([
    'title' => '',
    'items' => [],
    'icon' => 'bi-trophy',
    'color' => 'warning',
    'valueLabel' => 'Value',
    'maxItems' => 5,
])

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center">
        <h6 class="m-0 fw-bold text-{{ $color }}">
            <i class="bi {{ $icon }} me-2"></i>{{ $title }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse(array_slice($items, 0, $maxItems) as $item)
                <div class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $loop->iteration <= 3 ? $color : 'secondary' }} rounded-circle" style="width:24px;height:24px;line-height:18px;">
                            {{ $loop->iteration }}
                        </span>
                        <span class="small fw-semibold">{{ $item['name'] ?? '' }}</span>
                    </div>
                    <span class="small fw-bold text-{{ $color }}">{{ $item['value'] ?? '' }}</span>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="bi bi-inbox fs-1 text-gray-300"></i>
                    <p class="text-muted small mt-2 mb-0">No data available</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
