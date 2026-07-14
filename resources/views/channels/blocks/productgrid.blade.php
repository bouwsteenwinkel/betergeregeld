@php
    /** @var \App\Models\Channel\Block $block */
    /** @var \App\Support\ChannelSite $site */
    // Producten (naam/prijs/categorie); val terug op de pricelist-items als 'products'
    // (nog) niet gevuld is, zodat de winkel ook zonder het nieuwe veld iets toont.
    $products = collect($block->c('products', []))
        ->map(fn ($p) => is_array($p) ? $p : [])
        ->filter(fn ($p) => ! empty($p['name']))
        ->take(6)
        ->values();

    // Eén gegenereerd 3x2-productraster (slot 'products'); via background-position
    // snijden we het in 6 tegels. Ontbreekt het beeld, dan tonen kaarten een
    // kleurige gradient-tegel (de winkel-UX blijft overeind).
    $grid = $site->image('products');

    // Categorie-pillen uit de aanwezige categorieen.
    $cats = $products->pluck('category')->map(fn ($c) => trim((string) $c))->filter()->unique()->values();

    // Sprite-posities voor een 3-koloms, 2-rijen raster.
    $pos = ['0% 0%', '50% 0%', '100% 0%', '0% 100%', '50% 100%', '100% 100%'];
    // Vaste, nette placeholder-varianten (accent/accent-2) als er geen beeld is.
    $phGrad = [
        'linear-gradient(135deg,var(--c-accent),var(--c-accent-2))',
        'linear-gradient(135deg,var(--c-accent-2),var(--c-primary))',
        'linear-gradient(135deg,var(--c-primary),var(--c-accent-2))',
        'linear-gradient(135deg,var(--c-accent-2),var(--c-accent))',
        'linear-gradient(135deg,var(--c-accent),var(--c-primary))',
        'linear-gradient(135deg,var(--c-primary),var(--c-accent))',
    ];
    $badges = [0 => 'Bestseller', 3 => 'Nieuw'];
@endphp

<section data-block="productgrid" @if ($block->c('anchor')) id="{{ $block->c('anchor') }}" @endif
         @if ($grid) style="--pg-src:url('{{ $grid }}')" @endif>
    <div class="wrap">
        <div class="pg-head">
            <div>
                <span class="kicker"><span class="kicker-line"></span> {{ $block->c('eyebrow', 'Webshop') }}</span>
                <h2>{{ $block->c('heading', 'Onze producten') }}</h2>
            </div>
            <span class="pg-cart" aria-live="polite">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Winkelmandje <b class="pg-count">0</b>
            </span>
        </div>

        @if ($cats->count() > 1)
            <div class="pg-filter" role="tablist" aria-label="Productcategorieen">
                <button type="button" class="pg-pill is-active" data-cat="*">Alles</button>
                @foreach ($cats as $c)
                    <button type="button" class="pg-pill" data-cat="{{ \Illuminate\Support\Str::slug($c) }}">{{ $c }}</button>
                @endforeach
            </div>
        @endif

        <div class="pg-grid">
            @foreach ($products as $i => $p)
                <article class="pg-card" data-cat="{{ \Illuminate\Support\Str::slug($p['category'] ?? '') }}">
                    <div class="pg-img @unless ($grid) pg-ph @endunless"
                         @if ($grid) style="background-position:{{ $pos[$i] ?? '0% 0%' }}" @else style="background:{{ $phGrad[$i % 6] }}" @endif>
                        @isset ($badges[$i])<span class="pg-badge">{{ $badges[$i] }}</span>@endisset
                    </div>
                    <div class="pg-body">
                        @if (! empty($p['category']))<span class="pg-cat">{{ $p['category'] }}</span>@endif
                        <h3>{{ $p['name'] }}</h3>
                        <div class="pg-stars" aria-hidden="true">
                            @for ($s = 0; $s < 5; $s++)<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 15l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"/></svg>@endfor
                            <span class="pg-rev">({{ 12 + $i * 9 }})</span>
                        </div>
                        <div class="pg-foot">
                            <span class="pg-price">{{ $p['price'] ?? 'op aanvraag' }}</span>
                            <button type="button" class="pg-add">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                In winkelmand
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="pg-note">Gratis verzending vanaf € 50, voor 22:00 besteld morgen in huis.</p>
    </div>

    <style>
        [data-block="productgrid"]{background:var(--c-bg)}
        .pg-head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.4rem}
        .pg-head h2{margin:0}
        .pg-cart{display:inline-flex;align-items:center;gap:.5rem;font-weight:600;font-size:.92rem;color:var(--c-ink);background:var(--c-tint);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);padding:.5rem .9rem;border-radius:999px}
        .pg-cart svg{width:18px;height:18px;color:var(--c-accent)}
        .pg-cart .pg-count{display:inline-grid;place-items:center;min-width:20px;height:20px;padding:0 .3rem;border-radius:999px;background:var(--c-accent);color:var(--c-on-accent,#fff);font-size:.78rem}
        .pg-filter{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.4rem}
        .pg-pill{border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:var(--c-surface);color:var(--c-ink);font:inherit;font-size:.9rem;font-weight:600;padding:.45rem 1rem;border-radius:999px;cursor:pointer;transition:border-color .12s,color .12s,background .12s}
        .pg-pill:hover{border-color:var(--c-accent)}
        .pg-pill.is-active{background:var(--c-accent);color:var(--c-on-accent,#fff);border-color:var(--c-accent)}
        .pg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.1rem}
        .pg-card{background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;transition:transform .15s,box-shadow .2s}
        .pg-card:hover{transform:translateY(-3px);box-shadow:0 18px 34px -22px rgba(15,23,42,.5)}
        .pg-img{position:relative;aspect-ratio:1;background-size:300% 200%;background-repeat:no-repeat;background-color:var(--c-tint)}
        .pg-badge{position:absolute;top:.6rem;left:.6rem;background:var(--c-accent-2);color:var(--c-on-accent-2,#fff);font-size:.72rem;font-weight:700;letter-spacing:.02em;padding:.24rem .55rem;border-radius:6px}
        .pg-body{padding:.85rem .9rem 1rem;display:flex;flex-direction:column;gap:.3rem;flex:1}
        .pg-cat{font-size:.74rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--c-muted)}
        .pg-body h3{font-size:1rem;margin:0;color:var(--c-ink);line-height:1.25}
        .pg-stars{display:flex;align-items:center;gap:1px;color:#fbbf24}
        .pg-stars svg{width:14px;height:14px}
        .pg-rev{margin-left:.35rem;color:var(--c-muted);font-size:.8rem}
        .pg-foot{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-top:auto;padding-top:.5rem}
        .pg-price{font-weight:800;font-size:1.05rem;color:var(--c-ink)}
        .pg-add{display:inline-flex;align-items:center;gap:.35rem;background:var(--c-accent);color:var(--c-on-accent,#fff);border:0;border-radius:8px;font:inherit;font-size:.82rem;font-weight:700;padding:.5rem .7rem;cursor:pointer;transition:filter .15s,transform .15s}
        .pg-add:hover{filter:brightness(1.06);transform:translateY(-1px)}
        .pg-add svg{width:15px;height:15px}
        .pg-note{margin:1.3rem 0 0;text-align:center;color:var(--c-muted);font-size:.9rem}
        @media (prefers-reduced-motion:reduce){.pg-card:hover,.pg-add:hover{transform:none}}
    </style>

    <script>
    (function () {
        var sec = document.currentScript.closest('[data-block="productgrid"]');
        if (!sec) { return; }
        // Categoriefilter
        var pills = sec.querySelectorAll('.pg-pill');
        var cards = sec.querySelectorAll('.pg-card');
        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('is-active'); });
                pill.classList.add('is-active');
                var cat = pill.getAttribute('data-cat');
                cards.forEach(function (c) {
                    c.style.display = (cat === '*' || c.getAttribute('data-cat') === cat) ? '' : 'none';
                });
            });
        });
        // Winkelmandje-teller (front-end, puur voor de beleving)
        var count = sec.querySelector('.pg-count');
        var n = 0;
        sec.querySelectorAll('.pg-add').forEach(function (btn) {
            btn.addEventListener('click', function () { n++; if (count) { count.textContent = n; } });
        });
    })();
    </script>
</section>
