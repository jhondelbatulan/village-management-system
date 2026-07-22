<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — {{ \App\Helpers\SystemHelper::systemName() }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
</head>
<body class="dashboard-body d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--mvms-bg);">
    <div class="text-center px-4">
        <div class="mb-4">
            <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: var(--mvms-primary-light); opacity: 0.3;"></i>
        </div>

        <h1 class="display-1 fw-bold" style="color: var(--mvms-primary); letter-spacing: -0.02em;">404</h1>

        <h2 class="h4 fw-semibold mb-3" style="color: var(--mvms-primary-dark);">Page Not Found</h2>

        <p class="text-muted mb-4" style="max-width: 420px; margin-left: auto; margin-right: auto;">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        <div class="d-flex justify-content-center gap-3">
            <button onclick="window.history.back()" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i> Go Back
            </button>
            @php
                $dashboardUrl = '/';
                if (auth()->check()) {
                    $role = auth()->user()->role?->role_slug;
                    $dashboardUrl = match($role) {
                        'super_admin' => route('dashboard.super_admin'),
                        'admin' => route('dashboard.admin'),
                        'secretary' => route('dashboard.secretary'),
                        'treasurer' => route('dashboard.treasurer'),
                        'resident' => route('dashboard.resident'),
                        default => route('login'),
                    };
                } else {
                    $dashboardUrl = route('login');
                }
            @endphp
            <a href="{{ $dashboardUrl }}" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i> Go to Dashboard
            </a>
        </div>

        <p class="text-muted mt-5" style="font-size: 0.8rem;">
            {{ \App\Helpers\SystemHelper::systemName() }}
        </p>
    </div>
</body>
</html>
