@props([
    'title' => 'GIS Summary',
    'mappedHouseholds' => 0,
    'totalHouseholds' => 0,
    'coveragePct' => 0,
    'villagesMapped' => 0,
    'totalVillages' => 0,
    'route' => '#',
])

<div class="card shadow-sm h-100">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-success">
            <i class="bi bi-geo-alt me-2"></i>{{ $title }}
        </h6>
        <a href="{{ $route }}" class="btn btn-sm btn-outline-success">View Map</a>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col-6">
                <div class="bg-success-soft rounded p-3">
                    <div class="h4 mb-0 fw-bold text-success">{{ $coveragePct }}%</div>
                    <small class="text-muted">Coverage</small>
                </div>
            </div>
            <div class="col-6">
                <div class="bg-info-soft rounded p-3">
                    <div class="h4 mb-0 fw-bold text-info">{{ $villagesMapped }}/{{ $totalVillages }}</div>
                    <small class="text-muted">Villages Mapped</small>
                </div>
            </div>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: {{ $coveragePct }}%"></div>
        </div>
        <small class="text-muted d-block mt-2">{{ number_format($mappedHouseholds) }} of {{ number_format($totalHouseholds) }} households mapped</small>
    </div>
</div>
