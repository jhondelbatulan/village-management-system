@extends('layouts.app')

@section('title', 'Population Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">
            <i class="bi bi-people me-2"></i>Population Analytics
        </h1>
        <p class="text-muted mb-0">Descriptive Statistical Analysis · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($villageId)
            <a href="{{ route('analytics.population.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>System View
            </a>
        @endif
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filters
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                <h6 class="dropdown-header">Filter Options</h6>
                <div class="mb-2">
                    <label class="form-label small">Village</label>
                    <select class="form-select form-select-sm" id="filterVillage">
                        <option value="">All Villages</option>
                        @foreach($villages as $v)
                            <option value="{{ $v->id }}" {{ ($villageId ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Year</label>
                    <select class="form-select form-select-sm" id="filterYear">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Gender</label>
                    <select class="form-select form-select-sm" id="filterGender">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-primary w-100 mt-2" onclick="applyFilters()">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Executive KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Total Population"
            :value="number_format($summary['total_population'] ?? 0)"
            icon="bi-people-fill"
            color="primary"
            :change="$summary['population_growth'] ?? null"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Approved Residents"
            :value="number_format($summary['approved'] ?? 0)"
            icon="bi-check-circle"
            color="success"
            subtitle="Verified records"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Pending Residents"
            :value="number_format($summary['pending'] ?? 0)"
            icon="bi-hourglass-split"
            color="warning"
            subtitle="Awaiting approval"
            :large="true"
        />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-analytics.summary-card
            title="Rejected Residents"
            :value="number_format($summary['rejected'] ?? 0)"
            icon="bi-x-circle"
            color="danger"
            subtitle="Rejected records"
            :large="true"
        />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Registered Today"
            :value="number_format($summary['registered_today'] ?? 0)"
            icon="bi-calendar-check"
            color="info"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="This Week"
            :value="number_format($summary['registered_this_week'] ?? 0)"
            icon="bi-calendar-week"
            color="primary"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="This Month"
            :value="number_format($summary['registered_this_month'] ?? 0)"
            icon="bi-calendar-month"
            color="success"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="This Year"
            :value="number_format($summary['registered_this_year'] ?? 0)"
            icon="bi-calendar"
            color="warning"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Avg Monthly"
            :value="number_format($summary['avg_monthly_registration'] ?? 0, 1)"
            icon="bi-graph-up"
            color="info"
            subtitle="per month"
        />
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <x-analytics.summary-card
            title="Avg Annual"
            :value="number_format($summary['avg_annual_registration'] ?? 0, 1)"
            icon="bi-graph-up-arrow"
            color="secondary"
            subtitle="per year"
        />
    </div>
</div>

{{-- Charts Row 1: Population by Village + Gender Distribution --}}
<div class="row g-4 mb-4">
    @if(!$villageId && count($villageComparison) > 0)
        <div class="col-lg-6">
            <x-analytics.chart-card
                title="Population by Village"
                icon="bi-bar-chart"
                color="primary"
                canvasId="villageComparisonChart"
                height="300px"
            />
        </div>
    @else
        <div class="col-lg-6">
            <x-analytics.chart-card
                title="Population Growth Trend"
                icon="bi-graph-up"
                color="primary"
                canvasId="growthTrendChart"
                height="300px"
            />
        </div>
    @endif
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Gender Distribution"
            icon="bi-gender-ambiguous"
            color="info"
            canvasId="genderChart"
            height="300px"
        />
    </div>
</div>

{{-- Charts Row 2: Age Distribution + Approval Status --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Age Distribution"
            icon="bi-bar-chart"
            color="success"
            canvasId="ageChart"
            height="300px"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Monthly Registration Trend"
            icon="bi-calendar-check"
            color="primary"
            canvasId="registrationTrendChart"
            height="300px"
        />
    </div>
</div>

{{-- Charts Row 3: Household Size + Occupation --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Household Size Distribution"
            icon="bi-house-door"
            color="warning"
            canvasId="householdSizeChart"
            height="300px"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Top Occupations"
            icon="bi-briefcase"
            color="info"
            canvasId="occupationChart"
            height="300px"
        />
    </div>
</div>

{{-- Frequency Distribution Tables --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-clipboard-data me-2"></i>Gender Frequency Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Gender</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($genderDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-clipboard-data me-2"></i>Age Group Frequency Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Age Group</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ageDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-warning">
                    <i class="bi bi-clipboard-data me-2"></i>Civil Status Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Civil Status</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($civilDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-info">
                    <i class="bi bi-clipboard-data me-2"></i>Education Level Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Education Level</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($educationDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-secondary">
                    <i class="bi bi-clipboard-data me-2"></i>Employment Status Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Employment Status</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employmentDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="bi bi-clipboard-data me-2"></i>Household Size Distribution
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Household Size</th>
                            <th class="small text-end">Frequency</th>
                            <th class="small text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($householdDist['data'] as $item)
                            <tr>
                                <td class="small fw-semibold">{{ $item['label'] }}</td>
                                <td class="small text-end">{{ number_format($item['count']) }}</td>
                                <td class="small text-end">{{ number_format($item['percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Mean / Average Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-primary fw-bold h4 mb-0">{{ number_format($householdDist['average_size'] ?? 0, 2) }}</div>
            <small class="text-muted">Avg Household Size</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-success fw-bold h4 mb-0">{{ number_format($averageAge ?? 0, 1) }}</div>
            <small class="text-muted">Average Age</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-info fw-bold h4 mb-0">{{ number_format($summary['avg_monthly_registration'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Monthly Registration</small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm text-center py-3">
            <div class="text-warning fw-bold h4 mb-0">{{ number_format($summary['avg_annual_registration'] ?? 0, 1) }}</div>
            <small class="text-muted">Avg Annual Registration</small>
        </div>
    </div>
</div>

{{-- Growth Analysis --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.chart-card
            title="Yearly Registration Trend"
            icon="bi-graph-up-arrow"
            color="success"
            canvasId="yearlyTrendChart"
            height="280px"
        />
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-speedometer2 me-2"></i>Growth Analysis
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Weekly</div>
                            <div class="fw-bold {{ ($growth['weekly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['weekly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['weekly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['weekly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Monthly</div>
                            <div class="fw-bold {{ ($growth['monthly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['monthly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['monthly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['monthly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Quarterly</div>
                            <div class="fw-bold text-primary">+{{ number_format($growth['quarterly']['current'] ?? 0) }}</div>
                            <small class="text-muted">This quarter</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="small text-muted">Yearly</div>
                            <div class="fw-bold {{ ($growth['yearly']['change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($growth['yearly']['change'] ?? 0) >= 0 ? '+' : '' }}{{ $growth['yearly']['change'] ?? 0 }}
                            </div>
                            <small class="text-muted">{{ number_format($growth['yearly']['growth_rate'] ?? 0, 1) }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Village Comparison (Super Admin only) --}}
@if(!$villageId && count($rankings) > 0)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Highest Population"
            icon="bi-trophy"
            color="warning"
            valueLabel="Residents"
            :items="collect($rankings['by_population'] ?? [])->map(fn($r) => ['name' => $r['village']->village_name ?? '', 'value' => number_format($r['total'])])->toArray()"
            :maxItems="10"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Fastest Growth"
            icon="bi-lightning"
            color="success"
            valueLabel="Growth Rate"
            :items="collect($rankings['by_growth'] ?? [])->map(fn($r) => ['name' => $r['village']->village_name ?? '', 'value' => number_format($r['growth_rate'], 1) . '%'])->toArray()"
            :maxItems="10"
        />
    </div>
</div>
@endif

{{-- Insights --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.quick-insights
            title="Population Insights"
            icon="bi-lightbulb"
            color="info"
            :insights="$insights"
        />
    </div>
    @if(!$villageId && count($villageComparison) > 0)
        <div class="col-lg-6">
            <x-analytics.chart-card
                title="Average Household Size by Village"
                icon="bi-house-door"
                color="warning"
                canvasId="householdSizeComparisonChart"
                height="280px"
            />
        </div>
    @else
        <div class="col-lg-6">
            <x-analytics.chart-card
                title="Occupation Distribution"
                icon="bi-briefcase"
                color="info"
                canvasId="occupationDistChart"
                height="280px"
            />
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        primary: '#0d6efd',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        secondary: '#6c757d',
        dark: '#212529',
        purple: '#6f42c1',
        pink: '#d63384',
        teal: '#20c997',
        orange: '#fd7e14',
        cyan: '#0dcaf0'
    };

    const palette = [colors.primary, colors.success, colors.danger, colors.warning, colors.info, colors.secondary, colors.purple, colors.pink, colors.teal, colors.orange];

    // Gender Distribution - Pie Chart
    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($genderDist['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($genderDist['values'] ?? []) !!},
                    backgroundColor: [colors.primary, colors.danger, colors.secondary],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Age Distribution - Bar Chart
    const ageCtx = document.getElementById('ageChart');
    if (ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($ageDist['labels'] ?? []) !!},
                datasets: [{
                    label: 'Residents',
                    data: {!! json_encode($ageDist['values'] ?? []) !!},
                    backgroundColor: palette.slice(0, {!! json_encode(count($ageDist['labels'] ?? [])) !!}),
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // Monthly Registration Trend - Line Chart
    const regTrendCtx = document.getElementById('registrationTrendChart');
    if (regTrendCtx) {
        const monthLabels = {!! json_encode(array_keys($monthlyTrend ?? [])) !!};
        const monthData = {!! json_encode(array_values($monthlyTrend ?? [])) !!};
        new Chart(regTrendCtx, {
            type: 'line',
            data: {
                labels: monthLabels.map(m => {
                    const [y, mo] = m.split('-');
                    return new Date(y, mo - 1).toLocaleString('default', { month: 'short', year: '2-digit' });
                }),
                datasets: [{
                    label: 'Registrations',
                    data: monthData,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // Growth Trend Chart (single village)
    const growthTrendCtx = document.getElementById('growthTrendChart');
    if (growthTrendCtx) {
        const gLabels = {!! json_encode(array_keys($monthlyTrend ?? [])) !!};
        const gData = {!! json_encode(array_values($monthlyTrend ?? [])) !!};
        new Chart(growthTrendCtx, {
            type: 'line',
            data: {
                labels: gLabels.map(m => {
                    const [y, mo] = m.split('-');
                    return new Date(y, mo - 1).toLocaleString('default', { month: 'short', year: '2-digit' });
                }),
                datasets: [{
                    label: 'Population Growth',
                    data: gData,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Household Size Distribution - Doughnut
    const hhCtx = document.getElementById('householdSizeChart');
    if (hhCtx) {
        new Chart(hhCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($householdDist['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($householdDist['values'] ?? []) !!},
                    backgroundColor: palette.slice(0, {!! json_encode(count($householdDist['labels'] ?? [])) !!}),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // Occupation Chart - Horizontal Bar
    const occCtx = document.getElementById('occupationChart');
    if (occCtx) {
        new Chart(occCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_slice($occupationDist['labels'] ?? [], 0, 10)) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode(array_slice($occupationDist['values'] ?? [], 0, 10)) !!},
                    backgroundColor: colors.info,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Village Comparison - Bar Chart
    const vcCtx = document.getElementById('villageComparisonChart');
    if (vcCtx) {
        new Chart(vcCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!},
                datasets: [
                    {
                        label: 'Population',
                        data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['total'])->toArray()) !!},
                        backgroundColor: colors.primary,
                        borderRadius: 4
                    },
                    {
                        label: 'Male',
                        data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['male'])->toArray()) !!},
                        backgroundColor: colors.info,
                        borderRadius: 4
                    },
                    {
                        label: 'Female',
                        data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['female'])->toArray()) !!},
                        backgroundColor: colors.danger,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Yearly Trend - Line
    const ytCtx = document.getElementById('yearlyTrendChart');
    if (ytCtx) {
        const yLabels = {!! json_encode(array_keys($yearlyTrend ?? [])) !!};
        const yData = {!! json_encode(array_values($yearlyTrend ?? [])) !!};
        new Chart(ytCtx, {
            type: 'line',
            data: {
                labels: yLabels,
                datasets: [{
                    label: 'Annual Registrations',
                    data: yData,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '15',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Household Size Comparison by Village
    const hhCompCtx = document.getElementById('householdSizeComparisonChart');
    if (hhCompCtx) {
        new Chart(hhCompCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['village']->village_name ?? '')->toArray()) !!},
                datasets: [{
                    label: 'Avg Household Size',
                    data: {!! json_encode(collect($villageComparison ?? [])->map(fn($v) => $v['avg_household_size'])->toArray()) !!},
                    backgroundColor: [colors.primary, colors.success, colors.warning, colors.info, colors.danger],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Occupation Distribution Chart
    const occDistCtx = document.getElementById('occupationDistChart');
    if (occDistCtx) {
        new Chart(occDistCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_slice($occupationDist['labels'] ?? [], 0, 8)) !!},
                datasets: [{
                    label: 'Residents',
                    data: {!! json_encode(array_slice($occupationDist['values'] ?? [], 0, 8)) !!},
                    backgroundColor: palette.slice(0, 8),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { maxRotation: 45, minRotation: 45 } }
                }
            }
        });
    }
});

function applyFilters() {
    const village = document.getElementById('filterVillage').value;
    if (village) {
        window.location.href = '{{ route("analytics.population.village", ":id") }}'.replace(':id', village);
    } else {
        window.location.href = '{{ route("analytics.population.index") }}';
    }
}
</script>
@endpush
