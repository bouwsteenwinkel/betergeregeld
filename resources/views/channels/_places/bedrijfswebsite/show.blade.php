{{-- Bespoke plaats-pagina (lokale SEO) voor bedrijfswebsite, in homepage-stijl.
     Unieke per-plaats content blijft behouden (koppen met plaatsnaam, intro/wie/trust,
     FAQ, echte lokale bedrijven, nabije plaatsen, JSON-LD, noindex voor dunne plaatsen).
     Data: $site, $place, $placeName, $placeSlug, $content, $business, $businesses,
     $nearby, $indexable. --}}
@php
    $c        = (array) $content;
    $h1       = $c['h1'] ?? ('Website laten maken in ' . $placeName);
    $heroLead = $c['hero_lead'] ?? \Illuminate\Support\Str::limit($c['intro'] ?? '', 150);
    $faq      = (array) ($c['faq'] ?? []);
    $ctaTitle = $c['cta_title'] ?? ('Meer klanten in ' . $placeName . '?');
    $ctaSub   = $c['cta'] ?? 'Typ je bedrijfsnaam en zie binnen een minuut hoe jouw website eruit kan zien.';

    $ld = [
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Service',
            'name'     => $h1,
            'areaServed' => ['@type' => 'City', 'name' => $placeName],
            'provider' => ['@type' => 'Organization', 'name' => $site->displayName()],
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site->url('')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Plaatsen', 'item' => $site->url('plaatsen')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $placeName, 'item' => $site->url('plaatsen/' . $placeSlug)],
            ],
        ],
    ];
    // FAQPage-schema als er FAQ-content is (rich results + GEO/AI-citaten).
    if (! empty($faq)) {
        $ld[] = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type'          => 'Question',
                'name'           => $f['q'] ?? '',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? ''],
            ], array_values($faq)),
        ];
    }
@endphp
@extends('channels.layout')

@section('title', $c['meta_title'] ?? $h1)
@section('description', $c['meta_description'] ?? \Illuminate\Support\Str::limit($c['intro'] ?? '', 155))
@unless ($indexable ?? true)
    @section('robots', 'noindex,follow')
@endunless

@push('head')
    @foreach ($ld as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endpush

@section('content')
    @include('channels._sales._bg-page-styles')

    @include('channels._sales._bg-tool-hero', [
        'heroEyebrow' => 'Website laten maken in ' . $placeName,
        'heroTitle'   => \Illuminate\Support\Str::ucfirst($h1),
        'heroSub'     => $heroLead,
    ])

    @if (! empty($c['intro']))
        <section style="padding: 56px 0;">
            <div style="max-width: 760px; margin: 0 auto; padding: 0 24px;">
                <p style="font-size: 19px; line-height: 1.7; color: #2E2C29; margin: 0;">{{ $c['intro'] }}</p>
            </div>
        </section>
    @endif

    {{-- Echte lokale bedrijven (alleen tonen als er data is; degradeert zacht). --}}
    @if (! empty($businesses))
        <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF;">
            <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
                <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; color: #1A1A1A;">{{ $business['label'] ?? ('Bedrijven in ' . $placeName) }}</h2>
                @if (! empty($business['intro']))
                    <p style="font-size: 18px; color: #6B6864; margin: 0 0 36px; max-width: 52ch;">{{ $business['intro'] }}</p>
                @endif
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr)); gap: 16px;">
                    @foreach ($businesses as $b)
                        @php
                            $rating  = (float) ($b['rating'] ?? 0);
                            $reviews = (int) ($b['reviews'] ?? 0);
                            $full    = (int) round($rating);
                        @endphp
                        <div style="background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; padding: 22px;">
                            <div style="font-size: 17px; font-weight: 800; letter-spacing: -0.01em; color: #1A1A1A; margin-bottom: 4px;">{{ $b['name'] ?? '' }}</div>
                            @if (! empty($b['type']))
                                <div style="font-size: 14px; color: #8A8681; font-weight: 600; margin-bottom: 10px;">{{ $b['type'] }}</div>
                            @endif
                            @if ($rating > 0)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #FBBC04; font-size: 15px; letter-spacing: 1px; white-space: nowrap;">@for ($i = 0; $i < 5; $i++)<span style="opacity: {{ $i < $full ? 1 : 0.25 }}">&#9733;</span>@endfor</span>
                                    <span style="font-size: 14px; color: #6B6864; font-weight: 600;">{{ number_format($rating, 1, ',', '') }}@if ($reviews) &middot; {{ $reviews }} reviews @endif</span>
                                </div>
                            @endif
                            @if (! empty($b['address']))
                                <div style="font-size: 13px; color: #8A8681; margin-top: 10px;">{{ $b['address'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <p style="font-size: 14px; color: #8A8681; margin: 22px 2px 0;">Bron: openbare Google-bedrijfsgegevens voor {{ $placeName }}.</p>
            </div>
        </section>
    @endif

    {{-- Waarom / vertrouwen (unieke per-plaats content). --}}
    @if (! empty($c['wie']) || ! empty($c['trust']))
        <section style="padding: 64px 0; border-top: 1px solid #E5E3DF;">
            <div style="max-width: 900px; margin: 0 auto; padding: 0 24px;">
                @if (! empty($c['wie']))
                    <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.12; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 16px; color: #1A1A1A;">Waarom ondernemers in {{ $placeName }} voor ons kiezen</h2>
                    <p style="font-size: 19px; line-height: 1.7; color: #2E2C29; margin: 0 0 28px;">{{ $c['wie'] }}</p>
                @endif
                @if (! empty($c['trust']))
                    <blockquote style="margin: 0; padding: 24px 28px; background: #F1EFEB; border-radius: 6px; border-left: 4px solid #12386B;">
                        <p style="font-size: 20px; line-height: 1.5; font-weight: 600; margin: 0; color: #1A1A1A;">{{ $c['trust'] }}</p>
                    </blockquote>
                @endif
            </div>
        </section>
    @endif

    {{-- FAQ (native accordion, geen JS nodig). --}}
    @if (! empty($faq))
        <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF;">
            <div style="max-width: 820px; margin: 0 auto; padding: 0 24px;">
                <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 28px; color: #1A1A1A;">Veelgestelde vragen</h2>
                @foreach ($faq as $item)
                    <details class="bg-faq" style="border-top: 1px solid #DAD7D2; padding: 18px 0;">
                        <summary style="font-size: 19px; font-weight: 700; color: #1A1A1A;">{{ $item['q'] ?? '' }}</summary>
                        <p style="font-size: 17px; line-height: 1.6; color: #4A4844; margin: 12px 0 0;">{{ $item['a'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Nabije plaatsen (interne links voor SEO). --}}
    @if (! empty($nearby))
        <section style="padding: 56px 0; border-top: 1px solid #E5E3DF;">
            <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
                <h2 style="font-size: 22px; font-weight: 800; letter-spacing: -0.01em; margin: 0 0 20px; color: #1A1A1A;">Ook actief in de buurt</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    @foreach ($nearby as $slug => $naam)
                        <a href="{{ $site->url('plaatsen/' . $slug) }}" class="bg-card" style="padding: 10px 16px; background: #fff; border: 1.5px solid #E5E3DF; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: #2E2C29;">{{ $naam }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('channels._sales._bg-cta', ['ctaTitle' => $ctaTitle, 'ctaSub' => $ctaSub])
@endsection
