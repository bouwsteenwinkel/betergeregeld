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

       Witte knop op de navy footer: de huisstijl-CTA (#12386B op wit) valt hier weg
       tegen de achtergrond, dus omgekeerd. */
    .bgf-plan { display: flex; align-items: center; justify-content: space-between; gap: 16px 28px; flex-wrap: wrap;
        max-width: 1280px; margin: 0 auto 36px; padding: 18px 22px; border-radius: 10px;
        background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.16); }
    .bgf-plan-t { font-weight: 800; font-size: 18px; color: #fff; letter-spacing: -0.01em; }
    .bgf-plan-s { font-size: 14.5px; color: #B9C6DE; margin-top: 2px; }
    .bgf-plan-b { display: inline-flex; align-items: center; gap: 9px; flex: 0 0 auto; background: #fff; color: #12386B !important;
        padding: 13px 22px; border-radius: 6px; font-size: 16px; font-weight: 700; text-decoration: none; min-height: 44px; }
    .bgf-plan-b:hover { background: #EAF1FA; color: #12386B !important; }
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
            <div class="bgf-plan-s">Plan een videogesprek van {{ (int) config('scheduling.meeting_minutes', 60) }} minuten. Gratis en vrijblijvend, je kiest zelf het moment.</div>
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
                    <span style="font-weight: 800; font-size: 18px; letter-spacing: -0.02em; color: #fff;">betergeregeld</span>
                </div>
                <div style="font-size: 14px; line-height: 1.7; color: #B9C6DE;">
                    <div style="color: #fff; font-weight: 700;">Betergeregeld ICT</div>
                    <div>KvK 88321703</div>
                    <div>TB Huurmanlaan 3, 1403 SL Bussum</div>
                    <div><a href="mailto:info@betergeregeld.com" style="color: #fff; font-weight: 700; text-decoration: none;">info@betergeregeld.com</a></div>
                </div>
            </div>

            <div>
                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #7FB0DE; margin-bottom: 12px;">Pagina's</div>
                {{-- Links naar secties óp de homepage (de losse pagina's zijn verwijderd). --}}
                <div style="display: flex; flex-direction: column;">
                    <a href="{{ $site->url('') }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Home</a>
                    <a href="{{ $site->url('') . '#prijzen' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Prijzen</a>
                    <a href="{{ $site->url('') . '#werkwijze' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Werkwijze</a>
                    <a href="{{ $site->url('') . '#automatisering' }}" style="color: #AEC6E6; font-size: 15px; line-height: 1.9; text-decoration: none;">Automatisering</a>
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

        <a href="tel:+31882545101" style="flex: 0 1 300px; max-width: 340px; display: flex; align-items: stretch;">
            <img src="/channel-media/bedrijfswebsite/joshua-blue-3.png" alt="Liever even bellen? Bel 088 2545101" style="width: 100%; max-width: 303px; height: auto; object-fit: contain; object-position: center bottom; display: block; margin-bottom: -32px">
        </a>
    </div>

    <div style="max-width: 1280px; margin: 28px auto 0; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center; font-size: 14px; color: #9DB0D0;">
        <div>&copy; 2026 Betergeregeld ICT. Alle prijzen zijn exclusief btw.</div>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ $site->url('privacybeleid') }}" style="color: #9DB0D0; text-decoration: none;">Privacybeleid</a>
            <a href="{{ $site->url('cookiebeleid') }}" style="color: #9DB0D0; text-decoration: none;">Cookiebeleid</a>
            <a href="{{ $site->url('algemene-voorwaarden') }}" style="color: #9DB0D0; text-decoration: none;">Algemene voorwaarden</a>
        </div>
    </div>
</footer>
