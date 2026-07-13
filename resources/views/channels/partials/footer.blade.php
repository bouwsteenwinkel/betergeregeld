@php /** @var \App\Support\ChannelSite $site */ @endphp
<footer>
	<style>
		/* Merk-rij: eigen logo links + Groeidiamant rechts als witte kaarten met
		   gelijke hoogte. Desktop compact links, mobiel vol over 2 kolommen. */
		.foot-brand{display:flex;gap:.7rem;align-items:stretch;margin-bottom:1rem;max-width:340px}
		.foot-brand .fb-card{flex:1 1 50%;min-width:0;display:flex;align-items:center;justify-content:center;background:#fff;border-radius:12px;min-height:60px;padding:.55rem .65rem}
		.foot-brand .fb-card img{max-width:100%;max-height:42px;width:auto;height:auto;display:block}
		.foot-brand .fb-card.fb-gd{padding:.25rem .35rem}
		.foot-brand .fb-card.fb-gd img{max-height:56px}
		.foot-brand .fb-name{color:var(--c-ink);font-weight:800;font-size:1rem;text-align:center;line-height:1.2}
		@media(max-width:760px){.foot-brand{max-width:none;margin-bottom:.9rem}}
		/* Demo/preview: geen provincie-kolom, dus 3 gebalanceerde kolommen + wat lucht. */
		@media(min-width:760px){.foot-grid-demo{grid-template-columns:2fr 1fr 1.4fr;gap:2.4rem}}
		.foot-grid-demo h3{margin-bottom:.8rem}
		.foot-grid-demo .foot-menu a{display:block;padding:.28rem 0}
	</style>
	{{-- Identiteit is context-afhankelijk: op /voorbeeld de demo-mockup (klant),
	     elders óns aanbod. Logo-partial + metaDescription() regelen dat zelf. --}}
	<div class="wrap">
		<div class="foot-grid {{ $site->isDemoContext() ? 'foot-grid-demo' : '' }}">
			<div>
				<div class="foot-brand">
					<a href="{{ $site->url() }}" class="fb-card">
						@if (! $site->isDemoContext() && $site->brand('footer_logo'))
							<img src="{{ $site->brand('footer_logo') }}" alt="{{ $site->displayName() }}">
						@elseif ($site->logoImage())
							<img src="{{ $site->logoImage() }}" alt="{{ $site->name() }}">
						@else
							<span class="fb-name">{{ $site->displayName() }}</span>
						@endif
					</a>
					@unless ($site->isDemoContext())
						<a href="{{ $site->url('groeidiamant') }}" class="fb-card fb-gd" aria-label="De Groeidiamant by Betergeregeld">
							<img src="{{ asset('channel-media/_brand/groeidiamant.jpg') }}" alt="Groeidiamant by Betergeregeld">
						</a>
					@endunless
				</div>
				<p style="color:rgba(255,255,255,.75);max-width:44ch">
					{{ $site->metaDescription() }}
				</p>
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Menu</h3>
				<div class="foot-menu" style="display:grid;grid-template-columns:{{ $site->isDemoContext() ? '1fr' : '1fr 1fr' }};gap:0 1.2rem">
					@foreach ($site->navMenu() as $item)
						<p style="margin:.15rem 0"><a href="{{ $site->navHref($item['href'] ?? '') }}">{{ $item['label'] ?? '' }}</a></p>
					@endforeach
				</div>
			</div>
			@unless ($site->isDemoContext())
				<div>
					<h3 style="font-size:1rem;margin-bottom:.6rem">Plaatsen per provincie</h3>
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1.2rem">
						@foreach (app(\App\Services\ChannelSiteResolver::class)->provinces() as $p)
							<p style="margin:.15rem 0"><a href="{{ $site->url('plaatsen/provincie/' . $p['slug']) }}" style="font-size:.9rem">{{ $p['name'] }}</a></p>
						@endforeach
					</div>
					<p style="margin-top:.5rem"><a href="{{ $site->url('plaatsen') }}" style="font-size:.9rem;font-weight:600">Alle plaatsen →</a></p>
				</div>
			@endunless
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem"><a href="{{ $site->url('contact') }}" style="color:inherit">Contact</a></h3>
				@if ($site->brand('phone'))<p><a href="tel:{{ preg_replace('/\s+/', '', $site->brand('phone')) }}">{{ $site->brand('phone') }}</a></p>@endif
				@if ($site->brand('email'))<p><a href="mailto:{{ $site->brand('email') }}">{{ $site->brand('email') }}</a></p>@endif
				@unless ($site->isDemoContext())
					{{-- Op mobiel verborgen: de sticky-CTA onderaan dekt dit al. --}}
					<p class="foot-cta-link" style="margin-top:.6rem"><a href="{{ $site->navHref('#gratis-voorbeeld') }}">Gratis voorbeeld aanvragen</a></p>
				@endunless
			</div>
		</div>
		@unless ($site->isDemoContext())
			<div class="foot-links">
				<a href="{{ $site->url('cases') }}">Cases</a>
				<a href="{{ $site->url('werkwijze') }}">Onze werkwijze</a>
				<a href="{{ $site->url('veelgestelde-vragen') }}">Veelgestelde vragen</a>
				<a href="{{ $site->url('vergelijken') }}">Zelf bouwen of laten maken</a>
				<a href="{{ $site->url('groeidiamant') }}">De Groeidiamant</a>
				<a href="{{ $site->url('contact') }}">Contact</a>
			</div>
		@endunless
		<div class="foot-bottom">
			<span>© {{ now()->year }} {{ $site->displayName() }}</span>
			<span class="foot-legal">
				<a href="{{ $site->url('privacybeleid') }}">Privacy</a>
				<a href="{{ $site->url('cookiebeleid') }}">Cookies</a>
				<a href="{{ $site->url('algemene-voorwaarden') }}">Voorwaarden</a>
			</span>
		</div>
	</div>
</footer>
