@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="features">
    <div class="wrap">
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p class="muted" style="margin-bottom:2rem">{{ $block->c('sub') }}</p>@endif
        <div class="grid cols-4">
            @foreach ($items as $f)
                <div class="card">
                    <div style="font-size:2rem;margin-bottom:.5rem">{{ $f['icon'] ?? '•' }}</div>
                    <h3>{{ $f['title'] ?? '' }}</h3>
                    <p class="muted" style="font-size:.95rem">{{ $f['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
