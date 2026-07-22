@extends('layouts.app')

@section('title', 'Report History')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-clock-history me-2"></i>Report History
        </h1>
        <p class="text-muted mb-0">View your previously generated reports</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Generate New Report
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($reports->count() > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Report Name</th>
                    <th>Category</th>
                    <th>Village</th>
                    <th>Format</th>
                    <th>Size</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td>
                        <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                        <strong>{{ $report->report_name }}</strong>
                        @if($report->is_scheduled)
                            <span class="badge bg-info ms-1">Scheduled</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ ucfirst($report->category) }}</span></td>
                    <td>{{ $report->village?->village_name ?? 'All Villages' }}</td>
                    <td>
                        <span class="badge bg-{{ $report->format === 'pdf' ? 'danger' : ($report->format === 'csv' ? 'success' : 'info') }}">
                            {{ strtoupper($report->format) }}
                        </span>
                    </td>
                    <td class="small">{{ $report->file_size_formatted }}</td>
                    <td class="small text-muted">{{ $report->created_at->format('M d, Y h:i A') }}</td>
                    <td>
                        @if($report->file_path && file_exists($report->file_path))
                        <a href="{{ route('reports.generate', ['report_type' => $report->report_type, 'format' => $report->format, 'village_id' => $report->village_id]) }}" class="btn btn-sm btn-outline-primary" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="text-muted mt-2">No reports generated yet.</p>
            <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm">Generate Your First Report</a>
        </div>
        @endif
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $reports->links() }}
</div>
@endsection
