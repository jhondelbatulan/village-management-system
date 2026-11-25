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
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="HH#, Head name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-8 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-toggle="collapse" data-bs-target="#householdFilters"
                            aria-expanded="{{ request()->hasAny(['village_id', 'block_number', 'lot_number', 'status', 'sort']) ? 'true' : 'false' }}">
                        <i class="bi bi-sliders me-1"></i> More filters
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                    @if(request()->hasAny(['search', 'village_id', 'block_number', 'lot_number', 'status', 'sort']))
                        <span class="text-muted small align-self-center ms-1">
                            <i class="bi bi-info-circle me-1"></i>{{ $households->total() }} result(s) found
                        </span>
                    @endif
                </div>
            </div>
            <div class="collapse mt-3 {{ request()->hasAny(['village_id', 'block_number', 'lot_number', 'status', 'sort']) ? 'show' : '' }}" id="householdFilters">
            <div class="row g-3">
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
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="" {{ request('sort') == '' ? 'selected' : '' }}>Block / Lot</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A – Z (Head Name)</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z – A (Head Name)</option>
                    </select>
                </div>
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
                        <th class="text-center">Members</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($households as $household)
                        @php
                            $pb = $household->payment_behavior;
                            $pbEnabled = (bool) ($household->village && $household->village->paymentConfiguration && $household->village->paymentConfiguration->payment_enabled);
                            $householdData = [
                                'number' => $household->household_number,
                                'block' => $household->block_number,
                                'lot' => $household->lot_number,
                                'village' => $household->village?->village_name,
                                'head' => $household->household_head_name,
                                'purok' => $household->purok,
                                'members' => $household->member_count,
                                'status' => $household->status,
                                'behavior' => ($pb && $pbEnabled) ? (\App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$pb] ?? 'N/A') : null,
                                'behavior_color' => ($pb && $pbEnabled) ? (\App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_COLORS[$pb] ?? 'secondary') : null,
                                'behavior_score' => ($pb && $pbEnabled) ? $household->payment_reliability_score : null,
                                'show_url' => route('households.show', $household),
                            ];
                        @endphp
                        <tr data-household="{{ json_encode($householdData) }}">
                            <td>
                                <span class="fw-semibold">{{ $household->household_number }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">Blk {{ $household->block_number ?? '—' }} Lot {{ $household->lot_number ?? '—' }}</span>
                            </td>
                            <td>{{ $household->village?->village_name ?? '—' }}</td>
                            <td>{{ $household->household_head_name ?? '—' }}</td>
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
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="View"
                                            data-bs-toggle="modal" data-bs-target="#householdViewModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(auth()->user()->role?->role_slug === 'secretary')
                                        <a href="{{ route('households.edit', $household) }}" class="btn btn-outline-warning" title="Edit household information">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($household->status === 'active')
                                            <form action="{{ route('households.destroy', $household) }}" method="POST" class="d-inline"
                                                  data-password-confirm
                                                  data-password-confirm-message="Enter your password to submit this delete request. The household will only be archived after Admin and Super Admin approval."
                                                  data-confirm="Submit a delete request for this household? All of its residents will be archived after Admin and Super Admin approval.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Request delete approval">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    @if(auth()->user()->role?->role_slug === 'super_admin')
                                        <form action="{{ route('households.force-delete', $household) }}" method="POST" class="d-inline" data-password-confirm data-password-confirm-message="Enter your super admin password to permanently delete this household." data-confirm="Permanently delete household {{ $household->household_number }} and all of its residents? This cannot be undone.">
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
                            <td colspan="7" class="text-center py-5">
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
                                        <form action="{{ route('households.restore', $archived) }}" method="POST" class="d-inline" data-confirm="Restore this archived household?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->role?->role_slug === 'super_admin')
                                        <form action="{{ route('households.force-delete', $archived) }}" method="POST" class="d-inline" data-password-confirm data-password-confirm-message="Enter your super admin password to permanently delete this household." data-confirm="Permanently delete household {{ $archived->household_number }} and all of its residents? This cannot be undone.">
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

{{-- Quick household info modal --}}
<div class="modal fade" id="householdViewModal" tabindex="-1" aria-labelledby="householdViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="householdViewLabel">Household Details</h6>
                    <small class="text-muted" id="hhViewNumber">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge" id="hhViewStatus">—</span>
                    <span class="badge bg-light text-dark" id="hhViewVillage">—</span>
                </div>

                <div class="row g-2 small mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Head of Household</div>
                            <div class="fw-bold" id="hhViewHead">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="text-muted">Members</div>
                            <div class="fw-bold" id="hhViewMembers">—</div>
                        </div>
                    </div>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Block / Lot</dt>
                    <dd class="col-sm-8 fw-semibold" id="hhViewBlock">—</dd>
                    <dt class="col-sm-4 text-muted">Purok</dt>
                    <dd class="col-sm-8 fw-semibold" id="hhViewPurok">—</dd>
                    <dt class="col-sm-4 text-muted">Payment Behavior</dt>
                    <dd class="col-sm-8" id="hhViewBehavior">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="hhViewFull" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open full page
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = (value == null || value === '') ? '—' : value;
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-bs-target="#householdViewModal"]');
        if (!trigger) return;

        var row = trigger.closest('tr[data-household]');
        if (!row) return;

        var d;
        try { d = JSON.parse(row.getAttribute('data-household')); }
        catch (error) { console.error('Household row data could not be parsed:', error); return; }

        setText('hhViewNumber', 'Household #' + (d.number || ''));
        setText('hhViewHead', d.head);
        setText('hhViewMembers', d.members != null ? Number(d.members).toLocaleString() : '');
        setText('hhViewVillage', d.village);
        setText('hhViewBlock', (d.block || d.lot) ? 'Block ' + (d.block || '—') + ' / Lot ' + (d.lot || '—') : '');
        setText('hhViewPurok', d.purok);

        var status = document.getElementById('hhViewStatus');
        if (status) {
            status.textContent = d.status === 'active' ? 'Active' : (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : '—');
            status.className = 'badge ' + (d.status === 'active' ? 'bg-success' : 'bg-secondary');
        }

        var behavior = document.getElementById('hhViewBehavior');
        if (behavior) {
            behavior.innerHTML = '';
            if (d.behavior) {
                var badge = document.createElement('span');
                badge.className = 'badge bg-' + (d.behavior_color || 'secondary') + ' me-1';
                badge.textContent = d.behavior;
                behavior.appendChild(badge);
                if (d.behavior_score != null) {
                    var score = document.createElement('small');
                    score.className = 'text-muted';
                    score.textContent = 'Score: ' + d.behavior_score + '/100';
                    behavior.appendChild(score);
                }
            } else {
                behavior.textContent = '—';
            }
        }

        var full = document.getElementById('hhViewFull');
        if (full) full.href = d.show_url || '#';
    });
});
</script>
@endpush
