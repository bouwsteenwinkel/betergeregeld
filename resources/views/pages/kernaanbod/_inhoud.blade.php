{{--
	Gedeelde opbouw van de pagina's voor het kernaanbod: maatwerk, klantportaal,
	koppelingen en automatisering. Elke pagina levert alleen $p aan (zie de
	bestanden naast dit bestand); de opmaak volgt pages/ai.blade.php.

	WAAROM ER EEN APARTE SET IS. Tot 11-09-2026 verkocht de homepage "maatwerk
	webapplicaties, klantportalen, API-koppelingen en procesautomatisering", maar
	bestond er voor geen van die vier een pagina: de dertien diensten in
	config/services_catalog.php gaan allemaal over websites en beveiliging. In
	Search Console haalden de dienstenpagina's samen 45 vertoningen in 90 dagen.

	ALLEEN NEDERLANDS. De route stuurt /en/... door naar /nl/... en de pagina geeft
	$hreflangLocales = ['nl'] mee, zodat de layout geen Engelse versie belooft.

	PRAKTIJKVOORBEELDEN. Alles wat daar staat is terug te vinden in code of commits.
	Een voorbeeld met 'toon' => false wacht op akkoord van de klant en wordt niet
	getoond.
--}}
@php
	// Hoofdknop: zelf een moment kiezen. Tweede knop: eerst een bericht sturen.
	$afspraak = '/nl/afspraak?onderwerp=' . $p['topic'];
	$contact = '/nl/contact?topic=' . $p['topic'];
	$praktijk = array_values(array_filter($p['praktijk'] ?? [], fn ($c) => $c['toon'] ?? true));
@endphp

{{-- ============ HERO ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-60"></div>
	<div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1400px] mx-auto px-6 pt-16 pb-24 sm:py-28">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-8 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<a href="/nl/diensten" class="hover:text-white">Diensten</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">{{ $p['kruimel'] }}</span>
		</nav>

		<div class="max-w-4xl">
			<span class="pill pill-dark mb-6 max-w-full">
				<span class="w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)]"></span>
				{{ $p['pill'] }}
			</span>
			<h1 class="display-1 mb-6">
				{{ $p['h1a'] }}
				<span class="accent-word">{{ $p['h1b'] }}</span>
			</h1>
			<p class="text-lg sm:text-xl text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-3xl mb-8">{{ $p['lead'] }}</p>

			<div class="flex flex-wrap gap-3">
				<a href="{{ $afspraak }}" class="btn-accent">
					Plan een gratis adviesgesprek
					<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
				<a href="{{ $contact }}" class="btn-ghost-light">Liever eerst een bericht</a>
			</div>
		</div>
	</div>
</section>

{{-- ============ HERKENT U DIT ============ --}}
<section class="py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 lg:gap-14">
			<div class="lg:col-span-5">
				<span class="pill pill-ink mb-4">Herkent u dit?</span>
				<h2 class="display-2 mb-6">{{ $p['herken_titel'] }}</h2>
				@foreach ($p['intro'] as $alinea)
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">{{ $alinea }}</p>
				@endforeach
			</div>
			<div class="lg:col-span-7">
				<div class="card">
					<ul class="space-y-4">
						@foreach ($p['herken'] as $h)
							<li class="flex items-start gap-3">
								<span class="mt-0.5 shrink-0 w-6 h-6 rounded-full bg-[color:var(--color-accent-soft)] text-[color:var(--color-accent-hover)] flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
								<span class="font-medium leading-relaxed">{{ $h }}</span>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ WAT WE BOUWEN ============ --}}
<section class="bg-[color:var(--color-surface)] py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-teal mb-3">{{ $p['wat_pill'] }}</span>
			<h2 class="display-2 mb-5">{{ $p['wat_titel'] }}</h2>
			<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed">{{ $p['wat_intro'] }}</p>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
			@foreach ($p['wat'] as $w)
				<div class="card">
					<div class="w-11 h-11 rounded-lg bg-[color:var(--color-accent-soft)] flex items-center justify-center text-xl mb-4">{{ $w['icon'] }}</div>
					<h3 class="font-bold mb-2">{{ $w['t'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $w['b'] }}</p>
					@isset($w['link'])
						<a href="{{ $w['link'][0] }}" class="mt-3 text-sm font-semibold text-[color:var(--color-accent-hover)] inline-flex items-center gap-1.5">{{ $w['link'][1] }} →</a>
					@endisset
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ EERLIJK ADVIES ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24">
		<span class="pill pill-dark mb-5">Eerlijk advies</span>
		<h2 class="display-2 mb-8 max-w-3xl">{{ $p['eerlijk_titel'] }}</h2>
		<div class="grid md:grid-cols-2 gap-8 text-[color:var(--color-on-dark-muted)] leading-relaxed">
			@foreach ($p['eerlijk'] as $alinea)
				<p>{{ $alinea }}</p>
			@endforeach
		</div>
		@isset($p['eerlijk_link'])
			<a href="{{ $p['eerlijk_link'][0] }}" class="mt-8 text-white font-semibold inline-flex items-center gap-1.5 hover:text-[color:var(--color-accent)]">{{ $p['eerlijk_link'][1] }} →</a>
		@endisset
	</div>
</section>

{{-- ============ WERKWIJZE ============ --}}
<section class="py-20">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-ink mb-3">Werkwijze</span>
			<h2 class="display-2 mb-5">Zo pakken we het aan.</h2>
		</div>
		<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
			@foreach ($p['stappen'] as $st)
				<div class="card">
					<div class="text-5xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ sprintf('%02d', $loop->iteration) }}</div>
					<h3 class="font-bold mb-2">{{ $st['t'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $st['b'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ PRAKTIJK ============ --}}
@if ($praktijk)
<section id="voorbeelden" class="bg-[color:var(--color-surface)] py-20 scroll-mt-24">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="max-w-3xl mb-12">
			<span class="pill pill-teal mb-3">Uit de praktijk</span>
			<h2 class="display-2 mb-5">{{ $p['praktijk_titel'] }}</h2>
		</div>
		<div class="grid {{ count($praktijk) > 1 ? 'lg:grid-cols-2' : '' }} gap-5">
			@foreach ($praktijk as $c)
				<div class="card card-accent">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-2">{{ $c['soort'] }}</div>
					<h3 class="font-bold text-xl mb-3">{{ $c['naam'] }}</h3>
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-5">{{ $c['wat'] }}</p>
					<ul class="space-y-2">
						@foreach ($c['feiten'] as $f)
							<li class="flex items-start gap-2 text-sm leading-relaxed">
								<svg class="w-4 h-4 mt-0.5 shrink-0 text-[color:var(--color-accent-hover)]" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
								<span>{{ $f }}</span>
							</li>
						@endforeach
					</ul>
					@isset($c['link'])
						<a href="{{ $c['link'][0] }}" class="mt-5 text-sm font-semibold text-[color:var(--color-accent-hover)] inline-flex items-center gap-1.5">{{ $c['link'][1] }} →</a>
					@endisset
				</div>
			@endforeach
		</div>
	</div>
</section>
@else
	<span id="voorbeelden"></span>
@endif

{{-- ============ WAT KOST HET ============ --}}
<section class="py-20 border-b border-[color:var(--color-line)]">
	<div class="max-w-[1100px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 items-start">
			<div class="lg:col-span-6">
				<span class="pill pill-ink mb-3">Wat kost het?</span>
				<h2 class="display-2 mb-5">Een vaste prijs, na één gesprek.</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					Een bedrag zonder uw situatie te kennen is een gok, en daar heeft u niets aan. Wat het kost, hangt af van een paar dingen die we in een gratis adviesgesprek samen doorlopen.
				</p>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
					Daarna krijgt u een offerte met een vaste prijs en een duidelijke afbakening. Kan het in stappen, dan prijzen we de stappen apart, zodat u na de eerste kunt beslissen of u verder wilt.
				</p>
			</div>
			<div class="lg:col-span-6">
				<div class="card">
					<div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-4">De prijs hangt vooral af van</div>
					<ul class="space-y-3">
						@foreach ($p['prijs_factoren'] as $f)
							<li class="flex items-start gap-3 border-b border-[color:var(--color-line)] pb-3 last:border-0 last:pb-0">
								<span class="mt-2 w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)] shrink-0"></span>
								<span class="leading-relaxed">{{ $f }}</span>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- ============ VEELGESTELDE VRAGEN ============ --}}
<section class="py-20">
	<div class="max-w-[900px] mx-auto px-6">
		<span class="pill pill-ink mb-3">Veelgesteld</span>
		<h2 class="display-2 mb-10">Vragen die we vaak krijgen.</h2>
		<div class="space-y-4">
			@foreach ($p['faq'] as $q)
				<details class="card group">
					<summary class="font-bold cursor-pointer list-none flex items-start justify-between gap-4">
						<span>{{ $q['v'] }}</span>
						<span class="text-[color:var(--color-accent-hover)] group-open:rotate-45 transition-transform text-xl leading-none">+</span>
					</summary>
					<p class="text-[color:var(--color-ink-muted)] leading-relaxed mt-4">{{ $q['a'] }}</p>
				</details>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ OOK INTERESSANT ============ --}}
<section class="bg-[color:var(--color-surface)] py-16 border-t border-[color:var(--color-line)]">
	<div class="max-w-[1400px] mx-auto px-6">
		<h2 class="font-bold text-lg mb-6">Hoort hier vaak bij</h2>
		<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
			@foreach ($p['verder'] as $v)
				<a href="{{ $v[0] }}" class="card hover:border-[color:var(--color-accent)] transition-colors">
					<div class="font-semibold mb-1">{{ $v[1] }}</div>
					<div class="text-sm text-[color:var(--color-ink-muted)]">{{ $v[2] }}</div>
				</a>
			@endforeach
		</div>
	</div>
</section>

{{-- ============ CTA ============ --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>
	<div class="relative max-w-[1100px] mx-auto px-6 py-24 text-center">
		<h2 class="display-1 mb-6">
			{{ $p['cta_a'] }}
			<span class="accent-word">{{ $p['cta_b'] }}</span>
		</h2>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-2xl mx-auto mb-8">{{ $p['cta_tekst'] }}</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="{{ $afspraak }}" class="btn-accent">
				Plan een gratis adviesgesprek
				<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="{{ $contact }}" class="btn-ghost-light">Stuur een bericht</a>
		</div>
		<p class="text-sm text-[color:var(--color-on-dark-soft)] mt-6">Of bel <a href="tel:+31882545101" class="text-white hover:text-[color:var(--color-accent)]">088-2545101</a></p>
	</div>
</section>
