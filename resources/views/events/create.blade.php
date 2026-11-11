@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Create Event</h1>
        <p class="page-subtitle text-muted">Create a new event for {{ $village->village_name ?? 'your village' }}</p>
    </div>
    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if($role === 'super_admin')
                <input type="hidden" name="village_id" id="village_id_hidden" value="{{ old('village_id') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Target Village <span class="text-danger">*</span></label>
                        <select name="village_id_select" id="village_id_select" class="form-select" required>
                            <option value="">Select Village</option>
                            <option value="all" {{ old('village_id') === 'all' ? 'selected' : '' }}>
                                All Villages (post to every village)
                            </option>
                            @foreach($villages as $v)
                                <option value="{{ $v->id }}" {{ old('village_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->village_name }} ({{ $v->village_code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the village this event is for, or All Villages to post it everywhere.</small>
                    </div>
                </div>
            @else
                <input type="hidden" name="village_id" value="{{ $village->id }}">
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" value="{{ old('event_date') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Venue</label>
                    <input type="text" name="venue" class="form-control" value="{{ old('venue') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Participants</label>
                    <input type="number" name="max_participants" class="form-control" value="{{ old('max_participants') }}" min="1">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    <small class="text-muted">Max 2MB. JPG, PNG.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">Max 5MB. PDF, DOC, DOCX, XLS, XLSX.</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@if($role === 'super_admin')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('village_id_select');
    const hidden = document.getElementById('village_id_hidden');
    if (select && hidden) {
        select.addEventListener('change', function() { hidden.value = this.value; });
        if (select.value) hidden.value = select.value;
    }
});
</script>
@endpush
@endif
