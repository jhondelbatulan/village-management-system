<footer class="app-footer">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <span>{{ $branding->get('footer_text', config('app_settings.footer_text')) }}</span>
        <span class="text-muted small">Version {{ config('app_settings.version') }}</span>
    </div>
</footer>
