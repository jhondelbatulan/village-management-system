<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle ?? 'Report' }} — Print View</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; background: white; padding: 20px; }
        .no-print { margin-bottom: 20px; padding: 10px; background: #f0f0f0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-primary { background: #2e59d9; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }
        .report-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2e59d9; }
        .report-header h1 { font-size: 22px; color: #2e59d9; margin-bottom: 5px; }
        .report-header .subtitle { font-size: 12px; color: #666; }
        .report-header .logo-section { text-align: right; }
        .report-header .logo-section strong { font-size: 14px; color: #2e59d9; display: block; }
        .report-header .logo-section small { color: #888; font-size: 10px; }
        .report-meta { background: #f8f9fc; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; display: flex; gap: 25px; font-size: 11px; color: #555; flex-wrap: wrap; }
        .report-meta strong { color: #333; }
        h2 { font-size: 15px; color: #2e59d9; margin: 20px 0 8px 0; padding-bottom: 4px; border-bottom: 2px solid #2e59d9; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 15px 0; font-size: 11px; }
        th { background-color: #2e59d9; color: white; padding: 8px 10px; text-align: left; font-weight: 600; }
        td { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background-color: #f8f9fc; }
        .summary-grid { display: flex; gap: 12px; margin: 10px 0 15px 0; flex-wrap: wrap; }
        .summary-box { background: #f8f9fc; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px 15px; min-width: 130px; text-align: center; flex: 1; }
        .summary-box .value { font-size: 22px; font-weight: bold; color: #2e59d9; }
        .summary-box .label { font-size: 10px; color: #888; margin-top: 2px; }
        .report-footer { margin-top: 30px; padding-top: 10px; border-top: 2px solid #e0e0e0; text-align: center; font-size: 10px; color: #999; }
        .no-data { text-align: center; padding: 40px; color: #999; font-style: italic; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .report-header { border-bottom-color: #000; }
            th { background-color: #333 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>Print Preview</strong>
            <span class="text-muted"> — {{ $reportTitle ?? 'Report' }}</span>
        </div>
        <div>
            <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
            <button class="btn btn-secondary" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="report-header">
        <div>
            <h1>{{ $reportTitle ?? 'Report' }}</h1>
            <div class="subtitle">{{ $villageName ?? 'All Villages' }}</div>
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
    </div>

    @if(isset($total))
    <div class="summary-grid">
        <div class="summary-box">
            <div class="value">{{ number_format($total) }}</div>
            <div class="label">Total Records</div>
        </div>
    </div>
    @endif

    @if(isset($summary) && is_array($summary) && !empty($summary))
    <h2>Summary</h2>
    <div class="summary-grid">
        @foreach(array_slice($summary, 0, 8) as $key => $value)
            @if(!is_array($value))
            <div class="summary-box">
                <div class="value">{{ is_numeric($value) ? number_format($value, is_int($value) ? 0 : 1) : $value }}</div>
                <div class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    @if(isset($rows) && is_array($rows) && count($rows) > 0)
    <h2>Data ({{ count($rows) }} records)</h2>
    <table>
        <thead>
            <tr>
                @foreach(array_keys($rows[0]) as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No data available for this report.</div>
    @endif

    @if(isset($insights) && is_array($insights) && count($insights) > 0)
    <h2>Insights</h2>
    <ul style="margin-left: 20px;">
        @foreach($insights as $insight)
        <li style="margin-bottom: 4px;"><strong>{{ ucfirst($insight['type'] ?? 'Info') }}:</strong> {{ $insight['message'] ?? $insight }}</li>
        @endforeach
    </ul>
    @endif

    <div class="report-footer">
        Multi-Village Management System · GIS-Enabled Multi-Village Management ·
        Generated {{ $generatedAt ?? now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
