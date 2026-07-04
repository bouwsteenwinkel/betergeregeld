@php
    /** @var \App\Support\ChannelSite $site */
    $tel = preg_replace('/\s+/', '', (string) $site->brand('phone'));
    $cta = $site->navCta();
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
		<a href="{{ $site->url() }}" class="logo">
			@if (! $site->isDemoContext() && $site->brand('footer_logo'))
				<img src="{{ $site->brand('footer_logo') }}" alt="{{ $site->displayName() }}" class="logo-img">
			@elseif (! $site->isDemoContext() && $site->brand('footer_name'))
				{{ $site->brand('footer_name') }}
			@elseif ($site->logoImage())
				<img src="{{ $site->logoImage() }}" alt="{{ $site->name() }}" style="height:34px;display:block">
			@else
				{!! $site->brand('logo_text', $site->name()) !!}
			@endif
		</a>
		<nav class="nav-links">
			@foreach ($site->navMenu() as $item)
				<a href="{{ $site->navHref($item['href'] ?? '') }}">{{ $item['label'] ?? '' }}</a>
			@endforeach
		</nav>
		<div class="nav-actions">
			<a href="{{ $site->navHref($cta['href']) }}" class="btn">{{ $cta['label'] }}</a>
			<button type="button" class="nav-toggle" aria-expanded="false" aria-label="Menu openen" aria-controls="nav-drawer">
				<span class="nav-toggle-bars"><span></span><span></span><span></span></span>
			</button>
		</div>
	</div>

	{{-- Mobiel menu (drawer). Zichtbaar via de burger < 860px. --}}
	<div class="nav-drawer" id="nav-drawer">
		@foreach ($site->navMenu() as $item)
			<a href="{{ $site->navHref($item['href'] ?? '') }}">{{ $item['label'] ?? '' }}</a>
		@endforeach
		<div class="nav-drawer-contact">
			@if ($site->brand('phone'))
				<a href="tel:{{ $tel }}"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>{{ $site->brand('phone') }}</a>
			@endif
			@if ($site->brand('email'))
				<a href="mailto:{{ $site->brand('email') }}"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ $site->brand('email') }}</a>
			@endif
		</div>
		<div class="nav-drawer-cta"><a href="{{ $site->navHref($cta['href']) }}" class="btn" style="width:100%">{{ $cta['label'] }}</a></div>
		{{-- Cookievoorkeuren onderaan het mobiele menu (opent CMP-venster via
		     data-cmp-open-prefs); de zwevende cookie-knop is op mobiel verborgen. --}}
		<button type="button" class="nav-drawer-cookies" data-cmp-open-prefs>
			<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5Z"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/></svg>
			Cookievoorkeuren
		</button>
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
    nav.querySelectorAll('.nav-drawer a, .nav-drawer button').forEach(function (a) {
        a.addEventListener('click', function () { setOpen(false); });
    });
    window.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', function () { if (window.innerWidth >= 860) setOpen(false); });
})();
</script>
