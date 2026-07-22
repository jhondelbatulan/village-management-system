@extends('layouts.app')

@section('title', 'About System')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5 text-center">
                @php
                    $branding = app(\App\Services\BrandingService::class);
                    $logo = $branding->getLogoUrl();
                @endphp
                <img src="{{ $logo }}" alt="Logo" class="mb-4" style="max-width:120px;max-height:120px;object-fit:contain;border-radius:16px;">

                <h2 class="fw-bold mb-1">{{ $branding->get('system_name', config('app_settings.name')) }}</h2>
                <p class="text-muted mb-4">{{ $branding->get('system_tagline', config('app_settings.tagline')) }}</p>

                <div class="row g-4 text-start mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-primary me-3 p-2"><i class="bi bi-building fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Organization</div>
                                <div class="text-muted">{{ $branding->get('organization_name', 'N/A') }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-info me-3 p-2"><i class="bi bi-geo-alt fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">CPDO Office</div>
                                <div class="text-muted">{{ $branding->get('cpdo_office_name', 'N/A') }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-success me-3 p-2"><i class="bi bi-code-slash fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Developer</div>
                                <div class="text-muted">{{ $branding->get('developer_name', 'N/A') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-warning me-3 p-2"><i class="bi bi-layers fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Capstone Team</div>
                                <div class="text-muted">BS Information Technology</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-secondary me-3 p-2"><i class="bi bi-mortarboard fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Adviser</div>
                                <div class="text-muted">Faculty Adviser</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge bg-danger me-3 p-2"><i class="bi bi-calendar fs-5"></i></div>
                            <div>
                                <div class="fw-semibold">Academic Year</div>
                                <div class="text-muted">{{ date('Y') }}–{{ date('Y') + 1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-3 text-start mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-primary">Laravel {{ app()->version() }}</div>
                            <small class="text-muted">Framework</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-success">PHP {{ phpversion() }}</div>
                            <small class="text-muted">Runtime</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-light text-center">
                            <div class="fw-bold text-info">{{ config('database.default') === 'sqlite' ? 'SQLite' : config('database.default') }}</div>
                            <small class="text-muted">Database</small>
                        </div>
                    </div>
                </div>

                <div class="text-muted small">
                    {{ $branding->get('footer_copyright', config('app_settings.footer_text')) }}
                </div>
                <div class="text-muted small mt-1">Version {{ $branding->get('system_version', config('app_settings.version')) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
