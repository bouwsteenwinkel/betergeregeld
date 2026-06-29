@php /** @var \App\Support\ChannelSite $site */ @endphp
<footer>
	<div class="wrap">
		<div class="foot-grid">
			<div>
				@if ($site->logoImage())
					<img src="{{ $site->logoImage() }}" alt="{{ $site->name() }}" style="height:42px;margin-bottom:.6rem">
				@else
					<div class="logo" style="color:#fff;margin-bottom:.6rem">{!! $site->brand('logo_text', $site->name()) !!}</div>
				@endif
				<p style="color:rgba(255,255,255,.75);max-width:42ch">
					{{ $site->homeDescription() }}
				</p>
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Menu</h3>
				<p><a href="{{ $site->url() }}">Home</a></p>
				<p><a href="{{ $site->url('plaatsen') }}">Plaatsen</a></p>
				<p><a href="{{ $site->url('blog') }}">Blog</a></p>
				<p><a href="{{ $site->url('over-ons') }}">Over ons</a></p>
			</div>
			<div>
				<h3 style="font-size:1rem;margin-bottom:.6rem">Contact</h3>
				@if ($site->brand('phone'))<p><a href="tel:{{ preg_replace('/\s+/', '', $site->brand('phone')) }}">{{ $site->brand('phone') }}</a></p>@endif
				@if ($site->brand('email'))<p><a href="mailto:{{ $site->brand('email') }}">{{ $site->brand('email') }}</a></p>@endif
				<p style="margin-top:.6rem"><a href="{{ $site->url() }}#contact">Gratis voorbeeld aanvragen</a></p>
			</div>
		</div>
		<div class="foot-bottom">
			<span>© {{ now()->year }} {{ $site->name() }}</span>
			@if ($site->endorsement())
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
