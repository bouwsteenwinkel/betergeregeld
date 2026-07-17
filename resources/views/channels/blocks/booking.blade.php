@php
    /** @var \App\Models\Channel\Block $block */
    $phone = $site->brand('phone');

    // Dit blok is bewust GEEN boekingswidget (meer). Een nep-agenda die "afspraak
    // bevestigd / aanbetaling voldaan" toont, botste op een preview met onze eigen
    // conversie (de ondernemer denkt dat hij een gesprek met óns plant, en belandt
    // in een verzonnen boeking voor zijn eigen klanten). Vandaar: overal een eerlijke
    // contactsectie met het anker #contact. Het echte "plan een kennismaking met
    // Betergeregeld" loopt via de voorbeeld-chrome (balk, sticky-CTA, nav-CTA,
    // preview-prompt) en opent de afspraak-modal — niet via de site-body.
    $isPreview = (bool) $site->get('meta.preview.is_preview');
@endphp

<section data-block="booking" data-section="afspraak" id="contact" class="booking">
    <div class="wrap">
        <div class="bk-head">
            <span class="kicker" style="justify-content:center"><span class="kicker-line"></span> Contact</span>
            <h2>{{ $block->c('heading', 'Neem contact op') }}</h2>
            @if ($block->c('sub'))<p class="muted bk-sub">{{ $block->c('sub') }}</p>@endif
            @if ($phone)
                <p class="bk-sub" style="margin-top:1rem"><a class="btn" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Bel {{ $phone }}</a></p>
            @endif
            @if ($isPreview)
                {{-- Op een preview is de ondernemer zelf de bezoeker: hier hoort geen
                     klant-boeking maar de stap naar óns. Opent dezelfde afspraak-modal
                     als de rest van de voorbeeld-chrome. --}}
                <p class="bk-sub" style="margin-top:1.1rem">
                    <button type="button" class="btn" data-pv-appt-open>Plan een kennismaking met ons</button>
                </p>
            @endif
        </div>
    </div>
</section>

<style>
    .booking .bk-head{text-align:center;max-width:640px;margin:0 auto}
    .booking .bk-head h2{margin:.25rem 0 0;font-size:clamp(1.55rem,3.4vw,2rem)}
    .booking .bk-sub{margin-top:.3rem}
</style>
