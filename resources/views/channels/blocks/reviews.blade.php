@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="reviews">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center;margin-bottom:1.6rem">{{ $block->c('heading') }}</h2>@endif
        <div class="grid cols-3">
            @foreach ($items as $r)
                <div class="card">
                    <div style="color:var(--c-accent);letter-spacing:2px;margin-bottom:.6rem">{{ str_repeat('★', (int) ($r['stars'] ?? 5)) }}</div>
                    <p style="font-weight:600">“{{ $r['text'] ?? '' }}”</p>
                    @if (!empty($r['author']))<p class="muted" style="margin-top:.6rem;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em">{{ $r['author'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>
