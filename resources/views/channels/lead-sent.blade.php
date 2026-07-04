@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Bedankt')
@section('robots', 'noindex,nofollow')

@section('content')
	<section class="hero" style="min-height:50vh;display:grid;align-items:center">
		<div class="wrap" style="max-width:560px;text-align:center">
			<div style="font-size:3rem">✅</div>
			<h1>Bedankt voor je aanvraag!</h1>
			<p class="lead" style="margin:1rem auto">We gaan voor je aan de slag en nemen binnen 2 werkdagen contact met je op met een gratis voorbeeld.</p>
			<a href="{{ $site->url() }}" class="btn">Terug naar de homepage</a>
		</div>
	</section>
@endsection
