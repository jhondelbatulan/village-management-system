@extends('layouts.app')

@section('title', 'Report Schedules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-calendar-check me-2"></i>Report Schedules
        </h1>
        <p class="text-muted mb-0">Manage automated report generation schedules</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Report Center
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>Create Schedule</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.schedule.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Report Type *</label>
                    <select name="report_type" class="form-select" required>
                        <option value="">Select report...</option>
                        @foreach($reportTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Village</label>
                    <select name="village_id" class="form-select">
                        @if(auth()->user()?->role?->role_slug === 'super_admin')
                            <option value="">All Villages</option>
                        @endif
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" @if(auth()->user()?->role?->role_slug !== 'super_admin') selected @endif>{{ $village->village_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Frequency *</label>
                    <select name="frequency" class="form-select" required>
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected>Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-calendar-plus me-1"></i>Schedule</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-list-ul me-2"></i>Active Schedules</h6>
    </div>
    <div class="card-body p-0">
        @if($scheduledReports->count() > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Report</th>
                    <th>Village</th>
                    <th>Frequency</th>
                    <th>Next Run</th>
                    <th>Last Run</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scheduledReports as $schedule)
                <tr>
                    <td><i class="bi bi-file-earmark-text me-1 text-primary"></i>{{ $schedule->report_name }}</td>
                    <td>{{ $schedule->village?->village_name ?? 'All' }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($schedule->schedule_frequency) }}</span></td>
                    <td class="small">{{ $schedule->next_run_at?->format('M d, Y') ?? 'N/A' }}</td>
                    <td class="small text-muted">{{ $schedule->last_run_at?->format('M d, Y') ?? 'Never' }}</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>
                        <form action="{{ route('reports.schedule.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this schedule?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <p class="text-muted mt-2">No scheduled reports.</p>
        </div>
        @endif
    </div>
</div>
@endsection
