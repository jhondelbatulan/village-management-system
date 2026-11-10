@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800">Welcome, {{ $user->name ?? 'Resident' }}</h1>
        <p class="text-muted mb-0">{{ $village->village_name ?? 'Your Village' }} · {{ now()->format('F d, Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Reports
        </a>
        <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-megaphone me-1"></i>Announcements
        </a>
    </div>
</div>

@include('partials.village-photos-slideshow')

<div class="row g-4 mb-4">
    <div class="col-xl-6 col-md-6">
        <x-analytics.summary-card
            title="Outstanding Balance"
            :value="'₱'.number_format($balance['outstanding'] ?? 0, 2)"
            icon="bi-wallet2"
            color="danger"
            subtitle="Unpaid amount"
            :large="true"
        />
    </div>
    <div class="col-xl-6 col-md-6">
        <x-analytics.summary-card
            title="Total Paid"
            :value="'₱'.number_format($balance['paid'] ?? 0, 2)"
            icon="bi-check-circle"
            color="success"
            subtitle="All time payments"
            :large="true"
        />
    </div>
</div>

@if($dashboardWidgets['announcements'] ?? true || $dashboardWidgets['calendar'] ?? true)
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Recent Announcements"
            icon="bi-megaphone"
            color="primary"
            valueLabel="Date"
            :items="$recentAnnouncements->map(fn($a) => ['name' => $a->title, 'value' => $a->created_at->format('M d'), 'url' => route('announcements.show', $a)])->toArray()"
            :maxItems="5"
        />
    </div>
    <div class="col-lg-6">
        <x-analytics.ranking-card
            title="Upcoming Events"
            icon="bi-calendar-event"
            color="success"
            valueLabel="Date"
            :items="$upcomingEvents->map(fn($e) => ['name' => $e->title, 'value' => \Carbon\Carbon::parse($e->event_date)->format('M d'), 'url' => route('events.show', $e)])->toArray()"
            :maxItems="5"
        />
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-house-door me-2"></i>My Household
                </h6>
                    <a href="{{ route('households.my-household') }}" class="btn btn-sm btn-outline-primary">View</a>
            </div>
            <div class="card-body">
                @if($household)
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-primary-soft rounded p-3 text-center">
                                <div class="small text-muted">Block / Lot</div>
                                <div class="fw-bold">{{ $household->block_number }} / {{ $household->lot_number }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-success-soft rounded p-3 text-center">
                                <div class="small text-muted">Family Count</div>
                                <div class="fw-bold text-capitalize">{{ $household->household_type ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="small text-muted mb-1">Household Members</div>
                                <div class="fw-bold">{{ $household->member_count }} residents</div>
                                <small class="text-muted">Head: {{ $household->household_head_name ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-house-add fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No household assigned yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-cash me-2"></i>Recent Payments
                </h6>
                @if(isset($household) && $household)
                    <a href="{{ route('household-payments.show', $household) }}" class="btn btn-sm btn-outline-success">View All</a>
                @endif
            </div>
            <div class="card-body">
                @if(isset($household) && $household)
                    @php
                        $recentPayments = \App\Models\HouseholdPayment::with('paymentType')
                            ->where('household_id', $household->id)
                            ->latest()->take(5)->get();
                    @endphp
                    @if($recentPayments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPayments as $payment)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <div>
                                        <div class="small fw-semibold">{{ $payment->paymentType?->payment_name ?? 'Payment' }}</div>
                                        <small class="text-muted">{{ $payment->payment_date->format('M d, Y') }}</small>
                                    </div>
                                    <span class="fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-inbox fs-1 text-gray-300"></i>
                            <p class="text-muted small mt-2 mb-0">No payments recorded</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-inbox fs-1 text-gray-300"></i>
                        <p class="text-muted small mt-2 mb-0">No household payments</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
