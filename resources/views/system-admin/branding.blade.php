@extends('layouts.app')

@section('title', 'System Branding')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800"><i class="bi bi-palette-fill me-2"></i>System Branding</h1>
        <p class="text-muted mb-0">Customize the look and feel of the system</p>
    </div>
    <a href="{{ route('system-admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Settings
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Logo & Favicon --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-image me-2"></i>System Logo</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @php $logo = app(\App\Services\BrandingService::class)->getLogoUrl(); @endphp
                    <img src="{{ $logo }}" alt="System Logo" id="logoPreview" class="mb-2" style="max-width:150px;max-height:150px;object-fit:contain;border-radius:12px;border:2px solid #dee2e6;">
                </div>
                <form action="{{ route('system-admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <input type="file" name="logo" id="logoInput" class="form-control form-control-sm" accept="image/png,image/jpeg,image/svg+xml" onchange="previewImage(this, 'logoPreview')">
                    </div>
                    @error('logo')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mb-2">PNG, JPG, SVG (max 2MB)</small>
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Upload Logo</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-square me-2"></i>Favicon</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @php $favicon = app(\App\Services\BrandingService::class)->getFaviconUrl(); @endphp
                    <img src="{{ $favicon }}" alt="Favicon" id="faviconPreview" class="mb-2" style="width:48px;height:48px;border-radius:8px;border:2px solid #dee2e6;">
                </div>
                <form action="{{ route('system-admin.branding.upload-favicon') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <input type="file" name="favicon" class="form-control form-control-sm" accept="image/png,image/x-icon,image/svg+xml" onchange="previewImage(this, 'faviconPreview')">
                    </div>
                    @error('favicon')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mb-2">PNG, ICO, SVG (max 1MB)</small>
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Upload Favicon</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Branding Settings --}}
    <div class="col-lg-8">
        <form action="{{ route('system-admin.branding.update') }}" method="POST">
            @csrf
            @foreach($groups as $groupName => $fields)
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">
                            <i class="bi bi-{{ $groupName === 'Basic Information' ? 'info-circle' : ($groupName === 'Organization' ? 'building' : ($groupName === 'Footer & Credits' ? 'footer' : 'palette')) }} me-2"></i>
                            {{ $groupName }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($fields as $key => $field)
                                <div class="col-md-{{ $field['type'] === 'text' && strlen($field['label']) > 20 ? '12' : '6' }}">
                                    <label class="form-label fw-semibold">{{ $field['label'] }}</label>
                                    @if($field['type'] === 'select')
                                        <select name="{{ $key }}" class="form-select @error($key) is-invalid @enderror">
                                            @foreach($field['options'] as $optVal => $optLabel)
                                                <option value="{{ $optVal }}" {{ old($key, $branding[$key] ?? '') === $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($field['type'] === 'email')
                                        <input type="email" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror" value="{{ old($key, $branding[$key] ?? '') }}">
                                    @else
                                        <input type="{{ $field['type'] }}" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror" value="{{ old($key, $branding[$key] ?? '') }}">
                                    @endif
                                    @error($key)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(!empty($field['description']))
                                        <small class="text-muted">{{ $field['description'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Save Branding Settings</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
