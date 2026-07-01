@php /** @var \App\Support\ChannelSite $site */ $h = $home ?? $site->get('home', []); @endphp
@extends('channels.layout')

@section('title', $site->homeTitle())
@section('description', $site->homeDescription())

@section('content')
	{{-- Facet-afhankelijke blokken: worden bij een fase-keuze live (AJAX) vervangen. --}}
	<div id="facet-zone" data-facet="{{ $facet ?? 'website' }}">
		@include('channels.partials.facet-zone')
	</div>

	{{-- Anker zodat de gedeelde #contact-CTA's (nav, hero) op de home bij het
	     formulier landen; de wizard zelf houdt id="gratis-voorbeeld" (bespoke channels). --}}
	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard')

	<script>
	(function () {
		var zone = document.getElementById('facet-zone');
		if (!zone) return;

		// Klik op een fase in de groeiselector → live het facet-blok herrenderen
		// (geen volledige page-load). Delegatie, zodat het ook werkt na herrender.
		document.addEventListener('click', function (e) {
			var a = e.target.closest('[data-facet-link]');
			if (!a || !zone.contains(a)) return;
			e.preventDefault();
			var url = a.getAttribute('href');
			var facet = (url.match(/groeifase\/([^\/?#]+)/) || [])[1] || 'website';
			zone.style.transition = 'opacity .15s'; zone.style.opacity = '.45';

			fetch(url.replace(/\/$/, '') + '/fragment', { headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' })
				.then(function (r) { if (!r.ok) throw new Error('http'); return r.text(); })
				.then(function (html) {
					zone.innerHTML = html;
					zone.setAttribute('data-facet', facet);
					zone.style.opacity = '';
					// Lead-form taggen met de gekozen fase.
					document.querySelectorAll('input[name="facet"]').forEach(function (i) { i.value = facet; });
					try { history.pushState({ facet: facet }, '', url); } catch (err) {}
					zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
				})
				.catch(function () { window.location.href = url; }); // fallback: gewone navigatie
		});

		// Terug/vooruit-knop: eenvoudig herladen zodat de juiste fase weer staat.
		window.addEventListener('popstate', function () { location.reload(); });
	})();
	</script>
@endsection
