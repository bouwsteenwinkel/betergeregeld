@extends('layouts.app')

@section('title', __('Wat is mijn IP') . ' — ' . config('app.name'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Tools</span>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">IP lookup</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">{{ __('Wat is') }} <span class="accent-word">{{ __('mijn IP?') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Zie het IP-adres waarmee je het web bezoekt, inclusief geografie, provider en risicoprofiel.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		@include('tools._usage')

		<div class="card">
			{{-- Main IP panel --}}
			<div class="flex items-center gap-3 flex-wrap mb-6">
				<code class="font-mono text-2xl font-bold">{{ $result['ip'] }}</code>
				@if ($result['ip_version'])
					<span class="pill pill-ink uppercase text-[10px]">{{ $result['ip_version'] }}</span>
				@endif
				@if ($result['is_private'])
					<span class="pill" style="background:rgba(234,179,8,0.12);color:#92400e;border:1px solid rgba(234,179,8,0.3)">
						{{ __('Privé-IP') }}
					</span>
				@elseif ($result['is_valid'])
					<span class="pill pill-teal">{{ __('Publiek IP') }}</span>
				@endif
				@if ($result['network']['is_datacenter'])
					<span class="pill" style="background:rgba(139,92,246,0.12);color:#6d28d9;border:1px solid rgba(139,92,246,0.3)">
						{{ __('Datacenter') }}
					</span>
				@elseif ($result['network']['is_mobile'])
					<span class="pill" style="background:rgba(59,130,246,0.12);color:#1d4ed8;border:1px solid rgba(59,130,246,0.3)">
						{{ __('Mobiel') }}
					</span>
				@endif
			</div>

			@if (! $result['geo_available'])
				<div class="rounded-[var(--radius-control)] border border-amber-200 bg-amber-50 text-amber-900 p-3 text-sm mb-5">
					{{ __('GeoLite2-database niet geladen — geo- en ASN-info zijn niet beschikbaar.') }}
				</div>
			@endif

			<dl class="grid grid-cols-1 sm:grid-cols-[10rem_1fr] gap-x-6 gap-y-2.5 text-sm">
				@if ($result['reverse_dns'])
					<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Reverse DNS') }}</dt>
					<dd class="font-mono text-[color:var(--color-ink)]">{{ $result['reverse_dns'] }}</dd>
				@endif

				@if ($result['geo']['country_code'])
					<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Land') }}</dt>
					<dd class="text-[color:var(--color-ink)]">
						{{ $result['geo']['country_name'] ?? $result['geo']['country_code'] }}
						<span class="text-[color:var(--color-ink-muted)] ml-1">({{ $result['geo']['country_code'] }})</span>
					</dd>
				@endif

				@if ($result['geo']['region_code'] || $result['geo']['city'])
					<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Regio/stad') }}</dt>
					<dd class="text-[color:var(--color-ink)]">
						{{ collect([$result['geo']['city'], $result['geo']['region_code']])->filter()->join(' · ') }}
						@if ($result['geo']['postal'])
							<span class="text-[color:var(--color-ink-muted)]">· {{ $result['geo']['postal'] }}</span>
						@endif
					</dd>
				@endif

				@if ($result['geo']['timezone'])
					<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Tijdzone') }}</dt>
					<dd class="text-[color:var(--color-ink)]">{{ $result['geo']['timezone'] }}</dd>
				@endif

				<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Type netwerk') }}</dt>
				<dd class="text-[color:var(--color-ink)]">{{ __('iplookup.network.' . $result['network']['label']) }}</dd>

				<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Provider (ISP)') }}</dt>
				<dd class="text-[color:var(--color-ink)]">
					@if ($result['isp']['org'])
						@if ($result['asn_detail_visible'])
							<span>{{ $result['isp']['org'] }}</span>
							@if ($result['isp']['asn'])
								<span class="text-[color:var(--color-ink-muted)] font-mono ml-2">AS{{ $result['isp']['asn'] }}</span>
							@endif
						@else
							<span>{{ $result['isp']['org'] }}</span>
							<span class="ml-2 text-xs text-[color:var(--color-ink-muted)]">— <a href="{{ route('pricing') }}" class="underline hover:text-[color:var(--color-ink)]">{{ __('ASN-detail op Pro') }}</a></span>
						@endif
					@else
						<span class="text-[color:var(--color-ink-soft)]">—</span>
					@endif
				</dd>

				<dt class="font-semibold text-[color:var(--color-ink-muted)]">{{ __('Score') }}</dt>
				<dd class="text-[color:var(--color-ink)]">
					<span class="font-bold">{{ $result['score']['value'] }}/100</span>
					<span class="text-[color:var(--color-ink-muted)] ml-1">· {{ __('iplookup.score.' . $result['score']['label']) }}</span>
				</dd>
			</dl>

			<p class="text-xs text-[color:var(--color-ink-soft)] mt-6 pt-5 border-t border-[color:var(--color-line)] leading-relaxed">
				{{ __('iplookup.disclaimer') }}
			</p>
		</div>
	</div>
</section>

@endsection
