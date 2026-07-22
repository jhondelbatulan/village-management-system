<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Skills Analytics Report</title>
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
    <h1>Skills Analytics Report</h1>
    <p class="date">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>

    <h2>Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Skills</div>
            <div class="value">{{ number_format($summary['total_skills'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Skill Holders</div>
            <div class="value">{{ number_format($summary['total_holders'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Avg Holders / Skill</div>
            <div class="value">{{ number_format($summary['avg_holders_per_skill'] ?? 0, 1) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Most Common Skill</div>
            <div class="value">{{ $summary['most_common_skill'] ?? 'N/A' }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Categories</div>
            <div class="value">{{ number_format($summary['total_categories'] ?? 0) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Skill Coverage</div>
            <div class="value">{{ number_format($summary['skill_coverage'] ?? 0, 1) }}%</div>
        </div>
    </div>

    <h2>Skills Rankings</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Skill</th>
                <th>Category</th>
                <th>Holders</th>
                <th>Share (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rankings as $index => $rank)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $rank['name'] ?? $rank['skill'] ?? 'N/A' }}</td>
                    <td>{{ $rank['category'] ?? 'N/A' }}</td>
                    <td>{{ number_format($rank['count'] ?? $rank['holders'] ?? 0) }}</td>
                    <td>{{ number_format($rank['percentage'] ?? 0, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No ranking data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Category Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Skill Count</th>
                <th>Total Holders</th>
                <th>Avg / Skill</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventory as $index => $cat)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $cat['name'] ?? $cat['category'] ?? 'N/A' }}</td>
                    <td>{{ number_format($cat['skill_count'] ?? 0) }}</td>
                    <td>{{ number_format($cat['total_holders'] ?? 0) }}</td>
                    <td>{{ number_format($cat['avg_per_skill'] ?? 0, 1) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #718096;">No category inventory data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>