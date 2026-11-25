/**
 * Multi-Village Management System — Dashboard JavaScript
 * Sidebar toggle, responsive navigation, and UI helpers.
 */

(function () {
    'use strict';

    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    /**
     * Toggle sidebar visibility on mobile devices.
     */
    function toggleSidebar() {
        sidebar?.classList.toggle('show');
        overlay?.classList.toggle('show');
        document.body.style.overflow = sidebar?.classList.contains('show') ? 'hidden' : '';
    }

    /**
     * Close sidebar when overlay is clicked.
     */
    function closeSidebar() {
        sidebar?.classList.remove('show');
        overlay?.classList.remove('show');
        document.body.style.overflow = '';
    }

    /**
     * Auto-close sidebar on window resize to desktop.
     */
    function handleResize() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    }

    /**
     * Update current date display in navbar.
     */
    function updateCurrentDate() {
        const dateEl = document.getElementById('currentDate');
        if (!dateEl) return;

        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.textContent = new Date().toLocaleDateString('en-US', options);
    }

    /**
     * Update live clock display in navbar.
     */
    function updateCurrentTime() {
        const timeEl = document.getElementById('currentTime');
        if (!timeEl) return;

        const now = new Date();
        const hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hours12 = hours % 12 || 12;

        timeEl.textContent = `${hours12}:${minutes}:${seconds} ${ampm}`;
    }

    toggleBtn?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', closeSidebar);
    window.addEventListener('resize', handleResize);

    /* Desktop: collapse the sidebar to icons only (session-level toggle) */
    const collapseBtn = document.getElementById('sidebarCollapse');
    collapseBtn?.addEventListener('click', function () {
        document.body.classList.toggle('sidebar-collapsed');
        const collapsed = document.body.classList.contains('sidebar-collapsed');
        collapseBtn.querySelector('i')?.classList.toggle('bi-layout-sidebar', !collapsed);
        collapseBtn.querySelector('i')?.classList.toggle('bi-layout-sidebar-inset', collapsed);
        // Let embedded maps re-measure after the width transition.
        setTimeout(function () {
            window.dispatchEvent(new Event('resize'));
        }, 350);
    });

    /**
     * Dashboard "Show more details" toggle. One button reveals every
     * secondary analytics region (`.dash-detail`) at once so the default
     * view stays focused on the essential cards only.
     */
    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('[data-toggle-dash-details]') : null;
        if (!btn) return;

        const regions = document.querySelectorAll('.dash-detail');
        if (!regions.length) return;

        const expand = btn.getAttribute('aria-expanded') !== 'true';
        regions.forEach(function (region) {
            const instance = bootstrap.Collapse.getOrCreateInstance(region, { toggle: false });
            if (expand) instance.show();
            else instance.hide();
        });

        btn.setAttribute('aria-expanded', expand ? 'true' : 'false');
        const label = btn.querySelector('[data-dash-detail-label]');
        if (label) label.textContent = expand ? 'Hide details' : 'Show more details';
        const icon = btn.querySelector('[data-dash-detail-icon]');
        if (icon) icon.className = expand ? 'bi bi-chevron-up me-1' : 'bi bi-chevron-down me-1';

        // Charts inside the revealed regions need a moment to re-measure.
        setTimeout(function () {
            window.dispatchEvent(new Event('resize'));
        }, 350);
    });

    /**
     * Small online/offline status indicator in the navbar. GIS pages listen
     * for the `mvms:connection` event to swap to saved offline map tiles.
     */
    function updateConnectionStatus() {
        const el = document.getElementById('connectionStatus');
        const online = navigator.onLine !== false;
        if (el) {
            el.dataset.state = online ? 'online' : 'offline';
            const text = el.querySelector('.conn-text');
            if (text) text.textContent = online ? 'Online' : 'Offline';
            el.setAttribute(
                'title',
                online
                    ? 'Online — live map imagery available'
                    : 'Offline — saved map tiles are being used'
            );
        }
        document.dispatchEvent(new CustomEvent('mvms:connection', { detail: { online: online } }));
    }

    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    updateConnectionStatus();
    window.mvmsConnectionStatus = updateConnectionStatus;

    updateCurrentDate();
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    /* Auto-dismiss alerts after 5 seconds */
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });
})();
