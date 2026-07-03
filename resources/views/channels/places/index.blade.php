@php
	/** @var \App\Support\ChannelSite $site */
	$pl = (array) $site->get('places', []);
	$t  = array_merge((array) config('channel_places.defaults', []), array_filter($pl, fn ($v) => is_scalar($v) && $v !== ''));
	$map = [':trades' => $t['trades'] ?? 'bedrijven', ':trade' => $t['trade'] ?? 'bedrijf', ':niches' => $t['niches'] ?? 'diensten', ':niche' => $t['niche'] ?? 'vak', ':service' => $t['service'] ?? 'website'];
	$ix  = array_merge((array) config('channel_places.index', []), array_intersect_key($pl, array_flip(['eyebrow', 'h1', 'intro', 'pick_h2', 'pick_note'])));
	$r   = fn ($k, $d = '') => strtr((string) ($ix[$k] ?? $d), $map);
	$provinces = (array) ($provinces ?? []);
@endphp
@extends('channels.layout')

@section('title', $r('h1', 'Plaatsen'))
@section('description', $r('intro') ?: $site->homeDescription())

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{{ $r('eyebrow', 'Heel Nederland') }}</span>
			<h1>{{ $r('h1', 'In heel Nederland actief') }}</h1>
			@if ($r('intro'))<p class="lead" style="max-width:60ch">{{ $r('intro') }}</p>@endif
			<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
		</div>
	</section>

	<section>
		<div class="wrap">
			<h2>{{ $r('pick_h2', 'Kies je provincie') }}</h2>
			<p class="muted" style="margin-bottom:1.6rem;max-width:66ch">{{ $r('pick_note') }}</p>
			<div class="grid cols-4" style="gap:1rem">
				@foreach ($provinces as $p)
					<a href="{{ $site->url('plaatsen/provincie/' . $p['slug']) }}" class="card" style="display:flex;flex-direction:column;gap:.25rem;color:inherit;text-decoration:none">
						<h3 style="font-size:1.08rem">{{ $p['name'] }}</h3>
						<span class="muted" style="font-size:.85rem">{{ $p['count'] }} plaatsen</span>
						<span style="margin-top:.4rem;font-weight:700;color:var(--c-cta);font-size:.9rem">Bekijk plaatsen →</span>
					</a>
				@endforeach
			</div>
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
