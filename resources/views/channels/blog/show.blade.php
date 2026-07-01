@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', ($post->meta_title ?: $post->title) . ' — ' . $site->name())
@section('description', $post->excerpt)
@section('canonical', $site->url('blog/' . $post->slug))

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
		</section>

		<section style="padding-top:0">
			<div class="wrap prose" style="max-width:740px">
				{!! $post->body !!}
			</div>
		</section>
	</article>

	@include('channels.partials.lead-form')
@endsection
