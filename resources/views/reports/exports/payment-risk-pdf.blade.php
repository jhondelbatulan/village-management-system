<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Risk Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #dc3545; }
        .header h2 { margin: 0; color: #dc3545; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #dc3545; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 11px; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
        .risk-high { color: #dc3545; font-weight: bold; }
        .risk-at { color: #fd7e14; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>Payment Risk Report</h2>
            <p>Village: {{ $villageName }}</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Multi-Village Management System</strong></p>
            <p>Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    <p><strong>Summary:</strong> {{ $stats['high_risk_count'] ?? 0 }} high-risk households, {{ $stats['at_risk_count'] ?? 0 }} at-risk households requiring attention.</p>

    <table>
        <thead>
            <tr>
                <th>Household #</th>
                <th>Head Name</th>
                <th>Block/Lot</th>
                <th>Risk Level</th>
                <th>Score</th>
                <th>Reasons</th>
            </tr>
        </thead>
        <tbody>
            @forelse($highRiskHouseholds as $household)
            <tr>
                <td>{{ $household['household_number'] }}</td>
                <td>{{ $household['head_name'] }}</td>
                <td>{{ $household['block_lot'] }}</td>
                <td class="risk-{{ $household['behavior'] === 'high_risk' ? 'high' : 'at' }}">{{ $household['behavior_label'] }}</td>
                <td>{{ $household['score'] }} / 100</td>
                <td>{{ implode(', ', $household['reasons']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">No at-risk or high-risk households found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Multi-Village Management System — Payment Risk Report</p>
    </div>
</body>
</html>
