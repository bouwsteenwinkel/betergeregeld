@php /** @var \App\Support\ChannelSite $site */ @endphp
{{-- Gedeelde trust-/aanpak-secties voor de overzichts-home én de
     productlandingspagina's van de badkamerspecialist-triggersite. --}}

{{-- Aanpak --}}
<section data-section="aanpak">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Zo werkt het</span>
        <h2>Van aanvraag naar iets dat voor je werkt</h2>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Gratis voorbeeld</h3>
                <p>Beantwoord een paar korte vragen. Wij zetten een voorbeeld van jóuw bedrijf klaar, vaak al binnen 1 à 2 dagen. Gratis en vrijblijvend.</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Samen scherpstellen</h3>
                <p>We bespreken het voorbeeld bij jou op locatie of online. Jij bepaalt wat er wel en niet in moet, tot het klopt.</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>Live en groeit mee</h3>
                <p>We zetten 'm live. Later uitbreiden met een webshop, portaal of automatisering? Dat bouwen we er gewoon op voort. Je hoeft nooit opnieuw te beginnen.</p>
            </div>
        </div>
    </div>
</section>

{{-- Waarom betergeregeld --}}
<section data-section="waarom" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Waarom betergeregeld</span>
        <div class="grid cols-4 feature-grid" style="margin-top:1.4rem">
            <div class="feature-card"><h3>Vaste prijs</h3><span class="feature-rule"></span><p>Duidelijke prijs vooraf, geen verrassingen achteraf.</p></div>
            <div class="feature-card"><h3>Echt bereikbaar</h3><span class="feature-rule"></span><p>Een Nederlands team dat opneemt en meedenkt.</p></div>
            <div class="feature-card"><h3>Groeit met je mee</h3><span class="feature-rule"></span><p>De Groeidiamant: begin klein, breid uit wanneer je eraan toe bent.</p></div>
            <div class="feature-card"><h3>Bij jou langs</h3><span class="feature-rule"></span><p>Bezoek aan huis in de regio, of gewoon online. Net wat jou uitkomt.</p></div>
        </div>
    </div>
</section>

{{-- CTA-band --}}
<section class="cta-band" data-section="cta">
    <div class="wrap">
        <div class="cta-band-inner">
            <div>
                <h2>{{ $ctaTitle ?? 'Benieuwd hoe het voor jou zou werken?' }}</h2>
                <p>Vraag gratis en vrijblijvend een voorbeeld aan. Je zit nergens aan vast.</p>
            </div>
            <a href="{{ $ctaHref ?? '#gratis-voorbeeld' }}" class="btn">Gratis voorbeeld aanvragen</a>
        </div>
    </div>
</section>
