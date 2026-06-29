@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="pricelist">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center">{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p class="muted" style="text-align:center;margin-bottom:1.6rem">{{ $block->c('sub') }}</p>@endif
        <div style="max-width:680px;margin:0 auto;display:grid;gap:.4rem">
            @foreach ($items as $it)
                <div style="display:flex;align-items:baseline;gap:1rem;padding:1rem .2rem;border-bottom:1px dashed color-mix(in srgb,var(--c-ink) 14%,transparent)">
                    <span style="font-weight:700">{{ $it['name'] ?? '' }}</span>
                    <span class="muted" style="flex:1;font-size:.92rem">{{ $it['desc'] ?? '' }}</span>
                    <span style="white-space:nowrap;color:var(--c-primary);font-weight:600">{{ $it['price'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
