@php
    /** @var \App\Support\ChannelSite $site */
    // Compacte Groeidiamant-facetstrip: intro links + logo rechts, daaronder de
    // facetten als gelijke vakken over de volle contentbreedte. Herbruikbaar op de
    // trigger- én plaatsen-pagina's. Params: $kicker, $title, $lead, $current (highlight).
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $current = $current ?? null;
    $kicker  = $kicker ?? 'De Groeidiamant';
    $title   = $title ?? 'Van website tot slimme groei';
    $lead    = $lead ?? null;
    $bg      = $bg ?? null;
    $count   = max(1, count($facets));
    $gdLogo  = asset('channel-media/_brand/groeidiamant.jpg');
    $secStyle = 'padding:52px 0' . ($bg ? ';background:' . $bg : '');
@endphp
@if (!empty($facets))
<section class="facetstrip" data-section="facetstrip" style="{{ $secStyle }}">
    <style>
        .facetstrip .fs-head{display:flex;align-items:center;gap:2.4rem;margin-bottom:1.7rem;text-align:left}
        .facetstrip .fs-intro{flex:1 1 auto;min-width:0}
        .facetstrip .fs-intro .kicker{justify-content:flex-start}
        .facetstrip .fs-intro h2{margin:.25rem 0 .5rem}
        .facetstrip .fs-intro p{max-width:58ch;margin:0}
        .facetstrip .fs-logo{flex:0 0 auto;position:relative;padding:.7rem;border-radius:18px;background:#fff;
            box-shadow:0 20px 50px -22px color-mix(in srgb,var(--c-primary) 60%,transparent);
            border:1px solid color-mix(in srgb,var(--c-primary) 14%,transparent)}
        .facetstrip .fs-logo::before{content:"";position:absolute;inset:auto -18% -22% auto;width:75%;height:120%;
            background:radial-gradient(circle,color-mix(in srgb,var(--c-primary) 32%,transparent),transparent 70%);
            filter:blur(24px);z-index:-1}
        .facetstrip .fs-logo img{display:block;width:200px;max-width:30vw;height:auto;border-radius:11px}
        .facetstrip .fs-grid{display:grid;grid-template-columns:repeat({{ $count }},minmax(0,1fr));gap:.7rem}
        .facetstrip .fs-item{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.95rem .55rem;
            border-radius:var(--radius);border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);
            background:var(--c-surface);color:inherit;text-decoration:none;font-weight:700;font-size:.97rem;
            line-height:1.15;text-align:center;transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease}
        .facetstrip .fs-item:hover{transform:translateY(-2px);border-color:var(--c-primary)}
        .facetstrip .fs-item .fs-ic{display:inline-flex;color:var(--c-primary);flex:0 0 auto}
        .facetstrip .fs-item.is-current{background:var(--c-primary);color:#fff;border-color:var(--c-primary);
            box-shadow:0 12px 28px -12px color-mix(in srgb,var(--c-primary) 60%,transparent)}
        .facetstrip .fs-item.is-current .fs-ic{color:#fff}
        @media(max-width:820px){.facetstrip .fs-grid{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:760px){.facetstrip .fs-head{flex-direction:column;align-items:stretch;gap:1.3rem}
            /* Logo-kaart over de volle breedte, logo netjes gecentreerd. */
            .facetstrip .fs-logo{align-self:stretch;text-align:center;padding:1.4rem}
            .facetstrip .fs-logo img{width:200px;max-width:56vw;margin:0 auto}}
        @media(max-width:640px){.facetstrip .fs-grid{grid-template-columns:repeat(2,1fr)}}
        /* Mobiel: elke facet-knop op een eigen rij. */
        @media(max-width:560px){.facetstrip .fs-grid{grid-template-columns:1fr}
            .facetstrip .fs-item{justify-content:flex-start;padding:1rem 1.1rem;font-size:1rem}}
    </style>
    <div class="wrap">
        <div class="fs-head">
            <div class="fs-intro">
                <span class="kicker"><span class="kicker-line"></span> {{ $kicker }}</span>
                <h2>{{ $title }}</h2>
                @if ($lead)<p class="muted">{{ $lead }}</p>@endif
            </div>
            <div class="fs-logo">
                <img src="{{ $gdLogo }}" alt="Groeidiamant by Betergeregeld" width="200" loading="lazy" decoding="async">
            </div>
        </div>
        <div class="fs-grid">
            @foreach ($facets as $key => $f)
                <a href="{{ $site->url($key) }}" class="fs-item {{ $current === $key ? 'is-current' : '' }}">
                    <span class="fs-ic">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</span>
                    <span>{{ $f['label'] ?? $key }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
