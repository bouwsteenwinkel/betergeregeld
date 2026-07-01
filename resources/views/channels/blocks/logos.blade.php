@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
@if ($items)
<section data-block="logos" style="padding:40px 0">
    <div class="wrap">
        @if ($block->c('heading'))<p class="muted" style="text-align:center;text-transform:uppercase;letter-spacing:.1em;font-size:.8rem;margin-bottom:1.2rem">{{ $block->c('heading') }}</p>@endif
        <div style="display:flex;justify-content:center;align-items:center;gap:2.5rem;flex-wrap:wrap;opacity:.7">
            @foreach ($items as $it)
                @php $img = is_array($it) ? ($it['image'] ?? null) : null; $label = is_array($it) ? ($it['label'] ?? '') : (string) $it; @endphp
                @if ($img)<img src="{{ $img }}" alt="{{ $label }}" style="max-height:38px;width:auto">@else<span style="font-weight:700;font-size:1.1rem">{{ $label }}</span>@endif
            @endforeach
        </div>
    </div>
</section>
@endif
