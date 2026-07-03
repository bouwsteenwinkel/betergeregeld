@php
	/** @var \App\Support\ChannelSite $site */
	$pl = (array) $site->get('places', []);
	$t  = array_merge((array) config('channel_places.defaults', []), array_filter($pl, fn ($v) => is_scalar($v) && $v !== ''));
	$trade  = $t['trade'] ?? 'bedrijf';
	$trades = $t['trades'] ?? 'bedrijven';
	$provPlaces = (array) ($provPlaces ?? []);
@endphp
@extends('channels.layout')

@section('title', ucfirst($trade) . ' in ' . $provName)
@section('description', 'Wij helpen ' . $trades . ' in heel ' . $provName . ' online groeien. Kies je plaats en vraag een gratis voorbeeld van jouw bedrijf aan.')

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{{ $provName }}</span>
			<h1>{{ ucfirst($trade) }} in {{ $provName }}</h1>
			<p class="lead" style="max-width:60ch">Wij helpen {{ $trades }} in heel {{ $provName }} aan een website die gevonden wordt en aanvragen oplevert. Kies je plaats voor de details, of vraag direct een gratis voorbeeld aan.</p>
			<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
		</div>
	</section>

	<section>
		<div class="wrap">
			<span class="kicker"><span class="kicker-line"></span> {{ count($provPlaces) }} plaatsen</span>
			<h2>Plaatsen in {{ $provName }}</h2>
			@if ($provPlaces)
				<style>.prov-cols{columns:2;column-gap:1.5rem;line-height:2;margin-top:1rem}@media(min-width:560px){.prov-cols{columns:3}}@media(min-width:900px){.prov-cols{columns:5}}.prov-cols a{display:block;font-weight:600;break-inside:avoid;text-decoration:none;color:inherit;font-size:.95rem}.prov-cols a:hover{color:var(--c-primary)}</style>
				<div class="prov-cols">
					@foreach ($provPlaces as $slug => $name)
						<a href="{{ $site->url('plaatsen/' . $slug) }}">{{ $name }}</a>
					@endforeach
				</div>
			@else
				<p class="muted" style="margin-top:1rem">We werken ook in {{ $provName }}. Vraag een gratis voorbeeld van jouw bedrijf aan, dan nemen we contact op.</p>
			@endif
			<p style="margin-top:1.6rem"><a href="{{ $site->url('plaatsen') }}" style="font-weight:700;color:var(--c-cta)">← Alle provincies</a></p>
		</div>
	</section>

	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
