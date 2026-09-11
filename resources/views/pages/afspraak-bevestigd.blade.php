@extends('layouts.app')

@php
	$hreflangLocales = ['nl'];
@endphp

{{-- Bevestiging na een boeking op /nl/afspraak. Een eigen URL (en geen melding in de
     pagina) zodat een geboekte afspraak later als conversie meetbaar is, zoals op de
     channel-sites. Niet in de index. --}}
@section('robots', 'noindex,follow')
@section('title', \App\Support\PaginaTitel::met('Afspraak bevestigd'))

@section('content')
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[900px] mx-auto px-6 py-24 text-center">
		<h1 class="display-1 mb-6">
			Uw afspraak
			<span class="accent-word">staat.</span>
		</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] max-w-xl mx-auto mb-8">
			U ontvangt een bevestiging per e-mail, met de link naar het Google Meet-gesprek. Moet het toch een ander moment worden, dan kunt u de afspraak via die mail verzetten of afzeggen.
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="/nl" class="btn-accent">Terug naar de homepage</a>
			<a href="/nl/diensten" class="btn-ghost-light">Bekijk onze diensten</a>
		</div>
	</div>
</section>
@endsection
