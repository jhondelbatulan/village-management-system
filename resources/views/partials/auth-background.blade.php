{{--
    Background layer stack for the auth screens.

    Every village cover photo gets its own absolutely positioned layer; a tiny
    inline script cross-fades between them (opacity only — no sliding) so the
    photos blend into each other. The first layer is activated server-side, so
    a background still shows even if JavaScript never runs.
--}}
@php
    $backgrounds = collect($villageBackgrounds ?? [asset(config('app_settings.assets.login_background'))])
        ->filter()
        ->values();
@endphp

<div class="auth-background-stack" aria-hidden="true">
    @foreach($backgrounds as $index => $url)
        <div class="auth-background-layer{{ $index === 0 ? ' is-active' : '' }}"
             style="background-image: url('{{ $url }}');"></div>
    @endforeach
</div>
<div class="auth-overlay"></div>

@if($backgrounds->count() > 1)
<script>
    (function () {
        var layers = document.querySelectorAll('.auth-background-layer');
        if (layers.length < 2) return;

        // Respect the OS "reduce motion" setting by keeping a static photo.
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var current = 0;
        setInterval(function () {
            var next = (current + 1) % layers.length;
            layers[current].classList.remove('is-active');
            layers[next].classList.add('is-active');
            current = next;
        }, 6000);
    })();
</script>
@endif
