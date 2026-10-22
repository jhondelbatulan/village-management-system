@extends('layouts.app')

@section('title', 'Household Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Household Management</h1>
        <p class="page-subtitle text-muted">Manage household registrations and records</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('households.export') }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Export
        </a>
        @if(auth()->user()->role?->role_slug === 'secretary')
            <a href="{{ route('households.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Create Household
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('households.index') }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="HH#, Head name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Village</label>
                    <select name="village_id" class="form-select">
                        <option value="">All Villages</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                                {{ $village->village_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Block</label>
                    <select name="block_number" class="form-select">
                        <option value="">All</option>
                        @foreach($blockNumbers as $block)
                            <option value="{{ $block }}" {{ request('block_number') == $block ? 'selected' : '' }}>{{ $block }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Lot</label>
                    <select name="lot_number" class="form-select">
                        <option value="">All</option>
                        @foreach($lotNumbers as $lot)
                            <option value="{{ $lot }}" {{ request('lot_number') == $lot ? 'selected' : '' }}>{{ $lot }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="relocated" {{ request('status') == 'relocated' ? 'selected' : '' }}>Relocated</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Approval</label>
                    <select name="approval_status" class="form-select">
                        <option value="">All</option>
                        <option value="draft" {{ request('approval_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="" {{ request('sort') == '' ? 'selected' : '' }}>Block / Lot</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A – Z (Head Name)</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z – A (Head Name)</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                    @if(request()->hasAny(['search', 'village_id', 'block_number', 'lot_number', 'status', 'approval_status', 'sort']))
                        <span class="text-muted small align-self-center ms-1">
                            <i class="bi bi-info-circle me-1"></i>{{ $households->total() }} result(s) found
                        </span>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Household #</th>
                        <th>Block / Lot</th>
                        <th>Village</th>
                        <th>Head</th>
                        <th>Purok</th>
                        <th class="text-center">Members</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Payment Behavior</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($households as $household)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $household->household_number }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">Blk {{ $household->block_number ?? '—' }} Lot {{ $household->lot_number ?? '—' }}</span>
                            </td>
                            <td>{{ $household->village?->village_name ?? '—' }}</td>
                            <td>{{ $household->household_head_name ?? '—' }}</td>
                            <td>{{ $household->purok ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill">
                                    {{ $household->member_count }}
                                </span>
                            </td>
                            <td>
                                @if($household->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($household->status === 'inactive')
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @else
                                    <span class="badge bg-secondary">Relocated</span>
                                @endif
                            </td>
                            <td>
                                @if($household->approval_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($household->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($household->approval_status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pb = $household->payment_behavior;
                                    $pbColor = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$pb] ?? 'secondary';
                                    $pbIcon = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_ICONS[$pb] ?? 'bi-question-circle';
                                    $pbLabel = \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$pb] ?? 'N/A';
                                    $pbScore = $household->payment_reliability_score;
                                @endphp
                                @if($pb && $household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled)
                                <div class="d-flex align-items-center">
                                    <i class="bi {{ $pbIcon }} text-{{ $pbColor }} me-1"></i>
                                    <div>
                                        <span class="badge bg-{{ $pbColor }}">{{ $pbLabel }}</span>
                                        <div class="small text-muted">Score: {{ $pbScore }}/100</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('households.show', $household) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->role?->role_slug === 'secretary')
                                        @if($household->approval_status === 'draft' || $household->approval_status === 'rejected')
                                            <a href="{{ route('households.edit', $household) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if($household->approval_status === 'active')
                                            <form action="{{ route('households.destroy', $household) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this household? All of its residents will be archived as well.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    @if(auth()->user()->role?->role_slug === 'super_admin')
                                        <form action="{{ route('households.force-delete', $household) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete household {{ $household->household_number }} and all of its residents? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Permanently">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-house-door fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No households found.</p>
                                @if(auth()->user()->role?->role_slug === 'secretary')
                                    <a href="{{ route('households.create') }}" class="btn btn-primary btn-sm mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Register First Household
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($households->hasPages())
        <div class="card-footer bg-white">
            {{ $households->withQueryString()->links() }}
        </div>
    @endif
</div>

@if(isset($archivedHouseholds) && $archivedHouseholds->count() > 0)
    <div class="card shadow-sm mt-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-archive me-2"></i> Archived Households ({{ $archivedHouseholds->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Household #</th>
                            <th>Village</th>
                            <th>Head</th>
                            <th>Purok</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedHouseholds as $archived)
                            <tr class="table-light">
                                <td class="fw-semibold">{{ $archived->household_number }}</td>
                                <td>{{ $archived->village?->village_name ?? '—' }}</td>
                                <td>{{ $archived->household_head_name ?? '—' }}</td>
                                <td>{{ $archived->purok ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-secondary">Archived</span>
                                </td>
                                <td class="text-end">
                                    @if(auth()->user()->role?->role_slug === 'secretary')
                                        <form action="{{ route('households.restore', $archived) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->role?->role_slug === 'super_admin')
                                        <form action="{{ route('households.force-delete', $archived) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete household {{ $archived->household_number }} and all of its residents? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                <i class="bi bi-trash me-1"></i> Delete Permanently
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($archivedHouseholds->hasPages())
            <div class="card-footer bg-white">
                {{ $archivedHouseholds->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
