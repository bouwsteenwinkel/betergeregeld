@php
    /** @var \App\Models\Channel\Block $block */
    /** @var \App\Support\ChannelSite $site */
    $img    = $site->image('hero');
    $srcset = $site->imageSrcset('hero');
@endphp
{{-- Split-hero: LINKS de hero-tekst, RECHTS een preview van een online afspraak-
     planner. Compacte hoogte. Hergebruikt .hero--shot (foto + overlay). --}}
<style>
    .hero--shot{min-height:auto}
    .tdh-grid{display:grid;grid-template-columns:1fr minmax(0,360px);gap:2.4rem;align-items:center;width:100%;padding:1.2rem 0}
    @media(max-width:900px){.tdh-grid{grid-template-columns:1fr;gap:1.6rem;padding:.4rem 0}}
    .tdh-copy h1{max-width:16ch}
    .tdh-copy p.lead{max-width:40ch}
    /* Tool-preview kaart (rechts) */
    .tdtool{background:#fff;color:var(--c-ink);border-radius:calc(var(--radius) + 4px);padding:1.1rem 1.1rem 1.2rem;box-shadow:0 26px 60px -24px rgba(0,0,0,.55);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .tdtool-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem}
    .tdtool-badge{font-weight:800;font-size:.9rem;display:inline-flex;align-items:center;gap:.4rem}
    .tdtool-live{font-size:.6rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--c-primary);background:color-mix(in srgb,var(--c-accent) 22%,transparent);padding:.22rem .5rem;border-radius:999px}
    .tdtool-q{font-size:.78rem;font-weight:700;color:var(--c-muted);margin:.1rem 0 .5rem}
    .tdtool-pills{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.8rem}
    .tdtool-pills span{font-size:.8rem;font-weight:600;padding:.38rem .68rem;border-radius:999px;border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);color:var(--c-muted)}
    .tdtool-pills span.on{background:var(--c-primary);color:#fff;border-color:var(--c-primary)}
    .tdtool-row{display:flex;align-items:center;justify-content:space-between;font-size:.85rem;padding:.5rem 0;border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .tdtool-row span{color:var(--c-muted)}
    .tdtool-row b{font-weight:700}
    .tdtool-result{margin:.8rem 0 .1rem;padding:.8rem .95rem;border-radius:var(--radius);background:color-mix(in srgb,var(--c-accent) 14%,transparent);display:grid;gap:.1rem}
    .tdtool-result span{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--c-muted)}
    .tdtool-result strong{font-size:1.45rem;font-weight:800;color:var(--c-ink);line-height:1.1}
    .tdtool-result small{font-size:.73rem;color:var(--c-muted)}
    .tdtool-cta{width:100%;margin-top:.85rem}
    .tdtool-cap{font-size:.71rem;color:var(--c-muted);text-align:center;margin-top:.6rem;line-height:1.4}
</style>
<section class="hero @if ($img) hero--shot @endif" data-block="hero">
    @if ($img)
        <div class="hero-bg">
            <img src="{{ $img }}" @if ($srcset) srcset="{{ $srcset }}" sizes="100vw" @endif alt="Tandarts aan het werk in een moderne praktijk" loading="eager" fetchpriority="high">
        </div>
    @endif
    <div class="wrap">
        <div class="tdh-grid">
            {{-- LINKS: hero-tekst (geen eyebrow — afspraak) --}}
            <div class="tdh-copy">
                <h1>{{ $block->c('title', $site->name()) }}</h1>
                @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
                <a href="#gratis-voorbeeld" class="btn">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
                @if ($block->c('usps'))
                    <ul class="hero-usps">
                        @foreach ((array) $block->c('usps') as $usp)<li>{{ is_array($usp) ? ($usp['text'] ?? '') : $usp }}</li>@endforeach
                    </ul>
                @endif
            </div>

            {{-- RECHTS: preview van een online afspraak-planner --}}
            <aside class="tdtool" aria-label="Voorbeeld: online afspraak-planner">
                <div class="tdtool-head">
                    <span class="tdtool-badge"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="color:var(--c-primary)" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg> Direct online afspraak</span>
                    <span class="tdtool-live">voorbeeld</span>
                </div>
                <p class="tdtool-q">Waarvoor kom je langs?</p>
                <div class="tdtool-pills">
                    <span class="on">Controle</span>
                    <span>Mondhygiëne</span>
                    <span>Pijnklacht</span>
                </div>
                <div class="tdtool-row"><span>Wanneer?</span><b>Deze week &#9662;</b></div>
                <div class="tdtool-row"><span>Nieuwe patiënt?</span><b>Ja &#9662;</b></div>
                <div class="tdtool-result">
                    <span>Eerst mogelijke afspraak</span>
                    <strong>Morgen, 14:20</strong>
                    <small>Direct bevestigd &middot; 24/7 zelf te plannen</small>
                </div>
                <a href="#gratis-voorbeeld" class="btn tdtool-cta">Plan je afspraak</a>
                <p class="tdtool-cap">Voorbeeld van een online afspraak-tool die straks op jóuw site kan staan.</p>
            </aside>
        </div>
    </div>
</section>
