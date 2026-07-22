<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Executive Intelligence Report</title>
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
    <h1>Executive Intelligence Report</h1>
    <p class="date">Generated on: {{ now()->format('F d, Y h:i A') }}</p>

    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Insights</div>
            <div class="value">{{ count($topInsights) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">High Priority</div>
            <div class="value" style="color: #e74a3b;">{{ collect($topInsights)->where('priority', 'high')->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Villages Analyzed</div>
            <div class="value">{{ count($villageHighlights) }}</div>
        </div>
    </div>

    <h2>Priority Insights</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Message</th>
                <th>Recommendation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topInsights as $index => $insight)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ucfirst($insight['category'] ?? 'General') }}</td>
                    <td>{{ ucfirst($insight['priority'] ?? 'Low') }}</td>
                    <td>{{ $insight['message'] ?? '' }}</td>
                    <td>{{ $insight['recommendation'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No insights available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Village Highlights</h2>
    <table>
        <thead>
            <tr>
                <th>Village</th>
                <th>Key Metrics</th>
            </tr>
        </thead>
        <tbody>
            @forelse($villageHighlights as $village)
                <tr>
                    <td>{{ $village['name'] ?? $village['village'] ?? '' }}</td>
                    <td>{{ $village['metrics'] ?? $village['highlights'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #718096;">No village highlight data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>