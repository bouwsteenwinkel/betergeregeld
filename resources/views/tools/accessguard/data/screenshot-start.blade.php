@extends('layouts.app')

@section('title', __('Screenshot → matrix') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Screenshot → matrix');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[700px] mx-auto px-6">
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-900 mb-4">{{ session('error') }}</div>
		@endif

		<form method="POST" action="{{ route('tools.accessguard.data.screenshot-upload', ['locale' => $locale]) }}" enctype="multipart/form-data" class="card space-y-4">
			@csrf

			<div class="flex items-center gap-3">
				<span class="text-2xl">🤖</span>
				<div>
					<h2 class="text-lg font-bold">{{ __('Screenshot van SaaS admin → matrix') }}</h2>
					<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Plak of upload een screenshot van je M365 admin, Google Workspace, Salesforce, Slack of ander user-overzicht. AI extraheert namen + emails + rollen.') }}</p>
				</div>
			</div>

			<div>
				<label class="block text-xs font-semibold mb-1">{{ __('Screenshot') }} *</label>
				<input type="file" name="file" accept="image/png,image/jpeg,image/webp" required class="field-input py-1.5">
				<p class="text-xs text-[color:var(--color-ink-muted)] mt-1">{{ __('PNG, JPG of WebP. Max 8 MB. De screenshot wordt alleen voor extractie gebruikt en niet bewaard.') }}</p>
			</div>

			<div class="bg-amber-50 border border-amber-200 rounded p-3 text-xs">
				<strong class="text-amber-800">{{ __('Privacy') }}:</strong>
				<p class="mt-1 text-amber-900">{{ __('De screenshot wordt naar OpenAI gestuurd voor extractie. Zorg dat het geen echt gevoelige gegevens bevat (salarissen, medische info). Namen en emails zijn acceptabel, die landen sowieso in je AccessGuard.') }}</p>
			</div>

			<div class="flex items-center gap-3 border-t border-[color:var(--color-line)] pt-4">
				<button type="submit" class="btn-accent text-sm">🤖 {{ __('Extract + open mapping') }}</button>
				<a href="{{ route('tools.accessguard.data.show', ['locale' => $locale]) }}" class="text-sm text-[color:var(--color-ink-muted)] hover:text-[color:var(--color-ink)]">{{ __('Annuleren') }}</a>
			</div>
		</form>
	</div>
</section>

@endsection
