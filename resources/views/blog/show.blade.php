@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

@section('title', ($post->meta_title ?: $post->title) . ', Blog, ' . config('app.name'))
@section('description', $post->excerpt)
@section('og_type', 'article')

@push('head')
	{{-- Canonical, og:title, og:description en hreflang komen al uit layouts/app.blade.php
		 op basis van URL + @section('title')/@section('description'). Hier alleen blog-
		 specifieke article-meta + RSS-link + Article schema. --}}
	<link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}, Blog" href="{{ route('blog.rss', ['locale' => $locale]) }}">
	<meta property="article:section" content="{{ $post->category->name }}">
	@if ($post->published_at)
		<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
	@endif
	@if ($post->updated_at)
		<meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
	@endif

	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"      => 'https://schema.org',
		"\x40type"         => 'Article',
		'headline'      => $post->title,
		'description'   => $post->excerpt,
		'inLanguage'    => $locale,
		'datePublished' => optional($post->published_at)->toIso8601String(),
		'dateModified'  => optional($post->updated_at)->toIso8601String(),
		'mainEntityOfPage' => [
			"\x40type" => 'WebPage',
			"\x40id"   => route('blog.show', ['locale' => $locale, 'slug' => $post->slug]),
		],
		'articleSection' => $post->category->name,
		'keywords'       => $post->tags->pluck('name')->implode(', '),
		'author'         => [
			"\x40type" => 'Organization',
			'name'  => 'Beter Geregeld ICT',
			'url'   => url('/'),
		],
		'publisher'      => [
			"\x40type" => 'Organization',
			'name'  => 'Beter Geregeld ICT',
			"\x40id"   => url('/#organization'),
		],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>

	{{-- Breadcrumb-kruimelpad (Home › Blog › Categorie › Artikel) voor de rich-result
	     in Google; \x40 omdat Blade een letterlijke @ als directive zou lezen. --}}
	<script type="application/ld+json">
	{!! json_encode([
		"\x40context"     => 'https://schema.org',
		"\x40type"        => 'BreadcrumbList',
		'itemListElement' => [
			["\x40type" => 'ListItem', 'position' => 1, 'name' => 'Home',                'item' => url('/' . $locale)],
			["\x40type" => 'ListItem', 'position' => 2, 'name' => 'Blog',                'item' => route('blog.index', ['locale' => $locale])],
			["\x40type" => 'ListItem', 'position' => 3, 'name' => $post->category->name, 'item' => route('blog.category', ['locale' => $locale, 'categorySlug' => $post->category->slug])],
			["\x40type" => 'ListItem', 'position' => 4, 'name' => $post->title,          'item' => route('blog.show', ['locale' => $locale, 'slug' => $post->slug])],
		],
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
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
