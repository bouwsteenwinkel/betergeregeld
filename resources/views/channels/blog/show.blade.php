@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->excerpt)
@section('canonical', $site->url('blog/' . $post->slug))
@section('og_type', 'article')
@if ($post->image)@section('og_image', $post->image)@endif

@push('head')
    @php
        $articleLd = array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => 'BlogPosting',
            'headline'      => $post->title,
            'description'   => $post->excerpt,
            'image'         => $post->image ?: null,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified'  => optional($post->updated_at ?? $post->published_at)->toIso8601String(),
            'inLanguage'    => $site->locale(),
            'mainEntityOfPage' => $site->url('blog/' . $post->slug),
            'author'        => ['@type' => 'Organization', 'name' => $site->displayName()],
            'publisher'     => ['@type' => 'Organization', 'name' => $site->displayName()],
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
	<article>
		<section class="hero">
			<div class="wrap" style="max-width:760px">
				<a href="{{ $site->url('blog') }}" class="article-back">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
					Terug naar blog
				</a>
				<h1 style="margin-top:.8rem">{{ $post->title }}</h1>
				<p class="muted" style="margin-top:.7rem">{{ optional($post->published_at)->translatedFormat('j F Y') }} · {{ $post->reading_time_min }} min lezen</p>
			</div>
			@if ($post->image)
				<div class="wrap" style="max-width:900px;margin-top:1.4rem">
					<img src="{{ $post->image }}" alt="{{ $post->title }}" class="article-cover" loading="eager" decoding="async">
				</div>
			@endif
		</section>

		<section style="padding-top:0">
			<div class="wrap prose" style="max-width:740px">
				{!! $post->body !!}
			</div>
		</section>
	</article>

	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
