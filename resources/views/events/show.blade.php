@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">{{ $event->title }}</h1>
        <p class="page-subtitle text-muted">
            {{ $event->village?->village_name ?? 'All Villages' }} &middot;
            {{ $event->event_date->format('M d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if($event->isManagedBy(auth()->user()))
            <a href="{{ route('events.edit', $event) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endif
        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($event->status === 'upcoming')
                        <span class="badge bg-primary">Upcoming</span>
                    @elseif($event->status === 'ongoing')
                        <span class="badge bg-success">Ongoing</span>
                    @elseif($event->status === 'completed')
                        <span class="badge bg-secondary">Completed</span>
                    @elseif($event->status === 'cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @else
                        <span class="badge bg-warning text-dark">Draft</span>
                    @endif
                    <span class="badge bg-light text-dark">{{ $event->category }}</span>
                </div>

                @if($event->cover_image)
                    <div class="mb-3">
                        <img src="{{ $event->cover_image_url }}" alt="Cover" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                @endif

                <div class="event-body">
                    {!! nl2br(e($event->description)) !!}
                </div>

                @if($event->attachment)
                    <div class="mt-3">
                        <a href="{{ $event->attachment_url }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-paperclip me-1"></i> View Attachment
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if(auth()->user()->role?->role_slug === 'resident')
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Your Response</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('events.respond', $event) }}" method="POST">
                        @csrf
                        <div class="d-flex gap-2">
                            <button type="submit" name="response" value="going" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Going
                            </button>
                            <button type="submit" name="response" value="maybe" class="btn btn-warning">
                                <i class="bi bi-question-circle me-1"></i> Maybe
                            </button>
                            <button type="submit" name="response" value="not_going" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Not Going
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if(in_array($role, ['super_admin', 'admin', 'secretary']))
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-semibold">
                        Participations
                        @if($event->village_id === null && $participationVillageId !== null)
                            ({{ $event->participations->count() }} of {{ $totalParticipations }})
                        @else
                            ({{ $event->participations->count() }})
                        @endif
                    </h6>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        @if($event->village_id === null && $role === 'super_admin')
                            <form method="GET" action="{{ route('events.show', $event) }}" class="d-flex align-items-center gap-1">
                                <label class="form-label mb-0 small text-muted">Village</label>
                                <select name="village" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Villages</option>
                                    @foreach($villages as $v)
                                        <option value="{{ $v->id }}" {{ $participationVillageId == $v->id ? 'selected' : '' }}>{{ $v->village_name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @elseif($event->village_id === null && $participationVillage)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-geo-alt me-1"></i>{{ $participationVillage->village_name }}
                            </span>
                        @endif

                        <a href="{{ route('events.attendance-pdf', $event) }}" class="btn btn-sm btn-outline-secondary" title="Download attendance report">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </a>

                        @if($role === 'secretary')
                            <form action="{{ route('events.auto-generate', $event) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-magic me-1"></i> Auto-Generate from Responses
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addParticipationModal">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Block / Lot</th>
                                    <th>Household No.</th>
                                    @if($event->village_id === null)
                                        <th>Village</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Recorded By</th>
                                    @if($role === 'secretary')
                                        <th class="text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($event->participations as $p)
                                    <tr>
                                        <td>
                                            @if($p->household)
                                                Block {{ $p->household->block_number ?? 'N/A' }} - Lot {{ $p->household->lot_number ?? 'N/A' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $p->household?->household_number ?? '—' }}</td>
                                        @if($event->village_id === null)
                                            <td class="small">{{ $p->household?->village?->village_name ?? '—' }}</td>
                                        @endif
                                        <td>
                                            @if($p->status === 'present')
                                                <span class="badge bg-success">Present</span>
                                            @elseif($p->status === 'absent')
                                                <span class="badge bg-danger">Absent</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Excused</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $p->remarks ?? '—' }}</td>
                                        <td class="small">{{ $p->recorder?->full_name ?? '—' }}</td>
                                        @if($role === 'secretary')
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            title="Edit"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editParticipationModal"
                                                            data-action="{{ route('events.participations.update', [$event, $p]) }}"
                                                            data-status="{{ $p->status }}"
                                                            data-remarks="{{ $p->remarks ?? '' }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('events.participations.destroy', [$event, $p]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this participation?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">No participations recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Event Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Village</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->village?->village_name ?? 'All Villages' }}</dd>
                    <dt class="col-sm-5 text-muted">Category</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->category }}</dd>
                    <dt class="col-sm-5 text-muted">Date</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->event_date->format('M d, Y') }}</dd>
                    <dt class="col-sm-5 text-muted">Time</dt>
                    <dd class="col-sm-7 fw-semibold">
                        {{ $event->start_time?->format('h:i A') ?? '—' }}
                        @if($event->end_time) – {{ $event->end_time->format('h:i A') }} @endif
                    </dd>
                    <dt class="col-sm-5 text-muted">Venue</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->venue ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Max Participants</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->max_participants ?? 'Unlimited' }}</dd>
                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $event->creator?->full_name ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Responses</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Going</span>
                    <span class="badge bg-success rounded-pill">{{ $event->going_count }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Maybe</span>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $event->maybe_count }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Not Going</span>
                    <span class="badge bg-danger rounded-pill">{{ $event->not_going_count }}</span>
                </div>

                @if($event->responses->count())
                    <hr>
                    <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none"
                       data-bs-toggle="modal" data-bs-target="#eventResponsesModal">
                        <span class="small fw-semibold">{{ $event->responses->count() }} responded</span>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                @endif
            </div>
        </div>

        @if($event->isManagedBy(auth()->user()))
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    @if(! in_array($event->status, ['completed', 'cancelled']))
                        <form action="{{ route('events.complete', $event) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Event Completed
                            </button>
                        </form>
                        <form action="{{ route('events.cancel', $event) }}" method="POST" onsubmit="return confirm('Cancel this event?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-1"></i> Cancel Event
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Event
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@if($role === 'secretary')
    @php
        $pickerQuery = \App\Models\Household::where('status', 'active')->orderBy('household_number');
        if ($event->village_id !== null) {
            $pickerQuery->where('village_id', $event->village_id);
        } elseif ($participationVillageId !== null) {
            $pickerQuery->where('village_id', $participationVillageId);
        }
        $pickerHouseholds = $pickerQuery->get();
    @endphp
    <div class="modal fade" id="addParticipationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('events.participations.store', $event) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record Participation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Household <span class="text-danger">*</span></label>
                            <select name="household_id" class="form-select" required>
                                <option value="">Select Household</option>
                                @foreach($pickerHouseholds as $household)
                                    <option value="{{ $household->id }}">
                                        Block {{ $household->block_number ?? 'N/A' }} - Lot {{ $household->lot_number ?? 'N/A' }} ({{ $household->household_number }}){{ $event->village_id === null ? ' — ' . ($household->village?->village_name ?? '') : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">One participation is recorded per household per event.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional remarks...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editParticipationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="editParticipationForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Participation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" name="remarks" id="editRemarks" class="form-control" placeholder="Optional remarks...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('editParticipationModal');
            if (!modal) return;

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;

                document.getElementById('editParticipationForm').action = button.dataset.action;
                document.getElementById('editStatus').value = button.dataset.status;
                document.getElementById('editRemarks').value = button.dataset.remarks || '';
            });
        });
    </script>
    @endpush
@endif

<div class="modal fade" id="eventResponsesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Responding Households</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @forelse($event->responses as $resp)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">
                            Block {{ $resp->household?->block_number ?? 'N/A' }}, Lot {{ $resp->household?->lot_number ?? 'N/A' }}
                            <span class="text-muted">({{ $resp->household?->household_head_name ?? 'Unknown' }})</span>
                        </span>
                        @if($resp->response === 'going')
                            <span class="badge bg-success">Going</span>
                        @elseif($resp->response === 'maybe')
                            <span class="badge bg-warning text-dark">Maybe</span>
                        @else
                            <span class="badge bg-danger">Not Going</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">No responses yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
