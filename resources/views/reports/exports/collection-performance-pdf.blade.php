<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffc107; }
        .header h2 { margin: 0; color: #ffc107; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #ffc107; color: #333; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 11px; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>Collection Performance Report</h2>
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
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Total Households</td><td>{{ $stats['total_households'] ?? 0 }}</td></tr>
            <tr><td>Average Reliability Score</td><td>{{ $stats['avg_score'] ?? 0 }} / 100</td></tr>
            <tr><td>Collection Rate</td><td>{{ $stats['collection_rate'] ?? 0 }}%</td></tr>
            <tr><td>On-Time Payment Rate</td><td>{{ $stats['on_time_rate'] ?? 0 }}%</td></tr>
            <tr><td>Late Payment Rate</td><td>{{ $stats['late_rate'] ?? 0 }}%</td></tr>
            <tr><td>Partial Payment Rate</td><td>{{ $stats['partial_rate'] ?? 0 }}%</td></tr>
            <tr><td>Total Collected</td><td>P{{ number_format($stats['total_collected'] ?? 0, 2) }}</td></tr>
            <tr><td>Total Outstanding</td><td>P{{ number_format($stats['total_outstanding'] ?? 0, 2) }}</td></tr>
            <tr><td>Average Payment Delay</td><td>{{ $stats['avg_delay_days'] ?? 0 }} days</td></tr>
            <tr><td>High-Risk Households</td><td>{{ $stats['high_risk_count'] ?? 0 }}</td></tr>
            <tr><td>At-Risk Households</td><td>{{ $stats['at_risk_count'] ?? 0 }}</td></tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Multi-Village Management System — Collection Performance Report</p>
    </div>
</body>
</html>
