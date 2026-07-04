@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="pricelist" class="pricelist">
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
                        <div class="pl-price">{{ $price }}</div>
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
    .pricelist .pl-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.1rem;
        max-width:980px;margin:0 auto;align-items:stretch}
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
</style>
