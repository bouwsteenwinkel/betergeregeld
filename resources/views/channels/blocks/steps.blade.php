@php
    /** @var \App\Models\Channel\Block $block */
    $items = (array) $block->c('items', []);
    // Golfpad voor de speelse variant: een paar GROTE golven (niet veel kleine).
    $sWaveW = 0; $sWavePath = 'M0 12'; $sdir = 1;
    while ($sWaveW < 240) {
        $seg = 40; $amp = 8;
        $cx = $sWaveW + $seg / 2; $cy = 12 - $sdir * $amp;
        $sWaveW += $seg;
        $sWavePath .= " Q{$cx} {$cy} {$sWaveW} 12";
        $sdir = -$sdir;
    }
@endphp
{{-- "Zo werkt het" e.d.: gaat over ONS proces, niet over de niche → geen niche-foto,
     maar de gedeelde genummerde-stappen-component. Zelfde stijl op elke trigger-site. --}}
<section data-block="steps" @if ($block->c('anchor')) id="{{ $block->c('anchor') }}" @endif style="background:var(--c-tint)">
    <div class="wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto">
            @if ($block->c('eyebrow'))<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $block->c('eyebrow') }}</span>@endif
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted" style="margin-top:.5rem">{{ $block->c('sub') }}</p>@endif
        </div>

        <div class="steps {{ $block->c('playful') ? 'steps-playful' : '' }}" style="margin-top:2.2rem">
            @foreach ($items as $i => $s)
                <div class="step">
                    <div class="step-num">{{ $i + 1 }}</div>
                    <h3>{{ preg_replace('/^\s*\d+[\.\)]\s*/', '', (string) ($s['title'] ?? '')) }}</h3>
                    <p class="muted">{{ $s['text'] ?? '' }}</p>
                </div>
            @endforeach
            {{-- Golf-lijn als LAATSTE kind (anders telt 'ie mee voor :nth-child en breekt
                 de grid-kolommen). Absoluut gepositioneerd, dus buiten de grid-flow. --}}
            @if ($block->c('playful'))
                <svg class="sp-wave" viewBox="0 0 {{ $sWaveW }} 24" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    <path d="{{ $sWavePath }}" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/>
                </svg>
            @endif
        </div>
    </div>
    @if ($block->c('playful'))
        <style>
            /* Speelse variant: gecentreerde stappen, badges met verloop + ring op een
               GOLVENDE verbindingslijn (SVG), lichte hover-pop. */
            .steps-playful{position:relative}
            .steps-playful .step{text-align:center;position:relative;z-index:1}
            .steps-playful .step-num{background:linear-gradient(135deg,var(--c-accent),var(--c-step-grad-b));
                box-shadow:0 0 0 6px var(--c-tint),0 12px 24px -10px var(--c-accent);transition:transform .18s ease}
            .steps-playful .step:hover .step-num{transform:translateY(-5px) scale(1.07) rotate(-5deg)}
            .steps-playful .sp-wave{position:absolute;top:12px;left:16.6%;width:66.8%;height:24px;color:var(--c-accent-2);opacity:.55;z-index:0;pointer-events:none;display:none}
            @media (min-width:760px){
                .steps-playful .step:not(:last-child)::after{display:none}
                .steps-playful .sp-wave{display:block}
            }
        </style>
    @endif
</section>
