@extends('layouts.app')
@section('title', 'Participation - ' . $village->village_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-geo-alt me-2"></i>{{ $village->village_name }} Participation
            </h1>
            <p class="text-muted mb-0">{{ $village->village_code }} - Events &amp; Household Participation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar me-1"></i>All Events
            </a>
            <a href="{{ route('participation.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Participation Monitoring
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $participationStats['total_events'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-event fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Attendance Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $participationStats['attendance_rate'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Household Participations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($participationStats['total_participants']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Residents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($participationStats['total_residents']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-lines-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-calendar3 me-1"></i>Events in {{ $village->village_name }}

            </h6>
        </div>
        <div class="card-body">
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Participations</th>
                                <th>Responses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>
                                        <strong>{{ $event->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $event->category }}</small>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($event->status) }}</span></td>
                                    <td><span class="badge bg-primary">{{ $event->participations_count }} records</span></td>
                                    <td><span class="badge bg-info">{{ $event->responses_count }} responses</span></td>
                                    <td>
                                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($events->hasPages())
                    <div class="mt-2">{{ $events->links() }}</div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                    <h5 class="mt-2 text-gray-600">No Events</h5>
                    <p class="text-muted">No events found for this village.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-house me-1"></i>Household Participation
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-success">{{ $activeHouseholds->count() }} Active</span>
                <span class="badge bg-danger">{{ $inactiveHouseholds->count() }} Inactive</span>
            </div>
        </div>
        <div class="card-body">
            @if(count($householdStats) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Block / Lot</th>
                                <th>Household Head</th>
                                <th>Members</th>
                                <th>Events Attended</th>
                                <th>Participation %</th>
                                <th>Last Attendance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($householdStats as $stat)
                                <tr>
                                    <td>
                                        <a href="{{ route('participation.household', $stat['household']) }}" class="fw-semibold text-decoration-none">
                                            Block {{ $stat['household']->block_number ?? 'N/A' }} - Lot {{ $stat['household']->lot_number ?? 'N/A' }}

                                        </a>
                                    </td>
                                    <td>{{ $stat['household']->household_head_name ?? '—' }}</td>
                                    <td>{{ $stat['member_count'] }}</td>
                                    <td>{{ $stat['participated_count'] }} / {{ $stat['total_events'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px; width: 100px;">
                                                <div class="progress-bar bg-{{ $stat['participation_pct'] >= 50 ? 'success' : ($stat['participation_pct'] >= 25 ? 'warning' : 'danger') }}"
                                                     style="width: {{ $stat['participation_pct'] }}%"></div>
                                            </div>
                                            <strong class="{{ $stat['participation_pct'] >= 50 ? 'text-success' : ($stat['participation_pct'] >= 25 ? 'text-warning' : 'text-danger') }}">
                                                {{ $stat['participation_pct'] }}%
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($stat['last_attendance'])
                                            {{ $stat['last_attendance']->diffForHumans() }}

                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($stat['is_active'])
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('participation.household', $stat['household']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-house fs-1 text-gray-300"></i>
                    <h5 class="mt-2 text-gray-600">No Household Data</h5>
                    <p class="text-muted">No household participation data available.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

