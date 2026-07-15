@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Bedankt')
@section('robots', 'noindex,nofollow')

@section('content')
	<section class="hero" style="min-height:50vh;display:grid;align-items:center">
		<div class="wrap" style="max-width:560px;text-align:center">
			<div style="width:64px;height:64px;border-radius:50%;margin:0 auto 1rem;display:grid;place-items:center;background:color-mix(in srgb,var(--c-accent) 15%,transparent);color:var(--c-accent)">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
			</div>
			<h1>Bedankt voor je aanvraag!</h1>
			<p class="lead" style="margin:1rem auto">We gaan voor je aan de slag en nemen binnen 2 werkdagen contact met je op met een gratis voorbeeld.</p>

			{{-- De bezoeker koos een moment in de wizard en zag daar een bevestiging.
			     Zonder dit blok is een geslaagde boeking niet te onderscheiden van een
			     mislukte, want de enige andere terugkoppeling is de mail (die juist
			     uitblijft als het misging). --}}
			@php $appointment = session('appointment'); @endphp
			@if ($appointment && $appointment['failed'])
				<p style="margin:1rem auto;padding:14px 18px;border-radius:14px;background:rgba(180,83,9,.1);border:1px solid rgba(180,83,9,.4);color:#92400e;font-size:.95rem">
					Je gekozen moment was net vergeven. We bellen je om samen een nieuw moment te prikken.
				</p>
			@elseif ($appointment && $appointment['label'])
				<p style="margin:1rem auto;padding:14px 18px;border-radius:14px;background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.4);color:#065f46;font-size:.95rem">
					Je afspraak staat genoteerd op {{ $appointment['label'] }} uur. De bevestiging met de Google Meet-link staat in je mailbox.
				</p>
			@endif

			<a href="{{ $site->url() }}" class="btn">Terug naar de homepage</a>
		</div>
	</section>
@endsection
