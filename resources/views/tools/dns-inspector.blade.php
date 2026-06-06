@extends('layouts.app')

@section('title', __('DNS records inspector') . ', ' . config('app.name'))
@section('description', __('Bekijk alle DNS-records (A, AAAA, MX, NS, TXT, CNAME, SOA) van een domein in één keer.'))

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-20">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
			<span class="opacity-40">/</span>
			<a href="/{{ app()->getLocale() }}/tools" class="hover:text-white">Tools</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">DNS</span>
		</nav>
		<span class="pill pill-dark mb-5">Tool · {{ __('Gratis') }}</span>
		<h1 class="display-1 mb-5">DNS <span class="accent-word">{{ __('inspector') }}</span></h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			{{ __('Bekijk alle DNS-records (A, AAAA, MX, NS, TXT, CNAME, SOA) van een domein in één overzicht.') }}
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<form method="POST" action="{{ route('tools.dns-inspector.check') }}" class="card space-y-5">
			@csrf
			<div>
				<label for="domain" class="block text-sm font-semibold mb-2">{{ __('Domeinnaam') }}</label>
				<input id="domain" name="domain" type="text" required value="{{ $domain }}" autocomplete="off"
					placeholder="example.com" class="field-input font-mono">
				<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">
					{{ __('Plak een hostname of URL, we strippen automatisch protocol/pad/www.') }}
				</p>
			</div>
			@if ($error)
				<div class="text-sm rounded-[var(--radius-control)] border border-red-200 bg-red-50 text-red-800 p-3">
					{{ $error }}
				</div>
			@endif
			<button type="submit" class="btn-accent">
				{{ __('Records ophalen') }}
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</form>

		@if ($result)
			<div class="mt-8 space-y-6">
				<h2 class="display-3">{{ __('Records voor') }} <code class="font-mono text-[color:var(--color-accent)]">{{ $result['domain'] }}</code></h2>
				@foreach ($result['records'] as $type => $rows)
					<div class="card">
						<div class="flex items-baseline justify-between mb-3">
							<h3 class="font-bold text-lg">{{ $type }}-records</h3>
							<span class="text-xs text-[color:var(--color-ink-muted)]">{{ count($rows) }} {{ count($rows) === 1 ? 'record' : 'records' }}</span>
						</div>
						@if (empty($rows))
							<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Geen records gevonden.') }}</p>
						@else
							<table class="w-full text-sm">
								<tbody>
									@foreach ($rows as $r)
										<tr class="border-b border-[color:var(--color-line)] last:border-0">
											<td class="py-2 pr-4 font-mono text-xs text-[color:var(--color-ink-muted)] align-top whitespace-nowrap">TTL {{ $r['ttl'] ?? '?' }}</td>
											<td class="py-2 font-mono text-xs break-all">
												@switch($type)
													@case('A')
													@case('AAAA')
														{{ $r['ip'] ?? $r['ipv6'] ?? '?' }}
														@break
													@case('MX')
														<span class="text-[color:var(--color-ink-muted)]">prio {{ $r['pri'] ?? '?' }}</span> → {{ $r['target'] ?? '?' }}
														@break
													@case('NS')
													@case('CNAME')
														{{ $r['target'] ?? '?' }}
														@break
													@case('TXT')
														{{ $r['txt'] ?? '' }}
														@break
													@case('SOA')
														mname={{ $r['mname'] ?? '?' }} · rname={{ $r['rname'] ?? '?' }} · serial={{ $r['serial'] ?? '?' }}
														@break
													@default
														{{ json_encode($r) }}
												@endswitch
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						@endif
					</div>
				@endforeach
			</div>

			<div class="mt-10 card bg-[color:var(--color-surface)]">
				<h3 class="font-bold mb-2">{{ __('Iets vreemds gezien?') }}</h3>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-4">
					{{ __('Onbekende MX-records, ontbrekende SPF/DKIM/DMARC, ongebruikte CNAMEs, wij helpen met opschonen en correcte mail-beveiliging.') }}
				</p>
				<a href="/{{ app()->getLocale() }}/diensten/mail-beveiliging-fix" class="btn-accent">
					{{ __('E-mail-beveiliging laten regelen') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			</div>
		@endif
	</div>
</section>

@endsection
