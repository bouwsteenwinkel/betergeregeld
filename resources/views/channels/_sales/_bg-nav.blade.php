{{-- Gedeelde betergeregeld-header voor het bedrijfswebsite-kanaal.
     Gebruikt op de homepage (_sales/bedrijfswebsite) EN, via een key-guard in
     channels/layout.blade.php, op alle overige pagina's van dit kanaal, zodat de
     hele funnel dezelfde header heeft. Links wijzen naar echte routes (niet naar
     homepage-ankers) zodat de nav overal werkt. Verwacht $site in scope. --}}
<style>
    .bgn-links { display: flex; align-items: center; gap: 28px; }
    .bgn-links a:hover { color: #12386B; }
    .bgn-phone:hover { color: #0C2A50; }
    .bgn-cta-btn:hover { background: #0C2A50; }
    /* Hamburger + mobiele belknop: alleen op mobiel zichtbaar. */
    .bgn-burger { display: none; }
    .bgn-phone-icon { display: none; }
    .bgn-drawer { display: none; }
    .bgn-scrim { display: none; }
    @media (max-width: 759px) {
        /* De inline display:flex is verwijderd van .bgn-links, dus deze regel wint nu.
           Het telefoonNUMMER gaat er ook uit: 18px/800 naast de wordmark past niet in
           390px, en omdat "betergeregeld" niet afbreekt liep het nummer er dwars
           overheen. Bellen blijft één tik via .bgn-phone-icon + de drawer. */
        .bgn-links, .bgn-cta-btn, .bgn-phone { display: none; }
        .bgn-actions { gap: 8px !important; }
        .bgn-phone-icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; padding: 0; background: #12386B; border: 1.5px solid #12386B; border-radius: 6px; color: #fff; text-decoration: none; }
        .bgn-burger { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; padding: 0; background: transparent; border: 1.5px solid #E5E3DF; border-radius: 6px; color: #12386B; cursor: pointer; }
        .bgn-drawer.is-open { display: block; }
        .bgn-drawer a { display: block; padding: 14px 24px; font-size: 16px; font-weight: 600; color: #1A1A1A; text-decoration: none; border-top: 1px solid #E5E3DF; }
        .bgn-drawer a.bgn-drawer-cta { color: #fff; background: #12386B; font-weight: 700; }
        .bgn-drawer a.bgn-drawer-phone { color: #12386B; font-weight: 800; }
        /* Cookievoorkeuren: bewust ingetogen, het is een beheerlink en geen aanbod.
           Wel een volwaardig tikdoel (14px padding boven en onder = 45px hoog). */
        .bgn-drawer a.bgn-drawer-cookies { font-size: 14px; font-weight: 600; color: #6B6864; }
        /* Scrim vangt de tik naast het menu op, zodat de pagina eronder niet
           per ongeluk wegnavigeert, en dient meteen als klik-buiten. */
        .bgn-scrim.is-open { display: block; position: fixed; inset: 0; background: rgba(26,26,26,0.38); z-index: 0; }
        /* Stapelvolgorde binnen de nav: balk boven, drawer daaronder, scrim achter beide. */
        .bgn-bar { position: relative; z-index: 2; background: #FAF9F7; }
        .bgn-drawer { position: relative; z-index: 1; background: #FAF9F7; }
        /* Hamburger wordt een kruis zodra het menu open staat. */
        .bgn-burger svg line { transform-box: view-box; transform-origin: 12px 12px; transition: transform .18s ease, opacity .12s ease; }
        .bgn-burger[aria-expanded="true"] svg line:nth-of-type(1) { transform: rotate(45deg) translateY(6px); }
        .bgn-burger[aria-expanded="true"] svg line:nth-of-type(2) { opacity: 0; }
        .bgn-burger[aria-expanded="true"] svg line:nth-of-type(3) { transform: rotate(-45deg) translateY(-6px); }
        /* Logo-link als tikdoel op 44px brengen; de balk is al hoger, dus de
           balkhoogte verandert hier niet door. */
        .bgn-logo-link { padding: 7px 0; }
    }
    /* Smalle toestellen: de wordmark brak op 360px naar twee regels en raakte
       de belknop. Onder 380px dus kleiner en op één regel houden, met een
       harde minimumafstand tot de knoppen. */
    @media (max-width: 379px) {
        /* !important is hier nodig: de wordmark heeft een inline font-size: 18px,
           die won van deze regel. Daardoor bleef de wordmark 18px én nowrap, werd
           de balk-inhoud breder dan zijn padding-box en plakte de burger tegen de
           rechterrand (360px: nog 4px marge, links 24px). */
        .bgn-wordmark { font-size: 15px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Vangnet: laat de logo-link krimpen in plaats van de balk open te duwen,
           mochten de letterbreedtes op een toestel anders uitvallen. */
        .bgn-logo-link { min-width: 0; }
        .bgn-bar { gap: 12px; }
    }
    /* Zeer smalle toestellen (±320px): wordmark eruit, embleem blijft. */
    @media (max-width: 359px) {
        .bgn-wordmark { display: none; }
    }
</style>
<nav style="position: sticky; top: 0; z-index: 60; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; border-bottom: 1px solid #E5E3DF; font-family: 'Archivo', system-ui, sans-serif;">
    <div class="bgn-bar" style="max-width: 1280px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ $site->url('') }}" class="bgn-logo-link" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <div style="width: 30px; height: 30px; background: #12386B; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 17px;">B</div>
            <span class="bgn-wordmark" style="font-weight: 800; font-size: 18px; letter-spacing: -0.02em; color: #1A1A1A;">Jouw Bedrijfswebsite</span>
        </a>
        <div class="bgn-actions" style="display: flex; align-items: center; gap: 28px;">
            {{-- Links naar secties óp de homepage (de losse pagina's zijn verwijderd).
                 display:flex staat nu in de <style> zodat de mobiele media-query hem kan verbergen. --}}
            <div class="bgn-links">
                <a href="{{ $site->url('') . '#werkwijze' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Werkwijze</a>
                <a href="{{ $site->url('') . '#prijzen' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Prijzen</a>
                <a href="{{ $site->url('blog') }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Blog</a>
                <a href="{{ $site->url('plaatsen') }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Werkgebied</a>
                <a href="{{ $site->url('') . '#contact' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Contact</a>
            </div>
            <a href="tel:+31882545101" class="bgn-phone" style="font-size: 18px; font-weight: 800; letter-spacing: -0.01em; color: #12386B; text-decoration: none;">088 2545101</a>
            <a href="{{ $site->url('voorbeeld-maken') }}" class="bgn-cta-btn" style="background: #12386B; color: #fff; padding: 11px 20px; border-radius: 6px; font-size: 15px; font-weight: 700; text-decoration: none; white-space: nowrap;">Bekijk mijn voorbeeld</a>
            {{-- Mobiele belknop: vervangt het nummer, blijft één tik van bellen. --}}
            <a href="tel:+31882545101" class="bgn-phone-icon" aria-label="Bel 088 2545101">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </a>
            {{-- Mobiele hamburger: opent de drawer hieronder. --}}
            <button type="button" class="bgn-burger" aria-label="Menu" aria-expanded="false">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>
    {{-- Mobiele uitklap-drawer (alleen zichtbaar < 760px via .bgn-drawer regels). --}}
    <div id="bgn-drawer" class="bgn-drawer">
        <a href="{{ $site->url('') . '#werkwijze' }}">Werkwijze</a>
        <a href="{{ $site->url('') . '#prijzen' }}">Prijzen</a>
        <a href="{{ $site->url('blog') }}">Blog</a>
        <a href="{{ $site->url('plaatsen') }}">Werkgebied</a>
        <a href="{{ $site->url('') . '#contact' }}">Contact</a>
        <a href="tel:+31882545101" class="bgn-drawer-phone">Bel 088 2545101</a>
        <a href="{{ $site->url('voorbeeld-maken') }}" class="bgn-drawer-cta">Bekijk mijn voorbeeld</a>
        {{-- Cookievoorkeuren onderaan het menu. Dit is op mobiel de enige plek waar een
             bezoeker zijn toestemming kan herzien: de zwevende CMP-knop staat op alle
             niet-homepaginas uit. Intrekken hoort net zo makkelijk te zijn als geven.
             data-cmp-open-prefs laat de CMP het voorkeuren-venster openen; het href is
             de terugval als de CMP niet laadt, dan land je alsnog op het cookiebeleid. --}}
        <a href="{{ $site->url('cookiebeleid') }}" class="bgn-drawer-cookies" data-cmp-open-prefs>Cookievoorkeuren</a>
    </div>
    {{-- Scrim: vangt de tik naast het open menu op. --}}
    <div id="bgn-scrim" class="bgn-scrim" aria-hidden="true"></div>
</nav>
<script>
(function () {
    var nav = document.currentScript.previousElementSibling;
    var drawer = document.getElementById('bgn-drawer');
    var scrim = document.getElementById('bgn-scrim');
    var burger = nav ? nav.querySelector('.bgn-burger') : null;
    if (!drawer || !burger) return;

    function setOpen(open) {
        drawer.classList.toggle('is-open', open);
        if (scrim) scrim.classList.toggle('is-open', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
    }
    function isOpen() { return drawer.classList.contains('is-open'); }

    burger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        setOpen(!isOpen());
    });
    // Sluiten na een menuklik, zodat de sectiekop waar je heen springt vrij ligt.
    drawer.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { setOpen(false); });
    });
    if (scrim) scrim.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen()) { setOpen(false); burger.focus(); }
    });
    // Klik buiten de nav (bijvoorbeeld op de sticky balk zelf) sluit ook.
    document.addEventListener('click', function (e) {
        if (isOpen() && nav && !nav.contains(e.target)) setOpen(false);
    });
    // Terug naar desktop: alles netjes resetten.
    window.addEventListener('resize', function () {
        if (window.innerWidth > 759 && isOpen()) setOpen(false);
    });
})();
</script>
