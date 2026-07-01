@php
    /** @var \App\Models\Channel\Block $block */
    $body  = (array) preg_split('/\n\s*\n/', trim((string) $block->c('body', '')));
    $stats = (array) $block->c('stats', []);
@endphp
<section data-block="about">
    <div class="wrap" style="max-width:760px">
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('lead'))<p class="lead" style="font-size:1.15rem;margin:.6rem 0 1rem">{{ $block->c('lead') }}</p>@endif
        @foreach (array_filter($body) as $p)<p class="muted" style="margin-bottom:.8rem">{{ $p }}</p>@endforeach
        @if ($stats)
            <div style="display:flex;gap:2.4rem;flex-wrap:wrap;margin-top:1.4rem">
                @foreach ($stats as $s)
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:var(--c-primary);line-height:1">{{ $s['value'] ?? '' }}</div>
                        <div class="muted" style="font-size:.85rem">{{ $s['label'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
