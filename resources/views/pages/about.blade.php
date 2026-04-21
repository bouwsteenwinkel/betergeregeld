@extends('layouts.app')

@section('title', __('Over ons') . ' | Beter Geregeld ICT')
@section('description', __('Lees wie we zijn, hoe we werken en waarom bedrijven kiezen voor Beter Geregeld ICT.'))

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
@endphp

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[1100px] mx-auto px-4 py-10 sm:py-14">

		<nav class="text-sm text-[color:var(--color-ink-muted)] mb-6">
			<a href="{{ route('home') }}" class="hover:text-[color:var(--color-ink)]">{{ $isEn ? 'Home' : 'Home' }}</a>
			<span class="mx-2">/</span>
			<span>{{ $isEn ? 'About us' : 'Over ons' }}</span>
		</nav>

		<h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">{{ $isEn ? 'About us' : 'Over ons' }}</h1>
		<p class="text-lg text-[color:var(--color-ink-muted)] leading-relaxed mb-10">
			{{ $isEn
				? 'Since 1989 we\'ve helped organisations with automation and IT that simply works: secure, clear and reliable.'
				: 'Sinds 1989 helpen we organisaties met automatisering en ICT die gewoon klopt: veilig, duidelijk en betrouwbaar.' }}
		</p>

		<section class="grid md:grid-cols-2 gap-6 mb-10">
			<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
				<h2 class="text-2xl font-bold mb-3">{{ $isEn ? 'Our background' : 'Onze achtergrond' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-3">
					{{ $isEn
						? 'We\'ve been active in automation since 1989 and have operated under several names over the years: VOS electronics, VOS automatisering, ICM results, ICM Groep and ICM Performance.'
						: 'We zijn actief in automatisering sinds 1989 en hebben door de jaren heen onder verschillende namen gewerkt: VOS electronics, VOS automatisering, ICM results, ICM Groep en ICM Performance.' }}
				</p>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
					{{ $isEn
						? 'We bring that experience into how we work today: pragmatic, with short lines and a focus on what\'s truly needed.'
						: 'Die ervaring nemen we mee in hoe we vandaag werken: pragmatisch, met korte lijnen en focus op wat er écht nodig is.' }}
				</p>
			</article>

			<article class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-6 shadow-[var(--shadow-soft)]">
				<h2 class="text-2xl font-bold mb-3">{{ $isEn ? 'Our mission' : 'Onze missie' }}</h2>
				<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
					{{ $isEn
						? 'We make IT understandable and reliable, so you can focus on your business while we ensure stability, security and continuity.'
						: 'We maken ICT begrijpelijk en betrouwbaar. Zodat jij kunt ondernemen, terwijl wij zorgen voor stabiliteit, veiligheid en continuïteit.' }}
				</p>
				<ul class="space-y-2 text-sm text-[color:var(--color-ink-muted)] list-disc list-inside">
					<li>{{ $isEn ? 'Clear agreements and predictable results.' : 'Duidelijke afspraken en voorspelbare resultaten.' }}</li>
					<li>{{ $isEn ? 'Security built into the foundation.' : 'Beveiliging standaard in de basis.' }}</li>
					<li>{{ $isEn ? 'Smart automation where it truly saves time.' : 'Slim automatiseren waar het écht tijd scheelt.' }}</li>
				</ul>
			</article>
		</section>

		<section class="mb-10">
			<h2 class="text-2xl font-bold mb-4">{{ $isEn ? 'What we do' : 'Wat we doen' }}</h2>
			<div class="grid md:grid-cols-2 gap-4">
				@foreach ([
					['nl' => 'Hosting & beheer: snel, stabiel en goed gemonitord.', 'en' => 'Hosting & management: fast, stable and well monitored.'],
					['nl' => 'Security & updates: patching, hardening en preventie.', 'en' => 'Security & updates: patching, hardening and prevention.'],
					['nl' => 'Automatisering: processen slimmer maken met maatwerk tooling.', 'en' => 'Automation: smarter workflows with custom tooling.'],
					['nl' => 'Support: duidelijke communicatie en korte lijnen.', 'en' => 'Support: clear communication and short lines.'],
				] as $w)
					<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-control)] p-4 shadow-[var(--shadow-soft)] text-sm text-[color:var(--color-ink-muted)]">
						{{ $isEn ? $w['en'] : $w['nl'] }}
					</div>
				@endforeach
			</div>
			<p class="text-xs text-[color:var(--color-ink-muted)] mt-3 italic">
				{{ $isEn ? 'Our service offering may vary over time; we\'ll fine-tune this later on the site.' : 'Ons aanbod kan per periode verschillen; dat werken we later verder uit op de site.' }}
			</p>
		</section>

		<section class="mb-10">
			<h2 class="text-2xl font-bold mb-4">{{ $isEn ? 'How we work' : 'Hoe we werken' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
				{{ $isEn
					? 'Pragmatic and transparent. We explain what we do, why we do it and what it delivers. No surprises afterwards.'
					: 'Pragmatisch en transparant. We leggen uit wat we doen, waarom we het doen en wat het oplevert. Geen verrassingen achteraf.' }}
			</p>
			<ul class="space-y-2 text-[color:var(--color-ink-muted)] list-disc list-inside">
				<li>{{ $isEn ? 'Structure first, optimisation second.' : 'Eerst structuur, daarna optimalisatie.' }}</li>
				<li>{{ $isEn ? 'Security is standard, not optional.' : 'Beveiliging standaard, niet optioneel.' }}</li>
				<li>{{ $isEn ? 'Documentation you can actually use.' : 'Documentatie die je wél begrijpt.' }}</li>
			</ul>
		</section>

		<section class="mb-10">
			<h2 class="text-2xl font-bold mb-4">{{ $isEn ? 'Timeline' : 'Tijdlijn' }}</h2>
			<ol class="relative border-l border-[color:var(--color-line)] pl-5 space-y-5">
				<li>
					<span class="absolute -left-[5px] w-2.5 h-2.5 rounded-full bg-[color:var(--color-ink)] mt-1.5"></span>
					<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? '1989 — started with automation and technology.' : '1989 — start met automatisering en techniek.' }}</p>
				</li>
				<li>
					<span class="absolute -left-[5px] w-2.5 h-2.5 rounded-full bg-[color:var(--color-ink)] mt-1.5"></span>
					<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Evolution — VOS electronics → VOS automatisering → ICM results → ICM Groep / ICM Performance.' : 'Doorontwikkeling — VOS electronics → VOS automatisering → ICM results → ICM Groep / ICM Performance.' }}</p>
				</li>
				<li>
					<span class="absolute -left-[5px] w-2.5 h-2.5 rounded-full bg-[color:var(--color-ink)] mt-1.5"></span>
					<p class="text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Today — Beter Geregeld ICT: modern tooling, clear support and a strong focus on security and stability.' : 'Vandaag — Beter Geregeld ICT: moderne tooling, duidelijke support en een focus op veiligheid en stabiliteit.' }}</p>
				</li>
			</ol>
		</section>

		<section class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-8 shadow-[var(--shadow-soft)]">
			<h2 class="text-2xl font-bold mb-3">{{ $isEn ? 'Want to talk?' : 'Kennismaken?' }}</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-5">
				{{ $isEn ? 'Tell us what you need — we\'ll map the safest and smartest route together.' : 'Vertel kort wat je nodig hebt — dan kijken we samen wat de slimste, veiligste route is.' }}
			</p>
			<div class="flex flex-wrap gap-3">
				<a href="/{{ $locale }}/contact" class="rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
					{{ $isEn ? 'Contact us' : 'Neem contact op' }}
				</a>
				<a href="/{{ $locale }}/diensten" class="rounded-[var(--radius-control)] bg-white text-[color:var(--color-ink)] border border-[color:var(--color-line)] font-semibold px-5 py-3 hover:bg-gray-50 transition">
					{{ $isEn ? 'View services' : 'Bekijk diensten' }}
				</a>
			</div>
		</section>

	</div>
</div>
@endsection
