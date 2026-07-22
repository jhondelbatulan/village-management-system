<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $systemName ?? config('app_settings.name') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ $branding->getFaviconUrl() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="{{ $bodyClasses ?? 'dashboard-body' }}">
    <div class="app-wrapper">
        @include('partials.sidebar')

        <div class="app-main">
            @include('partials.navbar')

            <main class="app-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            @include('partials.footer')
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function applyTheme(theme) {
                document.body.classList.remove('theme-auto', 'theme-light', 'theme-dark');
                if (theme === 'auto') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.body.classList.add(prefersDark ? 'theme-dark' : 'theme-light');
                } else {
                    document.body.classList.add('theme-' + theme);
                }
            }
            if (document.body.classList.contains('theme-auto')) {
                applyTheme('auto');
            }
            function applyAccent(color) {
                document.body.classList.remove('accent-blue','accent-green','accent-purple','accent-orange','accent-red','accent-gray');
                document.body.classList.add('accent-' + color);
            }
            function applyFontSize(size) {
                document.body.classList.remove('font-small','font-medium','font-large','font-xlarge');
                document.body.classList.add('font-' + size);
            }
            function applyCompact(enabled) {
                document.body.classList.toggle('compact-mode', enabled);
            }
            function applySidebar(style) {
                document.body.classList.toggle('sidebar-collapsed', style === 'collapsed');
            }
            function applyAnimation(level) {
                document.body.classList.remove('animation-none','animation-reduced');
                if (level === 'none') document.body.classList.add('animation-none');
                if (level === 'reduced') document.body.classList.add('animation-reduced');
            }
            window.MVMS = { applyTheme, applyAccent, applyFontSize, applyCompact, applySidebar, applyAnimation };
        });
    </script>
    @stack('scripts')
    <script>
(function() {
    var badge = document.getElementById('notificationBadge');
    var list = document.getElementById('notificationList');
    var markAllBtn = document.getElementById('markAllRead');

    function fetchNotifications() {
        fetch('/notifications/unread').then(function(r) { return r.json(); }).then(function(data) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
            if (data.notifications && data.notifications.length > 0) {
                var html = '';
                data.notifications.forEach(function(n) {
                    var iconClass = n.icon || 'bi-bell';
                    var colorClass = n.color || 'secondary';
                    var url = n.action_url ? n.action_url : '#';
                    html += '<a class="dropdown-item d-flex align-items-start gap-2 py-2" href="' + url + '" data-id="' + n.id + '">';
                    html += '<span class="badge bg-' + colorClass + ' rounded-circle p-1 mt-1"><i class="bi ' + iconClass + '"></i></span>';
                    html += '<div class="flex-grow-1"><div class="fw-semibold small">' + esc(n.title) + '</div>';
                    if (n.message) html += '<div class="text-muted small">' + esc(n.message) + '</div>';
                    html += '<div class="text-muted" style="font-size:0.65rem;">' + n.created_at + '</div></div></a>';
                });
                list.innerHTML = html;
                if (markAllBtn) markAllBtn.style.display = '';
                list.querySelectorAll('a[data-id]').forEach(function(el) {
                    el.addEventListener('click', function() {
                        var id = this.getAttribute('data-id');
                        fetch('/notifications/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content') } });
                    });
                });
            } else {
                list.innerHTML = '<span class="dropdown-item-text text-muted small px-3 py-2 d-block">No notifications</span>';
                if (markAllBtn) markAllBtn.style.display = 'none';
            }
        }).catch(function() {});
    }
    function esc(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('/notifications/mark-all-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content') } }).then(function() { fetchNotifications(); });
        });
    }
    fetchNotifications();
    setInterval(fetchNotifications, 15000);
})();
    </script>
</body>
</html>
