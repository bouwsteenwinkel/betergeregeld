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

		/* Directe ingang naar de afspraakplanner, op élke pagina. /afspraak bestond al
		   als route maar er linkte nergens iets naartoe: de pagina was alleen te vinden
		   via de preview-CTA. Vandaar hier, en niet als zoveelste tekstlink in de
		   Contact-kolom: dit is de enige plek waar de bezoeker vanaf een blog- of
		   plaatspagina rechtstreeks een gesprek kan inplannen. */
		.foot-cta{display:flex;align-items:center;justify-content:space-between;gap:1.2rem 2rem;flex-wrap:wrap;
			background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.14);border-radius:calc(var(--radius) + 4px);
			padding:1.15rem 1.3rem;margin-bottom:2.2rem}
		.foot-cta-txt strong{display:block;font-size:1.05rem;line-height:1.3}
		.foot-cta-txt span{color:rgba(255,255,255,.75);font-size:.9rem}
		.foot-cta-btn{display:inline-flex;align-items:center;gap:.5rem;flex:0 0 auto;
			background:var(--c-cta);color:var(--c-on-cta);font-weight:700;text-decoration:none;
			padding:.8rem 1.25rem;border-radius:10px;min-height:44px;white-space:nowrap}
		.foot-cta-btn:hover{color:var(--c-on-cta);filter:brightness(1.06)}
		.foot-cta-btn svg{width:18px;height:18px;flex:0 0 auto}
		@media(max-width:560px){.foot-cta{flex-direction:column;align-items:stretch;text-align:left}
			.foot-cta-btn{justify-content:center}}
	</style>
	{{-- Identiteit is context-afhankelijk: op /voorbeeld de demo-mockup (klant),
	     elders óns aanbod. Logo-partial + metaDescription() regelen dat zelf. --}}
	<div class="wrap">
		@unless ($site->isDemoContext())
			<div class="foot-cta">
				<div class="foot-cta-txt">
					<strong>Even sparren over jouw website?</strong>
					<span>Plan een videogesprek van {{ (int) config('scheduling.meeting_minutes', 60) }} minuten. Gratis en vrijblijvend, je kiest zelf het moment.</span>
				</div>
				<a href="{{ $site->url('afspraak') }}" class="foot-cta-btn">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>
					</svg>
					Plan een gesprek
				</a>
			</div>
		@endunless

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
		{{-- Netwerk-regel. Deze kanaalsites linkten tot 02-08-2026 alleen naar zichzelf,
		     waardoor elk domein voor Google een eiland zonder aanbevelingen was. Twee
		     links, geen lijst van zeventien: het moet een eerlijke vermelding zijn en
		     geen linkfarm. Het moederdomein hoort erbij (feitelijk juist: we zijn
		     onderdeel van Betergeregeld) en de generieke bedrijfswebsite is de logische
		     doorverwijzing voor wie niet in dit vak zit. Niet op de demo-context: daar is
		     de bezoeker een prospect die naar een voorbeeld kijkt, geen zoekmachine. --}}
		@unless ($site->isDemoContext())
			@php($bw = \App\Support\ChannelNetwork::find('bedrijfswebsite'))
			<div class="foot-network" style="margin-top:1.1rem;padding-top:.9rem;border-top:1px solid rgba(255,255,255,.12);font-size:.88rem;opacity:.85">
				Onderdeel van <a href="https://betergeregeld.com" style="font-weight:600">Betergeregeld ICT</a>.
				@if ($bw && $site->key !== 'bedrijfswebsite')
					Zit je vak er niet bij? Bekijk <a href="{{ $bw['url'] }}" style="font-weight:600">een bedrijfswebsite laten maken</a>.
				@endif
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
