{{--
    Eén page_view per paginaweergave naar onze eigen event-log (channel_events).

    WAAROM DIT BESTAAT. Het kiosk-scherm (/scherm/betergeregeld) telt bezoekers als
    page_view-rijen in channel_events, maar geen enkele pagina stuurde dat event: de
    beacon in analytics-head vuurde alleen funnel-events, en betergeregeld.com zelf
    had helemaal geen beacon. Sinds de start van het scherm (12-09-2026) stond de
    teller dus altijd op 0, hoeveel bezoekers er ook waren (gemeld 14-09-2026).

    Dataminimaal, zelfde regels als ChannelEventController: alleen het pad, geen
    query-string, geen eigen cookie (visit_ref zit in de server-sessie). Bewust NIET
    via bgTrack: dat duwt het event ook naar de dataLayer, en een GTM-trigger op
    'page_view' zou GA4 dan dubbel laten tellen.

    Na de paginacache (CacheChannelPage) draait dit gewoon in de browser, dus een
    bezoek uit de cache telt ook. Bots zonder JavaScript tellen niet mee.

    Optioneel: $beaconUrl (standaard '/_ev').
--}}
<script>
(function () {
    try {
        if (document.visibilityState === 'prerender') return;
        var url = @json($beaconUrl ?? '/_ev');
        var payload = JSON.stringify({ e: 'page_view', p: location.pathname });
        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, new Blob([payload], { type: 'application/json' }));
        } else {
            fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: payload, keepalive: true }).catch(function () {});
        }
    } catch (e) {}
})();
</script>
