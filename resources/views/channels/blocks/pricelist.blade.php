@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); $plBg = $block->c('punchy') ? $site->image('hero') : null; @endphp
<section data-block="pricelist" @if ($block->c('anchor')) id="{{ $block->c('anchor') }}" @endif class="pricelist {{ $block->c('punchy') ? 'punchy' : '' }} {{ $plBg ? 'has-bg' : '' }}" @if ($plBg) style="--pl-bg:url('{{ $plBg }}')" @endif>
    <div class="wrap">
        <div class="pl-head">
            @if ($block->c('eyebrow'))<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $block->c('eyebrow') }}</span>@endif
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted pl-sub">{{ $block->c('sub') }}</p>@endif
        </div>

        <div class="pl-grid">
            @foreach ($items as $it)
                <div class="pl-card">
                    <div class="pl-name">{{ $it['name'] ?? '' }}</div>
                    @if (!empty($it['desc']))<p class="pl-desc">{{ $it['desc'] }}</p>@endif
                    @if (!empty($it['price']))
                        @php
                            // €-teken vóór het bedrag (alleen als er een getal in staat en nog geen €).
                            $price = (string) $it['price'];
                            if (! str_contains($price, '€') && preg_match('/\d/', $price)) {
                                $price = preg_replace('/(\d[\d.,]*)/', '€ $1', $price, 1);
                            }
                        @endphp
                        <div class="pl-price"><span class="pl-price-val">{{ $price }}</span></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    /* Generiek prijs-/tarievenblok, modern kaart-ontwerp. Zelfde stijl op elke
       trigger-site; kleuren via de thema-tokens. */
    .pricelist .pl-head{text-align:center;max-width:640px;margin:0 auto 2rem}
    .pricelist .pl-head h2{margin:.3rem 0 0}
    .pricelist .pl-sub{margin-top:.6rem}
    .pricelist .pl-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.1rem;align-items:stretch}
    .pricelist .pl-card{display:flex;flex-direction:column;background:var(--c-surface);
        border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);border-radius:calc(var(--radius) + 6px);
        padding:1.5rem 1.45rem;box-shadow:0 14px 34px -26px rgba(0,0,0,.4);
        transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease}
    .pricelist .pl-card:hover{transform:translateY(-4px);border-color:color-mix(in srgb,var(--c-primary) 45%,transparent);
        box-shadow:0 24px 50px -28px color-mix(in srgb,var(--c-primary) 55%,rgba(0,0,0,.5))}
    .pricelist .pl-name{font-size:1.15rem;font-weight:800;line-height:1.25;color:var(--c-ink)}
    .pricelist .pl-desc{color:var(--c-muted);font-size:.95rem;line-height:1.5;margin:.55rem 0 1.3rem;flex:1}
    .pricelist .pl-price{margin-top:auto;padding-top:1rem;border-top:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);
        color:var(--c-primary);font-weight:800;font-size:1.4rem;letter-spacing:-.01em;text-align:right}
    @media(max-width:560px){.pricelist .pl-card{padding:1.3rem 1.25rem}.pricelist .pl-price{font-size:1.3rem}}

    /* Punchy-variant: scherpere hoeken, accent-topbalk, prijs als accent-chip. */
    .pricelist.punchy .pl-card{border-radius:6px;border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);border-top:3px solid var(--c-accent)}
    .pricelist.punchy .pl-price{border-top:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);text-align:right}
    .pricelist.punchy .pl-price-val{display:inline-block;background:var(--c-accent);color:var(--c-on-accent,#fff);
        padding:.4rem .8rem;border-radius:5px;font-size:1.2rem;font-weight:800;letter-spacing:-.01em}
    .pricelist.punchy .pl-card:hover .pl-price-val{filter:brightness(1.06)}

    /* Beeld-achtergrond met zwarte gloed: donker genoeg voor leesbare witte tekst. */
    .pricelist.has-bg{position:relative;background:#0b0b10 var(--pl-bg) center/cover no-repeat}
    .pricelist.has-bg::before{content:"";position:absolute;inset:0;background:linear-gradient(rgba(8,8,12,.74),rgba(8,8,12,.82))}
    .pricelist.has-bg .wrap{position:relative;z-index:1}
    .pricelist.has-bg .pl-head h2{color:#fff}
    .pricelist.has-bg .pl-sub{color:rgba(255,255,255,.82)}
    .pricelist.has-bg .pl-card{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.16);border-top:3px solid var(--c-accent);box-shadow:0 24px 54px -30px rgba(0,0,0,.9);backdrop-filter:blur(3px)}
    .pricelist.has-bg .pl-name{color:#fff}
    .pricelist.has-bg .pl-desc{color:rgba(255,255,255,.78)}
    .pricelist.has-bg .pl-price{border-top-color:rgba(255,255,255,.18)}
</style>
