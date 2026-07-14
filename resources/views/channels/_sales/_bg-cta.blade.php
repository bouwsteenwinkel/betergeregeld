{{-- Homepage-stijl slot-CTA naar de voorbeeld-tool. Params: $ctaTitle (verplicht),
     $ctaSub (optioneel). Verwacht $site in scope. --}}
@php $toolUrl = $site->url('voorbeeld-maken'); @endphp
<section style="padding: 64px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF; font-family: 'Archivo', system-ui, sans-serif;">
    <div style="max-width: 1120px; margin: 0 auto; padding: 0 24px; text-align: center;">
        <h2 style="font-size: clamp(28px, 3.6vw, 42px); line-height: 1.06; letter-spacing: -0.02em; font-weight: 900; color: #1A1A1A; margin: 0 0 14px;">{{ $ctaTitle }}</h2>
        @isset($ctaSub)
            <p style="font-size: 18px; color: #6B6864; margin: 0 auto 28px; max-width: 46ch;">{{ $ctaSub }}</p>
        @endisset
        <a href="{{ $toolUrl }}" style="display: inline-block; background: #12386B; color: #fff; padding: 16px 32px; border-radius: 6px; font-size: 17px; font-weight: 700; text-decoration: none;">Bekijk mijn gratis voorbeeld &rarr;</a>
    </div>
</section>
