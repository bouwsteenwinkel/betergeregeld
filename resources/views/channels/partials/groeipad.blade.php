@php
    /** @var \App\Support\ChannelSite $site */
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $current = $facet ?? (string) config('groeidiamant.default', 'website');
    $curNr   = (int) ($facets[$current]['nr'] ?? 1);
    $count   = max(1, count($facets));

    // Two-lagen-model (bv. badkamerspecialist): heeft de site een landing-laag, dan
    // stuurt een fase-klik naar de triggerpagina /{facet} (waar de ondernemer koopt/
    // aanvraagt) i.p.v. de live demo-switch. Oude sites houden de AJAX-switch.
    $hasLanding = method_exists($site, 'landingView') && $site->landingView();
    $linkBase   = $hasLanding ? '' : ($facetBase ?? '');
    $gdLogo     = asset('channel-media/_brand/groeidiamant.jpg');
@endphp
@if (!empty($facets))
<section id="groeipad" data-section="groeipad" style="padding:56px 0">
    <style>
        #groeipad .gd-head{display:flex;align-items:center;gap:2.6rem;margin-bottom:1.9rem;text-align:left}
        #groeipad .gd-intro{flex:1 1 auto;min-width:0}
        #groeipad .gd-intro .kicker{justify-content:flex-start}
        #groeipad .gd-intro h2{margin:.25rem 0 .55rem}
        #groeipad .gd-intro p{max-width:56ch;margin:0}
        #groeipad .gd-logo{flex:0 0 auto;position:relative;padding:.85rem;border-radius:20px;background:#fff;
            box-shadow:0 22px 55px -22px color-mix(in srgb,var(--c-primary) 60%,transparent);
            border:1px solid color-mix(in srgb,var(--c-primary) 14%,transparent)}
        #groeipad .gd-logo::before{content:"";position:absolute;inset:auto -18% -22% auto;width:75%;height:120%;
            background:radial-gradient(circle,color-mix(in srgb,var(--c-primary) 32%,transparent),transparent 70%);
            filter:blur(26px);z-index:-1}
        #groeipad .gd-logo img{display:block;width:240px;max-width:34vw;height:auto;border-radius:12px}
        #groeipad .gd-steps{display:grid;grid-template-columns:repeat({{ $count }},minmax(0,1fr));gap:.8rem;align-items:stretch}
        #groeipad .gd-step{display:flex;flex-direction:column;border-radius:var(--radius);padding:1.1rem .95rem;
            text-align:left;text-decoration:none;color:inherit;transition:transform .15s ease,box-shadow .15s ease}
        #groeipad .gd-step:hover{transform:translateY(-3px)}
        #groeipad .gd-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.55rem}
        #groeipad .gd-badge{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.09em;white-space:nowrap}
        #groeipad .gd-label{font-weight:800;font-size:1.02rem;line-height:1.2}
        #groeipad .gd-tag{font-size:.82rem;margin-top:.28rem}
        #groeipad .gd-icon{display:inline-flex}
        #groeipad .gd-step.is-now{background:var(--c-primary);color:#fff;
            box-shadow:0 16px 36px -12px color-mix(in srgb,var(--c-primary) 60%,transparent)}
        #groeipad .gd-step.is-now:hover{transform:translateY(-3px)}
        #groeipad .gd-step.is-now .gd-icon{color:#fff}
        #groeipad .gd-step.is-now .gd-badge{color:rgba(255,255,255,.9)}
        #groeipad .gd-step.is-now .gd-tag{color:rgba(255,255,255,.85)}
        #groeipad .gd-step.is-done{background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-accent) 45%,transparent)}
        #groeipad .gd-step.is-done .gd-badge{color:var(--c-accent)}
        #groeipad .gd-step.is-next{background:var(--c-surface);border:1px dashed color-mix(in srgb,var(--c-muted) 42%,transparent);opacity:.82}
        #groeipad .gd-step.is-next:hover{opacity:1}
        #groeipad .gd-step.is-next .gd-badge{color:var(--c-muted)}
        #groeipad .gd-step:not(.is-now) .gd-icon{color:var(--c-primary)}
        #groeipad .gd-step:not(.is-now) .gd-label{color:var(--c-ink)}
        #groeipad .gd-step:not(.is-now) .gd-tag{color:var(--c-muted)}
        @media(max-width:900px){#groeipad .gd-steps{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:760px){#groeipad .gd-head{flex-direction:column;align-items:stretch;gap:1.4rem}
            /* Logo-kaart vol breedte, gecentreerd (zoals op badkamerspecialist). */
            #groeipad .gd-logo{align-self:stretch;text-align:center;padding:1.4rem}
            #groeipad .gd-logo img{width:220px;max-width:60vw;margin:0 auto}}
        /* Mobiel: compacte facet-strip-look (zoals badkamerspecialist) — gestapelde
           rijen met icoon + label, zonder badge/omschrijving; huidige stap in de
           site-kleur (--c-primary). */
        @media(max-width:640px){
            #groeipad .gd-steps{grid-template-columns:1fr;gap:.55rem}
            #groeipad .gd-step{flex-direction:row;align-items:center;gap:.65rem;padding:.95rem 1.1rem;
                border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:var(--c-surface)}
            #groeipad .gd-step:hover{transform:none}
            #groeipad .gd-top{margin:0;flex:0 0 auto}
            #groeipad .gd-badge,#groeipad .gd-tag{display:none}
            #groeipad .gd-label{font-size:1rem}
            #groeipad .gd-step.is-next{opacity:1}
            #groeipad .gd-step.is-done{border-color:color-mix(in srgb,var(--c-accent) 45%,transparent)}
            #groeipad .gd-step.is-now{background:var(--c-primary);color:#fff;border-color:var(--c-primary)}
            #groeipad .gd-step.is-now .gd-label,#groeipad .gd-step.is-now .gd-icon{color:#fff}
        }
    </style>
    <div class="wrap">
        <div class="gd-head">
            <div class="gd-intro">
                <span class="kicker"><span class="kicker-line"></span> De Groeidiamant</span>
                <h2>Je site groeit met je mee</h2>
                <p class="muted">Begin waar je nu staat, je hoeft nooit opnieuw te beginnen. <strong>Klik op een fase</strong> om te zien wat die voor jou betekent.</p>
            </div>
            <div class="gd-logo">
                <img src="{{ $gdLogo }}" alt="Groeidiamant by Betergeregeld" width="240" loading="lazy" decoding="async">
            </div>
        </div>

        <div class="gd-steps">
            @foreach ($facets as $key => $f)
                @php
                    $nr = (int) ($f['nr'] ?? 0);
                    $state = $nr < $curNr ? 'done' : ($key === $current ? 'now' : 'next');
                @endphp
                <a href="{{ $site->url($linkBase . $key) }}" @unless($hasLanding) data-facet-link data-facet="{{ $key }}" @endunless class="gd-step is-{{ $state }}">
                    <div class="gd-top">
                        <span class="gd-icon">@include('channels.partials.icon', ['name' => $f['icon'] ?? 'check'])</span>
                        <span class="gd-badge">@if($state==='done') ✓ heb je @elseif($state==='now') jouw stap @else later @endif</span>
                    </div>
                    <div class="gd-label">{{ $nr }}. {{ $f['label'] ?? $key }}</div>
                    <div class="gd-tag">{{ $f['tagline'] ?? '' }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
