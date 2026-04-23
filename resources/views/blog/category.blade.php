@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

@section('title', $category->name . ' — Blog — ' . config('app.name'))
@section('description', $category->intro ?: ('Artikelen over ' . $category->name . ' op het Betergeregeld-blog.'))

@section('content')

@include('blog._styles')

<div class="blog-root">
	<div class="blog-container" style="padding-top: 32px;">
		<nav class="blog-crumbs">
			<a href="{{ route('blog.index', ['locale' => $locale]) }}">Blog</a>
			<span>›</span>
			<span>{{ $category->name }}</span>
		</nav>
	</div>

	<div class="blog-hero" style="padding-top: 16px;">
		<h1>{{ $category->pillar_title ?: $category->name }}</h1>
		@if ($category->intro)
			<p>{{ $category->intro }}</p>
		@endif
	</div>

	@if ($pillar)
		<div class="blog-section">
			<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $pillar->slug]) }}" class="blog-featured-card">
				<span class="blog-card-cat">★ Pillar-gids</span>
				<h3>{{ $pillar->title }}</h3>
				<p>{{ $pillar->excerpt }}</p>
				<div class="blog-card-meta" style="color: rgba(255,255,255,.55);">
					<span>{{ $pillar->reading_time_min }} min leestijd</span>
				</div>
			</a>
		</div>
	@endif

	<div class="blog-section">
		<div class="blog-section-title">
			<h2>Alle artikelen in deze categorie</h2>
			<span style="font-size: 13px; color: var(--bl-ink-muted);">{{ $posts->total() }} artikelen</span>
		</div>
		<div class="blog-grid">
			@foreach ($posts as $post)
				<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}" class="blog-card">
					<span class="blog-card-cat">{{ $post->category->name }}</span>
					<h3 class="blog-card-title">{{ $post->title }}</h3>
					<p class="blog-card-excerpt">{{ $post->excerpt }}</p>
					<div class="blog-card-meta">
						<span>{{ $post->reading_time_min }} min</span>
						<span>·</span>
						<span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
					</div>
				</a>
			@endforeach
		</div>
		<div class="blog-pager">{{ $posts->links() }}</div>
	</div>

	<div class="blog-section">
		<div class="blog-section-title">
			<h2>Andere thema's</h2>
		</div>
		<div class="blog-category-list">
			@foreach ($otherCategories as $c)
				<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => $c->slug]) }}"
					class="blog-category-pill">{{ $c->name }} <span style="opacity:.55">·&nbsp;{{ $c->posts_count }}</span></a>
			@endforeach
		</div>
	</div>
</div>

@endsection
