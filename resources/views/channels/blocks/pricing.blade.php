@php /** @var \App\Models\Channel\Block $block */ $plans = (array) $block->c('plans', []); @endphp
<section data-block="pricing">
    <div class="wrap">
        @if ($block->c('heading'))<h2 style="text-align:center">{{ $block->c('heading') }}</h2>@endif
        @if ($block->c('sub'))<p class="muted" style="text-align:center;margin-bottom:1.8rem">{{ $block->c('sub') }}</p>@endif
        <div class="grid cols-3">
            @foreach ($plans as $p)
                <div class="card" style="@if(!empty($p['highlight']))border:2px solid var(--c-primary);position:relative @endif">
                    @if (!empty($p['highlight']))<span class="eyebrow" style="position:absolute;top:-.8rem;left:1.6rem">Populair</span>@endif
                    <h3>{{ $p['name'] ?? '' }}</h3>
                    <div style="font-size:2rem;font-weight:800;margin:.4rem 0">{{ $p['price'] ?? '' }}<span class="muted" style="font-size:.9rem;font-weight:500">{{ !empty($p['period']) ? ' '.$p['period'] : '' }}</span></div>
                    <ul style="list-style:none;margin:.8rem 0 1.2rem;display:grid;gap:.4rem">
                        @foreach ((array) ($p['features'] ?? []) as $feat)<li style="padding-left:1.4rem;position:relative"><span style="position:absolute;left:0;color:var(--c-primary);font-weight:800">✓</span>{{ $feat }}</li>@endforeach
                    </ul>
                    <a href="#gratis-voorbeeld" class="btn @if(empty($p['highlight']))btn-ghost @endif" style="width:100%;text-align:center">{{ $p['cta'] ?? 'Kies' }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
