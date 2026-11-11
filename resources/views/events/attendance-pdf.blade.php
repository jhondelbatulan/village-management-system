<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ $event->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; color: #2d3748; border-bottom: 2px solid #4e73df; padding-bottom: 5px; }
        h2 { font-size: 14px; color: #4e73df; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #4e73df; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f7fafc; }
        .meta { color: #4a5568; margin: 2px 0; }
        .date { color: #718096; font-size: 11px; }
        .badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; }
        .present { background: #c6f6d5; color: #22543d; }
        .absent { background: #fed7d7; color: #742a2a; }
        .excused { background: #fefcbf; color: #744210; }
        .late { background: #e9d8fd; color: #44337a; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <p class="date">Generated on: {{ $generatedAt->format('F d, Y h:i A') }}</p>

    <h2>Event Details</h2>
    <p class="meta"><strong>Event:</strong> {{ $event->title }}</p>
    <p class="meta"><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}
        @if($event->start_time)
            &nbsp;&middot;&nbsp; <strong>Time:</strong> {{ $event->start_time->format('h:i A') }}
            @if($event->end_time) – {{ $event->end_time->format('h:i A') }} @endif
        @endif
    </p>
    <p class="meta"><strong>Village:</strong> {{ $scopeVillage ?? 'All Villages' }}</p>
    <p class="meta"><strong>Venue:</strong> {{ $event->venue ?? '—' }}</p>
    <p class="meta"><strong>Category:</strong> {{ $event->category }}</p>

    <h2>Summary</h2>
    <p class="meta">
        <strong>Total Attendees:</strong> {{ $participations->count() }}
        &nbsp;&middot;&nbsp;
        <strong>Present:</strong> {{ $participations->where('status', 'present')->count() }}
        &nbsp;&middot;&nbsp;
        <strong>Absent:</strong> {{ $participations->where('status', 'absent')->count() }}
        &nbsp;&middot;&nbsp;
        <strong>Excused:</strong> {{ $participations->where('status', 'excused')->count() }}
    </p>

    <h2>Participants</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Block / Lot</th>
                <th>Household No.</th>
                <th>Household Head</th>
                @if($event->village_id === null)
                    <th>Village</th>
                @endif
                <th>Status</th>
                <th>Remarks</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participations as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($p->household)
                            Block {{ $p->household->block_number ?? 'N/A' }} - Lot {{ $p->household->lot_number ?? 'N/A' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $p->household?->household_number ?? '—' }}</td>
                    <td>{{ $p->household?->household_head_name ?? '—' }}</td>
                    @if($event->village_id === null)
                        <td>{{ $p->household?->village?->village_name ?? '—' }}</td>
                    @endif
                    <td>
                        @if($p->status === 'present')
                            <span class="badge present">Present</span>
                        @elseif($p->status === 'absent')
                            <span class="badge absent">Absent</span>
                        @elseif($p->status === 'late')
                            <span class="badge late">Late</span>
                        @else
                            <span class="badge excused">Excused</span>
                        @endif
                    </td>
                    <td>{{ $p->remarks ?? '—' }}</td>
                    <td>{{ $p->recorder?->full_name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #718096;">No participations recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
