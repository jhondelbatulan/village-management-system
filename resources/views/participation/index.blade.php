@extends('layouts.app')
@section('title', 'Participation Monitoring')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-people me-2"></i>Participation Monitoring
            </h1>
            <p class="text-muted mb-0">
                @if($role === 'super_admin')
                    Village Comparison Overview
                @elseif($role === 'admin')
                    {{ $village->village_name ?? 'Village' }} - President Dashboard
                @else
                    {{ $village->village_name ?? 'Village' }} - Secretary Dashboard
                @endif
            </p>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">
            <i class="bi bi-calendar me-1"></i>View Events
        </a>
    </div>

    @if($role === 'super_admin')

        @php
            $totalVillages = count($stats);
            $totalEvents = collect($stats)->sum('total_events');
            $totalParticipants = collect($stats)->sum('total_participants');
            $avgAttendance = $totalVillages > 0 ? round(collect($stats)->avg('attendance_rate'), 1) : 0;
        @endphp
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Villages</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalVillages }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-house-door fs-2 text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Events</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalEvents }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-event fs-2 text-gray-300"></i>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalParticipants) }}</div>
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
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Attendance Rate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $avgAttendance }}%</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($stats as $villageStat)
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $villageStat['village']->village_name }} ({{ $villageStat['village']->village_code }})
                        </h6>
                        <span class="badge bg-{{ $villageStat['attendance_rate'] >= 50 ? 'success' : ($villageStat['attendance_rate'] >= 25 ? 'warning' : 'danger') }}">
                            {{ $villageStat['attendance_rate'] }}%
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Events</small>
                                <strong>{{ $villageStat['total_events'] }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Residents</small>
                                <strong>{{ $villageStat['total_residents'] }}</strong>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Present</small>
                                <strong class="text-success">{{ $villageStat['present'] }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Absent</small>
                                <strong class="text-danger">{{ $villageStat['absent'] }}</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Late</small>
                                <strong class="text-warning">{{ $villageStat['late'] }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Excused</small>
                                <strong class="text-info">{{ $villageStat['excused'] }}</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Upcoming: {{ $villageStat['upcoming_events'] }}</span>
                            <span class="text-muted small">Completed: {{ $villageStat['completed_events'] }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('participation.village', $villageStat['village']) }}" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-bar-chart me-1"></i> View Events &amp; Households
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-people fs-1 text-gray-300"></i>
                        <h5 class="mt-3 text-gray-600">No Village Data</h5>
                        <p class="text-muted">No active villages found with participation data.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

    @else

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

        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-pie-chart me-1"></i>Participation Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $totalP = $participationStats['total_participants'] ?: 1;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                                <strong>{{ $participationStats['present'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ ($participationStats['present'] / $totalP) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                <strong>{{ $participationStats['absent'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ ($participationStats['absent'] / $totalP) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-warning"><i class="bi bi-clock me-1"></i>Late</span>
                                <strong>{{ $participationStats['late'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($participationStats['late'] / $totalP) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-info"><i class="bi bi-info-circle me-1"></i>Excused</span>
                                <strong>{{ $participationStats['excused'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ ($participationStats['excused'] / $totalP) * 100 }}%"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted d-block">Upcoming</small>
                                <strong class="text-primary">{{ $participationStats['upcoming_events'] }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Completed</small>
                                <strong class="text-success">{{ $participationStats['completed_events'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-calendar3 me-1"></i>Recent Events
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($recentEvents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Participations</th>
                                        <th>Responses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentEvents as $event)
                                    <tr>
                                        <td>
                                            <strong>{{ $event->title }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $event->category }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                                        <td><span class="badge bg-primary">{{ $event->participations_count }} records</span></td>
                                        <td><span class="badge bg-info">{{ $event->responses_count }} responses</span></td>
                                        <td>
                                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-primary">
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
                            <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                            <h5 class="mt-2 text-gray-600">No Recent Events</h5>
                            <p class="text-muted">No events found for this village.</p>
                        </div>
                        @endif
                    </div>
                </div>
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
                                        Block {{ $stat['household']->block_number ?? 'N/A' }} — Lot {{ $stat['household']->lot_number ?? 'N/A' }}

                                    </a>
                                </td>
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
    @endif
</div>
@endsection

