@php
    /** @var \App\Models\Channel\Block $block */
    $items = (array) $block->c('items', []);
    $connected = (bool) $block->c('connected');
    $cols = max(1, min(4, count($items)));
    $inset = round(50 / $cols, 2);
    // Golfpad over de VOLLE breedte: veel kleine golven met af en toe een grote.
    $waveW = 0; $wavePath = 'M0 12'; $wdir = 1; $wi = 0;
    while ($waveW < 520) {
        $big = ($wi % 6 === 2);
        $seg = $big ? 26 : 9;
        $amp = $big ? 9 : 3;
        $cx = $waveW + $seg / 2; $cy = 12 - $wdir * $amp;
        $waveW += $seg;
        $wavePath .= " Q{$cx} {$cy} {$waveW} 12";
        $wdir = -$wdir; $wi++;
    }
@endphp
<section data-block="features" @if ($block->c('anchor')) id="{{ $block->c('anchor') }}" @endif>
    <div class="wrap">
        @if ($connected)
            <div style="text-align:center;max-width:640px;margin:0 auto 2.2rem">
                @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
                @if ($block->c('sub'))<p class="muted" style="margin-top:.5rem">{{ $block->c('sub') }}</p>@endif
            </div>

            {{-- Speelse variant: ronde badges op een GOLVENDE verbindingslijn. --}}
            <div class="features-connected" style="grid-template-columns:repeat({{ $cols }},1fr)">
                @foreach ($items as $f)
                    <div class="fc-item">
                        <div class="fc-badge">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</div>
                        <h3>{{ $f['title'] ?? '' }}</h3>
                        <p>{{ $f['text'] ?? '' }}</p>
                    </div>
                @endforeach
                <svg class="fc-wave" style="left:{{ $inset }}%;width:{{ round(100 - 2 * $inset, 2) }}%" viewBox="0 0 {{ $waveW }} 24" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    <path d="{{ $wavePath }}" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/>
                </svg>
            </div>
            <style>
                .features-connected{position:relative;display:grid;gap:1.4rem;margin-top:.5rem}
                .fc-wave{position:absolute;top:20px;height:24px;color:var(--c-accent);opacity:.55;z-index:0;pointer-events:none;display:none}
                .fc-item{position:relative;text-align:center;padding:0 .3rem;z-index:1}
                .fc-badge{width:64px;height:64px;border-radius:50%;background:var(--c-accent);color:var(--c-on-accent,#fff);display:grid;place-items:center;margin:0 auto 1rem;position:relative;z-index:1;border:5px solid var(--c-bg,#fff);box-shadow:0 10px 22px -10px var(--c-accent)}
                .fc-item h3{font-size:1.05rem;margin:0 0 .35rem;color:var(--c-ink)}
                .fc-item p{color:var(--c-muted);font-size:.9rem;line-height:1.45;margin:0}
                @media (min-width:820px){.fc-wave{display:block}}
                @media (max-width:819px){.features-connected{grid-template-columns:repeat(2,1fr)!important}}
                @media (max-width:480px){.features-connected{grid-template-columns:1fr!important}}
            </style>
        @else
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted" style="margin-bottom:2rem;max-width:60ch">{{ $block->c('sub') }}</p>@endif
            <div class="grid cols-4">
                @foreach ($items as $f)
                    <div class="card">
                        <div class="feature-ico">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</div>
                        <h3>{{ $f['title'] ?? '' }}</h3>
                        <p class="muted" style="font-size:.95rem">{{ $f['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
