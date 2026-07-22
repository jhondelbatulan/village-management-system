<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Occupation Analytics Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; color: #2d3748; border-bottom: 2px solid #4e73df; padding-bottom: 5px; }
        h2 { font-size: 14px; color: #4e73df; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #4e73df; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f7fafc; }
        .summary-grid { display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0; }
        .summary-item { border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; width: 30%; }
        .label { font-size: 10px; color: #718096; text-transform: uppercase; }
        .value { font-size: 16px; font-weight: bold; color: #2d3748; }
        .date { color: #718096; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Occupation Analytics Report</h1>
    <p class="date">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>

    <h2>Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Occupations</div>
            <div class="value">{{ number_format($summary['total_occupations'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Workers</div>
            <div class="value">{{ number_format($summary['total_workers'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Avg Workers / Occupation</div>
            <div class="value">{{ number_format($summary['avg_workers_per_occupation'] ?? 0, 1) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Top Occupation</div>
            <div class="value">{{ $summary['top_occupation'] ?? 'N/A' }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Villages Represented</div>
            <div class="value">{{ number_format($summary['total_villages'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Unique Occupation Types</div>
            <div class="value">{{ number_format($summary['unique_categories'] ?? 0) }}</div>
        </div>
    </div>

    <h2>Occupation Rankings</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Occupation</th>
                <th>Category</th>
                <th>Workers</th>
                <th>Share (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rankings as $index => $rank)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $rank['name'] ?? $rank['occupation'] ?? 'N/A' }}</td>
                    <td>{{ $rank['category'] ?? 'N/A' }}</td>
                    <td>{{ number_format($rank['count'] ?? $rank['workers'] ?? 0) }}</td>
                    <td>{{ number_format($rank['percentage'] ?? 0, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No ranking data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>