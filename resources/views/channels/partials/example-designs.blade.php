@php
    /** @var \App\Support\ChannelSite $site */
    // 3 voorbeeld-website-designs voor de branche: browser-mockups met een thema-nav
    // en de branche-beelden als hero. Gebruikt bestaande beelden (hero + galerij),
    // dus altijd aanwezig en generiek voor elke channel-site. Kleuren = site-thema.
    $shots = [
        ['slot' => 'hero',     'label' => 'Website',            'desc' => 'Een professionele site die klanten oplevert en gevonden wordt in Google.'],
        ['slot' => 'gallery1', 'label' => 'Website + webshop',  'desc' => 'Online verkopen of afspraken, netjes gekoppeld aan je site.'],
        ['slot' => 'gallery2', 'label' => 'Compleet platform',  'desc' => 'Met klantenportaal en slimme automatisering die met je meegroeit.'],
    ];
    $designs = [];
    foreach ($shots as $s) {
        if ($img = $site->image($s['slot'])) {
            $designs[] = $s + ['img' => $img, 'set' => $site->imageSrcset($s['slot'])];
        }
    }
@endphp
@if (count($designs) >= 1)
<section data-block="example-designs" class="exd">
    <div class="wrap">
        <div class="exd-head">
            <span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $eyebrow ?? 'Voorbeeld-designs' }}</span>
            <h2>{{ $heading ?? 'Zo zou jouw site eruit kunnen zien' }}</h2>
            <p class="muted">{{ $lead ?? 'Drie voorbeelden van wat we voor je bouwen, van een eerste website tot een compleet platform. Jouw eigen huisstijl en foto\'s, geen standaard template.' }}</p>
        </div>

        <div class="exd-grid">
            @foreach ($designs as $d)
                <div class="exd-card">
                    <div class="exd-browser">
                        <div class="exd-bar"><span class="exd-dot"></span><span class="exd-dot"></span><span class="exd-dot"></span><span class="exd-addr"></span></div>
                        <div class="exd-site">
                            <div class="exd-nav"><span class="exd-brand"></span><span class="exd-links"><i></i><i></i><i></i></span><span class="exd-btn"></span></div>
                            <div class="exd-hero">
                                <img src="{{ $d['img'] }}" @if ($d['set']) srcset="{{ $d['set'] }}" sizes="(max-width:760px) 92vw, 32vw" @endif alt="Voorbeeld-website: {{ $d['label'] }}" loading="lazy" decoding="async">
                            </div>
                        </div>
                    </div>
                    <div class="exd-label">{{ $d['label'] }}</div>
                    <p class="exd-desc">{{ $d['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .exd-head{text-align:center;max-width:660px;margin:0 auto 2.2rem}
    .exd-head h2{margin:.3rem 0 .5rem}
    .exd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1.6rem;max-width:1080px;margin:0 auto}
    .exd-card{display:flex;flex-direction:column}
    .exd-browser{border-radius:14px;overflow:hidden;background:var(--c-surface);
        border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);
        box-shadow:0 26px 56px -32px rgba(0,0,0,.5);transition:transform .18s ease,box-shadow .18s ease}
    .exd-card:hover .exd-browser{transform:translateY(-5px);box-shadow:0 36px 70px -34px color-mix(in srgb,var(--c-primary) 45%,rgba(0,0,0,.5))}
    .exd-bar{height:34px;display:flex;align-items:center;gap:7px;padding:0 13px;background:color-mix(in srgb,var(--c-ink) 6%,var(--c-surface))}
    .exd-dot{width:10px;height:10px;border-radius:50%;background:color-mix(in srgb,var(--c-ink) 22%,transparent)}
    .exd-dot:first-child{background:#ff5f57}.exd-dot:nth-child(2){background:#febc2e}.exd-dot:nth-child(3){background:#28c840}
    .exd-addr{flex:1;height:16px;margin-left:8px;border-radius:8px;background:color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .exd-nav{height:38px;display:flex;align-items:center;gap:.7rem;padding:0 .9rem;background:var(--c-surface);
        border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
    .exd-brand{width:44px;height:12px;border-radius:4px;background:var(--c-primary)}
    .exd-links{display:flex;gap:.5rem;margin-left:auto}
    .exd-links i{display:block;width:26px;height:7px;border-radius:4px;background:color-mix(in srgb,var(--c-ink) 16%,transparent)}
    .exd-btn{width:52px;height:16px;border-radius:5px;background:var(--c-cta);margin-left:.4rem}
    .exd-hero{aspect-ratio:16/10;overflow:hidden}
    .exd-hero img{width:100%;height:100%;object-fit:cover;display:block}
    .exd-label{font-weight:800;font-size:1.08rem;margin:1rem 0 .2rem;text-align:center;color:var(--c-ink)}
    .exd-desc{color:var(--c-muted);font-size:.92rem;line-height:1.5;text-align:center;max-width:34ch;margin:0 auto}
</style>
@endif
