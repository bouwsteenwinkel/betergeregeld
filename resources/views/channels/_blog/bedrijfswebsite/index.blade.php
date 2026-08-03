{{-- Bespoke blog-overzicht voor bedrijfswebsite, in homepage-stijl.
     Data: $site, $posts (LengthAwarePaginator<BlogPost>). --}}
@extends('channels.layout')

@section('title', 'Blog over websites en online groeien voor ondernemers | betergeregeld')
@section('description', 'Praktische tips over websites, vindbaarheid en automatisering voor ondernemers.')
@section('canonical', $site->url('blog'))

@section('content')
    @include('channels._sales._bg-page-styles')

    <section style="padding: 56px calc(50vw - 50%) 40px; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #1D5DA0; margin-bottom: 14px;">Blog</div>
            <h1 style="font-size: clamp(30px, 4.4vw, 46px); line-height: 1.08; letter-spacing: -0.01em; font-weight: 800; margin: 0 0 14px; max-width: 20ch; color: #1A1A1A;">Praktische tips voor ondernemers</h1>
            <p style="font-size: 19px; line-height: 1.45; color: #4A4844; max-width: 48ch; margin: 0;">Over websites, online gevonden worden en slim automatiseren, in gewone taal.</p>
        </div>
    </section>

    <section style="padding: 24px 0 64px;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            @forelse ($posts as $post)
                @if ($loop->first)
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap: 28px;">
                @endif
                        <a href="{{ $site->url('blog/' . $post->slug) }}" class="bg-card" style="display: flex; flex-direction: column; background: #fff; border: 1.5px solid #E5E3DF; border-radius: 10px; overflow: hidden; text-decoration: none;">
                            @if (! empty($post->image))
                                <img src="{{ $post->image }}" alt="{{ $post->title }}" loading="lazy" style="width: 100%; height: 190px; object-fit: cover; display: block;">
                            @else
                                <div style="width: 100%; height: 190px; background: linear-gradient(135deg, #12386B, #1D5DA0);"></div>
                            @endif
                            <div style="padding: 22px; display: flex; flex-direction: column; flex: 1;">
                                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #8A8681; margin-bottom: 10px;">
                                    @if ($post->published_at){{ $post->published_at->translatedFormat('j F Y') }}@endif
                                    @if ($post->reading_time_min) &middot; {{ $post->reading_time_min }} min lezen @endif
                                </div>
                                <h2 style="font-size: 21px; font-weight: 800; letter-spacing: -0.01em; line-height: 1.2; margin: 0 0 10px; color: #1A1A1A;">{{ $post->title }}</h2>
                                <p style="font-size: 16px; line-height: 1.55; color: #4A4844; margin: 0;">{{ \Illuminate\Support\Str::limit($post->excerpt, 130) }}</p>
                            </div>
                        </a>
                @if ($loop->last)
                    </div>
                @endif
            @empty
                <p style="font-size: 18px; color: #6B6864; padding: 40px 0;">Er zijn nog geen artikelen. Kom snel terug.</p>
            @endforelse

            @if ($posts->hasPages())
                <div style="display: flex; gap: 12px; margin-top: 44px;">
                    @if ($posts->previousPageUrl())
                        <a href="{{ $posts->previousPageUrl() }}" class="bg-alink" style="padding: 12px 20px; border: 1.5px solid #1A1A1A; border-radius: 6px; font-weight: 700; font-size: 15px; color: #1A1A1A; text-decoration: none;">&larr; Vorige</a>
                    @endif
                    @if ($posts->nextPageUrl())
                        <a href="{{ $posts->nextPageUrl() }}" class="bg-alink" style="padding: 12px 20px; border: 1.5px solid #1A1A1A; border-radius: 6px; font-weight: 700; font-size: 15px; color: #1A1A1A; text-decoration: none;">Volgende &rarr;</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @include('channels._sales._bg-cta', [
        'ctaTitle' => 'Liever meteen aan de slag?',
        'ctaSub'   => 'Vertel kort wat je doet en zie binnen één werkdag hoe jouw website eruit kan zien.',
    ])
@endsection
