@extends('layouts.app')

@section('title', 'Personal Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold text-gray-800"><i class="bi bi-gear me-2"></i>Personal Settings</h1>
        <p class="text-muted mb-0">Customize your experience</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Profile --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-person me-2"></i>Profile</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @php
                        $avatar = $user->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_picture)
                            ? Storage::disk('public')->url($user->profile_picture)
                            : asset('assets/images/profile/default-avatar.svg');
                    @endphp
                    <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle mb-2" width="100" height="100" style="object-fit:cover;">
                    <div class="fw-bold">{{ $user->full_name }}</div>
                    <div class="text-muted small">{{ $user->role?->role_name ?? 'User' }}</div>
                </div>
                <form action="{{ route('settings.personal.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3 text-start">
                        <label class="form-label fw-semibold">Display Name</label>
                        <input type="text" name="display_name" class="form-control" value="{{ $preferences->display_name ?? $user->full_name }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Theme & Appearance --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-palette me-2"></i>Theme & Appearance</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.personal.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        {{-- Theme --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Theme</label>
                            <div class="d-flex gap-2">
                                @foreach(['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Automatic'] as $val => $lbl)
                                    <div class="form-check flex-fill">
                                        <input class="form-check-input" type="radio" name="theme" id="theme_{{ $val }}" value="{{ $val }}" {{ $preferences->theme === $val ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="theme_{{ $val }}">
                                            <div class="border rounded p-2 text-center {{ $preferences->theme === $val ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                                <i class="bi bi-{{ $val === 'light' ? 'sun' : ($val === 'dark' ? 'moon-stars' : 'display') }} fs-4 d-block mb-1"></i>
                                                <small>{{ $lbl }}</small>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Accent Color --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Accent Color</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach(['blue' => '#0d6efd', 'green' => '#198754', 'purple' => '#6f42c1', 'orange' => '#fd7e14', 'red' => '#dc3545', 'gray' => '#6c757d'] as $color => $hex)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="accent_color" id="accent_{{ $color }}" value="{{ $color }}" {{ $preferences->accent_color === $color ? 'checked' : '' }}>
                                        <label class="form-check-label" for="accent_{{ $color }}">
                                            <span class="d-inline-block rounded-circle" style="width:32px;height:32px;background:{{ $hex }};border:3px solid {{ $preferences->accent_color === $color ? $hex : '#dee2e6' }};vertical-align:middle;"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Font Size --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Font Size</label>
                            <select name="font_size" class="form-select">
                                @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra Large'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $preferences->font_size === $val ? 'selected' : '' }} style="font-size:{{ $val === 'small' ? '12px' : ($val === 'large' ? '18px' : ($val === 'xlarge' ? '20px' : '14px')) }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sidebar --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sidebar Style</label>
                            <select name="sidebar_style" class="form-select">
                                @foreach(['expanded' => 'Expanded', 'collapsed' => 'Collapsed', 'auto' => 'Auto Collapse'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $preferences->sidebar_style === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Animation --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Animation Level</label>
                            <select name="animation_level" class="form-select">
                                @foreach(['full' => 'Full', 'reduced' => 'Reduced', 'none' => 'None'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $preferences->animation_level === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Compact Mode --}}
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="compact_mode" value="0">
                                <input class="form-check-input" type="checkbox" name="compact_mode" id="compactMode" value="1" {{ $preferences->compact_mode ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="compactMode">Compact Mode</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Appearance</button>
                </form>
            </div>
        </div>

        {{-- Dashboard Widgets --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-grid-3x3 me-2"></i>Dashboard Widgets</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.personal.update') }}" method="POST">
                    @csrf
                    @php
                        $widgets = $preferences->dashboard_widgets ?? $preferences->getDefaultDashboardWidgets();
                        // The recent-activity widget was removed from every dashboard.
                        unset($widgets['recent_activities']);
                    @endphp
                    <div class="row g-3">
                        @foreach($widgets as $key => $enabled)
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="dashboard_widgets[{{ $key }}]" value="0">
                                    <input class="form-check-input" type="checkbox" name="dashboard_widgets[{{ $key }}]" id="widget_{{ $key }}" value="1" {{ $enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_{{ $key }}">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Dashboard Widgets</button>
                </form>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold"><i class="bi bi-bell me-2"></i>Notifications</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.personal.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="notification_sound" value="0">
                                <input class="form-check-input" type="checkbox" name="notification_sound" id="notifSound" value="1" {{ $preferences->notification_sound ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="notifSound">Notification Sound</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="browser_notifications" value="0">
                                <input class="form-check-input" type="checkbox" name="browser_notifications" id="browserNotif" value="1" {{ $preferences->browser_notifications ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="browserNotif">Browser Notifications (Future)</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Notifications</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function postJson(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        }).then(r => r.json());
    }

    document.querySelectorAll('input[name="theme"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            postJson('{{ route("settings.personal.theme") }}', { theme: this.value })
                .then(function(resp) {
                    if (resp.success && window.MVMS) {
                        MVMS.applyTheme(resp.theme);
                    }
                });
        });
    });

    document.querySelectorAll('input[name="accent_color"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            postJson('{{ route("settings.personal.accent") }}', { accent_color: this.value })
                .then(function(resp) {
                    if (resp.success && window.MVMS) {
                        MVMS.applyAccent(resp.accent_color);
                    }
                });
        });
    });
});
</script>
@endpush
