@php /** @var \App\Support\ChannelSite $site */ @endphp
<footer>
	{{-- Identiteit is context-afhankelijk: op /voorbeeld de demo-mockup (klant),
	     elders óns aanbod. Logo-partial + metaDescription() regelen dat zelf. --}}
	<div class="wrap">
		<div class="foot-grid">
			<div>
				<div class="logo" style="color:#fff;margin-bottom:.6rem">@include('channels.partials.logo')</div>
				<p style="color:rgba(255,255,255,.75);max-width:44ch">
					{{ $site->metaDescription() }}
				</p>
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Menu</h3>
				@foreach ($site->navMenu() as $item)
					<p><a href="{{ $site->navHref($item['href'] ?? '') }}">{{ $item['label'] ?? '' }}</a></p>
				@endforeach
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Plaatsen per provincie</h3>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1.2rem">
					@foreach (app(\App\Services\ChannelSiteResolver::class)->provinces() as $p)
						<p style="margin:.15rem 0"><a href="{{ $site->url('plaatsen/provincie/' . $p['slug']) }}" style="font-size:.9rem">{{ $p['name'] }}</a></p>
					@endforeach
				</div>
				<p style="margin-top:.5rem"><a href="{{ $site->url('plaatsen') }}" style="font-size:.9rem;font-weight:600">Alle plaatsen →</a></p>
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Contact</h3>
				@if ($site->brand('phone'))<p><a href="tel:{{ preg_replace('/\s+/', '', $site->brand('phone')) }}">{{ $site->brand('phone') }}</a></p>@endif
				@if ($site->brand('email'))<p><a href="mailto:{{ $site->brand('email') }}">{{ $site->brand('email') }}</a></p>@endif
				<p style="margin-top:.6rem"><a href="{{ $site->navHref('#gratis-voorbeeld') }}">Gratis voorbeeld aanvragen</a></p>
			</div>
		</div>
		<div class="foot-bottom">
			<span>© {{ now()->year }} {{ $site->displayName() }}</span>
			@if ($site->endorsementLogo())
				{{-- Moedermerk-keurmerk als logo-badge (witte chip op de donkere footer). --}}
				<span class="endorsement">
					<span class="endorse-label">Onderdeel van</span>
					@if ($site->endorsementUrl())
						<a class="endorse-badge" href="{{ $site->endorsementUrl() }}" target="_blank" rel="noopener" aria-label="{{ $site->endorsement() ?: 'Groeidiamant' }}">
							<img src="{{ $site->endorsementLogo() }}" alt="{{ $site->endorsement() ?: 'Groeidiamant by Betergeregeld' }}">
						</a>
					@else
						<span class="endorse-badge"><img src="{{ $site->endorsementLogo() }}" alt="{{ $site->endorsement() ?: 'Groeidiamant by Betergeregeld' }}"></span>
					@endif
				</span>
			@elseif ($site->endorsement())
				<span class="endorsement">
					<span class="diamond" aria-hidden="true">◆</span>
					@if ($site->endorsementUrl())
						<a href="{{ $site->endorsementUrl() }}" target="_blank" rel="noopener">{{ $site->endorsement() }}</a>
					@else
						{{ $site->endorsement() }}
					@endif
				</span>
			@endif
		</div>
	</div>
</footer>
