@php
    /** @var \App\Support\ChannelSite $site */
    // Herbruikbaar "Zo kan het worden"-blok: toont een productresultaat in een
    // browser-mockup, met een CTA naar het volledige live voorbeeld op de
    // demolaag (/voorbeeld/{facet}). Later per product te hergebruiken
    // (webshop, portaal/afspraken, automatisering, AI) door andere params.
    $facet     = $facet     ?? 'website';
    $label     = $label     ?? 'Website';
    $title     = $title     ?? 'Zo kan het worden';
    $intro     = $intro     ?? '';
    $brand     = $brand     ?? $site->name();
    $heroTitle = $heroTitle ?? $site->name();
    $urlLabel  = $urlLabel  ?? 'jouw-website.nl';
    $navCta    = $navCta    ?? 'Offerte';     // knop in de mockup-nav (bv. "Winkelmand")
    $heroBtn   = $heroBtn   ?? 'Gratis offerte'; // knop in de mockup-hero
    $bullets   = $bullets   ?? [];
    $ctaLabel  = $ctaLabel  ?? 'Bekijk het volledige voorbeeld';
    $galleryLabel = $galleryLabel ?? null;   // bv. "Recente badkamers", toont de esthetische kant
    // Thumbnails: string (url) of ['img' => url, 'price' => '€ ...'] voor shop-tegels.
    $gallery   = $gallery   ?? [];
    $href      = $site->url('voorbeeld/' . $facet);
    $img       = $site->image('hero');
    // Optionele, kant-en-klare voorbeeld-afbeelding (bv. een echte webshop-mockup
    // mét eigen browser-frame). Staat die er, dan tonen we die i.p.v. de CSS-mockup.
    $imageSlot  = $imageSlot ?? null;
    $previewImg = $imageSlot ? $site->image($imageSlot) : null;
    $previewSet = $imageSlot ? $site->imageSrcset($imageSlot) : '';
@endphp

@once
@push('head')
<style>
    .zkhw-grid{display:grid;gap:2rem;align-items:center;margin-top:1.6rem}
    @media(min-width:900px){.zkhw-grid{grid-template-columns:1.15fr .85fr;gap:3rem}}
    /* browser-venster */
    .zkhw-window{display:block;color:inherit;border-radius:calc(var(--radius) + 6px);overflow:hidden;background:#fff;
        box-shadow:0 34px 80px -30px rgba(0,0,0,.55);border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);
        transition:transform .2s ease,box-shadow .2s ease}
    .zkhw-window:hover{transform:translateY(-4px);box-shadow:0 44px 90px -30px rgba(0,0,0,.6)}
    .zkhw-bar{display:flex;align-items:center;gap:.5rem;padding:.6rem .9rem;background:#f1f3f6;border-bottom:1px solid rgba(0,0,0,.07)}
    .zkhw-dot{width:11px;height:11px;border-radius:50%;background:#d3d7de;flex:0 0 auto}
    .zkhw-dot:nth-child(1){background:#ff5f57}.zkhw-dot:nth-child(2){background:#febc2e}.zkhw-dot:nth-child(3){background:#28c840}
    .zkhw-url{margin-left:.6rem;flex:1;background:#fff;border-radius:999px;padding:.3rem .8rem;font-size:.76rem;color:#6b7280;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:var(--font)}
    /* nagebouwde mini-site */
    .zkhw-site{background:var(--c-bg)}
    .zkhw-nav{display:flex;align-items:center;gap:.7rem;padding:.7rem 1rem;background:color-mix(in srgb,var(--c-bg) 92%,#fff);border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .zkhw-brand{font-family:var(--font-display);font-weight:800;font-size:.95rem;color:var(--c-ink)}
    .zkhw-links{display:flex;gap:.5rem;margin-left:auto}
    .zkhw-links i{display:block;width:26px;height:7px;border-radius:4px;background:color-mix(in srgb,var(--c-ink) 14%,transparent)}
    .zkhw-navcta{background:var(--c-cta);color:var(--c-on-cta);font-weight:700;font-size:.72rem;padding:.32rem .7rem;border-radius:6px}
    .zkhw-hero{position:relative;min-height:190px;display:flex;align-items:center;background-size:cover;background-position:center}
    .zkhw-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,10,16,.82),rgba(8,10,16,.28))}
    .zkhw-hero-copy{position:relative;z-index:1;padding:1.2rem 1.4rem;max-width:74%}
    .zkhw-eyebrow-mini{display:inline-block;background:color-mix(in srgb,var(--c-accent) 85%,#000);color:#fff;font-weight:700;font-size:.62rem;letter-spacing:.06em;text-transform:uppercase;padding:.24rem .6rem;border-radius:999px;margin-bottom:.5rem}
    .zkhw-hero-copy strong{display:block;color:#fff;font-family:var(--font-display);font-size:1.16rem;line-height:1.18;text-shadow:0 2px 12px rgba(0,0,0,.5)}
    .zkhw-btn{display:inline-block;margin-top:.7rem;background:var(--c-cta);color:var(--c-on-cta);font-weight:700;font-size:.74rem;padding:.42rem .8rem;border-radius:6px}
    .zkhw-galrow{padding:.85rem 1rem 0;font-size:.66rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--c-muted)}
    .zkhw-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;padding:.5rem 1rem 1.1rem}
    .zkhw-cards span{position:relative;height:58px;border-radius:8px;background:var(--c-surface) center/cover no-repeat;border:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);box-shadow:0 6px 16px -12px rgba(0,0,0,.4);overflow:hidden}
    .zkhw-cards span em{position:absolute;left:5px;bottom:5px;font-style:normal;font-weight:800;font-size:.62rem;background:#fff;color:var(--c-ink);padding:.12rem .38rem;border-radius:5px;box-shadow:0 3px 8px -3px rgba(0,0,0,.5)}
    .zkhw-open{display:block;text-align:center;padding:.8rem;font-weight:700;font-size:.9rem;color:var(--c-cta);background:var(--c-surface);border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    /* tekstkolom */
    .zkhw-aside h3{font-size:1.15rem;margin-bottom:.3rem}
    .zkhw-list{list-style:none;margin:1rem 0 1.4rem;display:grid;gap:.7rem}
    .zkhw-list li{padding-left:1.8rem;position:relative;font-weight:600;color:var(--c-ink)}
    .zkhw-list li::before{content:"✓";position:absolute;left:0;top:-1px;display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:color-mix(in srgb,var(--c-accent) 20%,transparent);color:var(--c-primary);font-size:.72rem;font-weight:800}
    .zkhw-list li span{display:block;font-weight:400;font-size:.9rem;color:var(--c-muted)}
</style>
@endpush
@endonce

<section class="zkhw" data-section="zo-kan-het-worden-{{ $facet }}" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Zo kan het worden &middot; {{ $label }}</span>
        <h2>{{ $title }}</h2>
        @if ($intro)<p class="section-lead muted">{{ $intro }}</p>@endif

        <div class="zkhw-grid">
            {{-- Browser-mockup met CTA naar het volledige live voorbeeld --}}
            <div class="zkhw-window">
                @if ($previewImg)
                    <img src="{{ $previewImg }}" @if ($previewSet) srcset="{{ $previewSet }}" sizes="(max-width:900px) 92vw, 52vw" @endif
                         alt="{{ $ctaLabel }}" loading="lazy" decoding="async" style="width:100%;height:auto;display:block">
                @else
                <div class="zkhw-bar">
                    <span class="zkhw-dot"></span><span class="zkhw-dot"></span><span class="zkhw-dot"></span>
                    <span class="zkhw-url">{{ $urlLabel }}</span>
                </div>
                <div class="zkhw-site">
                    <div class="zkhw-nav">
                        <span class="zkhw-brand">{{ $brand }}</span>
                        <span class="zkhw-links"><i></i><i></i><i></i></span>
                        <span class="zkhw-navcta">{{ $navCta }}</span>
                    </div>
                    <div class="zkhw-hero" @if ($img) style="background-image:url('{{ $img }}')" @endif>
                        <div class="zkhw-hero-copy">
                            <span class="zkhw-eyebrow-mini">{{ $label }}</span>
                            <strong>{{ $heroTitle }}</strong>
                            <span class="zkhw-btn">{{ $heroBtn }}</span>
                        </div>
                    </div>
                    @if ($galleryLabel)<div class="zkhw-galrow">{{ $galleryLabel }}</div>@endif
                    <div class="zkhw-cards">
                        @forelse (array_slice($gallery, 0, 3) as $g)
                            @php
                                $gimg = is_array($g)
                                    ? ($g['img'] ?? (! empty($g['slot']) ? $site->image($g['slot']) : ''))
                                    : $g;
                                $gprice = is_array($g) ? ($g['price'] ?? null) : null;
                            @endphp
                            @continue(! $gimg)
                            <span style="background-image:url('{{ $gimg }}')">@if ($gprice)<em>{{ $gprice }}</em>@endif</span>
                        @empty
                            <span></span><span></span><span></span>
                        @endforelse
                    </div>
                </div>
                @endif
            </div>

            {{-- Tekstkolom: wat dit product oplevert --}}
            <div class="zkhw-aside">
                <h3>Wat het je oplevert</h3>
                <ul class="zkhw-list">
                    @foreach ($bullets as $b)
                        <li>{{ is_array($b) ? ($b['title'] ?? '') : $b }}
                            @if (is_array($b) && ! empty($b['text']))<span>{{ $b['text'] }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
