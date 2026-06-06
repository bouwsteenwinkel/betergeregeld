@extends('layouts.app')

@section('title', __('Upload CSV') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$kindLabels = ['people' => __('Personen'), 'systems' => __('Systemen')];
	$crumb = __('Importeren') . ' · ' . ($kindLabels[$kind] ?? $kind);
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-900 mb-4">{{ session('error') }}</div>
		@endif

		<form method="POST" action="{{ route('tools.accessguard.data.import-upload', ['locale' => $locale]) }}" enctype="multipart/form-data" class="card space-y-4">
			@csrf
			<input type="hidden" name="kind" value="{{ $kind }}">

			<h2 class="text-lg font-bold">{{ __('Upload CSV, :kind', ['kind' => $kindLabels[$kind] ?? $kind]) }}</h2>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('CSV-bestand') }} *</label>
				<input type="file" name="file" accept=".csv,text/csv" required class="field-input py-1.5">
				<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('Max 5 MB. Comma, semicolon of tab gescheiden.') }}</p>
			</div>

			<div class="text-xs bg-slate-50 border border-[color:var(--color-line)] rounded p-3">
				<strong class="block mb-1">{{ __('Beschikbare velden:') }}</strong>
				<ul class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-[color:var(--color-ink-muted)]">
					@foreach ($fields as $key => $label)
						<li><code>{{ $key }}</code> · {{ $label }}</li>
					@endforeach
				</ul>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">{{ __('Uploaden') }}</button>
				<a href="{{ route('tools.accessguard.data.show', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
