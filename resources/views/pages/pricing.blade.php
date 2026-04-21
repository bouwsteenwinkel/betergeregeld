@extends('layouts.app')

@section('title', __('Prijzen') . ' — ' . config('app.name'))
@section('description', __('Eenvoudige, transparante prijzen voor alle tools. Start gratis, upgrade als je meer nodig hebt.'))

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';

	$planCopy = [
		'tools_free' => [
			'tagline_nl' => 'Voor af en toe gebruik en om te proberen.',
			'tagline_en' => 'For occasional use and to try things out.',
			'highlights_nl' => [
				'Alle tools toegankelijk zonder account',
				'Beperkte daglimieten per tool',
				'Geschiedenis 7 dagen (met account)',
				'Community support',
			],
			'highlights_en' => [
				'All tools accessible without an account',
				'Limited daily quota per tool',
				'History kept 7 days (with account)',
				'Community support',
			],
		],
		'tools_pro' => [
			'badge_nl' => 'Populair',
			'badge_en' => 'Popular',
			'tagline_nl' => 'Voor ZZP en individuele professionals.',
			'tagline_en' => 'For freelancers and individual professionals.',
			'highlights_nl' => [
				'Ruime daglimieten — 1000 checks/dag',
				'IBAN risk-details en VIES historie',
				'PDF merge 50 files, 500 MB',
				'Geschiedenis 90 dagen',
				'E-mail support (2 werkdagen)',
			],
			'highlights_en' => [
				'High daily limits — 1000 checks/day',
				'IBAN risk details and VIES history',
				'PDF merge 50 files, 500 MB',
				'90-day history',
				'Email support (2 working days)',
			],
		],
		'tools_business' => [
			'tagline_nl' => 'Voor teams en MKB.',
			'tagline_en' => 'For teams and SMB.',
			'highlights_nl' => [
				'Onbeperkte checks op alle tools',
				'Tot 5 teamleden',
				'Bulk CSV-upload + API-toegang',
				'Audit-log voor redact + compliance',
				'E-mail support (1 werkdag)',
			],
			'highlights_en' => [
				'Unlimited checks across all tools',
				'Up to 5 team members',
				'Bulk CSV upload + API access',
				'Audit log for redact + compliance',
				'Email support (1 working day)',
			],
		],
	];

	$fmt = fn ($value, $key) => match (true) {
		strtolower((string) $value) === 'unlimited' => $isEn ? 'Unlimited' : 'Onbeperkt',
		$value === '0' || $value === 0 || $value === null || $value === '' => '—',
		$value === '1' || $value === 1 || $value === true => '✓',
		default => (string) $value,
	};

	$compareSections = [
		[
			'title_nl' => 'Daglimieten',
			'title_en' => 'Daily quotas',
			'rows' => [
				['nl' => 'IBAN-check', 'en' => 'IBAN check', 'key' => 'tool.iban.daily.user', 'anon' => 'tool.iban.daily.anon'],
				['nl' => 'VIES BTW-check', 'en' => 'VIES VAT check', 'key' => 'tool.vies.daily.user', 'anon' => 'tool.vies.daily.anon'],
				['nl' => 'Postcode lookup', 'en' => 'Postcode lookup', 'key' => 'tool.postcode.daily.user', 'anon' => 'tool.postcode.daily.anon'],
				['nl' => 'IP lookup', 'en' => 'IP lookup', 'key' => 'tool.ip_lookup.daily.user', 'anon' => 'tool.ip_lookup.daily.anon'],
				['nl' => 'Favicon generator', 'en' => 'Favicon generator', 'key' => 'tool.favicon.daily.user', 'anon' => 'tool.favicon.daily.anon'],
				['nl' => 'Speedtest', 'en' => 'Speedtest', 'key' => 'tool.speedtest.daily.user', 'anon' => 'tool.speedtest.daily.anon'],
			],
		],
		[
			'title_nl' => 'IBAN + VIES',
			'title_en' => 'IBAN + VIES',
			'rows' => [
				['nl' => 'IBAN risico-details zichtbaar', 'en' => 'IBAN risk details visible', 'key' => 'tool.iban.risk_detail'],
				['nl' => 'IBAN bulk (CSV upload)', 'en' => 'IBAN bulk (CSV upload)', 'key' => 'tool.iban.bulk'],
				['nl' => 'VIES historie bewaartermijn', 'en' => 'VIES history retention', 'key' => 'tool.vies.history_days'],
				['nl' => 'VIES bulk (CSV upload)', 'en' => 'VIES bulk (CSV upload)', 'key' => 'tool.vies.bulk'],
			],
		],
		[
			'title_nl' => 'PDF & documenten',
			'title_en' => 'PDF & documents',
			'rows' => [
				['nl' => 'PDF merge max. aantal files', 'en' => 'PDF merge max. files', 'key' => 'tool.pdf_merge.max_files'],
				['nl' => 'PDF merge max. grootte (MB)', 'en' => 'PDF merge max. size (MB)', 'key' => 'tool.pdf_merge.max_size_mb'],
				['nl' => 'Geen watermerk op PDF merge', 'en' => 'Watermark-free PDF merge', 'key' => 'tool.pdf_merge.watermark', 'invert' => true],
				['nl' => 'PDF merge met OCR', 'en' => 'PDF merge with OCR', 'key' => 'tool.pdf_merge.ocr'],
				['nl' => 'PDF redact', 'en' => 'PDF redact', 'key' => 'tool.pdf_redact.enabled'],
				['nl' => 'PDF redact — pattern mode', 'en' => 'PDF redact — pattern mode', 'key' => 'tool.pdf_redact.pattern_mode'],
			],
		],
		[
			'title_nl' => 'Loss-tool',
			'title_en' => 'Loss-tool',
			'rows' => [
				['nl' => 'Actieve cases', 'en' => 'Active cases', 'key' => 'tool.loss.active_cases'],
				['nl' => 'Team-weergave', 'en' => 'Team view', 'key' => 'tool.loss.team_view'],
			],
		],
		[
			'title_nl' => 'Team & platform',
			'title_en' => 'Team & platform',
			'rows' => [
				['nl' => 'Aantal zitplaatsen', 'en' => 'Seats', 'key' => 'seats'],
				['nl' => 'API-toegang', 'en' => 'API access', 'key' => 'api.enabled'],
				['nl' => 'Geschiedenis bewaartermijn (dagen)', 'en' => 'History retention (days)', 'key' => 'history.retention_days'],
			],
		],
	];

	$faq = [
		['q_nl' => 'Kan ik gratis beginnen?', 'q_en' => 'Can I start for free?',
		 'a_nl' => 'Ja. Zonder account kun je de meeste tools direct gebruiken tot een daglimiet. Met een gratis account krijg je hogere limieten en 7 dagen geschiedenis.',
		 'a_en' => 'Yes. Without an account most tools work right away up to a daily cap. A free account raises the limits and keeps 7 days of history.'],
		['q_nl' => 'Wat kost jaarlijks betalen?', 'q_en' => 'What about annual billing?',
		 'a_nl' => 'Bij jaarlijks betalen krijg je effectief twee maanden korting (€120 i.p.v. €144 voor Pro; €390 i.p.v. €468 voor Business).',
		 'a_en' => 'Annual billing effectively gives you two months off (€120 instead of €144 for Pro; €390 instead of €468 for Business).'],
		['q_nl' => 'Kan ik downgraden of opzeggen?', 'q_en' => 'Can I downgrade or cancel?',
		 'a_nl' => 'Altijd. Je kunt in 1 klik opzeggen vanuit je dashboard. Data blijft 90 dagen beschikbaar bij downgrade naar Free.',
		 'a_en' => 'Always. Cancel in one click from your dashboard. On downgrade to Free your data stays available for 90 days.'],
		['q_nl' => 'Is de 14-daagse proef vrijblijvend?', 'q_en' => 'Is the 14-day trial commitment-free?',
		 'a_nl' => 'Ja. Je hoeft geen creditcard op te geven om te beginnen. Na 14 dagen val je automatisch terug op Free, tenzij je zelf upgradet.',
		 'a_en' => 'Yes. No credit card needed to start. After 14 days you automatically drop back to Free unless you upgrade yourself.'],
		['q_nl' => 'Welke betaalmethodes worden ondersteund?', 'q_en' => 'Which payment methods are supported?',
		 'a_nl' => 'iDEAL, creditcard en SEPA-incasso via Mollie (NL). Factuur op verzoek voor jaarabonnementen.',
		 'a_en' => 'iDEAL, credit card and SEPA direct debit via Mollie (NL). Invoice on request for annual plans.'],
		['q_nl' => 'Hoe werkt API-toegang?', 'q_en' => 'How does API access work?',
		 'a_nl' => 'Business klanten krijgen tenant-keys voor de beschikbare tool-endpoints, met hetzelfde daglimiet als de web-versie (onbeperkt).',
		 'a_en' => 'Business customers get tenant keys for the available tool endpoints, with the same daily quota as the web version (unlimited).'],
	];
@endphp

@section('content')

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1200px] mx-auto px-6 py-20 sm:py-24 text-center">
		<span class="pill pill-dark mb-6">
			<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
			{{ __('Transparante prijzen') }}
		</span>
		<h1 class="display-1 mb-5">
			{{ __('Eenvoudige prijzen.') }}<br>
			<span class="accent-word">{{ __('Geen verrassingen.') }}</span>
		</h1>
		<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl mx-auto">
			{{ __('Begin gratis. Upgrade als je meer volume, advanced features of team-toegang nodig hebt. Maandelijks opzegbaar, geen verborgen kosten.') }}
		</p>
	</div>
</section>

{{-- ============ PRICING CARDS ============ --}}
<section class="py-16 -mt-16 relative z-10">
	<div class="max-w-[1200px] mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-5">
		@foreach ($plans as $plan)
			@php
				$copy = $planCopy[$plan['key']] ?? [];
				$isPro = $plan['key'] === 'tools_pro';
				$isFree = $plan['key'] === 'tools_free';
				$isCurrent = $currentPlanKey === $plan['key'];
				$highlights = $copy[$isEn ? 'highlights_en' : 'highlights_nl'] ?? [];
				$tagline = $copy[$isEn ? 'tagline_en' : 'tagline_nl'] ?? '';
				$badge = $copy[$isEn ? 'badge_en' : 'badge_nl'] ?? null;
				$yearlyEq = $plan['price_yearly'] > 0 ? round($plan['price_yearly'] / 12, 0) : 0;
			@endphp
			<div class="card flex flex-col {{ $isPro ? 'ring-2 ring-[color:var(--color-accent)] relative' : '' }}">
				@if ($badge)
					<span class="absolute -top-3 left-6 pill pill-teal">{{ $badge }}</span>
				@endif
				@if ($isCurrent)
					<span class="absolute -top-3 right-6 pill pill-ink">{{ __('Je huidige plan') }}</span>
				@endif

				<h2 class="text-2xl font-bold mb-1">{{ $plan['name'] }}</h2>
				<p class="text-sm text-[color:var(--color-ink-muted)] mb-5 min-h-[2.5rem]">{{ $tagline }}</p>

				<div class="mb-5">
					@if ($plan['price_monthly'] == 0)
						<div class="flex items-baseline gap-1">
							<span class="text-4xl font-bold">€0</span>
							<span class="text-sm text-[color:var(--color-ink-muted)]">/ {{ __('altijd') }}</span>
						</div>
						<p class="text-xs text-[color:var(--color-ink-soft)] mt-1">&nbsp;</p>
					@else
						<div class="flex items-baseline gap-1">
							<span class="text-4xl font-bold">€{{ (int) $plan['price_monthly'] }}</span>
							<span class="text-sm text-[color:var(--color-ink-muted)]">/ {{ __('maand') }}</span>
						</div>
						<p class="text-xs text-[color:var(--color-ink-soft)] mt-1">
							{{ __('of €:y/jaar — effectief €:m/mnd', ['y' => (int) $plan['price_yearly'], 'm' => $yearlyEq]) }}
						</p>
					@endif
				</div>

				<ul class="space-y-2.5 mb-6 text-sm">
					@foreach ($highlights as $h)
						<li class="flex items-start gap-2.5">
							<svg class="w-4 h-4 mt-0.5 shrink-0 text-[color:var(--color-accent)]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<span class="leading-snug">{{ $h }}</span>
						</li>
					@endforeach
				</ul>

				<div class="mt-auto pt-2">
					@if ($isCurrent)
						<button disabled class="w-full text-center py-2.5 px-4 rounded-[var(--radius-control)] border border-[color:var(--color-line)] text-sm text-[color:var(--color-ink-muted)] cursor-default">
							{{ __('Je huidige plan') }}
						</button>
					@elseif ($isFree)
						@auth
							<button disabled class="w-full text-center py-2.5 px-4 rounded-[var(--radius-control)] border border-[color:var(--color-line)] text-sm text-[color:var(--color-ink-muted)] cursor-default">
								{{ __('Standaard plan') }}
							</button>
						@else
							<a href="/{{ $locale }}/register" class="btn-accent w-full justify-center text-sm">
								{{ __('Gratis account') }}
							</a>
						@endauth
					@else
						@auth
							<a href="/{{ $locale }}/contact?plan={{ $plan['key'] }}" class="{{ $isPro ? 'btn-accent' : 'btn-dark' }} w-full justify-center text-sm">
								{{ $plan['trial_days'] > 0 ? __('Start :n-daagse proef', ['n' => $plan['trial_days']]) : __('Upgrade') }}
							</a>
						@else
							<a href="/{{ $locale }}/register?plan={{ $plan['key'] }}" class="{{ $isPro ? 'btn-accent' : 'btn-dark' }} w-full justify-center text-sm">
								{{ $plan['trial_days'] > 0 ? __('Start :n-daagse proef', ['n' => $plan['trial_days']]) : __('Kies :name', ['name' => $plan['name']]) }}
							</a>
						@endauth
						@if ($plan['trial_days'] > 0)
							<p class="text-xs text-[color:var(--color-ink-soft)] text-center mt-2">{{ __('Geen creditcard nodig') }}</p>
						@endif
					@endif
				</div>
			</div>
		@endforeach
	</div>
</section>

{{-- ============ COMPARE TABLE ============ --}}
<section class="py-16 bg-[color:var(--color-surface-soft,#fafafa)]">
	<div class="max-w-[1200px] mx-auto px-6">
		<div class="text-center mb-10">
			<h2 class="text-3xl font-bold mb-3">{{ __('Volledige vergelijking') }}</h2>
			<p class="text-[color:var(--color-ink-muted)]">{{ __('Elk detail, per plan.') }}</p>
		</div>

		<div class="overflow-x-auto">
			<table class="w-full text-sm border-collapse">
				<thead>
					<tr class="border-b-2 border-[color:var(--color-line)]">
						<th class="text-left py-3 pr-4 font-semibold">{{ __('Feature') }}</th>
						@foreach ($plans as $plan)
							<th class="text-center py-3 px-3 font-semibold {{ $plan['key'] === 'tools_pro' ? 'bg-[color:var(--color-accent)]/5' : '' }}">
								{{ $plan['name'] }}
							</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($compareSections as $section)
						<tr>
							<td colspan="{{ count($plans) + 1 }}" class="pt-6 pb-2 text-xs font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)]">
								{{ $section[$isEn ? 'title_en' : 'title_nl'] }}
							</td>
						</tr>
						@foreach ($section['rows'] as $row)
							<tr class="border-b border-[color:var(--color-line)]/60">
								<td class="py-3 pr-4">
									{{ $row[$isEn ? 'en' : 'nl'] }}
								</td>
								@foreach ($plans as $plan)
									@php
										$raw = $plan['features'][$row['key']] ?? null;
										// Fall back to the `anon` key if user key is missing and we have it defined
										if ($raw === null && ! empty($row['anon'])) {
											$raw = $plan['features'][$row['anon']] ?? null;
										}
										$cell = $fmt($raw, $row['key']);
										// 'invert' means 0 is a positive (e.g. "watermark = 0" → watermark-free = ✓)
										if (($row['invert'] ?? false) && in_array($raw, ['0', 0, null, ''], true)) {
											$cell = '✓';
										} elseif (($row['invert'] ?? false) && in_array($raw, ['1', 1, true], true)) {
											$cell = '—';
										}
									@endphp
									<td class="py-3 px-3 text-center {{ $plan['key'] === 'tools_pro' ? 'bg-[color:var(--color-accent)]/5' : '' }}">
										<span class="{{ $cell === '—' ? 'text-[color:var(--color-ink-soft)]' : ($cell === '✓' ? 'text-[color:var(--color-accent)] font-bold' : 'font-medium') }}">
											{{ $cell }}
										</span>
									</td>
								@endforeach
							</tr>
						@endforeach
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</section>

{{-- ============ FAQ ============ --}}
<section class="py-16">
	<div class="max-w-[900px] mx-auto px-6">
		<h2 class="text-3xl font-bold text-center mb-10">{{ __('Veelgestelde vragen') }}</h2>
		<div class="space-y-3">
			@foreach ($faq as $item)
				<details class="card group">
					<summary class="cursor-pointer list-none flex items-center justify-between gap-4">
						<span class="font-semibold">{{ $item[$isEn ? 'q_en' : 'q_nl'] }}</span>
						<svg class="w-4 h-4 shrink-0 transition-transform group-open:rotate-180" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5l3 3 3-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</summary>
					<p class="mt-3 text-sm text-[color:var(--color-ink-muted)] leading-relaxed">
						{{ $item[$isEn ? 'a_en' : 'a_nl'] }}
					</p>
				</details>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ FINAL CTA ============ --}}
<section class="section-dark py-20">
	<div class="max-w-[800px] mx-auto px-6 text-center">
		<h2 class="display-1 mb-5">
			{{ __('Klaar om') }} <span class="accent-word">{{ __('te beginnen') }}</span>?
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] mb-8 leading-relaxed">
			{{ __('Maak een gratis account aan en gebruik alle tools met hogere limieten. Upgraden kan altijd, opzeggen ook.') }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			@auth
				<a href="/{{ $locale }}/contact?plan=tools_pro" class="btn-accent">
					{{ __('Upgrade naar Pro') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
			@else
				<a href="/{{ $locale }}/register" class="btn-accent">
					{{ __('Gratis account aanmaken') }}
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
				<a href="/{{ $locale }}/login" class="btn-ghost-light">
					{{ __('Ik heb al een account') }}
				</a>
			@endauth
		</div>
	</div>
</section>

@endsection
