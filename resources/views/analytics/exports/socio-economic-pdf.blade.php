<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Socio-Economic Analytics Report</title>
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
    <h1>Socio-Economic Analytics Report</h1>
    <p class="date">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>

    <h2>Executive Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Population</div>
            <div class="value">{{ number_format($summary['total_population'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Households</div>
            <div class="value">{{ number_format($summary['total_households'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Avg Household Size</div>
            <div class="value">{{ number_format($summary['avg_household_size'] ?? 0, 1) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Villages</div>
            <div class="value">{{ number_format($summary['total_villages'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Poverty Rate</div>
            <div class="value">{{ number_format($summary['poverty_rate'] ?? 0, 1) }}%</div>
        </div>
        <div class="summary-item">
            <div class="label">Literacy Rate</div>
            <div class="value">{{ number_format($summary['literacy_rate'] ?? 0, 1) }}%</div>
        </div>
    </div>

    <h2>Government Programs</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Program</th>
                <th>Enrolled</th>
                <th>Beneficiaries</th>
                <th>Coverage (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($govtStats as $index => $program)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $program['name'] ?? $program['program'] ?? 'N/A' }}</td>
                    <td>{{ number_format($program['enrolled'] ?? 0) }}</td>
                    <td>{{ number_format($program['beneficiaries'] ?? 0) }}</td>
                    <td>{{ number_format($program['coverage'] ?? 0, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No government program data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Special Population</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Count</th>
                <th>% of Total</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($specialPop as $index => $pop)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pop['name'] ?? $pop['category'] ?? 'N/A' }}</td>
                    <td>{{ number_format($pop['count'] ?? 0) }}</td>
                    <td>{{ number_format($pop['percentage'] ?? 0, 1) }}%</td>
                    <td>{{ $pop['notes'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No special population data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Income Statistics</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Income Bracket</th>
                <th>Households</th>
                <th>Population</th>
                <th>Share (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomeStats as $index => $income)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $income['bracket'] ?? $income['name'] ?? 'N/A' }}</td>
                    <td>{{ number_format($income['households'] ?? 0) }}</td>
                    <td>{{ number_format($income['population'] ?? 0) }}</td>
                    <td>{{ number_format($income['percentage'] ?? 0, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No income statistics data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>