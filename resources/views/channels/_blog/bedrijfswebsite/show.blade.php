{{-- Bespoke blog-artikel voor bedrijfswebsite, in homepage-stijl.
     Unieke content (titel, body-HTML, datum, leestijd, coverbeeld) blijft behouden.
     Data: $site, $post (BlogPost). --}}
@php
    $ld = [
        '@context'      => 'https://schema.org',
        '@type'         => 'BlogPosting',
        'headline'      => $post->title,
        'description'   => $post->excerpt,
        'datePublished' => optional($post->published_at)->toIso8601String(),
        'dateModified'  => optional($post->updated_at ?? $post->published_at)->toIso8601String(),
        'author'        => ['@type' => 'Organization', 'name' => $site->displayName()],
        'publisher'     => ['@type' => 'Organization', 'name' => $site->displayName()],
    ];
    if (! empty($post->image)) {
        $ld['image'] = $post->image;
    }
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site->url('')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $site->url('blog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $site->url('blog/' . $post->slug)],
        ],
    ];
@endphp
@extends('channels.layout')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->excerpt)
@section('canonical', $site->url('blog/' . $post->slug))
@section('og_type', 'article')
@if (! empty($post->image))
    @section('og_image', $post->image)
@endif

@push('head')
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    @include('channels._sales._bg-page-styles')

    @push('head')
    <style>
        /* Mobiel: voorkom horizontale overflow uit body-HTML (brede code/tabellen/embeds) */
        .bg-prose pre { max-width: 100%; overflow-x: auto; white-space: pre; -webkit-overflow-scrolling: touch; }
        .bg-prose code { overflow-wrap: anywhere; word-break: break-word; }
        .bg-prose table { display: block; max-width: 100%; overflow-x: auto; }
        .bg-prose iframe, .bg-prose video, .bg-prose embed { max-width: 100%; }
        .bg-prose { overflow-wrap: anywhere; }

        /* Mobiel: terug-link krijgt een tikvlak van 44px zonder de positie te verschuiven */
        @media (max-width: 640px) {
            .bg-alink {
                display: inline-block;
                padding: 14px 0;
                margin: -14px 0;
            }
        }
    </style>
    @endpush

    <section style="padding: 48px 0 8px;">
        <div style="max-width: 760px; margin: 0 auto; padding: 0 24px;">
            <a href="{{ $site->url('blog') }}" class="bg-alink" style="font-size: 15px; font-weight: 700; color: #12386B; text-decoration: none;">&larr; Alle artikelen</a>
            <h1 style="font-size: clamp(30px, 4.4vw, 46px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 20px 0 14px; color: #1A1A1A;">{{ $post->title }}</h1>
            <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #8A8681;">
                @if ($post->published_at){{ $post->published_at->translatedFormat('j F Y') }}@endif
                @if ($post->reading_time_min) &middot; {{ $post->reading_time_min }} min lezen @endif
            </div>
        </div>
    </section>

    @if (! empty($post->image))
        <section style="padding: 24px 0;">
            <div style="max-width: 900px; margin: 0 auto; padding: 0 24px;">
                <img src="{{ $post->image }}" alt="{{ $post->title }}" style="width: 100%; height: auto; border-radius: 10px; display: block;">
            </div>
        </section>
    @endif

    <article style="padding: 24px 0 56px;">
        <div class="bg-prose" style="max-width: 720px; margin: 0 auto; padding: 0 24px;">
            {!! $post->body !!}
        </div>
    </article>

    @include('channels._sales._bg-cta', [
        'ctaTitle' => 'Zelf een sterke website?',
        'ctaSub'   => 'Typ je bedrijfsnaam en zie binnen een minuut hoe die van jou eruit kan zien.',
    ])
@endsection
