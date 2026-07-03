@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
@if ($items)
<section data-block="uspbar" style="padding:28px 0;background:color-mix(in srgb,var(--c-accent) 8%,transparent)">
    <div class="wrap" style="display:flex;justify-content:center;gap:2.4rem;flex-wrap:wrap">
        @foreach ($items as $it)
            <div style="display:flex;align-items:center;gap:.6rem;font-weight:600">
                <span style="display:inline-flex;color:var(--c-accent)">@include('channels.partials.icon', ['name' => is_array($it) ? ($it['icon'] ?? 'check') : 'check'])</span>
                {{ is_array($it) ? ($it['text'] ?? '') : $it }}
            </div>
        @endforeach
    </div>
</section>
@endif
