@extends('layouts.app')

@section('title', 'Import Residents')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">Import Residents</h4>
            <p class="page-subtitle text-muted mb-0">Bulk import residents from a CSV file.</p>
        </div>
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Residents
        </a>
    </div>
</div>

@if(session('import_errors'))
    <div class="alert alert-danger shadow-sm" role="alert">
        <h6 class="alert-heading fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle me-1"></i>Import Errors
        </h6>
        <hr>
        <ul class="mb-0">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ol me-2 text-primary"></i>How to Import
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="d-flex mb-4">
                    <div class="me-3">
                        <span class="badge bg-primary rounded-circle p-2 fs-6">1</span>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-1">Download the Template</h6>
                        <p class="text-muted mb-2">Get the CSV template with the correct column headers.</p>
                        <a href="{{ route('residents.download-template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i>Download Template
                        </a>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="me-3">
                        <span class="badge bg-primary rounded-circle p-2 fs-6">2</span>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-1">Fill in the Template</h6>
                        <p class="text-muted mb-0">Open the downloaded CSV file and fill in the resident data following the format. Save the file as CSV or TXT.</p>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="me-3">
                        <span class="badge bg-primary rounded-circle p-2 fs-6">3</span>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-1">Upload the File</h6>
                        <p class="text-muted mb-0">Select the target village and upload your completed CSV file below.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-upload me-2 text-primary"></i>Upload CSV File
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('residents.process-import') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="village_id" class="form-label fw-semibold">Assign to Village <span class="text-danger">*</span></label>
                            @if($villages->count() === 1)
                                @php $selectedVillage = $villages->first(); @endphp
                                <input type="hidden" name="village_id" value="{{ $selectedVillage->id }}">
                                <input type="text" class="form-control" value="{{ $selectedVillage->village_name }}" readonly>
                                <small class="text-muted">Importing to your assigned village.</small>
                            @else
                                <select class="form-select @error('village_id') is-invalid @enderror"
                                        id="village_id" name="village_id" required>
                                    <option value="">Select Village</option>
                                    @foreach($villages as $village)
                                        <option value="{{ $village->id }}" {{ old('village_id') == $village->id ? 'selected' : '' }}>
                                            {{ $village->village_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @error('village_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="csv_file" class="form-label fw-semibold">CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror"
                                   id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i>Import Residents
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Import Guidelines
                </h6>
            </div>
            <div class="card-body px-4 pb-4">
                <h6 class="fw-semibold small text-muted mb-2">Required Columns</h6>
                <ul class="small mb-3">
                    <li><code>first_name</code></li>
                    <li><code>last_name</code></li>
                    <li><code>gender</code></li>
                    <li><code>birthdate</code> <span class="text-muted">(YYYY-MM-DD)</span></li>
                    <li><code>civil_status</code></li>
                </ul>

                <h6 class="fw-semibold small text-muted mb-2">Optional Columns</h6>
                <ul class="small mb-3">
                    <li><code>middle_name</code></li>
                    <li><code>suffix</code></li>
                    <li><code>nationality</code></li>
                    <li><code>religion</code></li>
                    <li><code>occupation</code></li>
                    <li><code>educational_attainment</code></li>
                    <li><code>contact_number</code></li>
                    <li><code>email</code></li>
                    <li><code>household_number</code></li>
                    <li><code>relationship_to_head</code></li>
                    <li><code>voter_status</code> <span class="text-muted">(0 or 1)</span></li>
                    <li><code>is_household_head</code> <span class="text-muted">(0 or 1)</span></li>
                </ul>

                <div class="alert alert-info small mb-0">
                    <i class="bi bi-lightbulb me-1"></i>
                    Resident codes will be auto-generated. Duplicate emails will be skipped with a warning.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
