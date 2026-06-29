@php /** @var \App\Support\ChannelSite $site */ $a = $site->get('about', []); @endphp
@extends('channels.layout')

@section('title', ($a['title'] ?? 'Over ons') . ' — ' . $site->name())
@section('description', $a['lead'] ?? $site->homeDescription())

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">Over ons</span>
			<h1>{{ $a['title'] ?? 'Over ' . $site->name() }}</h1>
			@if (!empty($a['lead']))<p class="lead">{{ $a['lead'] }}</p>@endif
		</div>
	</section>

	<section>
		<div class="wrap" style="max-width:720px">
			<div class="prose">
				@foreach (($a['body'] ?? []) as $p)
					<p>{{ $p }}</p>
				@endforeach
			</div>

			@if (!empty($a['stats']))
				<div class="grid cols-3" style="margin-top:2rem">
					@foreach ($a['stats'] as $s)
						<div class="card" style="text-align:center">
							<div style="font-size:1.8rem;font-weight:800;color:var(--c-primary)">{{ $s['value'] }}</div>
							<div class="muted">{{ $s['label'] }}</div>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
