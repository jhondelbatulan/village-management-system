<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Household Reliability Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0dcaf0; }
        .header h2 { margin: 0; color: #0dcaf0; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #0dcaf0; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 11px; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
        .summary-box { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; text-align: center; }
        .summary-box .number { font-size: 24px; font-weight: bold; }
        .summary-box .label { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>Household Reliability Report</h2>
            <p>Village: {{ $villageName }}</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Multi-Village Management System</strong></p>
            <p>Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    <div style="margin: 20px 0;">
        <div class="summary-box" style="background: #d1e7dd;">
            <div class="number" style="color: #0f5132;">{{ $avgScore }}/100</div>
            <div class="label">Average Score</div>
        </div>
        @foreach(['excellent' => '#198754', 'good' => '#0d6efd', 'moderate' => '#ffc107', 'at_risk' => '#fd7e14', 'high_risk' => '#dc3545'] as $behavior => $color)
        <div class="summary-box" style="background: {{ $color }}15; border: 1px solid {{ $color }};">
            <div class="number" style="color: {{ $color }};">{{ $distribution[$behavior]['count'] ?? 0 }}</div>
            <div class="label">{{ ucfirst(str_replace('_', ' ', $behavior)) }}</div>
        </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Behavior</th>
                <th>Count</th>
                <th>Percentage</th>
                <th>Avg. Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['excellent' => 'Excellent Payer', 'good' => 'Good Payer', 'moderate' => 'Moderate Payer', 'at_risk' => 'At-Risk Payer', 'high_risk' => 'High-Risk Payer'] as $key => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ $distribution[$key]['count'] ?? 0 }}</td>
                <td>{{ $distribution[$key]['percentage'] ?? 0 }}%</td>
                <td>{{ $distribution[$key]['avg_score'] ?? 0 }} / 100</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Multi-Village Management System — Household Reliability Report</p>
    </div>
</body>
</html>
