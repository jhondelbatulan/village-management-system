<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Planning Analytics Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 20px; }
        h1 { font-size: 18px; color: #0d6efd; margin-bottom: 5px; }
        h2 { font-size: 14px; color: #6c757d; margin-top: 20px; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #dee2e6; padding: 6px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #dee2e6; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>Planning Analytics Dashboard</h1>
    <p>Generated: {{ date('F d, Y') }}</p>

    <h2>Summary Indicators</h2>
    <table>
        <tr><th>Indicator</th><th>Value</th></tr>
        @foreach($indicators as $section => $data)
        <tr><td colspan="2" style="background:#f0f0f0;font-weight:bold;text-transform:capitalize">{{ str_replace('_', ' ', $section) }}</td></tr>
        @foreach($data as $key => $value)
        @if(is_numeric($value) || is_string($value))
        <tr><td>{{ ucwords(str_replace('_', ' ', $key)) }}</td><td>{{ $value }}</td></tr>
        @endif
        @endforeach
        @endforeach
    </table>

    @if(!empty($comparison))
    <h2>Village Comparison</h2>
    <table>
        <tr><th>Village</th><th>Population</th><th>Density/km²</th><th>Growth</th><th>Dependency</th><th>Stability</th></tr>
        @foreach($comparison as $row)
        <tr>
            <td>{{ $row['village_name'] }}</td>
            <td>{{ number_format($row['population']) }}</td>
            <td>{{ number_format($row['population_density'], 1) }}</td>
            <td>{{ number_format($row['annual_growth'], 1) }}%</td>
            <td>{{ number_format($row['dependency_ratio'], 1) }}%</td>
            <td>{{ number_format($row['stability_index'], 1) }}</td>
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

    <div class="footer">Multi-Village Management System — Planning Analytics Report</div>
</body>
</html>
