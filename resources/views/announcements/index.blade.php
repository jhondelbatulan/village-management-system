@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Announcements</h1>
        <p class="page-subtitle text-muted">Manage village announcements and notices</p>
    </div>
    <div class="d-flex gap-2">
        @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin']))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementCreateModal">
                <i class="bi bi-plus-lg me-1"></i> Create Announcement
            </button>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('announcements.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Title, description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-7 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-toggle="collapse" data-bs-target="#announcementFilters"
                            aria-expanded="{{ request()->hasAny(['category', 'status', 'sort', 'creator']) ? 'true' : 'false' }}">
                        <i class="bi bi-funnel me-1"></i> More filters
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
            <div class="collapse {{ request()->hasAny(['category', 'status', 'sort', 'creator']) ? 'show' : '' }} mt-3" id="announcementFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sort</label>
                        <select name="sort" class="form-select">
                            <option value="">Newest First</option>
                            <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A – Z</option>
                            <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z – A</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Created By</label>
                        <select name="creator" class="form-select">
                            <option value="">Everyone</option>
                            <option value="super_admin" {{ request('creator') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ request('creator') == 'admin' ? 'selected' : '' }}>Admin</option>
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
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        @php
                            $canManage = $announcement->isManagedBy(auth()->user());
                            $modalData = [
                                'id' => $announcement->id,
                                'title' => $announcement->title,
                                'category' => $announcement->category,
                                'description' => $announcement->description,
                                'status' => $announcement->status,
                                'is_pinned' => (bool) $announcement->is_pinned,
                                'village' => $announcement->village->village_name ?? 'All Villages',
                                'creator' => $announcement->creator?->full_name ?? '—',
                                'start_date' => $announcement->start_date?->format('Y-m-d'),
                                'start_display' => $announcement->start_date?->format('M d, Y'),
                                'expiration_date' => $announcement->expiration_date?->format('Y-m-d'),
                                'expiration_display' => $announcement->expiration_date?->format('M d, Y'),
                                'visible_roles' => $announcement->visible_roles ?? [],
                                'cover_image_url' => $announcement->cover_image ? $announcement->cover_image_url : null,
                                'attachment_url' => $announcement->attachment ? $announcement->attachment_url : null,
                                'cover_name' => $announcement->cover_image ? basename($announcement->cover_image) : null,
                                'attachment_name' => $announcement->attachment ? basename($announcement->attachment) : null,
                                'show_url' => route('announcements.show', $announcement),
                                'update_url' => route('announcements.update', $announcement),
                                'created_display' => $announcement->created_at->format('M d, Y'),
                            ];
                        @endphp
                        <tr data-ann="{{ json_encode($modalData) }}">
                            <td>
                                <button type="button" class="btn btn-link p-0 fw-semibold text-start text-decoration-none"
                                        data-bs-toggle="modal" data-bs-target="#announcementViewModal">
                                    @if($announcement->is_pinned)
                                        <i class="bi bi-pin-fill text-warning me-1" title="Pinned"></i>
                                    @endif
                                    {{ $announcement->title }}
                                </button>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $announcement->category }}</span></td>
                            <td>
                                @if($announcement->status === 'published')
                                    <span class="badge bg-success">Published</span>
                                @elseif($announcement->status === 'archived')
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="View"
                                            data-bs-toggle="modal" data-bs-target="#announcementViewModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($canManage)
                                        @if($announcement->status === 'draft' || $announcement->status === 'archived')
                                            <button type="button" class="btn btn-outline-warning" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#announcementEditModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        @if($announcement->status === 'draft')
                                            <form action="{{ route('announcements.publish', $announcement) }}" method="POST" class="d-inline"
                                                  data-confirm="Publish this announcement now?">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Publish">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($announcement->status === 'published')
                                            <form action="{{ route('announcements.archive', $announcement) }}" method="POST" class="d-inline"
                                                  data-confirm="Archive this announcement? It will be hidden from the list.">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="d-inline"
                                              data-confirm="Delete this announcement? This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-megaphone fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No announcements found.</p>
                                @if(in_array(auth()->user()->role?->role_slug, ['super_admin', 'admin']))
                                    <button type="button" class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#announcementCreateModal">
                                        <i class="bi bi-plus-lg me-1"></i> Create First Announcement
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($announcements->hasPages())
        <div class="card-footer bg-white">
            {{ $announcements->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Create modal --}}
<div class="modal fade" id="announcementCreateModal" tabindex="-1" aria-labelledby="announcementCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_modal" value="create">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-semibold" id="announcementCreateLabel">Create Announcement</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any() && old('_modal') === 'create')
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @include('announcements._form-fields', ['context' => 'create', 'idPrefix' => 'cm-'])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View modal --}}
<div class="modal fade" id="announcementViewModal" tabindex="-1" aria-labelledby="announcementViewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title fw-semibold" id="announcementViewLabel">Announcement</h6>
                    <small class="text-muted" id="annViewSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge" id="annViewStatus">Draft</span>
                    <span class="badge bg-warning text-dark" id="annViewPin" style="display:none"><i class="bi bi-pin me-1"></i>Pinned</span>
                    <span class="badge bg-light text-dark" id="annViewCategory">—</span>
                </div>
                <h5 class="fw-bold mb-3" id="annViewTitle"></h5>
                <img id="annViewCover" src="" alt="Cover" class="img-fluid rounded mb-3" style="max-height: 260px; display:none">
                <div class="mb-3" id="annViewBody" style="white-space: pre-line"></div>
                <a href="#" id="annViewAttachment" class="btn btn-outline-primary btn-sm mb-3" target="_blank" style="display:none">
                    <i class="bi bi-paperclip me-1"></i> View Attachment
                </a>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Village</dt>
                    <dd class="col-sm-8 fw-semibold" id="annViewVillage">—</dd>
                    <dt class="col-sm-4 text-muted">Created By</dt>
                    <dd class="col-sm-8 fw-semibold" id="annViewCreator">—</dd>
                    <dt class="col-sm-4 text-muted">Visible To</dt>
                    <dd class="col-sm-8" id="annViewVisible"></dd>
                    <dt class="col-sm-4 text-muted">Start Date</dt>
                    <dd class="col-sm-8 fw-semibold" id="annViewStart">—</dd>
                    <dt class="col-sm-4 text-muted">Expires</dt>
                    <dd class="col-sm-8 fw-semibold" id="annViewExpires">—</dd>
                </dl>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="annViewFull" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open full page
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div class="modal fade" id="announcementEditModal" tabindex="-1" aria-labelledby="announcementEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('announcements.update', ['announcement' => '__ID__']) }}" method="POST" enctype="multipart/form-data" id="annEditForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="_id" id="annEditId" value="">
                <div class="modal-header py-2">
                    <div>
                        <h6 class="modal-title fw-semibold" id="announcementEditLabel">Edit Announcement</h6>
                        <small class="text-muted" id="annEditSubtitle"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any() && old('_modal') === 'edit')
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="small text-muted mb-3" id="annEditCurrent"></div>
                    @include('announcements._form-fields', ['context' => 'edit', 'idPrefix' => 'em-', 'announcement' => null])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Update Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value == null || value === '' ? '—' : value;
    }

    function statusLabel(status) {
        return status === 'published' ? 'Published' : status === 'archived' ? 'Archived' : 'Draft';
    }

    function statusClass(status) {
        return status === 'published' ? 'bg-success' : status === 'archived' ? 'bg-secondary' : 'bg-warning text-dark';
    }

    function fillViewModal(d) {
        setText('annViewTitle', d.title);
        setText('annViewSubtitle', (d.village || '') + ' · Created ' + (d.created_display || ''));

        var status = document.getElementById('annViewStatus');
        if (status) {
            status.textContent = statusLabel(d.status);
            status.className = 'badge ' + statusClass(d.status);
        }

        var pin = document.getElementById('annViewPin');
        if (pin) pin.style.display = d.is_pinned ? '' : 'none';

        setText('annViewCategory', d.category);

        var body = document.getElementById('annViewBody');
        if (body) body.textContent = d.description || 'No description provided.';

        var cover = document.getElementById('annViewCover');
        if (cover) {
            if (d.cover_image_url) { cover.src = d.cover_image_url; cover.style.display = ''; }
            else { cover.removeAttribute('src'); cover.style.display = 'none'; }
        }

        var attachment = document.getElementById('annViewAttachment');
        if (attachment) {
            if (d.attachment_url) { attachment.href = d.attachment_url; attachment.style.display = ''; }
            else { attachment.style.display = 'none'; }
        }

        setText('annViewVillage', d.village);
        setText('annViewCreator', d.creator);
        setText('annViewStart', d.start_display);
        setText('annViewExpires', d.expiration_display);

        var visible = document.getElementById('annViewVisible');
        if (visible) {
            visible.innerHTML = '';
            var roles = Array.isArray(d.visible_roles) ? d.visible_roles : [];
            if (!roles.length) {
                var pub = document.createElement('span');
                pub.className = 'badge bg-info-subtle text-info-emphasis border';
                pub.textContent = 'Public';
                visible.appendChild(pub);
            } else {
                roles.forEach(function (role) {
                    var badge = document.createElement('span');
                    badge.className = 'badge bg-primary-subtle text-primary-emphasis border me-1';
                    badge.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                    visible.appendChild(badge);
                });
            }
        }

        var full = document.getElementById('annViewFull');
        if (full) full.href = d.show_url || '#';
    }

    function fillEditModal(d) {
        var form = document.getElementById('annEditForm');
        if (!form) return;

        form.action = d.update_url;
        var idInput = document.getElementById('annEditId');
        if (idInput) idInput.value = d.id || '';

        setText('annEditSubtitle', d.title);

        var current = document.getElementById('annEditCurrent');
        if (current) {
            var files = [];
            if (d.cover_name) files.push('Cover: ' + d.cover_name);
            if (d.attachment_name) files.push('Attachment: ' + d.attachment_name);
            current.textContent = files.length ? 'Current files — ' + files.join(' · ') : 'No files uploaded yet.';
        }

        form.querySelector('[name="title"]').value = d.title || '';
        form.querySelector('[name="category"]').value = d.category || '';
        form.querySelector('[name="description"]').value = d.description || '';
        form.querySelector('[name="start_date"]').value = d.start_date || '';
        form.querySelector('[name="expiration_date"]').value = d.expiration_date || '';
        form.querySelector('[name="status"]').value = d.status || 'draft';
        form.querySelector('[name="is_pinned"]').checked = !!d.is_pinned;

        var roles = Array.isArray(d.visible_roles) ? d.visible_roles : [];
        var isPublic = roles.length === 0 || roles.indexOf('public') !== -1;
        form.querySelectorAll('[name="visible_roles[]"]').forEach(function (box) {
            box.checked = box.value === 'public' ? isPublic : roles.indexOf(box.value) !== -1;
        });
    }

    function rowData(trigger) {
        var row = trigger.closest('tr[data-ann]');
        if (!row) return null;
        try { return JSON.parse(row.getAttribute('data-ann')); }
        catch (error) { console.error('Announcement row data could not be parsed:', error); return null; }
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-bs-target="#announcementViewModal"], [data-bs-target="#announcementEditModal"]');
        if (!trigger) return;
        var data = rowData(trigger);
        if (!data) return;
        if (trigger.getAttribute('data-bs-target') === '#announcementViewModal') fillViewModal(data);
        else fillEditModal(data);
    });

    {{-- Reopen the submitted modal after a validation error --}}
    @if($errors->any() && old('_modal'))
        var reopen = @json(old('_modal'));
        if (reopen === 'create') {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('announcementCreateModal')).show();
        } else if (reopen === 'edit') {
            var editForm = document.getElementById('annEditForm');
            var editId = @json(old('_id'));
            if (editForm && editId) {
                editForm.action = @json(route('announcements.update', ['announcement' => '__ID__'])).replace('__ID__', String(editId));
                var idInput = document.getElementById('annEditId');
                if (idInput) idInput.value = String(editId);
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('announcementEditModal')).show();
        }
    @endif
});
</script>
@endpush
