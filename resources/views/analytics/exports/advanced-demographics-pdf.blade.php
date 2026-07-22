<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Advanced Demographics Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { font-size: 18px; color: #0d6efd; margin-bottom: 5px; }
        h2 { font-size: 14px; color: #6c757d; margin-top: 20px; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 6px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .stat { display: inline-block; width: 48%; margin: 5px 0; }
        .stat strong { color: #0d6efd; }
        .badge { padding: 2px 8px; border-radius: 4px; color: #fff; font-size: 10px; }
        .badge-warning { background: #ffc107; }
        .badge-success { background: #198754; }
        .badge-info { background: #0dcaf0; color: #000; }
        .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #dee2e6; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>Advanced Demographics & Planning Analytics</h1>
    <p>Generated: {{ date('F d, Y') }}</p>

    <h2>Key Indicators</h2>
    <table>
        <tr><th>Indicator</th><th>Value</th></tr>
        <tr><td>Total Dependency Ratio</td><td>{{ number_format($indicators['dependency']['total_dependency_ratio'], 1) }}%</td></tr>
        <tr><td>Child Dependency Ratio</td><td>{{ number_format($indicators['dependency']['child_dependency_ratio'], 1) }}%</td></tr>
        <tr><td>Senior Dependency Ratio</td><td>{{ number_format($indicators['dependency']['senior_dependency_ratio'], 1) }}%</td></tr>
        <tr><td>Population Density</td><td>{{ number_format($indicators['density']['population_density'], 1) }} people/km²</td></tr>
        <tr><td>Household Density</td><td>{{ number_format($indicators['density']['household_density'], 1) }} HH/km²</td></tr>
        <tr><td>Annual Growth Rate</td><td>{{ number_format($indicators['growth']['annual_growth'], 1) }}%</td></tr>
        <tr><td>Monthly Growth Rate</td><td>{{ number_format($indicators['growth']['monthly_growth'], 1) }}%</td></tr>
        <tr><td>Average Residency</td><td>{{ number_format($indicators['residency']['average_years'], 1) }} years</td></tr>
        <tr><td>Migration Rate</td><td>{{ number_format($indicators['migration']['migration_rate'], 1) }}%</td></tr>
        <tr><td>HH Stability Index</td><td>{{ number_format($indicators['household_stability']['stability_index'], 1) }}</td></tr>
        <tr><td>Working Age Population</td><td>{{ number_format($indicators['community_structure']['working_population']) }} ({{ number_format($indicators['community_structure']['working_pct'], 1) }}%)</td></tr>
        <tr><td>Youth Population</td><td>{{ number_format($indicators['community_structure']['youth_population']) }} ({{ number_format($indicators['community_structure']['youth_pct'], 1) }}%)</td></tr>
        <tr><td>Senior Population</td><td>{{ number_format($indicators['community_structure']['senior_population']) }} ({{ number_format($indicators['community_structure']['senior_pct'], 1) }}%)</td></tr>
        <tr><td>PWD Population</td><td>{{ number_format($indicators['community_structure']['pwd_population']) }}</td></tr>
        <tr><td>Average HH Size</td><td>{{ number_format($indicators['household_stability']['avg_members'], 1) }} members</td></tr>
    </table>

    @if(!empty($comparison))
    <h2>Village Comparison</h2>
    <table>
        <tr><th>Village</th><th>Population</th><th>Density/km²</th><th>Growth</th><th>Avg Age</th><th>Dependency</th><th>HH Density</th><th>Residency</th></tr>
        @foreach($comparison as $row)
        <tr>
            <td>{{ $row['village_name'] }}</td>
            <td>{{ number_format($row['population']) }}</td>
            <td>{{ number_format($row['population_density'], 1) }}</td>
            <td>{{ number_format($row['annual_growth'], 1) }}%</td>
            <td>{{ number_format($row['average_age'], 1) }}</td>
            <td>{{ number_format($row['dependency_ratio'], 1) }}%</td>
            <td>{{ number_format($row['household_density'], 1) }}</td>
            <td>{{ number_format($row['avg_residency_years'], 1) }} yrs</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if(!empty($insights))
    <h2>Planning Insights</h2>
    @foreach($insights as $insight)
    <p><strong>{{ $insight['title'] }}:</strong> {{ $insight['description'] }}</p>
    @endforeach
    @endif

    <div class="footer">Multi-Village Management System — Advanced Demographics Report</div>
</body>
</html>
