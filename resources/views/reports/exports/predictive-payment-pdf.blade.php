<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Prediction Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0d6efd; }
        .header h2 { margin: 0; color: #0d6efd; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #0d6efd; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 11px; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
        .behavior-excellent { color: #198754; font-weight: bold; }
        .behavior-good { color: #0d6efd; font-weight: bold; }
        .behavior-moderate { color: #ffc107; font-weight: bold; }
        .behavior-at_risk { color: #fd7e14; font-weight: bold; }
        .behavior-high_risk { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>Payment Prediction Report</h2>
            <p>Village: {{ $villageName }}</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Multi-Village Management System</strong></p>
            <p>Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Household #</th>
                <th>Head Name</th>
                <th>Block/Lot</th>
                <th>Behavior</th>
                <th>Score</th>
                <th>Confidence (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($households as $household)
            <tr>
                <td>{{ $household['household_number'] }}</td>
                <td>{{ $household['head_name'] }}</td>
                <td>{{ $household['block_lot'] }}</td>
                <td class="behavior-{{ $household['behavior'] }}">{{ $household['behavior_label'] }}</td>
                <td>{{ $household['score'] }} / 100</td>
                <td>{{ $household['confidence'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">No payment prediction data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Multi-Village Management System — Payment Prediction Report</p>
    </div>
</body>
</html>
