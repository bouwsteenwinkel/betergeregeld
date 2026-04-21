@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
@endphp

@section('title', ($isEn ? 'Thanks for your message' : 'Bedankt voor je bericht') . ' — Beter Geregeld ICT')

@section('content')
<div class="bg-[#f5f7fb]">
	<div class="max-w-[700px] mx-auto px-4 py-16 text-center">
		<div class="bg-white border border-[color:var(--color-line)] rounded-[var(--radius-card)] p-10 shadow-[var(--shadow-soft)]">
			<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-800 text-3xl mb-4">✓</div>
			<h1 class="text-3xl font-bold mb-3">{{ $isEn ? 'Thanks for your message' : 'Bedankt voor je bericht' }}</h1>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-6">
				{{ $isEn
					? 'We\'ve received your message and will get back to you personally — usually within one business day.'
					: 'We hebben je bericht ontvangen en reageren persoonlijk, meestal binnen één werkdag.' }}
			</p>
			<a href="{{ route('home') }}" class="inline-block rounded-[var(--radius-control)] bg-[color:var(--color-ink)] text-white font-semibold px-5 py-3 hover:opacity-90 transition">
				{{ $isEn ? 'Back to home' : 'Terug naar home' }}
			</a>
		</div>
	</div>
</div>
@endsection
