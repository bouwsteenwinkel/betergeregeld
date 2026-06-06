@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

@section('title', 'Artikelen met tag "' . $tag->name . '", Blog')
@section('description', 'Alle blog-artikelen met het onderwerp ' . $tag->name . '.')

@section('content')

@include('blog._styles')

<div class="blog-root">
	<div class="blog-container" style="padding-top: 32px;">
		<nav class="blog-crumbs">
			<a href="{{ route('blog.index', ['locale' => $locale]) }}">Blog</a>
			<span>›</span>
			<span>Tag: {{ $tag->name }}</span>
		</nav>
	</div>

	<div class="blog-hero" style="padding-top: 16px;">
		<h1>#{{ $tag->name }}</h1>
		<p>{{ $posts->total() }} artikelen met dit onderwerp</p>
	</div>

	<div class="blog-section">
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
</div>

@endsection
