@php
    /** @var \App\Models\Channel\Block $block */
    $heroImg = $site->image('hero');
    $heroSet = $site->imageSrcset('hero');
    $fullBleed = (bool) $block->c('full_bleed');
    $isPreview = (bool) $site->get('meta.preview.is_preview');
@endphp

@if ($fullBleed)
    {{-- Full-width hero: kleur-band tot het (branche-)beeld er is, dan het beeld
         over de volle breedte met een scrim zodat de tekst leesbaar blijft. Op
         preview-sites genereert JS het beeld asynchroon en fade't het in. --}}
    <section class="hero hero-full {{ $heroImg ? 'has-img' : '' }}" data-block="hero" data-hero-slot="hero"
             @if (! $heroImg && $isPreview) data-hero-pending="1" @endif
             @if ($heroImg) style="--hero-img:url('{{ $heroImg }}')" @endif>
        <div class="hero-full-scrim"></div>
        <div class="wrap">
            <div class="hero-full-inner">
                <h1>{{ $block->c('title', $site->name()) }}</h1>
                @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
                <div class="hero-full-cta">
                    <a href="{{ $site->navHref($block->c('cta_href', '#gratis-voorbeeld')) }}" class="btn hero-btn-primary">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
                    @if ($block->c('cta2_label'))<a href="{{ $site->navHref($block->c('cta2_href', '#gratis-voorbeeld')) }}" class="btn hero-btn-ghost">{{ $block->c('cta2_label') }}</a>@endif
                </div>
            </div>
        </div>
    </section>
    <style>
        .hero-full{position:relative;overflow:hidden;padding:0;min-height:clamp(520px,72vh,760px);display:flex;align-items:center;
            background:linear-gradient(120deg,var(--c-primary),var(--c-hero-grad-b))}
        /* Subtiele 2e-accent gloed rechtsboven voor diepte zolang er nog geen fotobeeld is. */
        .hero-full::after{content:"";position:absolute;top:-30%;right:-10%;width:60%;height:120%;background:radial-gradient(circle,color-mix(in srgb,var(--c-accent-2) 55%,transparent),transparent 62%);opacity:.5;pointer-events:none;z-index:0}
        .hero-full.has-img::after{opacity:0}
        .hero-full::before{content:"";position:absolute;inset:0;background-image:var(--hero-img,none);background-size:cover;background-position:center;opacity:0;transition:opacity .8s ease;z-index:0}
        .hero-full.has-img::before{opacity:1}
        /* Scrim: donkere verloop links zodat witte tekst altijd leesbaar is, ook op het beeld. */
        .hero-full-scrim{position:absolute;inset:0;background:linear-gradient(90deg,rgba(10,14,26,.72) 0%,rgba(10,14,26,.45) 42%,rgba(10,14,26,.05) 78%)}
        .hero-full .wrap{position:relative;z-index:1;width:100%}
        .hero-full-inner{max-width:600px;color:#fff}
        .hero-full-inner h1{color:#fff;margin:0 0 1rem}
        .hero-full-inner .lead{color:rgba(255,255,255,.92)}
        .hero-full-eyebrow{display:inline-block;font-size:.78rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#fff;background:rgba(255,255,255,.16);padding:.32rem .7rem;border-radius:999px;margin-bottom:1rem}
        .hero-full-cta{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:1.4rem}
        .hero-btn-primary{background:#fff;color:var(--c-ink);border:0}
        .hero-btn-primary:hover{background:#f1f5f9}
        .hero-btn-ghost{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.7)}
        .hero-btn-ghost:hover{background:rgba(255,255,255,.12)}
        .hero-full-note{color:rgba(255,255,255,.8);font-size:.9rem;margin-top:1rem}
        @media (max-width:640px){.hero-full-scrim{background:linear-gradient(180deg,rgba(10,14,26,.35),rgba(10,14,26,.7))}}
    </style>
@else
    <section class="hero" data-block="hero">
        <div class="wrap">
            <div @if ($heroImg) class="grid cols-2" style="align-items:start;gap:2.5rem" @endif>
                <div>
                    @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
                    <h1>{{ $block->c('title', $site->name()) }}</h1>
                    @if ($block->c('sub'))<p class="lead">{{ $block->c('sub') }}</p>@endif
                    <a href="{{ $site->navHref($block->c('cta_href', '#gratis-voorbeeld')) }}" class="btn">{{ $block->c('cta_label', 'Gratis voorbeeld aanvragen') }}</a>
                    @if ($block->c('cta2_label'))<a href="{{ $site->navHref($block->c('cta2_href', '#gratis-voorbeeld')) }}" class="btn btn-ghost" style="margin-left:.6rem">{{ $block->c('cta2_label') }}</a>@endif
                    @if ($block->c('note'))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $block->c('note') }}</p>@endif

                    @if ($block->c('usps'))
                        <ul class="hero-usps">
                            @foreach ((array) $block->c('usps') as $usp)<li>{{ is_array($usp) ? ($usp['text'] ?? '') : $usp }}</li>@endforeach
                        </ul>
                    @endif
                </div>

                @if ($heroImg)
                    <div>
                        <img src="{{ $heroImg }}"
                             @if ($heroSet) srcset="{{ $heroSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                             alt="{{ $site->name() }}" loading="eager" decoding="async"
                             style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
