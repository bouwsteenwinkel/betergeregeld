@php
    /** @var \App\Models\Channel\Block $block */
    // Alleen ÉCHTE, expliciet ingevulde tegels (bv. referentie-/klantsites). Geen
    // fallback naar sector-galerijbeelden: die zijn geen echt "werk", dus het blok
    // blijft verborgen tot er echte voorbeelden zijn (referentie-sites/reviews).
    $tiles = (array) $block->c('tiles', []);
@endphp
@if ($tiles)
<section data-block="gallery" style="background:var(--c-surface)">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center">{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p class="muted" style="text-align:center;margin-bottom:1.6rem">{{ $block->c('sub') }}</p>@endif
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.9rem">
            @foreach ($tiles as $t)
                @php
                    $img   = is_array($t) ? ($t['image'] ?? null) : (filter_var($t, FILTER_VALIDATE_URL) ? $t : null);
                    $set   = is_array($t) ? ($t['srcset'] ?? '') : '';
                    $label = is_array($t) ? ($t['label'] ?? '') : (string) $t;
                @endphp
                <div style="position:relative;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;@if(!$img)background:linear-gradient(160deg,var(--c-primary),var(--c-accent))@endif">
                    @if ($img)
                        <img src="{{ $img }}" @if ($set) srcset="{{ $set }}" sizes="(max-width:680px) 46vw, 23vw" @endif
                             alt="" loading="lazy" decoding="async"
                             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
                    @endif
                    @if ($label)<span style="position:relative;padding:.7rem .8rem;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.4),transparent);width:100%">{{ $label }}</span>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
