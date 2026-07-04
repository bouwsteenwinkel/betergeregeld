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
    @if ($post->published_at)<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">@endif
    <meta property="article:modified_time" content="{{ optional($post->updated_at ?? $post->published_at)->toIso8601String() }}">
    <meta property="article:section" content="{{ $site->branche() }}">
    <meta property="article:author" content="{{ $site->displayName() }}">
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

	{{-- Interne links naar de diensten (helpt SEO en houdt lezers op de site). --}}
	<section style="background:var(--c-surface)">
		<div class="wrap" style="max-width:760px">
			<span class="kicker"><span class="kicker-line"></span> Lees ook</span>
			<h2 style="margin:.3rem 0 1rem">Wat we voor je kunnen bouwen</h2>
			<div class="grid cols-3" style="gap:.7rem">
				@foreach ((array) config('groeidiamant.facets', []) as $fk => $fv)
					<a href="{{ $site->url($fk) }}" class="feature-card" style="display:block;text-decoration:none;color:inherit">
						<h3 style="font-size:1.02rem">{{ $fv['label'] ?? $fk }}</h3>
						<span class="feature-rule"></span>
						<p style="font-size:.92rem">{{ $fv['tagline'] ?? '' }}</p>
					</a>
				@endforeach
			</div>
			<p style="margin-top:1rem"><a href="{{ $site->url('diensten') }}" style="font-weight:600">Bekijk alle diensten →</a></p>
		</div>
	</section>

	<div id="contact" class="scroll-anchor" aria-hidden="true"></div>
	@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => 'website'])
@endsection
