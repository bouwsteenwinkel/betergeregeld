@extends('layouts.app')

@php
	// AFSPRAAK OP BETERGEREGELD.COM ZELF (11-09-2026).
	// Tot die datum bestond boeken alleen op de channel-sites (channels/afspraak.blade.php).
	// Deze pagina gebruikt precies dezelfde onderdelen: de gedeelde kalender uit
	// partials/slot-calendar, /afspraak/beschikbaarheid en /afspraak/boeken. Een boeking
	// wordt dus een Appointment + WebsiteLead met interne melding, net als daar
	// (AppointmentController::recordLead), met source_site 'betergeregeld.com'.
	//
	// Het onderwerp komt mee als ?onderwerp=<sleutel> vanaf de dienstpagina's en gaat als
	// eerste regel in de notitie, zodat je vóór het gesprek weet waar het over gaat.
	$hreflangLocales = ['nl'];
	$onderwerpen = [
		'ai-telefoniste'          => 'AI Telefoniste',
		'maatwerk-webapplicatie'  => 'Maatwerk webapplicatie',
		'klantportaal'            => 'Klantportaal',
		'api-koppelingen'         => 'API-koppelingen',
		'processen-automatiseren' => 'Processen automatiseren',
		'ai-procesanalyse'        => 'Slimmer werken met AI',
		'website'                 => 'Website of webshop',
		'anders'                  => 'Iets anders, of weet ik nog niet',
	];
	$gekozen = request()->query('onderwerp');
	$gekozen = array_key_exists((string) $gekozen, $onderwerpen) ? $gekozen : '';
	$duur = (int) config('scheduling.meeting_minutes', 60);
@endphp

@section('title', \App\Support\PaginaTitel::met('Plan een gratis adviesgesprek'))
@section('description', 'Plan zelf een gratis en vrijblijvend adviesgesprek van ' . $duur . ' minuten via Google Meet. Kies een moment dat u uitkomt.')

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[1400px] mx-auto px-6 py-16">
		<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-6 flex items-center gap-2">
			<a href="{{ route('home') }}" class="hover:text-white">Home</a>
			<span class="opacity-40">/</span>
			<span class="text-[color:var(--color-on-dark-muted)]">Afspraak</span>
		</nav>
		<h1 class="display-1 mb-5">
			Plan een gratis
			<span class="accent-word">adviesgesprek.</span>
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-2xl">
			Kies een moment dat u uitkomt. Het gesprek duurt {{ $duur }} minuten, via Google Meet, en is vrijblijvend.
		</p>
	</div>
</section>

<section class="py-16">
	<div class="max-w-[1400px] mx-auto px-6">
		<div class="grid lg:grid-cols-12 gap-10 items-start">
			<div class="lg:col-span-4 space-y-6">
				<div>
					<h2 class="font-bold text-lg mb-3">Wat u kunt verwachten</h2>
					<ul class="space-y-3 text-[color:var(--color-ink-muted)] leading-relaxed">
						<li class="flex gap-3"><span class="mt-2 w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)] shrink-0"></span>We luisteren naar waar het nu knelt en welke systemen u al gebruikt.</li>
						<li class="flex gap-3"><span class="mt-2 w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)] shrink-0"></span>We zeggen eerlijk wat we zouden doen, ook als dat geen maatwerk is.</li>
						<li class="flex gap-3"><span class="mt-2 w-1.5 h-1.5 rounded-full bg-[color:var(--color-accent)] shrink-0"></span>Wilt u verder, dan krijgt u daarna een offerte met een vaste prijs.</li>
					</ul>
				</div>
				<div class="card">
					<div class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">
						Liever eerst mailen of bellen?
						<a href="/nl/contact{{ $gekozen ? '?topic=' . $gekozen : '' }}" class="font-semibold text-[color:var(--color-accent-hover)]">Stuur een bericht</a>
						of bel <a href="tel:+31882545101" class="font-semibold text-[color:var(--color-accent-hover)]">088-2545101</a>.
					</div>
				</div>
			</div>

			<div class="lg:col-span-8">
				{{-- De gedeelde kalender hangt aan --c-* variabelen; hier vertaald naar de huisstijl
				     van de hoofdsite (zie partials/slot-calendar voor de lijst). --}}
				<div class="card" data-booking data-site="betergeregeld.com"
					style="--c-primary: var(--color-accent-hover); --c-on-primary: #fff; --c-accent: var(--color-accent); --c-ink: var(--color-ink); --c-muted: var(--color-ink-muted); --c-bg: var(--color-surface); --radius: 10px;">
					<div class="bkg-kalender">
						@include('partials.slot-calendar', [
							'emptyText' => 'Er zijn op dit moment geen vrije momenten. Stuur ons een bericht, dan plannen we samen iets in.',
						])
					</div>

					<form class="bkg-form space-y-4" hidden>
						<p class="bkg-chosen font-bold"></p>
						<input type="text" name="website" class="absolute -left-[9999px] w-px h-px opacity-0" tabindex="-1" autocomplete="off" aria-hidden="true">
						<div class="grid sm:grid-cols-2 gap-4">
							<label class="block text-sm font-semibold">Naam
								<input type="text" name="name" required autocomplete="name" class="field-input mt-1">
							</label>
							<label class="block text-sm font-semibold">E-mailadres
								<input type="email" name="email" required autocomplete="email" class="field-input mt-1">
							</label>
							<label class="block text-sm font-semibold">Telefoon <span class="font-normal text-[color:var(--color-ink-soft)]">(optioneel)</span>
								<input type="tel" name="phone" autocomplete="tel" class="field-input mt-1">
							</label>
							<label class="block text-sm font-semibold">Organisatie <span class="font-normal text-[color:var(--color-ink-soft)]">(optioneel)</span>
								<input type="text" name="organisatie" autocomplete="organization" class="field-input mt-1">
							</label>
						</div>
						<label class="block text-sm font-semibold">Waar gaat het over?
							<select name="onderwerp" class="field-input mt-1">
								@foreach ($onderwerpen as $sleutel => $label)
									<option value="{{ $label }}" @selected($sleutel === $gekozen)>{{ $label }}</option>
								@endforeach
							</select>
						</label>
						<label class="block text-sm font-semibold">Toelichting <span class="font-normal text-[color:var(--color-ink-soft)]">(optioneel)</span>
							<textarea name="toelichting" rows="3" maxlength="800" class="field-input mt-1" placeholder="Bijvoorbeeld: welke systemen u gebruikt, of wat er nu handwerk is."></textarea>
						</label>
						<p class="bkg-error text-sm font-semibold rounded-lg border border-red-200 bg-red-50 text-red-800 p-3" role="alert" hidden></p>
						<div class="flex flex-wrap items-center justify-between gap-3">
							<button type="button" class="bkg-back text-sm underline text-[color:var(--color-ink-muted)]">Ander moment kiezen</button>
							<button type="submit" class="btn-accent bkg-submit">Afspraak bevestigen</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
(function () {
	var root = document.querySelector('[data-booking]');
	if (!root || !window.bgSlotCalendar) return;
	var token = document.querySelector('meta[name=csrf-token]');
	token = token ? token.getAttribute('content') : '';

	var elKalender = root.querySelector('.bkg-kalender'),
		elForm = root.querySelector('.bkg-form'),
		elChosen = root.querySelector('.bkg-chosen'),
		elError = root.querySelector('.bkg-error');
	var picked = null;

	var cal = window.bgSlotCalendar(root.querySelector('[data-slotcal]'), {
		onPick: function (keuze) {
			picked = keuze.waarde;
			elChosen.textContent = 'Gekozen: ' + keuze.kort + ' om ' + keuze.time + ' uur (online via Google Meet)';
			elKalender.hidden = true; elForm.hidden = false; elError.hidden = true;
			elForm.querySelector('input[name=name]').focus();
		}
	});

	root.querySelector('.bkg-back').addEventListener('click', function () {
		elForm.hidden = true; elKalender.hidden = false; picked = null;
	});

	elForm.addEventListener('submit', function (e) {
		e.preventDefault();
		if (!picked) return;
		var btn = elForm.querySelector('.bkg-submit');
		btn.disabled = true; btn.textContent = 'Bezig…';
		elError.hidden = true;

		// Onderwerp, organisatie en toelichting passen niet in de velden van /afspraak/boeken;
		// ze gaan samen in de notitie (max 1000 tekens), onderwerp bovenaan.
		var notitie = 'Onderwerp: ' + elForm.onderwerp.value;
		if (elForm.organisatie.value) notitie += '\nOrganisatie: ' + elForm.organisatie.value;
		if (elForm.toelichting.value) notitie += '\n\n' + elForm.toelichting.value;

		fetch('/afspraak/boeken', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
			body: JSON.stringify({
				name: elForm.name.value, email: elForm.email.value, phone: elForm.phone.value,
				website: elForm.website.value, starts_at: picked, source_site: 'betergeregeld.com',
				note: notitie.slice(0, 1000)
			})
		}).then(function (r) {
			return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; });
		}).then(function (res) {
			if (!res.ok || !res.j.ok) {
				btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
				elError.textContent = res.j.message || 'Er ging iets mis. Probeer een ander moment.';
				elError.hidden = false;
				if (res.j && res.j.message && res.j.message.indexOf('bezet') > -1) {
					cal.refresh().then(function () { elForm.hidden = true; elKalender.hidden = false; picked = null; });
				}
				return;
			}
			window.location.href = '/nl/afspraak-bevestigd';
		}).catch(function () {
			btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
			elError.textContent = 'Er ging iets mis. Probeer het later opnieuw.';
			elError.hidden = false;
		});
	});
})();
</script>

@endsection
