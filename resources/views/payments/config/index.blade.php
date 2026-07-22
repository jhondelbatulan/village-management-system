@extends('layouts.app')

@section('title', 'Village Payment Configuration')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Village Payment Configuration</h4>
            <p class="page-subtitle text-muted mb-0">Configure payment settings for each village.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Village</th>
                        <th class="text-center">Payment Module</th>
                        <th>Manager</th>
                        <th class="text-center">Penalty Rate</th>
                        <th class="text-center">Grace Period</th>
                        <th>Billing Prefix</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villages as $village)
                        @php
                            $setting = $settings->firstWhere('village_id', $village->id);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $village->village_name }}</div>
                                <small class="text-muted">{{ $village->village_code }}</small>
                            </td>
                            <td class="text-center">
                                @if($setting && $setting->payment_enabled)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($setting->payment_manager_role ?? '—') }}</td>
                            <td class="text-center">{{ $setting->penalty_rate ?? 0 }}%</td>
                            <td class="text-center">{{ $setting->grace_period_days ?? 0 }} days</td>
                            <td>{{ $setting->billing_prefix ?? 'BILL' }}</td>
                            <td class="text-end">
                                <a href="{{ route('payment-config.show', $village) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('payment-config.edit', $village) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($setting && $setting->payment_manager_role === 'super_admin')
                                <a href="{{ route('household-amounts.index', $village) }}" class="btn btn-sm btn-outline-success" title="Household Amounts">
                                    <i class="bi bi-house-door"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
