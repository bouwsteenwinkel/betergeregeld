@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="steps" style="background:var(--c-surface)">
    <div class="wrap">
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        <div class="grid cols-3" style="margin-top:1.6rem">
            @foreach ($items as $i => $s)
                <div>
                    <div style="width:42px;height:42px;border-radius:50%;background:var(--c-primary);color:#fff;display:grid;place-items:center;font-weight:800;margin-bottom:.7rem">{{ $i + 1 }}</div>
                    <h3>{{ $s['title'] ?? '' }}</h3>
                    <p class="muted">{{ $s['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
