@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

@section('title', ($post->meta_title ?: $post->title) . ' — Blog — ' . config('app.name'))
@section('description', $post->excerpt)

@push('head')
	<link rel="canonical" href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}">
	<link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }} — Blog" href="{{ route('blog.rss', ['locale' => $locale]) }}">
	<meta property="og:title" content="{{ $post->title }}">
	<meta property="og:description" content="{{ $post->excerpt }}">
	<meta property="og:type" content="article">
	<meta property="article:section" content="{{ $post->category->name }}">
	@if ($post->published_at)
		<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
	@endif
@endpush

@section('content')

@include('blog._styles')

<div class="blog-root">
	<div class="blog-container" style="padding-top: 32px;">
		<nav class="blog-crumbs">
			<a href="{{ route('blog.index', ['locale' => $locale]) }}">Blog</a>
			<span>›</span>
			<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => $post->category->slug]) }}">{{ $post->category->name }}</a>
			<span>›</span>
			<span>{{ Str::limit($post->title, 60) }}</span>
		</nav>
	</div>

	<article class="blog-post-body" style="padding: 24px;">
		<div class="blog-meta-row">
			<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => $post->category->slug]) }}"
				class="blog-card-cat" style="text-decoration: none;">
				{{ $post->category->name }}
			</a>
			<span>·</span>
			<span>{{ $post->reading_time_min }} min leestijd</span>
			@if ($post->published_at)
				<span>·</span>
				<span>{{ $post->published_at->translatedFormat('d F Y') }}</span>
			@endif
			@if ($post->is_pillar)
				<span>·</span>
				<span style="color: var(--bl-accent); font-weight: 700;">★ Pillar-gids</span>
			@endif
		</div>

		<h1>{{ $post->title }}</h1>
		<p style="font-size: 18px; color: var(--bl-ink-muted); font-weight: 500; margin-bottom: 32px;">{{ $post->excerpt }}</p>

		{!! $post->body !!}

		@if ($post->tags->isNotEmpty())
			<div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--bl-border);">
				<p style="font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--bl-ink-muted); margin-bottom: 8px;">Onderwerpen</p>
				@foreach ($post->tags as $tag)
					<a href="{{ route('blog.tag', ['locale' => $locale, 'tagSlug' => $tag->slug]) }}" class="blog-tag-pill">#{{ $tag->name }}</a>
				@endforeach
			</div>
		@endif

		@if ($categoryPillar)
			<div class="blog-cta-inline">
				<h4>Volledige gids: {{ $categoryPillar->title }}</h4>
				<p>Dit artikel is onderdeel van onze uitgebreide {{ $post->category->name }}-gids. Lees de pillar voor het complete plaatje.</p>
				<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $categoryPillar->slug]) }}">Lees de pillar →</a>
			</div>
		@endif
	</article>

	<div class="blog-related">
		<div class="blog-section-title">
			<h2>Verwant leesmateriaal</h2>
		</div>
		<div class="blog-grid">
			@foreach ($related as $r)
				<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $r->slug]) }}" class="blog-card">
					<span class="blog-card-cat">{{ $r->category->name }}</span>
					<h3 class="blog-card-title">{{ $r->title }}</h3>
					<p class="blog-card-excerpt">{{ $r->excerpt }}</p>
					<div class="blog-card-meta">
						<span>{{ $r->reading_time_min }} min</span>
					</div>
				</a>
			@endforeach
		</div>
	</div>

	<div class="blog-section" style="text-align: center;">
		<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => $post->category->slug]) }}"
			class="blog-category-pill blog-category-pill-active">
			← Alle artikelen in "{{ $post->category->name }}"
		</a>
	</div>
</div>

@endsection
