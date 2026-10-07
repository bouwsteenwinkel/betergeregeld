{{--
	Tool of onderdeel dat niet in het (gratis) plan van de bezoeker zit. Tot 08-10-2026 kreeg iedereen hier de
	kale Laravel-pagina "Payment Required", in het Engels en zonder uitweg, terwijl de footer van elke pagina
	naar de PDF-tool linkt. Statuscode blijft 402; de pagina legt uit wat het is en waar je verder kunt.
--}}
@extends('layouts.app')

@section('title', \App\Support\PaginaTitel::met(__('Deze tool zit in een abonnement')))
@section('description', __('Deze tool is onderdeel van een betaald abonnement. Bekijk de prijzen of log in.'))

@section('content')
<section class="relative">
	<div class="max-w-[720px] mx-auto px-6 py-20 text-center">
		<h1 class="text-3xl font-extrabold mb-4">{{ __('Deze tool zit in een abonnement') }}</h1>
		<p class="text-[color:var(--color-ink-muted)] mb-8">
			{{ __('Dit onderdeel is niet gratis te gebruiken. Met een abonnement kun je het direct gebruiken. Heb je al een account, log dan in.') }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="/{{ app()->getLocale() }}/prijzen" class="btn-accent">{{ __('Bekijk de prijzen') }}</a>
			@guest
				<a href="{{ route('login') }}" class="btn-outline">{{ __('Inloggen') }}</a>
			@endguest
			<a href="/{{ app()->getLocale() }}/tools" class="btn-outline">{{ __('Gratis tools') }}</a>
		</div>
	</div>
</section>
@endsection
