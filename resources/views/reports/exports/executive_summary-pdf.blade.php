<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Executive Summary Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .report-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2e59d9; }
        .report-header h1 { font-size: 22px; color: #2e59d9; margin-bottom: 5px; }
        .report-header .subtitle { font-size: 12px; color: #666; }
        .report-header .logo-section { text-align: right; }
        .report-header .logo-section strong { font-size: 13px; color: #2e59d9; display: block; }
        .report-header .logo-section small { color: #888; font-size: 10px; }
        .report-meta { background: #f0f4ff; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; display: flex; gap: 25px; font-size: 10px; color: #555; flex-wrap: wrap; }
        .report-meta span strong { color: #333; }
        h2 { font-size: 16px; color: #2e59d9; margin: 22px 0 10px 0; padding-bottom: 5px; border-bottom: 2px solid #2e59d9; }
        h3 { font-size: 13px; color: #555; margin: 14px 0 6px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 15px 0; font-size: 10px; }
        th { background-color: #2e59d9; color: white; padding: 7px 10px; text-align: left; font-weight: 600; }
        td { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background-color: #f8f9fc; }
        .summary-grid { display: flex; gap: 12px; margin: 12px 0 18px 0; flex-wrap: wrap; }
        .summary-box { background: #f8f9fc; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px 15px; min-width: 130px; text-align: center; flex: 1; }
        .summary-box .value { font-size: 22px; font-weight: bold; color: #2e59d9; }
        .summary-box .label { font-size: 9px; color: #888; margin-top: 3px; }
        .section-grid { display: flex; gap: 12px; margin: 10px 0; flex-wrap: wrap; }
        .section-grid .section { flex: 1; min-width: 200px; background: #fafbfc; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; }
        .section-grid .section h3 { margin-top: 0; color: #2e59d9; }
        .insight-box { background: #f0f4ff; border-left: 4px solid #2e59d9; padding: 10px 15px; margin: 8px 0; border-radius: 0 6px 6px 0; }
        .insight-box .type { font-weight: bold; color: #2e59d9; font-size: 10px; }
        .report-footer { margin-top: 30px; padding-top: 10px; border-top: 2px solid #e0e0e0; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="report-header">
        <div>
            <h1>Executive Summary Report</h1>
            <div class="subtitle">{{ $villageName ?? 'All Villages' }} — Comprehensive Overview</div>
        </div>
        <div class="logo-section">
            <strong>Multi-Village Management System</strong>
            <small>GIS-Enabled Analytics Platform</small>
        </div>
    </div>

    <div class="report-meta">
        <span><strong>Village:</strong> {{ $villageName ?? 'All Villages' }}</span>
        <span><strong>Generated:</strong> {{ $generatedAt ?? now()->format('F d, Y h:i A') }}</span>
        <span><strong>By:</strong> {{ $generatedBy ?? 'System' }}</span>
        <span><strong>Report Type:</strong> Executive Summary</span>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div class="value">{{ number_format($population['total'] ?? 0) }}</div>
            <div class="label">Total Residents</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($household['total'] ?? 0) }}</div>
            <div class="label">Total Households</div>
        </div>
        <div class="summary-box">
            <div class="value">₱{{ number_format($payment['total_collected'] ?? 0, 2) }}</div>
            <div class="label">Total Collected</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ number_format($participation['total_events'] ?? 0) }}</div>
            <div class="label">Total Events</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ ($govAssistance['coverage_rate'] ?? 0) }}%</div>
            <div class="label">Gov. Assistance Coverage</div>
        </div>
    </div>

    <h2>Population Summary</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Total Residents</td><td>{{ number_format($population['total'] ?? 0) }}</td></tr>
        <tr><td>Male</td><td>{{ number_format($population['male_count'] ?? 0) }}</td></tr>
        <tr><td>Female</td><td>{{ number_format($population['female_count'] ?? 0) }}</td></tr>
    </table>

    <h2>Household Summary</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Total Households</td><td>{{ number_format($household['total'] ?? 0) }}</td></tr>
        <tr><td>Average Size</td><td>{{ $household['average_size'] ?? 0 }}</td></tr>
    </table>

    <h2>Payment Summary</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        @foreach($payment as $key => $value)
            @if(!is_array($value))
            <tr><td>{{ ucwords(str_replace('_', ' ', $key)) }}</td><td>{{ is_numeric($value) && !str_contains($key, 'rate') ? '₱' . number_format($value, 2) : $value }}</td></tr>
            @endif
        @endforeach
    </table>

    <h2>Government Assistance</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        @foreach($govAssistance as $key => $value)
            @if(!is_array($value))
            <tr><td>{{ ucwords(str_replace('_', ' ', $key)) }}</td><td>{{ $value }}</td></tr>
            @endif
        @endforeach
    </table>

    <h2>Top Occupations</h2>
    @if(!empty($topOccupations))
    <table>
        <tr><th>#</th><th>Occupation</th><th>Count</th></tr>
        @foreach($topOccupations as $i => $occ)
        <tr><td>{{ $i + 1 }}</td><td>{{ $occ['occupation'] ?? $occ }}</td><td>{{ $occ['count'] ?? '' }}</td></tr>
        @endforeach
    </table>
    @endif

    <h2>Top Skills</h2>
    @if(!empty($topSkills))
    <table>
        <tr><th>#</th><th>Skill</th><th>Count</th></tr>
        @foreach($topSkills as $i => $skill)
        <tr><td>{{ $i + 1 }}</td><td>{{ $skill['skill'] ?? $skill }}</td><td>{{ $skill['count'] ?? '' }}</td></tr>
        @endforeach
    </table>
    @endif

    @if(!empty($villageComparison))
    <h2>Village Rankings</h2>
    <table>
        <tr><th>#</th><th>Village</th><th>Population</th><th>Households</th></tr>
        @foreach($villageComparison as $i => $v)
        <tr><td>{{ $i + 1 }}</td><td>{{ $v['village_name'] ?? '' }}</td><td>{{ $v['total'] ?? $v['population'] ?? 0 }}</td><td>{{ $v['households'] ?? $v['total_households'] ?? 0 }}</td></tr>
        @endforeach
    </table>
    @endif

    @if(!empty($insights))
    <h2>Executive Insights</h2>
    @foreach($insights as $insight)
    <div class="insight-box">
        <div class="type">{{ ucfirst($insight['type'] ?? 'Insight') }}</div>
        <div>{{ $insight['message'] ?? $insight }}</div>
    </div>
    @endforeach
    @endif

    <div class="report-footer">
        Multi-Village Management System · GIS-Enabled Multi-Village Management ·
        Generated {{ $generatedAt ?? now()->format('F d, Y h:i A') }} · Generated By: {{ $generatedBy ?? 'System' }}
    </div>
</body>
</html>
