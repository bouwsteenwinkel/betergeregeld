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

    <div style="display: flex; flex-wrap: wrap; gap: 44px; align-items: stretch; max-width: 1280px; margin: 0 auto;">
        <div style="flex: 1 1 520px; display: grid; gap: 44px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <div style="width: 30px; height: 30px; background: #fff; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #12386B; font-weight: 900; font-size: 17px;">B</div>
                    <span style="font-weight: 800; font-size: 18px; letter-spacing: -0.02em; color: #fff;">Jouw Bedrijfswebsite</span>
                </div>
                <div style="font-size: 14px; line-height: 1.7; color: #B9C6DE;">
                    <div style="color: #fff; font-weight: 700;">Betergeregeld ICT</div>
                    <div>KvK 88321703</div>
                    <div>TB Huurmanlaan 3, 1403 SL Bussum</div>
                    <div><a href="mailto:info@jouw-bedrijfswebsite.nl" style="color: #fff; font-weight: 700; text-decoration: none;">info@jouw-bedrijfswebsite.nl</a></div>
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
                    <a href="{{ $site->url('') }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Home</a>
                    <a href="{{ $site->url('') . '#prijzen' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Prijzen</a>
                    <a href="{{ $site->url('') . '#werkwijze' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Werkwijze</a>
                    <a href="{{ $site->url('') . '#automatisering' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Automatisering</a>
                    <a href="{{ $site->url('blog') }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Blog</a>
                    <a href="{{ $site->url('plaatsen') }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Werkgebied</a>
                    <a href="{{ $site->url('') . '#contact' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Contact</a>
                </div>
            </div>

            <div>
                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #7FB0DE; margin-bottom: 12px;">Voor jouw vak</div>
                <div style="display: flex; flex-direction: column; font-size: 15px; line-height: 1.9; color: #B9C6DE;">
                    <span>Dakdekker</span>
                    <span>Installateur</span>
                    <span>Hovenier</span>
                    <span>Kapper</span>
                    <span>Garage</span>
                    <span>Bakkerij</span>
                    <span style="margin-top: 6px;">En veel meer vakken.</span>
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
        <div style="flex: 0 1 300px; max-width: 340px; display: flex; align-items: stretch;">
            <img src="/channel-media/bedrijfswebsite/joshua-blue-3.webp" alt="Liever even bellen? Bel 088 2545101" width="303" height="303" loading="lazy" decoding="async" draggable="false" style="width: 100%; max-width: 303px; height: auto; object-fit: contain; object-position: center bottom; display: block; margin-bottom: -28px; pointer-events: none; user-select: none; -webkit-user-select: none; -webkit-user-drag: none;">
        </div>
    </div>

    {{-- position/z-index: de foto hierboven heeft margin-bottom:-32px en steekt over deze
         balk heen. Een <img> is inline-content en wordt volgens de schilderregels ná de
         randen van blokken getekend, dus zonder eigen stapelcontext verdween de streep
         precies achter de foto. --}}
    <div style="position: relative; z-index: 1; max-width: 1280px; margin: 28px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center; font-size: 14px; color: #9DB0D0;">
        <div>&copy; 2026 Jouw Bedrijfswebsite. Alle prijzen zijn exclusief btw.</div>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ $site->url('privacybeleid') }}" style="color: #9DB0D0; text-decoration: none;">Privacybeleid</a>
            <a href="{{ $site->url('cookiebeleid') }}" style="color: #9DB0D0; text-decoration: none;">Cookiebeleid</a>
            <a href="{{ $site->url('algemene-voorwaarden') }}" style="color: #9DB0D0; text-decoration: none;">Algemene voorwaarden</a>
        </div>
    </div>
</footer>
