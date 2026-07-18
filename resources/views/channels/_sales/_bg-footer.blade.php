{{-- Gedeelde betergeregeld-footer voor het bedrijfswebsite-kanaal (homepage + overige
     pagina's, via de key-guard in channels/layout.blade.php). Links wijzen naar echte
     routes. id="contact" blijft behouden zodat de sticky-CTA-anker uit de layout werkt.
     Verwacht $site in scope. --}}
<style>
    .bgf a:hover { color: #fff; }
    /* Op mobiel valt de full-bleed horizontale padding (calc(50vw - 50%)) terug naar ~0,
       waardoor de inner content tegen de schermranden plakt. Geef een minimum-gutter.
       !important omdat de padding een inline-stijl op .bgf is. */
    @media (max-width: 760px) {
        .bgf { padding-left: 20px !important; padding-right: 20px !important; }
    }

    /* Directe ingang naar de afspraakplanner, op élke pagina van dit kanaal. /afspraak
       bestond al als route, maar er linkte nergens iets naartoe: de pagina was alleen
       bereikbaar via de preview-CTA. Naast de bel-CTA hiernaast is dit de tweede manier
       om contact te leggen, voor wie liever plant dan belt.

       Groene CTA op de navy footer (#12386B): een huisstijl-witte of navy knop valt hier
       weg. Fel groen #22C55E geeft ~5:1 contrast met de achtergrond, dus hij springt eruit.
       Tekst bewust donkergroen i.p.v. wit: om op navy te knallen moet het groen zo licht
       zijn dat witte tekst onder 4,5:1 zakt, dus fel-groen-met-donkere-tekst is de enige
       combinatie die zowel opvalt als leesbaar blijft. */
    .bgf-plan { display: flex; align-items: center; justify-content: space-between; gap: 16px 28px; flex-wrap: wrap;
        max-width: 1280px; margin: 0 auto 36px; padding: 18px 22px; border-radius: 10px;
        background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.16); }
    .bgf-plan-t { font-weight: 800; font-size: 18px; color: #fff; letter-spacing: -0.01em; }
    .bgf-plan-s { font-size: 14.5px; color: #B9C6DE; margin-top: 2px; }
    .bgf-plan-b { display: inline-flex; align-items: center; gap: 9px; flex: 0 0 auto; background: #22C55E; color: #052E13 !important;
        padding: 13px 22px; border-radius: 6px; font-size: 16px; font-weight: 700; text-decoration: none; min-height: 44px; }
    .bgf-plan-b:hover { background: #16A34A; color: #052E13 !important; }
    .bgf-plan-b svg { width: 18px; height: 18px; flex: 0 0 auto; }
    @media (max-width: 560px) {
        .bgf-plan { flex-direction: column; align-items: stretch; }
        .bgf-plan-b { justify-content: center; }
    }

    /* Onderaan zweeft op mobiel altijd iets: op /blog en /plaatsen de 59px hoge
       .sticky-cta uit de layout, op de homepage de ronde #cmp-reopen. De inline
       padding op .bgf verslaat de footer{padding-bottom:84px} uit layout.blade.php,
       dus lag de laatste juridische link permanent onder dat vaste element.
       !important omdat het om een inline-stijl gaat. */
    @media (max-width: 860px) {
        .bgf { padding-bottom: calc(96px + env(safe-area-inset-bottom)) !important; }
    }

    /* Tikdoelen: de kolomlinks (29px), het mailadres (15px) en de juridische links
       (15 tot 22px) bleven ruim onder de 44px. Alleen het raakvlak groeit, kleur,
       grootte en tekst blijven gelijk. De juridische rij stapelt op mobiel, want
       naast elkaar brak hij op 360/390 als 2 + 1 met een gat rechts. */
    @media (max-width: 760px) {
        /* line-height 1.9 (voor de desktop-kolommen) maakte de rijen op mobiel ~44px:
           te sparse. Op mobiel een strak regelhoogte + compacte tik-padding, gelijk
           voor beide kolommen. !important verslaat de inline/geërfde line-height. */
        .bgf-navlink, .bgf-vaklink { display: inline-block; line-height: 1.3 !important; padding-top: 7px; padding-bottom: 7px; }
        .bgf-mail { display: inline-block; padding-top: 11px; padding-bottom: 11px; }
        .bgf-legal { flex-direction: column; gap: 2px !important; }
        .bgf-legal a { display: inline-block; padding-top: 7px; padding-bottom: 7px; }
        /* De 44px-gap tussen de link-kolommen en de sfeerfoto oogde op mobiel als een
           grote lege zone (de foto stapelt eronder). Kleiner op mobiel. */
        .bgf-body { gap: 18px !important; }
    }

    /* De fotokolom is 300px breed en liep op 360 tot vlak tegen de schermrand,
       terwijl de footertekst 20px marge houdt. Kleiner en gecentreerd. */
    @media (max-width: 560px) {
        .bgf-photo { flex: 1 1 auto; justify-content: center; margin: 0 auto; }
        /* !important omdat de max-width een inline-stijl op de <img> is. */
        .bgf-photo img { max-width: 240px !important; margin-left: auto; margin-right: auto; }
    }

    /* Mobiel/tablet: de 3-koloms auto-fit-grid viel terug naar 1 kolom (2x160px + 44px
       gap paste niet), waardoor logo, Pagina's en Voor-jouw-vak vol onder elkaar
       stonden = lange, rommelige footer. Nu bewust 2 koloms: het merk-/adresblok
       vol-breed bovenaan, daaronder Pagina's naast Voor-jouw-vak (korte woorden, dus
       ze passen prima naast elkaar). !important verslaat de inline grid-stijl op .bgf-cols. */
    @media (max-width: 640px) {
        .bgf-cols { grid-template-columns: 1fr 1fr !important; gap: 26px 20px !important; }
        .bgf-brand { grid-column: 1 / -1; }
    }
</style>
<footer id="contact" class="bgf" style="padding: 48px calc(50vw - 50%) 32px; margin: 40px calc(50% - 50vw) 0; width: 100vw; background: #12386B; color: #DCE4F0; font-family: 'Archivo', system-ui, sans-serif;">
    <div class="bgf-plan">
        <div>
            <div class="bgf-plan-t">Even sparren over jouw website?</div>
            <div class="bgf-plan-s">Plan met mij een gesprek, online of telefonisch. Gratis en vrijblijvend, je kiest zelf het moment.</div>
        </div>
        <a href="{{ $site->url('afspraak') }}" class="bgf-plan-b">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>
            </svg>
            Plan een gesprek
        </a>
    </div>

    <div class="bgf-body" style="display: flex; flex-wrap: wrap; gap: 44px; align-items: stretch; max-width: 1280px; margin: 0 auto;">
        <div class="bgf-cols" style="flex: 1 1 520px; display: grid; gap: 44px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
            <div class="bgf-brand">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <div style="width: 30px; height: 30px; background: #fff; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #12386B; font-weight: 900; font-size: 17px;">B</div>
                    <span style="font-weight: 800; font-size: 18px; letter-spacing: -0.02em; color: #fff;">Jouw Bedrijfswebsite</span>
                </div>
                <div style="font-size: 14px; line-height: 1.7; color: #B9C6DE;">
                    <div style="color: #fff; font-weight: 700;">Betergeregeld ICT</div>
                    <div>KvK 88321703</div>
                    <div>TB Huurmanlaan 3, 1403 SL Bussum</div>
                    <div><a href="mailto:info@jouw-bedrijfswebsite.nl" class="bgf-mail" style="color: #fff; font-weight: 700; text-decoration: none;">info@jouw-bedrijfswebsite.nl</a></div>
                </div>
            </div>

            <div>
                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #7FB0DE; margin-bottom: 12px;">Pagina's</div>
                {{-- Links naar secties óp de homepage (de losse pagina's zijn verwijderd),
                     plus de twee échte rubrieken. Blog en Plaatsen stonden nergens in de
                     site gelinkt: 598 plaatspagina's en 22 blogartikelen waren daardoor
                     weespagina's, alleen te vinden via de sitemap. Google leidt daar geen
                     enkele autoriteit naartoe, en een bezoeker kwam er nooit. --}}
                <div style="display: flex; flex-direction: column;">
                    <a href="{{ $site->url('') }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Home</a>
                    <a href="{{ $site->url('') . '#prijzen' }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Prijzen</a>
                    <a href="{{ $site->url('') . '#werkwijze' }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Werkwijze</a>
                    <a href="{{ $site->url('') . '#automatisering' }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Automatisering</a>
                    <a href="{{ $site->url('blog') }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Blog</a>
                    <a href="{{ $site->url('plaatsen') }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Werkgebied</a>
                    <a href="{{ $site->url('') . '#contact' }}" class="bgf-navlink" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Contact</a>
                </div>
            </div>

            <div>
                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #7FB0DE; margin-bottom: 12px;">Voor jouw vak</div>
                <div style="display: flex; flex-direction: column; font-size: 15px; line-height: 1.9; color: #B9C6DE;">
                    <span class="bgf-vaklink">Dakdekker</span>
                    <span class="bgf-vaklink">Installateur</span>
                    <span class="bgf-vaklink">Hovenier</span>
                    <span class="bgf-vaklink">Kapper</span>
                    <span class="bgf-vaklink">Garage</span>
                    <span class="bgf-vaklink">Bakkerij</span>
                    <span class="bgf-vaklink">En veel meer vakken.</span>
                </div>
            </div>
        </div>

        {{-- .webp van 606px (2x de weergavemaat) i.p.v. de PNG van 1080x1080: die was
             592 KB en laadde eager in de footer van álle 622 pagina's van dit kanaal,
             goed voor het leeuwendeel van het paginagewicht. Nu 26 KB, en lazy: hij
             staat onderaan, dus niemand heeft hem nodig bij het eerste scherm. --}}
        {{-- Geen link en niet aan te klikken of te slepen: het is sfeerbeeld, geen knop.
             Bellen kan via de sticky nav, die drie tel:-ingangen heeft (ook op mobiel).
             margin-bottom is -28px en niet -32px: de balk hieronder begint op 28px, dus
             bij -32px stak de foto er 4px voorbij. Nu sluit hij exact op de streep aan. --}}
        <div class="bgf-photo" style="flex: 0 1 300px; max-width: 340px; display: flex; align-items: stretch;">
            <img src="/channel-media/bedrijfswebsite/joshua-blue-3.webp" alt="Liever even bellen? Bel 088 2545101" width="303" height="303" loading="lazy" decoding="async" draggable="false" style="width: 100%; max-width: 303px; height: auto; object-fit: contain; object-position: center bottom; display: block; margin-bottom: -28px; pointer-events: none; user-select: none; -webkit-user-select: none; -webkit-user-drag: none;">
        </div>
    </div>

    {{-- position/z-index: de foto hierboven heeft margin-bottom:-32px en steekt over deze
         balk heen. Een <img> is inline-content en wordt volgens de schilderregels ná de
         randen van blokken getekend, dus zonder eigen stapelcontext verdween de streep
         precies achter de foto. --}}
    <div style="position: relative; z-index: 1; max-width: 1280px; margin: 28px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center; font-size: 14px; color: #9DB0D0;">
        <div>&copy; 2026 Jouw Bedrijfswebsite. Alle prijzen zijn exclusief btw.</div>
        <div class="bgf-legal" style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ $site->url('privacybeleid') }}" style="color: #9DB0D0; text-decoration: none;">Privacybeleid</a>
            <a href="{{ $site->url('cookiebeleid') }}" style="color: #9DB0D0; text-decoration: none;">Cookiebeleid</a>
            <a href="{{ $site->url('algemene-voorwaarden') }}" style="color: #9DB0D0; text-decoration: none;">Algemene voorwaarden</a>
        </div>
    </div>
</footer>
