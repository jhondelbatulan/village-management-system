{{--
    Shared announcement form fields — used by the create/edit pages and by
    the create/edit modals on the announcements index.

    Expected view data: $categories (and, for the create context, $role,
    $village and $villages).

    Parameters:
        $context     — 'create' (default) or 'edit'
        $idPrefix   — prefix for element ids so several forms can share a page
        $announcement — the record being edited (null on create)
--}}
@php
    $announcement = $announcement ?? null;
    $idPrefix = $idPrefix ?? '';
    $isEdit = ($context ?? 'create') === 'edit';
@endphp

@if(! $isEdit)
    @if(($role ?? '') === 'super_admin')
        <input type="hidden" name="village_id" id="{{ $idPrefix }}village_id_hidden" value="{{ old('village_id') }}">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Target Village <span class="text-danger">*</span></label>
                <select name="village_id_select" id="{{ $idPrefix }}village_id_select" class="form-select" required>
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
                <small class="text-muted">Select the village this announcement is for, or All Villages to post it everywhere.</small>
            </div>
        </div>
    @else
        <input type="hidden" name="village_id" value="{{ old('village_id', $village->id ?? '') }}">
    @endif
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement?->title) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select" required>
            <option value="">Select Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $announcement?->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="5">{{ old('description', $announcement?->description) }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $announcement?->start_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Expiration Date</label>
        <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $announcement?->expiration_date?->format('Y-m-d')) }}">
    </div>

    @if($isEdit)
        <div class="col-md-4">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
                <option value="draft" {{ old('status', $announcement?->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $announcement?->status) == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ old('status', $announcement?->status) == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label fw-semibold">Cover Image</label>
        <input type="file" name="cover_image" class="form-control" accept="image/*">
        @if($announcement?->cover_image)
            <small class="text-muted">Current: {{ basename($announcement->cover_image) }}</small>
        @else
            <small class="text-muted">Max 2MB. JPG, PNG.</small>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Attachment</label>
        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
        @if($announcement?->attachment)
            <small class="text-muted">Current: {{ basename($announcement->attachment) }}</small>
        @else
            <small class="text-muted">Max 5MB. PDF, DOC, DOCX, XLS, XLSX.</small>
        @endif
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement?->is_pinned) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold">Pin this announcement</label>
        </div>
    </div>

    @php
        $selectedRoles = old('visible_roles', $announcement?->visible_roles);
        $isPublic = ! is_array($selectedRoles) || $selectedRoles === [] || in_array('public', $selectedRoles, true);
    @endphp
    <div class="col-12">
        <label class="form-label fw-semibold">Who Can View <span class="text-danger">*</span></label>
        <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="visible_roles[]" value="public" id="{{ $idPrefix }}visible_public" {{ $isPublic ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $idPrefix }}visible_public"><i class="bi bi-globe2 me-1"></i>Public (all roles)</label>
            </div>
            @foreach(['admin', 'secretary', 'treasurer', 'resident'] as $roleSlug)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="visible_roles[]" value="{{ $roleSlug }}" id="{{ $idPrefix }}visible_{{ $roleSlug }}" {{ is_array($selectedRoles) && in_array($roleSlug, $selectedRoles, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $idPrefix }}visible_{{ $roleSlug }}">{{ ucfirst($roleSlug) }}</label>
                </div>
            @endforeach
        </div>
        <small class="text-muted">Leave "Public" checked to show this announcement to everyone. Uncheck it and tick only the roles allowed to view it.</small>
    </div>
</div>

@if(! $isEdit && ($role ?? '') === 'super_admin')
@push('scripts')
<script>
(function () {
    var select = document.getElementById('{{ $idPrefix }}village_id_select');
    var hidden = document.getElementById('{{ $idPrefix }}village_id_hidden');
    if (select && hidden) {
        select.addEventListener('change', function () { hidden.value = this.value; });
        if (select.value) hidden.value = select.value;
    }
})();
</script>
@endpush
@endif
