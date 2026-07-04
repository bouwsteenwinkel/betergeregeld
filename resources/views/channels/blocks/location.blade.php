@php
    /** @var \App\Models\Channel\Block $block */
    $hours   = (array) $block->c('hours', []);
    $address = $block->c('address') ?? $site->brand('address');
    $phone   = $block->c('phone') ?? $site->brand('phone');
    $email   = $block->c('email') ?? $site->brand('email');
    $tel     = $phone ? preg_replace('/\s+/', '', (string) $phone) : null;
@endphp
<section data-block="location" class="loc">
    <div class="wrap">
        <div class="loc-card">
            <div class="loc-head">
                @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
                @if ($block->c('sub'))<p>{{ $block->c('sub') }}</p>@endif
            </div>

            <div class="loc-contact">
                @if ($phone)
                    <a href="tel:{{ $tel }}"><span class="ic">@include('channels.partials.icon', ['name' => 'phone'])</span>{{ $phone }}</a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}"><span class="ic">@include('channels.partials.icon', ['name' => 'mail'])</span>{{ $email }}</a>
                @endif
                @if ($address)
                    <div><span class="ic">@include('channels.partials.icon', ['name' => 'location'])</span>{{ $address }}</div>
                @endif
            </div>

            @if ($hours)
                <div class="loc-hours">
                    <h3>Openingstijden</h3>
                    @foreach ($hours as $h)
                        <div class="loc-hours-row">
                            <span>{{ is_array($h) ? ($h['day'] ?? $h[0] ?? '') : '' }}</span>
                            <span>{{ is_array($h) ? ($h['time'] ?? $h[1] ?? '') : $h }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="loc-cta">
                <a href="{{ $site->navHref('#gratis-voorbeeld') }}" class="btn">Plan een afspraak</a>
            </div>
        </div>
    </div>
</section>

<style>
    /* Smal, donker contact-/openingstijden-blok in de huisstijl. Generiek. */
    .loc .loc-card{max-width:600px;margin:0 auto;background:var(--c-footer-bg,var(--c-ink));color:#fff;
        border-radius:calc(var(--radius) + 8px);padding:2.4rem clamp(1.4rem,4vw,2.6rem);box-shadow:0 34px 74px -42px rgba(0,0,0,.65)}
    .loc .loc-head{text-align:center;margin-bottom:1.7rem}
    .loc .loc-head h2{color:#fff;margin:0}
    .loc .loc-head p{color:rgba(255,255,255,.72);margin-top:.45rem}
    .loc .loc-contact{display:grid;gap:.75rem;justify-content:center;margin-bottom:1.7rem}
    .loc .loc-contact a,.loc .loc-contact div{display:inline-flex;align-items:center;gap:.6rem;color:#fff;font-weight:600}
    .loc .loc-contact a:hover{color:var(--c-accent)}
    .loc .loc-contact .ic{display:inline-flex;color:var(--c-accent);flex:0 0 auto}
    .loc .loc-contact .ic svg{width:18px;height:18px}
    .loc .loc-hours{border-top:1px solid rgba(255,255,255,.15);padding-top:1.1rem;max-width:420px;margin:0 auto}
    .loc .loc-hours h3{text-align:center;text-transform:uppercase;letter-spacing:.1em;font-size:.76rem;font-weight:700;
        color:rgba(255,255,255,.6);margin-bottom:.6rem}
    .loc .loc-hours-row{display:flex;justify-content:space-between;gap:1rem;padding:.38rem .2rem;font-size:.95rem;border-bottom:1px solid rgba(255,255,255,.09)}
    .loc .loc-hours-row:last-child{border-bottom:0}
    .loc .loc-hours-row span:first-child{color:#fff}
    .loc .loc-hours-row span:last-child{color:rgba(255,255,255,.72);font-variant-numeric:tabular-nums;white-space:nowrap}
    .loc .loc-cta{text-align:center;margin-top:1.7rem}
</style>
