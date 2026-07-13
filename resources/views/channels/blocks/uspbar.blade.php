@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
@if ($items)
<section data-block="uspbar" style="padding:16px 0;background:color-mix(in srgb,var(--c-accent) 8%,transparent)">
    {{-- Compacte grid: de punten passen naast elkaar op één rij (auto-fit), en
         zakken pas op smallere schermen naar minder kolommen. --}}
    <div class="wrap" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.5rem 1.5rem;align-items:center">
        @foreach ($items as $it)
            <div style="display:flex;align-items:center;gap:.5rem;font-weight:600;font-size:.9rem;line-height:1.3">
                <span style="display:inline-flex;flex:0 0 auto;color:var(--c-accent)">@include('channels.partials.icon', ['name' => is_array($it) ? ($it['icon'] ?? 'check') : 'check'])</span>
                <span>{{ is_array($it) ? ($it['text'] ?? '') : $it }}</span>
            </div>
        @endforeach
    </div>
</section>
@endif
