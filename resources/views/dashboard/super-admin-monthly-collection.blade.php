@extends('layouts.app')

@section('title', 'Monthly Collection Breakdown')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">Monthly Collection</h1>
        <p class="text-muted mb-0">Per-village collections for {{ now()->format('F Y') }} · Updated {{ now()->format('F d, Y') }}</p>
    </div>
    <a href="{{ route('dashboard.super_admin') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4 col-md-6">
        <x-analytics.summary-card
            title="Collected This Month"
            :value="'₱' . number_format($system['this_month_collected'] ?? 0, 2)"
            icon="bi-cash-stack"
            color="info"
            subtitle="Across all villages"
            :change="$system['change'] ?? null"
            :large="true"
        />
    </div>
    <div class="col-xl-4 col-md-6">
        <x-analytics.summary-card
            title="Collected Last Month"
            :value="'₱' . number_format($system['previous_month_collected'] ?? 0, 2)"
            icon="bi-clock-history"
            color="secondary"
            subtitle="Same period last month"
            :large="true"
        />
    </div>
    <div class="col-xl-4 col-md-6">
        <x-analytics.summary-card
            title="All-Time Collected"
            :value="'₱' . number_format($system['total_collected'] ?? 0, 2)"
            icon="bi-safe"
            color="primary"
            subtitle="Since inception"
            :large="true"
        />
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart me-2 text-info"></i>Collections by Village</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-end">This Month</th>
                        <th class="text-end">Last Month</th>
                        <th class="text-end">Change</th>
                        <th class="text-end">All-Time Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row['village']->village_name }}</td>
                        <td class="text-end">₱{{ number_format($row['this_month'], 2) }}</td>
                        <td class="text-end">₱{{ number_format($row['previous_month'], 2) }}</td>
                        <td class="text-end">
                            @if($row['change'] > 0)
                                <span class="text-success fw-semibold">+{{ $row['change'] }}%</span>
                            @elseif($row['change'] < 0)
                                <span class="text-danger fw-semibold">{{ $row['change'] }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">₱{{ number_format($row['total_collected'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No active villages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
