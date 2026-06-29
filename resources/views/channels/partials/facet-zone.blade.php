@php
    /** @var \App\Support\ChannelSite $site */
    // Facet-afhankelijke blokken. Wordt zowel vanuit home.blade geïnclude als
    // los gerenderd door ChannelSiteController@homeFragment (AJAX fase-switch).
    $h = $h ?? ($home ?? $site->get('home', []));
@endphp
<section class="hero">
    <div class="wrap">
        @if (!empty($h['hero_eyebrow']))<span class="eyebrow">{{ $h['hero_eyebrow'] }}</span>@endif
        <h1>{{ $h['hero_title'] ?? $site->name() }}</h1>
        <p class="lead">{{ $h['hero_sub'] ?? '' }}</p>
        <a href="#contact" class="btn">{{ $h['hero_cta'] ?? 'Gratis voorbeeld aanvragen' }}</a>
        @if (!empty($h['hero_note']))<p class="muted" style="margin-top:.8rem;font-size:.9rem">{{ $h['hero_note'] }}</p>@endif

        @if (!empty($h['usps']))
            <ul class="hero-usps">
                @foreach ($h['usps'] as $usp)<li>{{ $usp }}</li>@endforeach
            </ul>
        @endif
    </div>
</section>

@if (!empty($h['features']))
    <section>
        <div class="wrap">
            <h2>Alles wat je zaak nodig heeft</h2>
            <p class="muted" style="margin-bottom:2rem">In één strakke website — wij regelen de techniek.</p>
            <div class="grid cols-4">
                @foreach ($h['features'] as $f)
                    <div class="card">
                        <div style="font-size:2rem;margin-bottom:.5rem">{{ $f['icon'] ?? '•' }}</div>
                        <h3>{{ $f['title'] }}</h3>
                        <p class="muted" style="font-size:.95rem">{{ $f['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (!empty($h['steps']))
    <section style="background:var(--c-surface)">
        <div class="wrap">
            <h2>Zo werkt het</h2>
            <div class="grid cols-3" style="margin-top:1.6rem">
                @foreach ($h['steps'] as $i => $s)
                    <div>
                        <div style="width:42px;height:42px;border-radius:50%;background:var(--c-primary);color:#fff;display:grid;place-items:center;font-weight:800;margin-bottom:.7rem">{{ $i + 1 }}</div>
                        <h3>{{ $s['title'] }}</h3>
                        <p class="muted">{{ $s['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (!empty($h['proof']))
    <section>
        <div class="wrap">
            <blockquote class="card" style="font-size:1.3rem;font-weight:600;border-left:5px solid var(--c-accent)">
                {{ $h['proof'] }}
            </blockquote>
        </div>
    </section>
@endif

@include('channels.partials.groeipad')
