@extends('layouts.app')

@section('title', 'Participation History - ' . $resident->full_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person me-2"></i>{{ $resident->full_name }}
            </h1>
            <p class="text-muted mb-0">Participation History - {{ $resident->village?->village_name ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('participation.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- Resident Info -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">Household</small>
                    <strong>{{ $resident->household?->family_name ?? 'N/A' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Village</small>
                    <strong>{{ $resident->village?->village_name ?? 'N/A' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge bg-{{ $resident->status === 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($resident->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Participation Records -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history me-1"></i>Participation Records
            </h6>
        </div>
        <div class="card-body">
            @if($participations->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="25%">Event</th>
                            <th width="15%">Date</th>
                            <th width="12%">Status</th>
                            <th width="20%">Remarks</th>
                            <th width="15%">Recorded By</th>
                            <th width="13%">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participations as $participation)
                        <tr>
                            <td>
                                <a href="{{ route('events.show', $participation->event) }}">
                                    {{ $participation->event->title ?? 'N/A' }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $participation->event->category ?? '' }}</small>
                            </td>
                            <td>{{ $participation->event?->event_date ? \Carbon\Carbon::parse($participation->event->event_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @if($participation->status === 'present')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                                @elseif($participation->status === 'absent')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                @elseif($participation->status === 'excused')
                                    <span class="badge bg-info"><i class="bi bi-info-circle me-1"></i>Excused</span>
                                @elseif($participation->status === 'late')
                                    <span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Late</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($participation->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $participation->remarks ?? '-' }}</td>
                            <td>{{ $participation->recorder?->name ?? 'System' }}</td>
                            <td>{{ $participation->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $participations->links() }}
            @else
            <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-gray-300"></i>
                <h5 class="mt-2 text-gray-600">No Participation Records</h5>
                <p class="text-muted">This resident has no recorded participation yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
