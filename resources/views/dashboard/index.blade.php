@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Dashboard</h4>
            <p class="page-subtitle text-muted mb-0">
                Welcome back, {{ $authUser['name'] ?? 'User' }}. System overview and key metrics.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" disabled>
                <i class="bi bi-download me-1"></i>Export
            </button>
            <button class="btn btn-primary btn-sm" disabled>
                <i class="bi bi-plus-lg me-1"></i>Quick Action
            </button>
        </div>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    @foreach($stats as $stat)
        <x-stat-card
            :title="$stat['title']"
            :value="$stat['value']"
            :icon="$stat['icon']"
            :color="$stat['color']"
            :subtitle="$stat['subtitle']"
        />
    @endforeach
</div>

<div class="row g-3 g-lg-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-activity me-2 text-primary"></i>Latest Activities
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Activity</th>
                                <th scope="col">User</th>
                                <th scope="col" class="text-end">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>
                                        <i class="bi {{ $activity['icon'] }} text-primary me-2"></i>
                                        {{ $activity['action'] }}
                                    </td>
                                    <td>{{ $activity['user'] }}</td>
                                    <td class="text-end text-muted">{{ $activity['time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>System Status
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <ul class="list-group list-group-flush status-list">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Phase</span>
                        <span class="badge bg-primary rounded-pill">Phase 1 — Foundation</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>GIS Module</span>
                        <span class="badge bg-secondary rounded-pill">Pending</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Analytics</span>
                        <span class="badge bg-secondary rounded-pill">Pending</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Database</span>
                        <span class="badge bg-success rounded-pill">Ready</span>
                    </li>
                </ul>

                <div class="alert alert-info mt-4 mb-0 small" role="alert">
                    <i class="bi bi-lightbulb me-1"></i>
                    Business modules and CRUD functions will be added in Phase 2.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
