<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error — {{ \App\Helpers\SystemHelper::systemName() }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
</head>
<body class="dashboard-body d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--mvms-bg);">
    <div class="text-center px-4">
        <div class="mb-4">
            <i class="bi bi-server" style="font-size: 4rem; color: var(--mvms-primary-light); opacity: 0.3;"></i>
        </div>

        <h1 class="display-1 fw-bold" style="color: var(--mvms-primary); letter-spacing: -0.02em;">500</h1>

        <h2 class="h4 fw-semibold mb-3" style="color: var(--mvms-primary-dark);">Server Error</h2>

        <p class="text-muted mb-4" style="max-width: 420px; margin-left: auto; margin-right: auto;">
            Something went wrong on our end. We are working to fix the issue. Please try again in a few moments.
        </p>

        <div class="d-flex justify-content-center gap-3">
            <button onclick="window.history.back()" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i> Go Back
            </button>
            <a href="{{ url()->current() }}" class="btn btn-primary px-4">
                <i class="bi bi-arrow-clockwise me-1"></i> Try Again
            </a>
        </div>

        <p class="text-muted mt-5" style="font-size: 0.8rem;">
            {{ \App\Helpers\SystemHelper::systemName() }}
        </p>
    </div>
</body>
</html>
