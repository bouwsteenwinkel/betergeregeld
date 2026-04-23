@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

@section('title', 'Blog — ' . config('app.name'))
@section('description', 'Praktische gidsen over toegangsbeheer, IT-governance, compliance en MKB-administratie. Geschreven door het Betergeregeld-team.')

@section('content')

@include('blog._styles')

<div class="blog-root">
	<div class="blog-hero">
		<h1>Blog</h1>
		<p>Praktische gidsen over toegangsbeheer, IT-governance, compliance en MKB-administratie — direct bruikbaar, zonder jargon.</p>
	</div>

	<div class="blog-section">
		<div class="blog-category-list" style="justify-content: center;">
			@foreach ($categories as $c)
				<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => $c->slug]) }}"
					class="blog-category-pill">{{ $c->name }} <span style="opacity:.55">·&nbsp;{{ $c->posts_count }}</span></a>
			@endforeach
		</div>
	</div>

	@if ($featured->isNotEmpty())
		<div class="blog-section">
			<div class="blog-section-title">
				<h2>Uitgelicht</h2>
			</div>
			<div class="blog-grid">
				@foreach ($featured as $i => $post)
					<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
						class="{{ $i === 0 ? 'blog-featured-card' : 'blog-card' }}">
						<span class="blog-card-cat">{{ $post->category->name }}</span>
						@if ($i === 0)
							<h3>{{ $post->title }}</h3>
							<p>{{ $post->excerpt }}</p>
						@else
							<h3 class="blog-card-title">{{ $post->title }}</h3>
							<p class="blog-card-excerpt">{{ $post->excerpt }}</p>
						@endif
						<div class="blog-card-meta">
							<span>{{ $post->reading_time_min }} min leestijd</span>
							<span>·</span>
							<span>{{ $post->published_at?->translatedFormat('d F Y') }}</span>
						</div>
					</a>
				@endforeach
			</div>
		</div>
	@endif

	@if ($pillars->isNotEmpty())
		<div class="blog-section">
			<div class="blog-section-title">
				<h2>★ Pillar-gidsen</h2>
				<p style="margin:0; font-size: 13px; color: var(--bl-ink-muted);">Diepgaande overzichten per thema</p>
			</div>
			<div class="blog-grid">
				@foreach ($pillars as $post)
					<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}" class="blog-card blog-card-pillar">
						<span class="blog-card-cat">{{ $post->category->name }}</span>
						<h3 class="blog-card-title">{{ $post->title }}</h3>
						<p class="blog-card-excerpt">{{ $post->excerpt }}</p>
						<div class="blog-card-meta">
							<span>{{ $post->reading_time_min }} min</span>
						</div>
					</a>
				@endforeach
			</div>
		</div>
	@endif

	<div class="blog-section">
		<div class="blog-section-title">
			<h2>Recente artikelen</h2>
		</div>
		<div class="blog-grid">
			@foreach ($recent as $post)
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
		<div class="blog-pager">{{ $recent->links() }}</div>
	</div>
</div>

@endsection
