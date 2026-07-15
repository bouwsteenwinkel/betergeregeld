@php
    /** @var \App\Models\Channel\Block $block */
    /** @var \App\Support\ChannelSite $site */
    $img    = $site->image('hero');
    $srcset = $site->imageSrcset('hero');
@endphp
{{-- Split-hero: LINKS de hero-tekst, RECHTS een preview van een geautomatiseerde
     advies-/offertetool. Compacte hoogte. Hergebruikt .hero--shot (foto + overlay). --}}
<style>
    .hero--shot{min-height:auto}                 /* lager dan de standaard 74vh */
    .elh-grid{display:grid;grid-template-columns:1fr minmax(0,360px);gap:2.4rem;align-items:center;width:100%;padding:1.2rem 0}
    @media(max-width:900px){.elh-grid{grid-template-columns:1fr;gap:1.6rem;padding:.4rem 0}}
    .elh-copy h1{max-width:16ch}
    .elh-copy p.lead{max-width:40ch}
    /* Tool-preview kaart (rechts) */
    .eltool{background:#fff;color:var(--c-ink);border-radius:calc(var(--radius) + 4px);padding:1.1rem 1.1rem 1.2rem;box-shadow:0 26px 60px -24px rgba(0,0,0,.55);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .eltool-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem}
    .eltool-badge{font-weight:800;font-size:.9rem;display:inline-flex;align-items:center;gap:.4rem}
    .eltool-live{font-size:.6rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--c-primary);background:color-mix(in srgb,var(--c-accent) 22%,transparent);padding:.22rem .5rem;border-radius:999px}
    .eltool-q{font-size:.78rem;font-weight:700;color:var(--c-muted);margin:.1rem 0 .5rem}
    .eltool-pills{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.8rem}
    .eltool-pills span{font-size:.8rem;font-weight:600;padding:.38rem .68rem;border-radius:999px;border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);color:var(--c-muted)}
    .eltool-pills span.on{background:var(--c-primary);color:#fff;border-color:var(--c-primary)}
    .eltool-row{display:flex;align-items:center;justify-content:space-between;font-size:.85rem;padding:.5rem 0;border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .eltool-row span{color:var(--c-muted)}
    .eltool-row b{font-weight:700}
    .eltool-result{margin:.8rem 0 .1rem;padding:.8rem .95rem;border-radius:var(--radius);background:color-mix(in srgb,var(--c-accent) 14%,transparent);display:grid;gap:.1rem}
    .eltool-result span{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--c-muted)}
    .eltool-result strong{font-size:1.45rem;font-weight:800;color:var(--c-ink);line-height:1.1}
    .eltool-result small{font-size:.73rem;color:var(--c-muted)}
    .eltool-cta{width:100%;margin-top:.85rem}
    .eltool-cap{font-size:.71rem;color:var(--c-muted);text-align:center;margin-top:.6rem;line-height:1.4}
</style>
<section class="hero @if ($img) hero--shot @endif" data-block="hero">
    @if ($img)
        <div class="hero-bg">
            <img src="{{ $img }}" @if ($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif alt="Elektricien aan het werk" loading="eager" fetchpriority="high">
        </div>
    @endif
    <div class="wrap">
        <div class="elh-grid">
            {{-- LINKS: hero-tekst (geen eyebrow — afspraak) --}}
            <div class="elh-copy">
                <h1>{{ $block->c('title', $site->name()) }}</h1>
                @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
                <a href="#gratis-voorbeeld" class="btn">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
                @if ($block->c('usps'))
                    <ul class="hero-usps">
                        @foreach ((array) $block->c('usps') as $usp)<li>{{ is_array($usp) ? ($usp['text'] ?? '') : $usp }}</li>@endforeach
                    </ul>
                @endif
            </div>

            {{-- RECHTS: preview van een geautomatiseerde advies-/offertetool --}}
            <aside class="eltool" aria-label="Voorbeeld: automatische offerte-tool">
                <div class="eltool-head">
                    <span class="eltool-badge"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color:var(--c-primary)" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg> Directe offerte-tool</span>
                    <span class="eltool-live">voorbeeld</span>
                </div>
                <p class="eltool-q">Wat kan ik voor je regelen?</p>
                <div class="eltool-pills">
                    <span class="on">Laadpaal</span>
                    <span>Groepenkast</span>
                    <span>Verlichting</span>
                </div>
                <div class="eltool-row"><span>Type woning</span><b>Tussenwoning &#9662;</b></div>
                <div class="eltool-row"><span>Meterkast op orde?</span><b>Ja &#9662;</b></div>
                <div class="eltool-result">
                    <span>Richtprijs laadpaal, incl. montage</span>
                    <strong>&euro; 850 &ndash; &euro; 1.150</strong>
                    <small>Meteen een indicatie &middot; 24/7</small>
                </div>
                <a href="#gratis-voorbeeld" class="btn eltool-cta">Vraag je exacte offerte aan</a>
                <p class="eltool-cap">Voorbeeld van een tool die straks op jóuw site kan staan.</p>
            </aside>
        </div>
    </div>
</section>
