@extends('layouts.app')

@php $locale = app()->getLocale(); @endphp

{{-- Paginering: elke pagina verwijst naar zichzelf, niet naar pagina 1.
     Alleen binnen het bereik — zie blog/index.blade.php. --}}
@section('canonical', url('/' . $locale . '/blog/tag/' . $tag->slug) . ($posts->currentPage() > 1 && $posts->currentPage() <= $posts->lastPage() ? '?page=' . $posts->currentPage() : ''))

{{-- Geen index. 312 tagpagina's stonden naast 572 artikelen in de sitemap en
     haalden in 90 dagen samen 32 vertoningen (gemeten 11-09-2026): lijstjes met
     dezelfde artikelen als de categoriepagina's, die Googles aandacht van de
     artikelen zelf afhaalden. "follow" blijft, zodat de links naar de artikelen
     wel meetellen. Ze staan ook niet meer in de sitemap. --}}
@section('robots', 'noindex,follow')

@section('title', \App\Support\PaginaTitel::met($tag->name . ' – Blog'))
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
