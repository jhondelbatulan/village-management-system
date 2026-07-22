@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Edit Event</h1>
        <p class="page-subtitle text-muted">Update event: {{ $event->title }}</p>
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
        <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $event->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $event->start_time?->format('H:i')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $event->end_time?->format('H:i')) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Venue</label>
                    <input type="text" name="venue" class="form-control" value="{{ old('venue', $event->venue) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Max Participants</label>
                    <input type="number" name="max_participants" class="form-control" value="{{ old('max_participants', $event->max_participants) }}" min="1">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="upcoming" {{ old('status', $event->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing" {{ old('status', $event->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    @if($event->cover_image)
                        <small class="text-muted">Current: {{ basename($event->cover_image) }}</small>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    @if($event->attachment)
                        <small class="text-muted">Current: {{ basename($event->attachment) }}</small>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
