<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Community Development Report</title>
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
    <h1>Community Development Report</h1>
    <p class="date">Generated on: {{ now()->format('F d, Y h:i A') }}</p>

    <h2>Development Scores</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Development Score</div>
            <div class="value">{{ $devScore ?? 'N/A' }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Socioeconomic Score</div>
            <div class="value">{{ $socioScore ?? 'N/A' }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Readiness Index</div>
            <div class="value">{{ $readiness ?? 'N/A' }}</div>
        </div>
    </div>

    <h2>Development Indicators</h2>
    <table>
        <thead>
            <tr>
                <th>Indicator</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($indicators as $indicator)
                <tr>
                    <td>{{ $indicator['name'] ?? $indicator['indicator'] ?? '' }}</td>
                    <td>{{ $indicator['value'] ?? '' }}</td>
                    <td>{{ $indicator['status'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #718096;">No indicator data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>