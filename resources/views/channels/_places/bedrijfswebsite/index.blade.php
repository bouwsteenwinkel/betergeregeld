{{-- Bespoke plaatsen-overzicht voor bedrijfswebsite, in homepage-stijl.
     Data: $site, $provinces (slug => ['name','slug','count']), $placesList. --}}
@php
    $pl    = (array) $site->get('places', []);
    $h1    = $pl['h1'] ?? 'Website laten maken in heel Nederland';
    $intro = $pl['intro'] ?? 'Kies je provincie en zie wat we voor ondernemers in jouw regio doen.';
@endphp
@extends('channels.layout')

@section('title', $h1)
@section('description', \Illuminate\Support\Str::limit($intro, 155))

@section('content')
    @include('channels._sales._bg-page-styles')

    @include('channels._sales._bg-tool-hero', [
        'heroEyebrow' => 'Overal in Nederland',
        'heroTitle'   => $h1,
        'heroSub'     => $intro,
    ])

    <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #fff; border-top: 1px solid #E5E3DF;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; color: #1A1A1A;">Kies je provincie</h2>
            <p style="font-size: 18px; color: #6B6864; margin: 0 0 36px; max-width: 46ch;">We werken voor ondernemers in heel Nederland. Bekijk per provincie de plaatsen waar we actief zijn.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr)); gap: 16px;">
                @foreach ($provinces as $prov)
                    <a href="{{ $site->url('plaatsen/provincie/' . $prov['slug']) }}" class="bg-card" style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 20px 22px; background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; text-decoration: none;">
                        <span style="font-size: 18px; font-weight: 800; letter-spacing: -0.01em; color: #1A1A1A;">{{ $prov['name'] }}</span>
                        <span style="font-size: 14px; font-weight: 600; color: #8A8681; white-space: nowrap;">{{ $prov['count'] }} plaatsen</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('channels._sales._bg-cta', [
        'ctaTitle' => 'Klaar om online te groeien?',
        'ctaSub'   => 'Vertel kort wat je doet en zie binnen één werkdag hoe jouw website eruit kan zien.',
    ])
@endsection
