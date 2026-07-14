{{-- Bespoke provincie-pagina voor bedrijfswebsite, in homepage-stijl.
     Data: $site, $provName, $provSlug, $provPlaces (slug => naam). --}}
@extends('channels.layout')

@section('title', 'Website laten maken in ' . $provName)
@section('description', 'Website, webshop of klantenportaal laten maken in ' . $provName . '. Bekijk de plaatsen waar we voor ondernemers werken.')

@section('content')
    @include('channels._sales._bg-page-styles')

    @include('channels._sales._bg-tool-hero', [
        'heroEyebrow' => 'Provincie ' . $provName,
        'heroTitle'   => 'Website laten maken in ' . $provName,
        'heroSub'     => 'Meer klanten uit ' . $provName . '. Typ je bedrijfsnaam en zie meteen een voorbeeld van jouw website.',
    ])

    <section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #fff; border-top: 1px solid #E5E3DF;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
            <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.1; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; color: #1A1A1A;">Plaatsen in {{ $provName }}</h2>
            <p style="font-size: 18px; color: #6B6864; margin: 0 0 36px; max-width: 46ch;">{{ count($provPlaces) }} plaatsen in {{ $provName }} waar we ondernemers online laten groeien.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap: 12px;">
                @foreach ($provPlaces as $slug => $naam)
                    <a href="{{ $site->url('plaatsen/' . $slug) }}" class="bg-card" style="padding: 16px 18px; background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: 700; color: #1A1A1A;">{{ $naam }}</a>
                @endforeach
            </div>
            <p style="margin: 36px 0 0;"><a href="{{ $site->url('plaatsen') }}" class="bg-alink" style="font-size: 16px; font-weight: 700; color: #12386B; text-decoration: none;">&larr; Alle provincies</a></p>
        </div>
    </section>

    @include('channels._sales._bg-cta', [
        'ctaTitle' => 'Meer klanten in ' . $provName . '?',
        'ctaSub'   => 'Bekijk gratis en vrijblijvend hoe jouw website eruit kan zien.',
    ])
@endsection
