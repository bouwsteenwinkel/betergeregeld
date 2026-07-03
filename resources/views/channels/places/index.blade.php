@php
	/** @var \App\Support\ChannelSite $site */
	$pl = (array) $site->get('places', []);
	$t  = array_merge((array) config('channel_places.defaults', []), array_filter($pl, fn ($v) => is_scalar($v) && $v !== ''));
	$map = [':trades' => $t['trades'] ?? 'bedrijven', ':trade' => $t['trade'] ?? 'bedrijf', ':niches' => $t['niches'] ?? 'diensten', ':niche' => $t['niche'] ?? 'vak', ':service' => $t['service'] ?? 'website'];
	$ix  = array_merge((array) config('channel_places.index', []), array_intersect_key($pl, array_flip(['eyebrow', 'h1', 'intro', 'pick_h2', 'pick_note'])));
	$r   = fn ($k, $d = '') => strtr((string) ($ix[$k] ?? $d), $map);
	$provinces = (array) ($provinces ?? []);
@endphp
@extends('channels.layout')

@section('title', $r('h1', 'Plaatsen'))
@section('description', $r('intro') ?: $site->homeDescription())

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{{ $r('eyebrow', 'Heel Nederland') }}</span>
			<h1>{{ $r('h1', 'In heel Nederland actief') }}</h1>
			@if ($r('intro'))<p class="lead" style="max-width:60ch">{{ $r('intro') }}</p>@endif
			<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
		</div>
	</section>

	@php
		// Fallback: als er (nog) geen provincie-groepen zijn maar wél plaatsen
		// (bv. config-cache nog niet herbouwd), toon dan gewoon de platte lijst.
		$placesList = (array) ($placesList ?? []);
	@endphp
	<section>
		<div class="wrap">
			@if ($provinces)
				<h2>{{ $r('pick_h2', 'Kies je provincie') }}</h2>
				<p class="muted" style="margin-bottom:1.6rem;max-width:66ch">{{ $r('pick_note', 'Kies je provincie en daarna je plaats.') }}</p>
				<div class="grid cols-4" style="gap:1rem">
					@foreach ($provinces as $p)
						<a href="{{ $site->url('plaatsen/provincie/' . $p['slug']) }}" class="card" style="display:flex;flex-direction:column;gap:.25rem;color:inherit;text-decoration:none">
							<h3 style="font-size:1.08rem">{{ $p['name'] }}</h3>
							<span class="muted" style="font-size:.85rem">{{ $p['count'] }} plaatsen</span>
							<span style="margin-top:.4rem;font-weight:700;color:var(--c-cta);font-size:.9rem">Bekijk plaatsen →</span>
						</a>
					@endforeach
				</div>
			@elseif ($placesList)
				<h2>Kies je plaats</h2>
				<p class="muted" style="margin-bottom:1.6rem;max-width:66ch">{{ $r('pick_note', 'Staat jouw plaats er niet bij? We werken door heel Nederland — vraag gewoon een gratis voorbeeld aan.') }}</p>
				<style>.places-cols{columns:2;column-gap:1.5rem;line-height:2}@media(min-width:560px){.places-cols{columns:3}}@media(min-width:900px){.places-cols{columns:5}}.places-cols a{display:block;font-weight:600;break-inside:avoid;text-decoration:none;color:inherit;font-size:.95rem}.places-cols a:hover{color:var(--c-primary)}</style>
				<div class="places-cols">
					@foreach ($placesList as $slug => $name)
						<a href="{{ $site->url('plaatsen/' . $slug) }}">{{ $name }}</a>
					@endforeach
				</div>
			@else
				<h2>We werken door heel Nederland</h2>
				<p class="muted" style="max-width:60ch">Vertel ons waar je zit, dan zetten we een gratis voorbeeld van jouw bedrijf klaar.</p>
				<a href="#contact" class="btn" style="margin-top:1rem">Gratis voorbeeld aanvragen</a>
			@endif
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
