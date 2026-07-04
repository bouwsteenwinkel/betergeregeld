@php
    /** @var \App\Models\Channel\Block $block */
    $items = (array) $block->c('items', []);
@endphp
{{-- "Zo werkt het" e.d.: gaat over ONS proces, niet over de niche → geen niche-foto,
     maar de gedeelde genummerde-stappen-component. Zelfde stijl op elke trigger-site. --}}
<section data-block="steps" style="background:var(--c-surface)">
    <div class="wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto">
            @if ($block->c('eyebrow'))<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $block->c('eyebrow') }}</span>@endif
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted" style="margin-top:.5rem">{{ $block->c('sub') }}</p>@endif
        </div>

        <div class="steps" style="margin-top:2.2rem">
            @foreach ($items as $i => $s)
                <div class="step">
                    <div class="step-num">{{ $i + 1 }}</div>
                    <h3>{{ preg_replace('/^\s*\d+[\.\)]\s*/', '', (string) ($s['title'] ?? '')) }}</h3>
                    <p class="muted">{{ $s['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
