@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Blog — ' . $site->name())
@section('description', 'Tips en inzichten over ' . ($site->get('places.service') ?: 'websites') . ' voor ' . $site->branche() . '.')

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="kicker"><span class="kicker-line"></span> Blog</span>
			<h1>Tips & inzichten</h1>
			<p class="lead">Praktische artikelen, speciaal voor jouw branche.</p>
		</div>
	</section>

	<section style="padding-top:1.5rem">
		<div class="wrap">
			@if ($posts->isEmpty())
				<div class="empty-state">
					<h3>Binnenkort de eerste artikelen</h3>
					<p class="muted">We schrijven praktische tips speciaal voor jouw vak. Kom snel terug, of vraag alvast een gratis voorbeeld aan.</p>
					<a href="#contact" class="btn">Gratis voorbeeld aanvragen</a>
				</div>
			@else
				<div class="grid cols-3 blog-grid">
					@foreach ($posts as $post)
						<a href="{{ $site->url('blog/' . $post->slug) }}" class="blog-card">
							@if ($post->image)<span class="blog-card-img"><img src="{{ $post->image }}" alt="{{ $post->title }}" loading="lazy" decoding="async"></span>@endif
							<span class="blog-meta">{{ optional($post->published_at)->translatedFormat('j F Y') }} · {{ $post->reading_time_min }} min</span>
							<h3>{{ $post->title }}</h3>
							<p class="muted">{{ $post->excerpt }}</p>
							<span class="blog-more">Lees artikel
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
							</span>
						</a>
					@endforeach
				</div>
				<div style="margin-top:2.4rem">{{ $posts->links() }}</div>
			@endif
		</div>
	</section>

	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
