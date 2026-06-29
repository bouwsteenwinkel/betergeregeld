@php /** @var \App\Support\ChannelSite $site */ $pl = $site->get('places', []); @endphp
@extends('channels.layout')

@section('title', ($pl['h1'] ?? 'Plaatsen') . ' — ' . $site->name())
@section('description', $pl['intro'] ?? $site->homeDescription())

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">Heel Nederland</span>
			<h1>{{ $pl['h1'] ?? 'In heel Nederland actief' }}</h1>
			@if (!empty($pl['intro']))<p class="lead" style="max-width:54ch">{{ $pl['intro'] }}</p>@endif
			<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
		</div>
	</section>

	<section>
		<div class="wrap">
			<h2>Kies je plaats</h2>
			<p class="muted" style="margin-bottom:1.6rem">Staat jouw plaats er niet bij? Geen probleem — we werken in heel Nederland. Vraag gewoon een voorbeeld aan.</p>
			<style>.places-cols{columns:2;column-gap:1.5rem;line-height:2.1}@media(min-width:760px){.places-cols{columns:4}}</style>
			<div class="places-cols">
				@foreach ($placesList as $slug => $name)
					<a href="{{ $site->url('plaatsen/' . $slug) }}" style="display:block;font-weight:600;break-inside:avoid">{{ $name }}</a>
				@endforeach
			</div>
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
