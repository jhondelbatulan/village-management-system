@extends('layouts.app')

@section('title', 'Edit Announcement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Edit Announcement</h1>
        <p class="page-subtitle text-muted">Update announcement: {{ $announcement->title }}</p>
    </div>
    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
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
        <form action="{{ route('announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $announcement->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="5">{{ old('description', $announcement->description) }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $announcement->start_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Expiration Date</label>
                    <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $announcement->expiration_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $announcement->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $announcement->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status', $announcement->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cover Image</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                    @if($announcement->cover_image)
                        <small class="text-muted">Current: {{ basename($announcement->cover_image) }}</small>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    @if($announcement->attachment)
                        <small class="text-muted">Current: {{ basename($announcement->attachment) }}</small>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold">Pin this announcement</label>
                    </div>
                </div>

                @php
                    $selectedRoles = old('visible_roles', $announcement->visible_roles);
                    $isPublic = ! is_array($selectedRoles) || $selectedRoles === [] || in_array('public', $selectedRoles, true);
                @endphp
                <div class="col-12">
                    <label class="form-label fw-semibold">Who Can View <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="visible_roles[]" value="public" id="visible_public" {{ $isPublic ? 'checked' : '' }}>
                            <label class="form-check-label" for="visible_public"><i class="bi bi-globe2 me-1"></i>Public (all roles)</label>
                        </div>
                        @foreach(['admin', 'secretary', 'treasurer', 'resident'] as $roleSlug)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="visible_roles[]" value="{{ $roleSlug }}" id="visible_{{ $roleSlug }}" {{ is_array($selectedRoles) && in_array($roleSlug, $selectedRoles, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="visible_{{ $roleSlug }}">{{ ucfirst($roleSlug) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Leave "Public" checked to show this announcement to everyone. Uncheck it and tick only the roles allowed to view it.</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Announcement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
