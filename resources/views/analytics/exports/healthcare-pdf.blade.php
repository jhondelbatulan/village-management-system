<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Healthcare Coverage Report</title>
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
    <h1>Healthcare Coverage Report</h1>
    <p class="date">Generated on: {{ now()->format('F d, Y h:i A') }}</p>

    <h2>Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Covered</div>
            <div class="value">{{ number_format($summary['total_covered'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Coverage Types</div>
            <div class="value">{{ count($coverageDistribution) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Overall Coverage</div>
            <div class="value">{{ $summary['overall_coverage'] ?? '0%' }}</div>
        </div>
    </div>

    <h2>Coverage Distribution</h2>
    <table>
        <thead>
            <tr>
                <th>Coverage Type</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coverageDistribution as $coverage => $count)
                <tr>
                    <td>{{ $coverage }}</td>
                    <td>{{ number_format($count) }}</td>
                    <td>{{ $summary['total_covered'] > 0 ? round(($count / $summary['total_covered']) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #718096;">No coverage data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>