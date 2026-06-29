@php
	/** @var \App\Support\ChannelSite $site */
	$pl = $site->get('places', []);
	$repl = fn ($t) => str_replace(':city', $placeName, (string) $t);
	$h    = $site->get('home', []);
	$placePrefill = $placeName;
@endphp
@extends('channels.layout')

@section('title', $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)) . ' — ' . $site->name())
@section('description', $repl($pl['city_intro'] ?? $site->homeDescription()))

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{{ $placeName }}</span>
			<h1>{{ $repl($pl['city_h1'] ?? ('Website laten maken in ' . $placeName)) }}</h1>
			<p class="lead" style="max-width:56ch">{{ $repl($pl['city_intro'] ?? '') }}</p>
			<a href="#contact" class="btn">Gratis voorbeeld in {{ $placeName }}</a>
		</div>
	</section>

	@if (!empty($h['features']))
		<section>
			<div class="wrap">
				<h2>Wat je krijgt in {{ $placeName }}</h2>
				<div class="grid cols-4" style="margin-top:1.4rem">
					@foreach ($h['features'] as $f)
						<div class="card">
							<div style="font-size:1.8rem;margin-bottom:.4rem">{{ $f['icon'] ?? '•' }}</div>
							<h3>{{ $f['title'] }}</h3>
							<p class="muted" style="font-size:.95rem">{{ $f['text'] }}</p>
						</div>
					@endforeach
				</div>
			</div>
		</section>
	@endif

	<section style="background:var(--c-surface)">
		<div class="wrap" style="max-width:720px">
			<h2>De beste partner voor {{ $pl['service'] ?? 'een website' }} in {{ $placeName }}</h2>
			<div class="prose" style="margin-top:1rem">
				<p>Ondernemers in {{ $placeName }} kiezen voor een website die werkt: snel, vindbaar in Google en gemaakt voor jouw klanten. Wij kennen de lokale markt en zorgen dat je gevonden wordt door mensen die in {{ $placeName }} en omgeving naar jou op zoek zijn.</p>
				<p>Geen lange contracten of technisch gedoe. We zetten vooraf een gratis voorbeeld klaar dat helemaal is afgestemd op jouw zaak in {{ $placeName }}, zodat je precies ziet wat je krijgt voordat je iets beslist.</p>
			</div>
			<a href="#contact" class="btn" style="margin-top:1.2rem">Vraag je gratis voorbeeld aan</a>
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
