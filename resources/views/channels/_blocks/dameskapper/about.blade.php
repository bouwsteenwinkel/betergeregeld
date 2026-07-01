@include('channels._blocks.dameskapper._style')
@php
    /** @var \App\Models\Channel\Block $block */
    $body  = (array) preg_split('/\n\s*\n/', trim((string) $block->c('body', '')));
    $stats = (array) $block->c('stats', []);
@endphp
<section data-block="about" id="over" class="lum"><div class="wrap lum-about">
    <div>
        @if ($block->c('eyebrow'))<span class="eyebrow">{{ $block->c('eyebrow') }}</span>@endif
        @if ($block->c('heading'))<h2 style="font-size:clamp(30px,4vw,44px);margin-bottom:16px">{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('lead'))<p style="color:var(--c-muted);margin-bottom:14px">{{ $block->c('lead') }}</p>@endif
        @foreach (array_filter($body) as $p)<p style="color:var(--c-muted);margin-bottom:14px">{{ $p }}</p>@endforeach
        @if ($stats)
            <div class="lum-stats">
                @foreach ($stats as $s)<div><div class="v">{{ $s['value'] ?? '' }}</div><div class="l">{{ $s['label'] ?? '' }}</div></div>@endforeach
            </div>
        @endif
    </div>
    <div class="visual"></div>
</div></section>
