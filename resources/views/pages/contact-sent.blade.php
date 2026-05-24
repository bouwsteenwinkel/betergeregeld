@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';
@endphp

@section('title', ($isEn ? 'Thanks for your message' : 'Bedankt voor je bericht') . ' — Beter Geregeld ICT')
@section('robots', 'noindex,follow')

@section('content')

<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="relative max-w-[700px] mx-auto px-6 py-24 text-center">
		<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[color:var(--color-accent)] text-white text-2xl mb-6">
			<svg class="w-7 h-7" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</div>
		<h1 class="display-1 mb-5">{{ $isEn ? 'Message received.' : 'Bericht ontvangen.' }}</h1>
		<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed mb-8">
			{{ $isEn
				? 'We\'ll get back to you personally — usually within one business day.'
				: 'We reageren persoonlijk, meestal binnen één werkdag.' }}
		</p>
		<a href="{{ route('home') }}" class="btn-accent">
			{{ $isEn ? 'Back to home' : 'Terug naar home' }}
		</a>
	</div>
</section>

@endsection
