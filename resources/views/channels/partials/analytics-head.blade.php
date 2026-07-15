@php
    // Meting + Google Consent Mode v2. MOET zo vroeg mogelijk in de <head> staan,
    // vóór de CMP-loader en vóór GTM: de 'default'-call moet als eerste in de
    // dataLayer staan, anders vuren tags al voordat de bezoeker gekozen heeft.
    //
    // Zonder ANALYTICS_GTM_ID laadt er geen container; de consent-defaults en de
    // dataLayer zetten we wél altijd klaar, zodat de events van de site (bgTrack)
    // en de CMP-updates een geldige queue hebben zodra de container er komt.
    $gtmId = trim((string) config('analytics.gtm_id', ''));
    $ga4Id = trim((string) config('analytics.ga4_id', ''));
@endphp
<script>
(function () {
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    window.gtag = window.gtag || gtag;

    // Alles dicht tot de bezoeker kiest. wait_for_update geeft de (async) CMP
    // even de tijd om een eerdere keuze door te geven voordat tags vuren.
    gtag('consent', 'default', {
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        analytics_storage: 'denied',
        functionality_storage: 'denied',
        personalization_storage: 'denied',
        security_storage: 'granted',
        wait_for_update: 500
    });

    // Kwam de bezoeker al eerder langs en heeft hij toen gekozen? Dan die keuze
    // meteen toepassen, zonder te wachten op de async CMP-loader. Zelfde bron
    // (localStorage 'cmp_choices') en zelfde mapping als resources/cmp/loader.js.
    try {
        var saved = JSON.parse(localStorage.getItem('cmp_choices') || 'null');
        if (saved && typeof saved === 'object') {
            var yes = function (k) { return saved[k] === true || saved[k] === 1 || saved[k] === '1'; };
            gtag('consent', 'update', {
                ad_storage: yes('marketing') ? 'granted' : 'denied',
                ad_user_data: yes('marketing') ? 'granted' : 'denied',
                ad_personalization: yes('marketing') ? 'granted' : 'denied',
                analytics_storage: yes('analytics') ? 'granted' : 'denied',
                functionality_storage: yes('functional') ? 'granted' : 'denied',
                personalization_storage: yes('functional') ? 'granted' : 'denied'
            });
        }
    } catch (e) {}

    // Eén plek waar de site z'n events naartoe duwt. Bestaat altijd, ook zonder
    // container, zodat een aanroep nooit stukloopt.
    window.bgTrack = window.bgTrack || function (name, data) {
        try { window.dataLayer.push(Object.assign({ event: name }, data || {})); } catch (e) {}
    };
})();
</script>
@if ($gtmId !== '')
    {{-- Google Tag Manager. Laadt bewust vóór consent: GTM zet zelf geen cookies
         en beslist mét Consent Mode of tags mogen vuren. Zet GA4/Ads IN deze
         container, niet los in een view. --}}
    <script>
    (function (w, d, s, l, i) {
        w[l] = w[l] || []; w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', @json($gtmId));
    </script>
@elseif ($ga4Id !== '')
    {{-- Alleen als er géén GTM draait: GA4 rechtstreeks, óók onder Consent Mode. --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>window.gtag('js', new Date()); window.gtag('config', @json($ga4Id));</script>
@endif
