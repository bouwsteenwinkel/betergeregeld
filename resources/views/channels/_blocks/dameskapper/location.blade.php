@include('channels._blocks.dameskapper._style')
@php
    /** @var \App\Models\Channel\Block $block */
    $address = $block->c('address') ?? $site->brand('address');
    $phone   = $block->c('phone') ?? $site->brand('phone');
    $email   = $block->c('email') ?? $site->brand('email');
    $hours   = (array) $block->c('hours', []);
@endphp
<section data-block="location" id="contact" class="lum"><div class="wrap">
    <div class="lum-contact">
        <div>
            <span class="eyebrow" style="color:var(--c-accent)">{{ $block->c('eyebrow', 'Afspraak & contact') }}</span>
            <h2>{{ $block->c('heading', 'Maak je afspraak') }}</h2>
            @if ($block->c('sub'))<p style="color:rgba(255,255,255,.8);margin:14px 0 22px;max-width:38ch">{{ $block->c('sub') }}</p>@endif
            @if ($phone)<a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="btn">Bel {{ $phone }}</a>@endif
            <div style="margin-top:24px">
                @if ($address)<p style="color:rgba(255,255,255,.8);margin-bottom:8px">📍 {{ $address }}</p>@endif
                @if ($email)<p style="color:rgba(255,255,255,.8)">✉ {{ $email }}</p>@endif
            </div>
        </div>
        <div>
            <h3 style="color:#fff;font-size:24px;margin-bottom:14px">Openingstijden</h3>
            <div class="lum-hours">
                @foreach ($hours as $h)<div class="r"><span>{{ is_array($h) ? ($h['day'] ?? $h[0] ?? '') : '' }}</span><span>{{ is_array($h) ? ($h['time'] ?? $h[1] ?? '') : $h }}</span></div>@endforeach
            </div>
        </div>
    </div>
</div></section>
