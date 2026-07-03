@php
    /** @var \App\Support\ChannelSite $site */
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $current = $facet ?? (string) config('groeidiamant.default', 'website');
    $curNr   = (int) ($facets[$current]['nr'] ?? 1);

    // Two-lagen-model (bv. badkamerspecialist): heeft de site een landing-laag, dan
    // stuurt een fase-klik naar de triggerpagina /{facet} (waar de ondernemer koopt/
    // aanvraagt) i.p.v. de live demo-switch. Oude sites houden de AJAX-switch.
    $hasLanding = method_exists($site, 'landingView') && $site->landingView();
    $linkBase   = $hasLanding ? '' : ($facetBase ?? '');
@endphp
@if (!empty($facets))
<section id="groeipad" data-section="groeipad" style="padding:48px 0">
    <div class="wrap" style="text-align:center">
        <span class="kicker" style="justify-content:center"><span class="kicker-line"></span> De Groeidiamant</span>
        <h2>Je site groeit met je mee</h2>
        <p class="muted" style="max-width:54ch;margin:.4rem auto 1.6rem">
            Begin waar je nu staat, je hoeft nooit opnieuw te beginnen. <strong>Klik op een fase</strong> om te zien wat die voor jou betekent.
        </p>

        <div style="display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;align-items:stretch">
            @foreach ($facets as $key => $f)
                @php
                    $nr = (int) ($f['nr'] ?? 0);
                    $state = $nr < $curNr ? 'done' : ($key === $current ? 'now' : 'next');
                @endphp
                <a href="{{ $site->url($linkBase . $key) }}" @unless($hasLanding) data-facet-link data-facet="{{ $key }}" @endunless style="display:block;flex:1;min-width:150px;max-width:200px;border-radius:var(--radius);padding:1.1rem .9rem;text-align:left;text-decoration:none;color:inherit;
                    @if($state==='now') background:var(--c-primary);color:#fff;box-shadow:0 10px 30px color-mix(in srgb,var(--c-primary) 35%,transparent);
                    @elseif($state==='done') background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-accent) 45%,transparent);
                    @else background:var(--c-surface);border:1px dashed color-mix(in srgb,var(--c-muted) 40%,transparent);opacity:.7; @endif">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
                        <span style="display:inline-flex;@if($state==='now') color:#fff @else color:var(--c-primary) @endif">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</span>
                        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                            @if($state==='now') color:rgba(255,255,255,.85) @elseif($state==='done') color:var(--c-accent) @else color:var(--c-muted) @endif">
                            @if($state==='done') ✓ heb je @elseif($state==='now') jouw stap @else later @endif
                        </span>
                    </div>
                    <div style="font-weight:700;font-size:1.02rem;@if($state!=='now')color:var(--c-ink)@endif">{{ $nr }}. {{ $f['label'] ?? $key }}</div>
                    <div style="font-size:.82rem;margin-top:.2rem;@if($state==='now')color:rgba(255,255,255,.85)@else color:var(--c-muted)@endif">{{ $f['tagline'] ?? '' }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
