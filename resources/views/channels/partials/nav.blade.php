@php
    /** @var \App\Support\ChannelSite $site */
    $tel = preg_replace('/\s+/', '', (string) $site->brand('phone'));
@endphp
{{-- Trust-balk: keurmerk-belofte links, directe contactopties rechts. --}}
<div class="topbar">
    <div class="wrap topbar-inner">
        <span class="topbar-trust">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
            {{ $site->brand('trustline') }}
        </span>
        <span class="topbar-contact">
            @if ($site->brand('phone'))
                <a href="tel:{{ $tel }}">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ $site->brand('phone') }}
                </a>
            @endif
            @if ($site->brand('email'))
                <a href="mailto:{{ $site->brand('email') }}">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    {{ $site->brand('email') }}
                </a>
            @endif
        </span>
    </div>
</div>

<header class="nav">
    <div class="wrap nav-inner">
        <a href="{{ $site->url() }}" class="logo">@include('channels.partials.logo')</a>
        <nav class="nav-links">
            <a href="{{ $site->url() }}">Home</a>
            <a href="{{ $site->url('plaatsen') }}">Plaatsen</a>
            <a href="{{ $site->url('blog') }}">Blog</a>
            <a href="{{ $site->url('over-ons') }}">Over ons</a>
        </nav>
        <div class="nav-actions">
            <a href="{{ $site->url() }}#contact" class="btn">Gratis voorbeeld</a>
            <button class="nav-toggle" type="button" aria-label="Menu openen" aria-expanded="false" aria-controls="navDrawer">
                <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>
        </div>
    </div>

    {{-- Mobiel uitklapmenu (< 860px), getoggeld via .nav.open --}}
    <div class="nav-drawer" id="navDrawer">
        <a href="{{ $site->url() }}">Home</a>
        <a href="{{ $site->url('plaatsen') }}">Plaatsen</a>
        <a href="{{ $site->url('blog') }}">Blog</a>
        <a href="{{ $site->url('over-ons') }}">Over ons</a>
        <div class="nav-drawer-contact">
            @if ($site->brand('phone'))
                <a href="tel:{{ $tel }}">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ $site->brand('phone') }}
                </a>
            @endif
            @if ($site->brand('email'))
                <a href="mailto:{{ $site->brand('email') }}">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    {{ $site->brand('email') }}
                </a>
            @endif
        </div>
        <a href="{{ $site->url() }}#contact" class="btn nav-drawer-cta">Gratis voorbeeld aanvragen</a>
    </div>
</header>

<script>
(function () {
    var nav = document.querySelector('header.nav');
    var toggle = nav && nav.querySelector('.nav-toggle');
    if (!nav || !toggle) return;

    function setOpen(open) {
        nav.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Menu sluiten' : 'Menu openen');
    }
    toggle.addEventListener('click', function () { setOpen(!nav.classList.contains('open')); });
    nav.querySelectorAll('.nav-drawer a').forEach(function (a) {
        a.addEventListener('click', function () { setOpen(false); });
    });
    window.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', function () { if (window.innerWidth >= 860) setOpen(false); });
})();
</script>
