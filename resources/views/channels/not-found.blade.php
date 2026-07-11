@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('robots', 'noindex,nofollow')
@section('title', 'Pagina niet gevonden')

@section('content')
	<section class="hero">
		<div class="wrap" style="text-align:center;max-width:640px">
			<span class="kicker"><span class="kicker-line"></span> 404</span>
			<h1>Deze pagina bestaat niet</h1>
			<p class="lead">De pagina die je zocht, konden we niet vinden op {{ $site->name() }}. Ga terug naar de homepagina om verder te kijken.</p>
			<p style="margin-top:1.75rem">
				<a href="{{ $site->url() }}" class="btn">Terug naar home</a>
			</p>
		</div>
	</section>
@endsection
