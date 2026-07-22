@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="page-title mb-1">System Settings</h4>
            <p class="page-subtitle text-muted mb-0">Configure application settings and preferences.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@foreach($grouped as $groupName => $settings)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold">
                @php
                    $groupIcons = [
                        'general' => 'bi-gear',
                        'security' => 'bi-shield-lock',
                        'email' => 'bi-envelope',
                        'payment' => 'bi-cash-coin',
                        'notification' => 'bi-bell',
                        'registration' => 'bi-person-plus',
                        'maintenance' => 'bi-tools',
                    ];
                    $groupIcon = $groupIcons[strtolower($groupName)] ?? 'bi-sliders';
                @endphp
                <i class="{{ $groupIcon }} me-2 text-primary"></i>{{ ucfirst($groupName) }} Settings
            </h6>
        </div>
        <div class="card-body">
            {{-- System Logo Upload (inside General Settings only) --}}
            @if(strtolower($groupName) === 'general')
                @php $currentLogo = app(\App\Services\BrandingService::class)->getLogoUrl(); @endphp
                <div class="row g-4 mb-4 pb-4 border-bottom">
                    <div class="col-md-3 text-center">
                        <img src="{{ $currentLogo }}" alt="System Logo" id="settingsLogoPreview"
                             class="mb-3" style="max-width:140px;max-height:140px;object-fit:contain;border-radius:12px;border:2px solid #dee2e6;background:#f8f9fa;padding:8px;">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label fw-semibold">System Logo</label>
                        <p class="text-muted small mb-3">Upload a logo that appears on the header, login page, and sidebar. Recommended size: 200x200px (PNG, JPG, SVG — max 2MB).</p>
                        <form action="{{ route('system-admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data" id="logoUploadForm">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="logo" id="settingsLogoInput" class="form-control" accept="image/png,image/jpeg,image/svg+xml">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload</button>
                            </div>
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </form>
                    </div>
                </div>
            @endif

            {{-- Standard setting fields --}}
            <form method="POST" action="{{ route('system-admin.settings.update') }}">
                @csrf
                <div class="row g-4">
                    @foreach($settings as $setting)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="setting_{{ $setting->key }}">
                                {{ ucfirst(str_replace('_', ' ', $setting->key)) }}
                                @if($setting->description)
                                    <i class="bi bi-info-circle text-muted ms-1" title="{{ $setting->description }}"></i>
                                @endif
                            </label>
                            @if($setting->description)
                                <div class="text-muted small mb-2">{{ $setting->description }}</div>
                            @endif

                            @if($setting->type === 'text')
                                <input type="text"
                                       id="setting_{{ $setting->key }}"
                                       name="settings[{{ $setting->key }}]"
                                       class="form-control @error('settings.' . $setting->key) is-invalid @enderror"
                                       value="{{ old('settings.' . $setting->key, $setting->value) }}">
                            @elseif($setting->type === 'number')
                                <input type="number"
                                       id="setting_{{ $setting->key }}"
                                       name="settings[{{ $setting->key }}]"
                                       class="form-control @error('settings.' . $setting->key) is-invalid @enderror"
                                       value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                       min="0">
                            @elseif($setting->type === 'toggle')
                                <div class="form-check form-switch">
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <input type="checkbox"
                                           id="setting_{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           class="form-check-input @error('settings.' . $setting->key) is-invalid @enderror"
                                           value="1"
                                           {{ old('settings.' . $setting->key, $setting->value) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="setting_{{ $setting->key }}">
                                        {{ old('settings.' . $setting->key, $setting->value) == '1' ? 'Enabled' : 'Disabled' }}
                                    </label>
                                </div>
                            @elseif($setting->type === 'select')
                                <select id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        class="form-select @error('settings.' . $setting->key) is-invalid @enderror">
                                    @php
                                        $options = is_array($setting->options) ? $setting->options : (json_decode($setting->options, true) ?? []);
                                        $currentValue = old('settings.' . $setting->key, $setting->value);
                                    @endphp
                                    @foreach($options as $optKey => $optVal)
                                        @php
                                            $optValue = is_int($optKey) ? $optVal : $optKey;
                                            $optLabel = is_int($optKey) ? $optVal : $optVal;
                                        @endphp
                                        <option value="{{ $optValue }}" {{ $currentValue == $optValue ? 'selected' : '' }}>
                                            {{ is_string($optLabel) ? $optLabel : $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error('settings.' . $setting->key)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            }
        });
    });

    const logoInput = document.getElementById('settingsLogoInput');
    const logoPreview = document.getElementById('settingsLogoPreview');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>
@endpush
@endsection
