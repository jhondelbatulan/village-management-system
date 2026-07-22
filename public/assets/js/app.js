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
