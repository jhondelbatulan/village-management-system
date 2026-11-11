@extends('layouts.app')

@section('title', $village->village_name . ' - Socio-Economic Dashboard')

@section('styles')
<style>
    .stat-card { border-left: 4px solid; border-radius: 8px; }
    .stat-card.blue { border-color: #4e73df; }
    .stat-card.green { border-color: #1cc88a; }
    .stat-card.cyan { border-color: #36b9cc; }
    .stat-card.yellow { border-color: #f6c23e; }
    .stat-card.red { border-color: #e74a3b; }
    .stat-card.gray { border-color: #858796; }
    .chart-container { position: relative; height: 400px; }
    .insight-item { padding: 10px 15px; border-left: 4px solid; margin-bottom: 8px; border-radius: 4px; background: #f8f9fc; }
    .insight-item.success { border-color: #1cc88a; }
    .insight-item.warning { border-color: #f6c23e; }
    .insight-item.danger { border-color: #e74a3b; }
    .insight-item.info { border-color: #4e73df; }
    .table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('analytics.socio-economic.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h1 class="h3 mb-0 text-gray-800">{{ $village->village_name }}</h1>
        <small class="text-muted">{{ $village->village_code }}</small>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('analytics.socio-economic.export', ['format' => 'csv', 'village' => $village->id]) }}"><i class="fas fa-file-csv me-2"></i>CSV</a>
            <a class="dropdown-item" href="{{ route('analytics.socio-economic.export', ['format' => 'pdf', 'village' => $village->id]) }}"><i class="fas fa-file-pdf me-2"></i>PDF</a>
        </div>
    </div>
</div>

{{-- Executive Summary --}}
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card blue shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Residents</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['total_residents']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card green shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Occupation Coverage</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['occupation_coverage'], 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card cyan shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Skill Coverage</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['skill_coverage'], 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card yellow shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Income</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($incomeStats['average'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card red shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Membership Rate</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($govtStats['membership_rate'], 1) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card gray shadow-sm h-100">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Participation Rate</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary['participation_rate'], 1) }}%</div>
            </div>
        </div>
    </div>
</div>

{{-- Population Pyramid --}}
<div class="row mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Population Pyramid</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="pyramidChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Population Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td class="font-weight-bold">Total Population</td><td class="text-end">{{ number_format($pyramidStats['total']) }}</td></tr>
                    <tr><td class="font-weight-bold">Male</td><td class="text-end">{{ number_format($pyramidStats['total_male']) }}</td></tr>
                    <tr><td class="font-weight-bold">Female</td><td class="text-end">{{ number_format($pyramidStats['total_female']) }}</td></tr>
                    <tr><td class="font-weight-bold">Average Age</td><td class="text-end">{{ number_format($pyramidStats['average_age'], 1) }}</td></tr>
                    <tr><td class="font-weight-bold">Median Age</td><td class="text-end">{{ number_format($pyramidStats['median_age'], 1) }}</td></tr>
                    <tr><td class="font-weight-bold">Youngest</td><td class="text-end">{{ $pyramidStats['youngest'] }}</td></tr>
                    <tr><td class="font-weight-bold">Oldest</td><td class="text-end">{{ $pyramidStats['oldest'] }}</td></tr>
                </table>
                <hr>
                <h6 class="font-weight-bold text-primary mb-2">Age Distribution</h6>
                <div style="max-height:200px;overflow-y:auto;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Age Group</th><th>Male</th><th>Female</th><th>Total</th><th>%</th></tr></thead>
                        <tbody>
                            @foreach($pyramidAgeDist as $label => $data)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $data['male'] }}</td>
                                <td>{{ $data['female'] }}</td>
                                <td>{{ $data['total'] }}</td>
                                <td>{{ number_format($data['percentage'], 1) }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Occupation Distribution --}}
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Occupation Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:350px;">
                    <canvas id="occupationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Occupation by Gender</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:350px;">
                    <canvas id="occupationGenderChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Income Analysis --}}
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Income Range Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px;">
                    <canvas id="incomeRangeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Income by Gender</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px;">
                    <canvas id="incomeGenderChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Income Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Count</div>
                        <div class="font-weight-bold text-gray-800">{{ number_format($incomeStats['count']) }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Average</div>
                        <div class="font-weight-bold text-gray-800">₱{{ number_format($incomeStats['average'], 2) }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Median</div>
                        <div class="font-weight-bold text-gray-800">₱{{ number_format($incomeStats['median'], 2) }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Minimum</div>
                        <div class="font-weight-bold text-gray-800">₱{{ number_format($incomeStats['min'], 2) }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Maximum</div>
                        <div class="font-weight-bold text-gray-800">₱{{ number_format($incomeStats['max'], 2) }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="text-xs font-weight-bold text-primary text-uppercase">Range</div>
                        <div class="font-weight-bold text-gray-800">₱{{ number_format($incomeStats['max'] - $incomeStats['min'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Skills Distribution --}}
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Skills Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:350px;">
                    <canvas id="skillChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Livelihood Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:350px;">
                    <canvas id="livelihoodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Government Programs --}}
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Government Programs</h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold text-gray-700 mb-2">Memberships</h6>
                <table class="table table-sm table-bordered mb-3">
                    <thead><tr><th>Program</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                        <tr><td>Pag-IBIG</td><td class="text-end">{{ number_format($govtStats['memberships']['pagibig']) }}</td></tr>
                        <tr><td>PhilHealth</td><td class="text-end">{{ number_format($govtStats['memberships']['philhealth']) }}</td></tr>
                        <tr><td>SSS</td><td class="text-end">{{ number_format($govtStats['memberships']['sss']) }}</td></tr>
                        <tr><td>GSIS</td><td class="text-end">{{ number_format($govtStats['memberships']['gsis']) }}</td></tr>
                    </tbody>
                </table>
                <h6 class="font-weight-bold text-gray-700 mb-2">Assistance Programs</h6>
                <table class="table table-sm table-bordered mb-0">
                    <thead><tr><th>Program</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                        <tr><td>4Ps</td><td class="text-end">{{ number_format($govtStats['assistance']['4ps']) }}</td></tr>
                        <tr><td>Senior Pension</td><td class="text-end">{{ number_format($govtStats['assistance']['senior_pension']) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Government Program Rates</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:350px;">
                    <canvas id="govtChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Special Population --}}
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Special Population Groups</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-primary">{{ number_format($specialPop['pwd']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">PWD</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-warning">{{ number_format($specialPop['solo_parent']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">Solo Parent</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-danger">{{ number_format($specialPop['senior_citizen']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">Senior Citizen</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-success">{{ number_format($specialPop['youth']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">Youth</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-info">{{ number_format($specialPop['student']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">Student</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold text-secondary">{{ number_format($specialPop['ofw']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">OFW</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <div class="h4 font-weight-bold" style="color:#36b9cc;">{{ number_format($specialPop['infant']) }}</div>
                            <div class="text-xs text-uppercase text-gray-500">Infant</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Certifications --}}
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top Certifications</h6>
            </div>
            <div class="card-body" style="max-height:300px;overflow-y:auto;">
                <table class="table table-sm table-bordered mb-0">
                    <thead><tr><th>Certification</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                        @foreach($topCerts as $cert => $count)
                        <tr>
                            <td>{{ $cert }}</td>
                            <td class="text-end">{{ number_format($count) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Community Participation</h6>
            </div>
            <div class="card-body" style="max-height:300px;overflow-y:auto;">
                <table class="table table-sm table-bordered mb-0">
                    <thead><tr><th>Activity</th><th class="text-end">Count</th></tr></thead>
                    <tbody>
                        @foreach($topActivities as $activity => $count)
                        <tr>
                            <td>{{ $activity }}</td>
                            <td class="text-end">{{ number_format($count) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Insights --}}
@if(!empty($insights))
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Analytics Insights</h6>
            </div>
            <div class="card-body">
                <x-analytics.quick-insights :insights="$insights" />
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9','#17a673','#2c9faf'];

// Population Pyramid
new Chart(document.getElementById('pyramidChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($pyramidData['labels']) !!},
        datasets: [
            { label: 'Male', data: {!! json_encode($pyramidData['male']) !!}, backgroundColor: '#4e73df' },
            { label: 'Female', data: {!! json_encode($pyramidData['female']) !!}, backgroundColor: '#e74a3b' }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: { x: { stacked: false } },
        plugins: { legend: { position: 'top' } }
    }
});

// Occupation Distribution
new Chart(document.getElementById('occupationChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($topOccupations)) !!},
        datasets: [{
            label: 'Count',
            data: {!! json_encode(array_values($topOccupations)) !!},
            backgroundColor: colors.slice(0, {!! count($topOccupations) !!})
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});

// Occupation by Gender
new Chart(document.getElementById('occupationGenderChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($occByGender)) !!},
        datasets: [
            { label: 'Male', data: {!! json_encode(array_map(fn($v) => $v['Male'] ?? 0, $occByGender)) !!}, backgroundColor: '#4e73df' },
            { label: 'Female', data: {!! json_encode(array_map(fn($v) => $v['Female'] ?? 0, $occByGender)) !!}, backgroundColor: '#e74a3b' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: true, beginAtZero: true }, y: { stacked: true } }
    }
});

// Income Range Distribution
new Chart(document.getElementById('incomeRangeChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($incomeRange)) !!},
        datasets: [{
            label: 'Count',
            data: {!! json_encode(array_map(fn($v) => $v['count'], $incomeRange)) !!},
            backgroundColor: colors.slice(0, {!! count($incomeRange) !!})
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Income by Gender
new Chart(document.getElementById('incomeGenderChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($incomeByGender)) !!},
        datasets: [{
            label: 'Avg Income',
            data: {!! json_encode(array_values($incomeByGender)) !!},
            backgroundColor: ['#4e73df', '#e74a3b']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Skills Distribution
new Chart(document.getElementById('skillChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($skillDist)) !!},
        datasets: [{
            label: 'Count',
            data: {!! json_encode(array_map(fn($v) => $v['count'], $skillDist)) !!},
            backgroundColor: colors.slice(0, {!! count($skillDist) !!})
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});

// Livelihood Distribution
new Chart(document.getElementById('livelihoodChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($livelihoodDist)) !!},
        datasets: [{
            data: {!! json_encode(array_map(fn($v) => $v['count'], $livelihoodDist)) !!},
            backgroundColor: colors.slice(0, {!! count($livelihoodDist) !!})
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right' } }
    }
});

// Government Programs Chart
new Chart(document.getElementById('govtChart'), {
    type: 'bar',
    data: {
        labels: ['Pag-IBIG', 'PhilHealth', 'SSS', 'GSIS', '4Ps', 'Senior Pension'],
        datasets: [{
            label: 'Count',
            data: [
                {{ $govtStats['memberships']['pagibig'] }},
                {{ $govtStats['memberships']['philhealth'] }},
                {{ $govtStats['memberships']['sss'] }},
                {{ $govtStats['memberships']['gsis'] }},
                {{ $govtStats['assistance']['4ps'] }},
                {{ $govtStats['assistance']['senior_pension'] }}
            ],
            backgroundColor: colors.slice(0, 6)
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endsection
