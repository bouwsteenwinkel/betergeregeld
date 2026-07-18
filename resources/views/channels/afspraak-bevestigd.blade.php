{{-- Bevestigingspagina na een geslaagde boeking. Bestaat als aparte URL zodat een
     ads-conversie op een echte pageview (/afspraak-bevestigd) gemeten kan worden i.p.v.
     op een inline melding op /afspraak. De booking-widget redirect hierheen.
     $confirmed = kwam hier na een echte boeking (server-flash), niet via refresh/direct. --}}
@extends('channels.layout')

@section('title', 'Afspraak bevestigd')
@section('description', 'Je afspraak staat gepland. Je ontvangt de bevestiging per e-mail.')
{{-- Thank-you page hoort niet in Google en mag niet los vindbaar zijn. --}}
@section('robots', 'noindex,nofollow')

@section('content')
<section class="bkg" data-section="afspraak-bevestigd">
    <div class="wrap" style="max-width:60ch">
        <span class="kicker"><span class="kicker-line"></span> Gelukt</span>
        <h1>Je afspraak staat gepland</h1>
        <p class="muted" style="margin-top:.6rem">
            Je ontvangt zo een bevestiging per e-mail met alle details en, bij een videogesprek,
            de Google Meet-link. Geen mail binnen een paar minuten? Kijk even in je spam.
        </p>
        <p style="margin-top:1.6rem">
            <a href="/" class="btn">Terug naar de homepage</a>
        </p>
    </div>
</section>

@if ($confirmed)
<script>
    // Conversie-event, alleen na een echte boeking (server-flash) — dus niet bij een
    // refresh of direct bezoek van deze URL. Zo telt de conversie precies één keer.
    // bgTrack duwt dit naar de dataLayer én (consent-gated) als Meta 'Lead' naar de pixel.
    if (window.bgTrack) window.bgTrack('appointment_booked', {});
</script>
@endif
@endsection
