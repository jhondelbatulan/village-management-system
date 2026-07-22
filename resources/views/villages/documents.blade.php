@extends('layouts.app')

@section('title', 'Village Documents - ' . $village->village_name)

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-earmark-text me-1"></i>{{ $village->village_name }} — Documents
            </h4>
            <p class="page-subtitle text-muted mb-0">Manage village documents and files.</p>
        </div>
        <a href="{{ route('villages.show', $village) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Village
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" href="#uploadForm" role="button" aria-expanded="false" aria-controls="uploadForm">
            <i class="bi bi-cloud-upload me-1"></i>Upload Document
        </a>
    </div>
    <div class="collapse" id="uploadForm">
        <div class="card-body border-top">
            <form action="{{ route('villages.documents.store', $village) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="1">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="file" class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                               id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">PDF, DOC, DOCX, JPG, PNG</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="reset" class="btn btn-outline-secondary btn-sm">Clear</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-center">File Type</th>
                        <th scope="col">Uploaded By</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td class="fw-semibold">{{ $document->title }}</td>
                            <td>{{ Str::limit($document->description ?? '', 60) ?: '—' }}</td>
                            <td class="text-center">
                                @php $ext = strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION)); @endphp
                                @if($ext === 'PDF')
                                    <span class="badge bg-danger">{{ $ext }}</span>
                                @elseif(in_array($ext, ['DOC', 'DOCX']))
                                    <span class="badge bg-primary">{{ $ext }}</span>
                                @else
                                    <span class="badge bg-success">{{ $ext }}</span>
                                @endif
                            </td>
                            <td>{{ $document->uploader?->full_name ?? '—' }}</td>
                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('villages.documents.download', ['village' => $village, 'document' => $document]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ route('villages.documents.destroy', ['village' => $village, 'document' => $document]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                                No documents found. Upload a document using the form above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($documents->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection
