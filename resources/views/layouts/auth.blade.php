<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $branding = app(\App\Services\BrandingService::class); @endphp
    <title>@yield('title', 'Login') — {{ $branding->get('system_name', config('app_settings.name')) }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $branding->getFaviconUrl() }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="auth-body">
    @yield('content')
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
