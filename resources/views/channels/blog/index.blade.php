@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Blog — ' . $site->name())
@section('description', 'Tips en inzichten over ' . ($site->get('places.service') ?: 'websites') . ' voor ' . $site->branche() . '.')

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">Blog</span>
			<h1>Tips & inzichten</h1>
			<p class="lead">Praktische artikelen, speciaal voor jouw branche.</p>
		</div>
	</section>

	<section>
		<div class="wrap">
			@if ($posts->isEmpty())
				<p class="muted">Er zijn nog geen artikelen gepubliceerd. Kom binnenkort terug!</p>
			@else
				<div class="grid cols-3">
					@foreach ($posts as $post)
						<a href="{{ $site->url('blog/' . $post->slug) }}" class="card" style="display:block">
							<div class="muted" style="font-size:.8rem;margin-bottom:.4rem">{{ optional($post->published_at)->translatedFormat('j F Y') }} · {{ $post->reading_time_min }} min</div>
							<h3 style="margin-bottom:.4rem">{{ $post->title }}</h3>
							<p class="muted" style="font-size:.95rem">{{ $post->excerpt }}</p>
						</a>
					@endforeach
				</div>
				<div style="margin-top:2rem">{{ $posts->links() }}</div>
			@endif
		</div>
	</section>

	@include('channels.partials.lead-form')
@endsection
