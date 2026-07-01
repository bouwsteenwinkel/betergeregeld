@include('channels._blocks.herenkapper._style')
@php
    /** @var \App\Models\Channel\Block $block */
    $address = $block->c('address') ?? $site->brand('address');
    $phone   = $block->c('phone') ?? $site->brand('phone');
    $email   = $block->c('email') ?? $site->brand('email');
    $hours   = (array) $block->c('hours', []);
@endphp
<section data-block="location" id="contact" class="brink"><div class="wrap">
    <div class="brink-contact">
        <div>
            <span class="eyebrow">{{ $block->c('eyebrow', 'Afspraak & contact') }}</span>
            <h2>{{ $block->c('heading', 'Boek je stoel') }}</h2>
            @if ($block->c('sub'))<p style="color:#c5bdb0;margin:14px 0 22px;max-width:38ch">{{ $block->c('sub') }}</p>@endif
            @if ($phone)<a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="btn">Bel {{ $phone }}</a>@endif
            <div style="margin-top:24px">
                @if ($address)<p style="color:#c5bdb0;margin-bottom:8px">📍 {{ $address }}</p>@endif
                @if ($email)<p style="color:#c5bdb0">✉ {{ $email }}</p>@endif
            </div>
        </div>
        <div>
            <h3 style="font-size:24px;margin-bottom:14px">Openingstijden</h3>
            <div class="brink-hours">
                @foreach ($hours as $h)<div class="r"><span>{{ is_array($h) ? ($h['day'] ?? $h[0] ?? '') : '' }}</span><span>{{ is_array($h) ? ($h['time'] ?? $h[1] ?? '') : $h }}</span></div>@endforeach
            </div>
        </div>
    </div>
</div></section>
