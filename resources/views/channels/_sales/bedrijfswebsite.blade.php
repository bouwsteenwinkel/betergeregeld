{{-- Homepage bedrijfswebsite-channel (jouw-bedrijfswebsite.nl).
     Zelfstandige verkooppagina (extend channels.layout NIET): het design heeft
     een eigen nav, footer, lettertype (Archivo) en kleurenschema. Nagebouwd uit
     het Claude-design "Betergeregeld bedrijfswebsite". De interactieve generator,
     Groeidiamant, reviewscroller, automatiseringsdemo en prijsschakelaar draaien
     op inline vanilla JS (geen build-stap). Placeholder-reviews en de fictieve
     voorbeeldbedrijven komen 1-op-1 uit het design; vervangen vóór livegang.
     Het blok-systeem (/voorbeeld) blijft ongemoeid. --}}
@verbatim
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@endverbatim
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Deze pagina staat buiten channels.layout en miste daardoor de robots-guard
     die daar op $site->isLive() zit: een concept-kanaal werd hier gewoon
     geïndexeerd. Zelfde regel als de layout, zodat draft = uit de zoekresultaten. --}}
<meta name="robots" content="{{ $site->isLive() ? 'index,follow,max-image-preview:large' : 'noindex,nofollow' }}">

{{-- Meten + consent. Deze pagina is het landingspunt van de advertenties en had
     als enige geen dataLayer en geen cookiebanner. Beide staan bewust vóór de
     rest van de <head>. --}}
@include('channels.partials.analytics-head')
<script src="{{ url('/cmp/loader.js') }}?tenant=channels&lang=nl" async></script>
@verbatim
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<title>Website laten maken voor ondernemers | betergeregeld</title>
<meta name="description" content="Website laten maken zonder gedoe. Typ je bedrijfsnaam, zie meteen een voorbeeld. Vaste prijs en een vaste contactpersoon. Overal in Nederland, telefonisch geregeld.">
<link rel="icon" href="/channel-media/bedrijfswebsite/logo.webp">
<style>
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body { font-family: 'Archivo', system-ui, sans-serif; background: #FAF9F7; color: #1A1A1A; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
  a { color: #12386B; text-decoration: none; }
  a:hover { color: #0C2A50; }
  ::selection { background: #1D5DA0; color: #fff; }
  input { font-family: inherit; }
  @keyframes bg-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
  @keyframes bg-shimmer { 0% { background-position: -200px 0; } 100% { background-position: 200px 0; } }
  @keyframes bg-fadeup { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
  @keyframes bg-pop { 0% { transform: scale(0.4); opacity: 0; } 60% { transform: scale(1.15); } 100% { transform: scale(1); opacity: 1; } }
  [data-reviewscroll]::-webkit-scrollbar { display: none; }
  .btn-primary:hover { background: #0C2A50 !important; }
  .lnk:hover { color: #12386B !important; }
  .flnk:hover { color: #fff !important; }
  .sg:hover { background: #F7F5F1 !important; }
  @media (max-width: 759px) { .nav-links { display: none !important; } }
  @media (max-width: 760px) {
    .m-stack2 { grid-template-columns: 1fr !important; }
    .m-nolift { transform: none !important; }
  }
  /* Smalle toestellen (±320px): de CTA-knoppen hebben inline white-space:nowrap
     en royale padding, waardoor hun min-content ~305px is. In een flexbox trekt
     die min-breedte de hele kolom mee, ook met width:100%, en dat duwde de pagina
     ~9px buiten beeld. nowrap en padding staan inline, dus die moeten hier met
     !important overruled worden. */
  @media (max-width: 380px) {
    #hero-btn, #slot-btn {
      width: 100%; min-width: 0;
      white-space: normal !important;
      padding: 0 14px !important;
    }
  }
  @media (prefers-reduced-motion: reduce) { .anim-float { animation: none !important; } }
</style>
</head>
<body>

<div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">

@endverbatim
  @include('channels._sales._bg-nav')
@verbatim

  <!-- ===================== HERO / GENERATOR ===================== -->
  <!-- H1 + intro staan statisch in de HTML (crawler-/AI-zichtbaar voor SEO/GEO);
       JS vult alleen het invoerveld (#gen-form) en de live preview (#gen-preview). -->
  <section id="top" style="padding: 40px 0 64px;">
    <div style="display: flex; flex-wrap: wrap; gap: 56px; align-items: center;">
      <div style="flex: 1 1 400px;">
        <h1 style="font-size: clamp(30px, 4.4vw, 46px); line-height: 1.08; letter-spacing: 0; font-weight: 800; margin: 0 0 14px;">Zie je nieuwe website. Binnen een minuut.</h1>
        <p style="font-size: 19px; line-height: 1.45; color: #4A4844; max-width: 42ch; margin: 0 0 24px;">Typ je bedrijfsnaam en zie meteen een voorbeeld van je eigen website. Ben je enthousiast, dan maken we hem samen af.</p>
        <div id="gen-form"></div>
      </div>
      <div class="anim-float" style="flex: 1 1 360px; animation: bg-float 6s ease-in-out infinite;">
        <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #8A8681; margin-bottom: 12px;">Zojuist gegenereerd</div>
        <div id="gen-preview"></div>
      </div>
    </div>
  </section>

  <!-- ===================== BEWIJSSTROOK ===================== -->
  <section style="padding: 24px calc(50vw - 50%) 56px; margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF;">
    <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #8A8681; margin-bottom: 22px;">Voorbeelden per branche</div>
    <div id="proof-cards" style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: thin;"></div>
  </section>

  <!-- ===================== HERKEN JE DIT ===================== -->
  <section style="padding: 72px 0;">
    <h2 style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; max-width: 16ch;">Waarom stel je die website steeds uit?</h2>
    <p style="font-size: 18px; color: #6B6864; margin: 0 0 48px; max-width: 40ch;">Dit horen we bijna elke week.</p>
    <div class="m-stack2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
      <div style="border-top: 2px solid #1A1A1A; padding-top: 20px;">
        <div style="font-size: 44px; font-weight: 900; color: #12386B; letter-spacing: -0.03em; margin-bottom: 10px;">3 jaar</div>
        <p style="font-size: 18px; line-height: 1.5; margin: 0; max-width: 32ch;">Je hebt al drie jaar "geen tijd" om die website eindelijk te regelen.</p>
      </div>
      <div style="border-top: 2px solid #1A1A1A; padding-top: 20px;">
        <div style="font-size: 44px; font-weight: 900; color: #12386B; letter-spacing: -0.03em; margin-bottom: 10px;">€3.500</div>
        <p style="font-size: 18px; line-height: 1.5; margin: 0; max-width: 32ch;">De vorige offerte van een bureau. Voor iets wat je niet begreep.</p>
      </div>
      <div style="border-top: 2px solid #1A1A1A; padding-top: 20px;">
        <div style="font-size: 44px; font-weight: 900; color: #12386B; letter-spacing: -0.03em; margin-bottom: 10px;">21:40</div>
        <p style="font-size: 18px; line-height: 1.5; margin: 0; max-width: 32ch;">'s Avonds nog offertes zitten typen die je overdag niet af kreeg.</p>
      </div>
      <div style="border-top: 2px solid #1A1A1A; padding-top: 20px;">
        <div style="font-size: 44px; font-weight: 900; color: #12386B; letter-spacing: -0.03em; margin-bottom: 10px;">0</div>
        <p style="font-size: 18px; line-height: 1.5; margin: 0; max-width: 32ch;">Reacties van de laatste "webbouwer" die je een appje stuurde.</p>
      </div>
    </div>
    <p style="font-size: 18px; line-height: 1.5; color: #4A4844; margin: 40px 0 0; max-width: 44ch;">Klinkt bekend? Dan is dit voor jou. Typ je bedrijfsnaam en je ziet meteen hoe je website eruit kan zien. De rest doen wij.</p>
    <blockquote style="margin: 40px 0 0; padding: 28px 32px; background: #F1EFEB; border-radius: 6px; border-left: 4px solid #12386B;">
      <p style="font-size: 22px; line-height: 1.4; font-weight: 600; margin: 0; max-width: 44ch;">"Ik ben vakman, geen websitebouwer. Ik wil gewoon dat het geregeld is."</p>
      <footer style="font-size: 15px; color: #8A8681; margin-top: 14px; font-weight: 600;">Marco, loodgieter uit Apeldoorn</footer>
    </blockquote>
  </section>

  <!-- ===================== GROEIDIAMANT ===================== -->
  <section data-diamond style="padding: 56px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #12386B; color: #FAF9F7; border-top: 1px solid #E5E3DF;">
    <div style="max-width: 46ch; margin-bottom: 28px;">
      <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #9DBCE4; margin-bottom: 12px;">De Groeidiamant</div>
      <h2 style="font-size: clamp(26px, 3.4vw, 38px); line-height: 1.08; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; color: #FAF9F7;">Je begint met een website. Daarna bouw je er alleen maar op door.</h2>
      <p style="font-size: 17px; color: #C9D6E8; margin: 0; line-height: 1.5;">Elke stap bouwt op de vorige. Klik op een stap om te zien wat 'ie oplevert.</p>
    </div>
    <div class="m-stack2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
      <div id="diamond-steps"></div>
      <div id="diamond-panel"></div>
    </div>
  </section>

  <!-- ===================== ZO WERKT HET ===================== -->
  <section id="werkwijze" style="padding: 72px 0; border-top: 1px solid #E5E3DF;">
    <h2 style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 48px;">Hoe werkt het? In drie stappen.</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px;">
      <div>
        <div style="font-size: 15px; font-weight: 800; color: #12386B; margin-bottom: 12px;">01</div>
        <h3 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 10px;">Binnen een minuut</h3>
        <p style="font-size: 17px; line-height: 1.5; color: #4A4844; margin: 0;">Typ je bedrijfsnaam en zie meteen een compleet concept van je eigen site.</p>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 800; color: #12386B; margin-bottom: 12px;">02</div>
        <h3 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 10px;">Eén telefoontje</h3>
        <p style="font-size: 17px; line-height: 1.5; color: #4A4844; margin: 0;">In tien minuten aan de telefoon zetten we je teksten, foto's en kleuren goed. Je hoeft nergens naartoe.</p>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 800; color: #12386B; margin-bottom: 12px;">03</div>
        <h3 style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 10px;">Live en vindbaar</h3>
        <p style="font-size: 17px; line-height: 1.5; color: #4A4844; margin: 0;">Wij zorgen dat je website online staat en gevonden wordt in jouw eigen plaats. Daarna word je gebeld door nieuwe klanten.</p>
      </div>
    </div>
  </section>

  <!-- ===================== WAAROM BETERGEREGELD ===================== -->
  <section style="padding: 72px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF;">
    <h2 style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 20px; max-width: 20ch;">Waarom kiezen ondernemers voor betergeregeld?</h2>
    <p style="font-size: 18px; line-height: 1.5; color: #4A4844; margin: 0 0 36px; max-width: 52ch;">Wij werken voor ondernemers in heel Nederland. Alles gaat telefonisch en online.</p>
    <div style="display: flex; gap: 28px; align-items: center; margin: 0 0 40px; flex-wrap: wrap;">
      <img src="/channel-media/bedrijfswebsite/joshua.png" alt="Joshua de Vos" style="width: 148px; height: 148px; border-radius: 50%; object-fit: cover; object-position: center 12%; background: #fff; flex-shrink: 0;">
      <div style="max-width: 46ch;">
        <p style="font-size: 20px; line-height: 1.5; color: #2E2C29; margin: 0 0 10px; font-weight: 600;">"Bel je? Dan neem ik zelf op."</p>
        <div style="font-size: 15px; color: #6B6864; font-weight: 700;">Joshua de Vos, eigenaar</div>
        {{-- Bellen én zelf plannen: niet iedereen belt liever, en /afspraak was
             tot nu toe nergens vanaf gelinkt. --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px 18px; margin-top: 12px;">
          <a href="tel:+31882545101" style="font-size: 16px; font-weight: 700; color: #12386B;">Bel 088 2545101</a>
          <span style="color: #C4C1BC;">of</span>
@endverbatim
          <a href="{{ $site->url('afspraak') }}" class="lnk" style="font-size: 16px; font-weight: 700; color: #12386B; text-decoration: underline; text-underline-offset: 3px;">plan zelf een gesprek &rarr;</a>
@verbatim
        </div>
      </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px;">
      <div>
        <h3 style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 8px; color: #1A1A1A;">Vaste prijs vooraf</h3>
        <p style="font-size: 16px; line-height: 1.5; color: #4A4844; margin: 0;">Je weet vooraf wat je betaalt, en dat bedrag verandert niet.</p>
      </div>
      <div>
        <h3 style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 8px; color: #1A1A1A;">Eén vast aanspreekpunt</h3>
        <p style="font-size: 16px; line-height: 1.5; color: #4A4844; margin: 0;">Je houdt dezelfde persoon, dus je hoeft je verhaal nooit opnieuw te doen.</p>
      </div>
      <div>
        <h3 style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 8px; color: #1A1A1A;">Betalen in twee delen</h3>
        <p style="font-size: 16px; line-height: 1.5; color: #4A4844; margin: 0;">De helft bij akkoord, de rest pas als je site live staat. Tot die tijd passen we aan tot het klopt.</p>
      </div>
    </div>
  </section>

  <!-- ===================== GOOGLE REVIEWS =====================
       Deze sectie presenteert zich als echte Google-reviews (Google-logo, "op
       Google", link naar het profiel). Ze rendert daarom ALLEEN als er echte
       reviews in `reviews` staan én `reviewStats` de echte score bevat. Staat
       een van beide leeg, dan verbergt renderReviews() de hele sectie. Vul
       nooit met verzonnen teksten: nepreviews zijn een oneerlijke
       handelspraktijk (ACM) en dit blok claimt expliciet dat ze van Google
       komen. -->
  <section data-reviews hidden style="padding: 64px 0; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; color: #1A1A1A; border-top: 1px solid #E5E3DF; overflow: hidden;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
      <h2 style="font-size: clamp(28px, 3.4vw, 40px); line-height: 1.08; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 20px; max-width: 22ch;">Wat andere ondernemers zeggen</h2>
      <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
        <svg width="22" height="22" viewBox="0 0 48 48" style="display:block">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
        </svg>
        <span style="font-size: 16px; font-weight: 700; color: #1A1A1A;">Google-reviews</span>
      </div>
      <div style="display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px 18px; margin-bottom: 36px;">
        <span id="rev-score" style="font-size: 52px; font-weight: 900; letter-spacing: -0.03em; line-height: 1;"></span>
        <span style="position: relative; display: inline-block; font-size: 26px; letter-spacing: 2px; line-height: 1;">
          <span style="color: #D8D5D0;">★★★★★</span>
          <span id="rev-stars" style="position: absolute; left: 0; top: 0; color: #FBBC04; overflow: hidden; width: 0; white-space: nowrap;">★★★★★</span>
        </span>
        <span id="rev-count" style="font-size: 17px; color: #6B6864; font-weight: 600;"></span>
        <a href="https://www.google.com/search?q=Betergeregeld+ICT" target="_blank" rel="noopener" style="font-size: 15px; font-weight: 800; color: #12386B; text-decoration: underline; text-underline-offset: 3px;">Bekijk op Google →</a>
      </div>
      <div data-reviewscroll id="reviewscroll" style="display: flex; gap: 0; overflow-x: auto; padding: 4px 0; cursor: grab; scrollbar-width: none;"></div>
    </div>
  </section>

  <!-- ===================== AUTOMATISERING (compact) ===================== -->
  <section id="automatisering" style="padding: 44px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #1A1A1A; color: #FAF9F7; border-top: 1px solid #E5E3DF;">
    <div style="max-width: 1120px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 20px 48px; align-items: center; justify-content: space-between;">
      <div style="flex: 1 1 400px;">
        <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #7FB0DE; margin-bottom: 10px;">Wil je later meer? Dat kan.</div>
        <h2 style="font-size: clamp(22px, 2.6vw, 30px); line-height: 1.12; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 8px;">Later ook je offertes en planning automatisch</h2>
        <p style="font-size: 16px; line-height: 1.5; color: #C9C6C1; margin: 0; max-width: 52ch;">Aanvraag binnen, offerte er automatisch uit, afspraak plant zichzelf in. Een stap voor als je zover bent, geen must.</p>
      </div>
      <div style="flex: 0 1 auto; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
        <span style="background: rgba(29,93,160,0.22); color: #DCE4F0; border: 1px solid rgba(127,176,222,0.35); border-radius: 999px; padding: 8px 16px; font-size: 15px; font-weight: 700; white-space: nowrap;">Aanvraag</span>
        <span style="color: #7FB0DE; font-weight: 800;">→</span>
        <span style="background: rgba(29,93,160,0.22); color: #DCE4F0; border: 1px solid rgba(127,176,222,0.35); border-radius: 999px; padding: 8px 16px; font-size: 15px; font-weight: 700; white-space: nowrap;">Offerte</span>
        <span style="color: #7FB0DE; font-weight: 800;">→</span>
        <span style="background: rgba(29,93,160,0.22); color: #DCE4F0; border: 1px solid rgba(127,176,222,0.35); border-radius: 999px; padding: 8px 16px; font-size: 15px; font-weight: 700; white-space: nowrap;">Akkoord</span>
        <span style="color: #7FB0DE; font-weight: 800;">→</span>
        <span style="background: rgba(29,93,160,0.22); color: #DCE4F0; border: 1px solid rgba(127,176,222,0.35); border-radius: 999px; padding: 8px 16px; font-size: 15px; font-weight: 700; white-space: nowrap;">Agenda</span>
      </div>
    </div>
  </section>

  <!-- ===================== PRIJZEN ===================== -->
  <section id="prijzen" style="padding: 72px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #F1EFEB; border-top: 1px solid #E5E3DF;">
    <h2 style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px;">Wat kost een website laten maken?</h2>
    <p style="font-size: 18px; color: #6B6864; margin: 0 0 32px;">Een website laten maken begint bij 799 euro eenmalig of 69 euro per maand. Je weet het bedrag vooraf, en het verandert niet. Kies wat past, je kunt altijd een stap opschuiven in de Groeidiamant.</p>
    <div id="price-toggle"></div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; align-items: stretch;">
      <div style="background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; padding: 32px; display: flex; flex-direction: column;">
        <div style="font-size: 15px; font-weight: 700; color: #8A8681; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 16px;">Start</div>
        <div id="price-start"></div>
        <div style="font-size: 14px; color: #8A8681; margin: 0 0 20px; line-height: 1.4;">Eén pagina. Voor vindbaarheid en offerteaanvragen kies je Groei.</div>
        <ul style="list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 12px;">
          <li style="font-size: 16px; color: #2E2C29;">Eén nette webpagina</li>
          <li style="font-size: 16px; color: #2E2C29;">Domein en hosting geregeld</li>
          <li style="font-size: 16px; color: #2E2C29;">Teksten en Google-profiel doen wij</li>
          <li style="font-size: 16px; color: #2E2C29;">Vindbaar in je eigen plaats</li>
          <li style="font-size: 16px; color: #2E2C29;">Live binnen een week</li>
        </ul>
        <a href="#top" class="js-tool" style="margin-top: auto; text-align: center; border: 1.5px solid #1A1A1A; color: #1A1A1A; padding: 14px; border-radius: 6px; font-weight: 700; font-size: 16px;">Bekijk mijn voorbeeld</a>
      </div>

      <div class="m-nolift" style="background: #F1F5FA; border: 2px solid #12386B; border-radius: 8px; padding: 36px 32px; display: flex; flex-direction: column; box-shadow: 0 22px 48px rgba(18,56,107,0.16); transform: translateY(-10px); position: relative; z-index: 1;">
        <div style="font-size: 15px; font-weight: 700; color: #12386B; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 16px;">Groei</div>
        <div id="price-groei"></div>
        <div style="font-size: 15px; font-weight: 700; color: #12386B; margin: 0 0 20px; line-height: 1.4;">Hiermee word je gevonden en komen er aanvragen binnen.</div>
        <ul style="list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 12px;">
          <li style="font-size: 16px; color: #2E2C29;">Alles uit Start</li>
          <li style="font-size: 16px; color: #2E2C29;">Aparte pagina per dienst</li>
          <li style="font-size: 16px; color: #2E2C29;">Lokale SEO en offerteformulier</li>
          <li style="font-size: 16px; color: #2E2C29;">Agenda waarin klanten zelf plannen</li>
          <li style="font-size: 16px; color: #2E2C29;">Live binnen twee tot drie weken</li>
        </ul>
        <a href="#top" class="btn-primary js-tool" style="margin-top: auto; text-align: center; background: #12386B; color: #fff; padding: 14px; border-radius: 6px; font-weight: 700; font-size: 16px;">Bekijk mijn voorbeeld</a>
      </div>

      <div style="background: #fff; border: 1.5px solid #E5E3DF; border-radius: 8px; padding: 32px; display: flex; flex-direction: column;">
        <div style="font-size: 15px; font-weight: 700; color: #8A8681; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 16px;">Compleet</div>
        <div id="price-compleet"></div>
        <ul style="list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 12px;">
          <li style="font-size: 16px; color: #2E2C29;">Alles uit Groei</li>
          <li style="font-size: 16px; color: #2E2C29;">Automatische offertes</li>
          <li style="font-size: 16px; color: #2E2C29;">Online akkoord, meteen in je agenda</li>
          <li style="font-size: 16px; color: #2E2C29;">Herinneringen en boekhoudkoppeling</li>
          <li style="font-size: 16px; color: #2E2C29;">Live binnen vier tot zes weken</li>
        </ul>
        <a href="#top" class="js-tool" style="margin-top: auto; text-align: center; border: 1.5px solid #1A1A1A; color: #1A1A1A; padding: 14px; border-radius: 6px; font-weight: 700; font-size: 16px;">Bekijk mijn voorbeeld</a>
      </div>
    </div>
    <p style="font-size: 16px; margin: 28px 0 0; color: #6B6864;"><span id="btw-text"></span> <a href="#top" style="font-weight: 700;">Bekijk je gratis voorbeeld →</a></p>
  </section>

  <!-- ===================== VEELGESTELDE VRAGEN ===================== -->
  <section id="faq" style="padding: 72px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #fff; border-top: 1px solid #E5E3DF;">
    <style>
      #faq details { border-top: 1px solid #E5E3DF; }
      #faq details:last-child { border-bottom: 1px solid #E5E3DF; }
      #faq summary { cursor: pointer; list-style: none; padding: 20px 0; font-size: 19px; font-weight: 700; color: #1A1A1A; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
      #faq summary::-webkit-details-marker { display: none; }
      #faq summary::after { content: '+'; font-size: 26px; font-weight: 400; color: #12386B; line-height: 1; flex-shrink: 0; }
      #faq details[open] summary::after { content: '\2212'; }
      #faq .faq-a { padding: 0 0 20px; font-size: 17px; line-height: 1.6; color: #4A4844; max-width: 62ch; margin: 0; }
    </style>
    <div style="max-width: 820px; margin: 0 auto; padding: 0 24px;">
      <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #1D5DA0; margin-bottom: 12px;">Veelgestelde vragen</div>
      <h2 style="font-size: clamp(30px, 4.4vw, 44px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 28px; color: #1A1A1A;">Antwoord op je vragen</h2>
      <details><summary>Wat kost het om een website te laten maken?</summary><p class="faq-a">Een website begint bij 799 euro eenmalig of 69 euro per maand. Je weet het bedrag vooraf en het verandert niet. Een complete website met een pagina per dienst en lokale vindbaarheid start bij 1.999 euro of 119 euro per maand.</p></details>
      <details><summary>Hoe snel staat mijn website online?</summary><p class="faq-a">Een eenpaginawebsite staat meestal binnen een week live. Een uitgebreidere website met een pagina per dienst staat binnen twee tot drie weken online.</p></details>
      <details><summary>Voor wie maakt betergeregeld websites?</summary><p class="faq-a">Voor mkb-ondernemers en vakmensen in heel Nederland, van dakdekkers en installateurs tot kappers, bakkers, garages en adviseurs.</p></details>
      <details><summary>Moet ik zelf teksten en foto's aanleveren?</summary><p class="faq-a">Nee. Wij schrijven de teksten en richten je Google-profiel in. In een telefoongesprek van tien minuten zetten we je teksten, foto's en kleuren samen goed.</p></details>
      <details><summary>Zit ik ergens aan vast?</summary><p class="faq-a">Het voorbeeld is gratis en vrijblijvend. Kies je voor de eenmalige prijs, dan betaal je de helft bij akkoord en de rest pas als je website live staat.</p></details>
      <details><summary>Werken jullie ook bij mij in de buurt?</summary><p class="faq-a">Ja, we werken door heel Nederland. Alles gaat telefonisch en online, dus je hoeft nergens naartoe.</p></details>
      <details><summary>Kan ik ook een webshop laten maken?</summary><p class="faq-a">Ja. Naast websites maken we ook webshops en klantenportalen. Je kunt je website later altijd uitbreiden.</p></details>
      <details><summary>Wat is de gratis voorbeeld-tool?</summary><p class="faq-a">Typ je bedrijfsnaam en je ziet binnen een minuut een compleet voorbeeld van je eigen website, zonder verplichtingen.</p></details>
    </div>
  </section>
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Wat kost het om een website te laten maken?","acceptedAnswer":{"@type":"Answer","text":"Een website begint bij 799 euro eenmalig of 69 euro per maand. Je weet het bedrag vooraf en het verandert niet. Een complete website met een pagina per dienst en lokale vindbaarheid start bij 1.999 euro of 119 euro per maand."}},{"@type":"Question","name":"Hoe snel staat mijn website online?","acceptedAnswer":{"@type":"Answer","text":"Een eenpaginawebsite staat meestal binnen een week live. Een uitgebreidere website met een pagina per dienst staat binnen twee tot drie weken online."}},{"@type":"Question","name":"Voor wie maakt betergeregeld websites?","acceptedAnswer":{"@type":"Answer","text":"Voor mkb-ondernemers en vakmensen in heel Nederland, van dakdekkers en installateurs tot kappers, bakkers, garages en adviseurs."}},{"@type":"Question","name":"Moet ik zelf teksten en foto's aanleveren?","acceptedAnswer":{"@type":"Answer","text":"Nee. Wij schrijven de teksten en richten je Google-profiel in. In een telefoongesprek van tien minuten zetten we je teksten, foto's en kleuren samen goed."}},{"@type":"Question","name":"Zit ik ergens aan vast?","acceptedAnswer":{"@type":"Answer","text":"Het voorbeeld is gratis en vrijblijvend. Kies je voor de eenmalige prijs, dan betaal je de helft bij akkoord en de rest pas als je website live staat."}},{"@type":"Question","name":"Werken jullie ook bij mij in de buurt?","acceptedAnswer":{"@type":"Answer","text":"Ja, we werken door heel Nederland. Alles gaat telefonisch en online, dus je hoeft nergens naartoe."}},{"@type":"Question","name":"Kan ik ook een webshop laten maken?","acceptedAnswer":{"@type":"Answer","text":"Ja. Naast websites maken we ook webshops en klantenportalen. Je kunt je website later altijd uitbreiden."}},{"@type":"Question","name":"Wat is de gratis voorbeeld-tool?","acceptedAnswer":{"@type":"Answer","text":"Typ je bedrijfsnaam en je ziet binnen een minuut een compleet voorbeeld van je eigen website, zonder verplichtingen."}}]}</script>

  <!-- ===================== SLOT-CTA ===================== -->
  <section style="padding: 60px calc(50vw - 50%); margin: 0 calc(50% - 50vw) -40px; width: 100vw; background: #FAF9F7; color: #1A1A1A; border-top: 1px solid #E5E3DF;">
    <div style="max-width: 1120px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 56px; align-items: center;">
      <div style="flex: 1 1 400px;">
        <h2 style="font-size: clamp(32px, 4.4vw, 50px); line-height: 1.03; letter-spacing: -0.02em; font-weight: 900; color: #1A1A1A; margin: 0 0 16px;">Je weet nu wat je krijgt. Typ je bedrijfsnaam.</h2>
        <p style="font-size: 18px; color: #6B6864; margin: 0 0 34px; max-width: 34ch; text-wrap: balance;">Het kost je niks en duurt een minuut. Daarna beslis jij.</p>
        <div style="position: relative; max-width: 600px;">
          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1 1 240px;">
              <input id="slot-input" placeholder="Typ je bedrijfsnaam…" style="width: 100%; height: 68px; padding: 0 20px; font-size: 18px; font-weight: 500; background: #fff; border: 1.5px solid #D8D5D0; border-radius: 6px; outline: none; color: #1A1A1A;">
              <div id="slot-suggest" style="display:none; position: absolute; top: 76px; left: 0; right: 0; background: #fff; border: 1px solid #E5E3DF; border-radius: 6px; box-shadow: 0 16px 40px rgba(0,0,0,0.28); overflow: hidden; z-index: 20; text-align: left;"></div>
            </div>
            <button id="slot-btn" class="btn-primary" style="height: 68px; padding: 0 34px; background: #12386B; color: #fff; border: none; border-radius: 6px; font-size: 18px; font-weight: 800; cursor: pointer; white-space: nowrap;">Bekijk mijn voorbeeld →</button>
          </div>
          <p style="font-size: 14px; color: #8A8681; margin: 18px 0 0; font-weight: 500;">Gratis. Je zit nergens aan vast.</p>
        </div>
      </div>
      <div data-slotcard style="flex: 1 1 380px;">
        <div id="slot-framed"></div>
        <div id="slot-meta" style="font-size: 15px; color: #4A4844; margin-top: 14px; font-weight: 700;"></div>
      </div>
    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
@endverbatim
  @include('channels._sales._bg-footer')
@verbatim

</div>
@endverbatim
<script>window.__bgTool = { url: @json($site->url('voorbeeld-maken')) };</script>
@verbatim
<script>
(function () {
  "use strict";
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var PHOTO = '/channel-media/bedrijfswebsite/preview/';
  var companies = [
    { name: 'Van der Meer Dakwerken', place: 'Zwolle', branche: 'Dakdekker', color: '#1F3A5F', headline: 'Uw dak weer in orde in Zwolle', sub: 'Plat en hellend dak, reparatie en volledige vervanging.', cta: 'Vraag een offerte', services: ['Dakbedekking', 'Lekkage verholpen', 'Zink en lood'], photos: [PHOTO + 'dakdekker-1.jpg', PHOTO + 'dakdekker-2.jpg', PHOTO + 'dakdekker-3.jpg'] },
    { name: 'Bakkerij ’t Stoepje', place: 'Groningen', branche: 'Bakkerij', color: '#A8521C', headline: 'Elke ochtend vers uit de oven in Groningen', sub: 'Ambachtelijk brood, banket en taarten op bestelling.', cta: 'Bekijk het assortiment', services: ['Vers brood', 'Taarten op maat', 'Koffie en gebak'], photos: [PHOTO + 'bakkerij-1.jpg', PHOTO + 'bakkerij-2.jpg', PHOTO + 'bakkerij-3.jpg'] },
    { name: 'Installatiebedrijf De Vries', place: 'Eindhoven', branche: 'Installateur', color: '#0E6E64', headline: 'Cv en water zonder gedoe in Eindhoven', sub: 'Onderhoud, storingen en complete installaties.', cta: 'Plan een monteur', services: ['CV-onderhoud', 'Storing verholpen', 'Badkamer'], photos: [PHOTO + 'installateur-1.jpg', PHOTO + 'installateur-2.jpg', PHOTO + 'installateur-3.jpg'] },
    { name: 'Hoveniersbedrijf Groenrijk', place: 'Breda', branche: 'Hovenier', color: '#35633F', headline: 'Een tuin om trots op te zijn in Breda', sub: 'Aanleg, onderhoud en bestrating, het hele jaar door.', cta: 'Vraag tuinadvies', services: ['Tuinaanleg', 'Onderhoud', 'Bestrating'], photos: [PHOTO + 'hovenier-1.jpg', PHOTO + 'hovenier-2.jpg', PHOTO + 'hovenier-3.jpg'] },
    { name: 'Autobedrijf Jansen', place: 'Rotterdam', branche: 'Garage', color: '#B0202E', headline: 'Uw auto veilig de weg op in Rotterdam', sub: 'Onderhoud, APK en reparatie van alle merken.', cta: 'Maak een APK-afspraak', services: ['APK-keuring', 'Onderhoudsbeurt', 'Banden'], photos: [PHOTO + 'garage-1.jpg', PHOTO + 'garage-2.jpg', PHOTO + 'garage-3.jpg'] },
    { name: 'Kapsalon Knip & Zo', place: 'Den Haag', branche: 'Kapper', color: '#5E2A50', headline: 'Goed geknipt de deur uit in Den Haag', sub: 'Knippen, kleuren en styling voor dames en heren.', cta: 'Boek online', services: ['Knippen', 'Kleuren', 'Styling'], photos: [PHOTO + 'kapper-1.jpg', PHOTO + 'kapper-2.jpg', PHOTO + 'kapper-3.jpg'] }
  ];

  var diamondDefs = [
    { n: 1, title: 'Je website', body: 'De basis. Een strakke, snelle site met je diensten, je werk en je contactgegevens. Vindbaar en van jou.', example: 'Van der Meer Dakwerken staat sinds dag één online en werd de eerste week al drie keer gebeld via de site.' },
    { n: 2, title: 'Vindbaarheid', body: 'We zetten je bovenaan in je eigen regio. Mensen die "dakdekker Zwolle" zoeken, vinden jou, niet je concurrent.', example: 'Binnen zes weken van pagina 3 naar de top-3 voor "dakdekker Zwolle". Zonder één advertentie.' },
    { n: 3, title: 'Reviews & reputatie', body: 'Na elke klus vragen we automatisch om een review. Je reputatie groeit vanzelf mee, zichtbaar op je site en op Google.', example: 'Van 4 naar 87 Google-reviews in een jaar. Nieuwe klanten bellen nu met "ik zag je goede beoordelingen".' },
    { n: 4, title: 'Automatisering', body: 'Offertes, planning en herinneringen lopen automatisch. De klant vraagt aan, jij drukt op akkoord. Geen avonduren meer.', example: 'Marco bespaart naar eigen zeggen zo’n zes uur per week aan administratie. Dat is een hele werkdag terug.' },
    { n: 5, title: 'Groei & schaal', body: 'Meer aanvragen dan je aankunt? We helpen je opschalen: extra mensen inwerken, meerdere vestigingen, nieuwe diensten.', example: 'Van der Meer ging van twee naar vijf man en opende een tweede standplaats. De site groeide gewoon mee.' }
  ];

  // ECHTE Google-reviews, overgenomen uit het Google Business Profile.
  // Leeg = de reviewsectie rendert niet. Dat is bewust: het blok toont een
  // Google-logo en zegt "op Google" onder elke naam, dus alles hierin wordt
  // gepresenteerd als een echte klantbeoordeling.
  //   { name: 'Voornaam A.', branche: 'Dakdekker', place: 'Zwolle', rating: 5, text: '...' }
  // `name`/`branche`/`place` zijn vrij; `text` moet letterlijk de review zijn.
  var reviews = [];

  // Gemiddelde score en het totale aantal reviews op Google. Vul allebei met de
  // echte cijfers van het profiel; laat op null zolang die er niet zijn.
  var reviewStats = { score: null, count: null };

  // PRIJZEN: eenmalige bedragen staan vast. Maandbedragen conform design.
  var pricing = {
    start:    { once: '€799',   onceMonthly: '€39', monthly: '€69' },
    groei:    { once: '€1.999', onceMonthly: '€49', monthly: '€119' },
    compleet: { once: '€3.999', onceMonthly: '€89', monthly: '€199' }
  };

  var state = { view: 'A', query: '', selected: null, diamondStep: 1, slotIdx: 0, autoStep: 0, autoShowAll: false, priceMode: 'monthly' };
  var timers = {};

  function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
  function $(id) { return document.getElementById(id); }
  function isMobile() { return window.innerWidth < 760; }

  // ---------- doorverwijzing naar de echte voorbeeld-tool (/voorbeeld-maken) ----------
  // De ingetypte bedrijfsnaam gaat mee als ?bedrijf=, waarmee het company-veld
  // in de tool automatisch voorgevuld wordt.
  function toolUrl(name) {
    var u = (window.__bgTool && window.__bgTool.url) || 'voorbeeld-maken';
    name = (name || '').trim();
    return name ? u + (u.indexOf('?') < 0 ? '?' : '&') + 'bedrijf=' + encodeURIComponent(name) : u;
  }
  function goToTool(name) { window.location.href = toolUrl(name); }
  // Statische CTA-links (.js-tool) verwijzen standaard naar de tool zonder prefill.
  function wireStaticCtas() {
    var links = document.querySelectorAll('a.js-tool');
    for (var i = 0; i < links.length; i++) links[i].setAttribute('href', toolUrl(''));
  }

  function shade(hex, amt) {
    var n = parseInt(hex.slice(1), 16);
    var r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
    var t = amt < 0 ? 0 : 255, p = Math.abs(amt);
    r = Math.round(r + (t - r) * p); g = Math.round(g + (t - g) * p); b = Math.round(b + (t - b) * p);
    return 'rgb(' + r + ',' + g + ',' + b + ')';
  }
  function initials(name) { return name.split(' ').filter(Boolean).map(function (w) { return w[0]; }).slice(0, 2).join('').toUpperCase(); }
  function slug(name) { return 'www.' + name.toLowerCase().replace(/[^a-z0-9]+/g, '') + '.nl'; }
  function starRow(n, size) {
    var out = '<span style="color:#FBBC04;font-size:' + size + ';letter-spacing:1px;white-space:nowrap">';
    for (var i = 0; i < 5; i++) out += '<span style="opacity:' + (i < n ? 1 : 0.25) + '">★</span>';
    return out + '</span>';
  }

  function matches() {
    var q = state.query.trim().toLowerCase();
    if (q.length < 2) return [];
    return companies.filter(function (c) { return c.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 5);
  }
  function chosen() { return state.selected || matches()[0] || companies[0]; }

  // ---------- preview + frames ----------
  function sitePreview(company, reveal, building) {
    var col = company.color;
    function skel(h) { return '<div style="height:' + h + ';border-radius:4px;background:linear-gradient(90deg,#ECEAE6 25%,#F5F3EF 50%,#ECEAE6 75%);background-size:400px 100%;animation:bg-shimmer 1.1s linear infinite"></div>'; }
    function wrap(idx, node, skelH) {
      if (reveal >= idx) return '<div style="animation:bg-fadeup 0.4s ease">' + node + '</div>';
      if (building) return '<div>' + skel(skelH) + '</div>';
      return '';
    }
    var header = '<div style="display:flex;align-items:center;justify-content:space-between;padding:11px 16px;background:' + col + ';color:#fff">'
      + '<div style="display:flex;align-items:center;gap:8px">'
      + '<div style="width:22px;height:22px;border-radius:4px;background:' + shade(col, -0.2) + ';display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800">' + esc(initials(company.name)) + '</div>'
      + '<div style="font-size:12px;font-weight:700">' + esc(company.name) + '</div></div>'
      + '<div style="display:flex;gap:12px;font-size:10px;opacity:0.85"><span>Diensten</span><span>Werk</span><span>Contact</span></div></div>';
    // Echte vak-foto met subtiele merk-tint; valt terug op een effen kleurvlak als de foto niet laadt.
    function photoImg(src, h, radius) {
      if (!src) return '<div style="height:' + h + ';border-radius:' + radius + ';background:' + col + '"></div>';
      return '<div style="position:relative;height:' + h + ';border-radius:' + radius + ';overflow:hidden;background:' + col + '">'
        + '<img src="' + src + '" alt="" loading="lazy" onerror="this.style.display=\'none\'" style="width:100%;height:100%;object-fit:cover;display:block">'
        + '<div style="position:absolute;inset:0;background:' + col + ';opacity:0.14;mix-blend-mode:multiply;pointer-events:none"></div></div>';
    }
    var photos = company.photos || [];
    var hero = '<div style="padding:20px 16px;display:grid;grid-template-columns:1.1fr 0.9fr;gap:14px;align-items:center">'
      + '<div><div style="font-size:17px;font-weight:800;line-height:1.12;letter-spacing:-0.02em">' + esc(company.headline) + '</div>'
      + '<div style="font-size:10px;color:#6B6864;margin:7px 0 12px;line-height:1.4">' + esc(company.sub) + '</div>'
      + '<div style="display:inline-block;background:' + col + ';color:#fff;font-size:10px;font-weight:700;padding:7px 12px;border-radius:4px">' + esc(company.cta) + '</div></div>'
      + photoImg(photos[0], '78px', '5px') + '</div>';
    var services = '<div style="padding:0 16px 4px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">'
      + company.services.map(function (label, i) {
          return '<div style="border:1px solid #E5E3DF;border-radius:5px;overflow:hidden">'
            + photoImg(photos[i], '46px', '0')
            + '<div style="padding:8px 10px 10px;font-size:10px;font-weight:800;color:#1A1A1A;letter-spacing:-0.01em;line-height:1.15">' + esc(label) + '</div></div>';
        }).join('') + '</div>';
    var gallery = '<div style="padding:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">'
      + [0, 1, 2].map(function (i) { return photoImg(photos[i], '54px', '4px'); }).join('') + '</div>';
    var contact = '<div style="padding:13px 16px;background:#1A1A1A;color:#fff;display:flex;justify-content:space-between;align-items:center">'
      + '<div style="font-size:11px;font-weight:700">Bel voor een afspraak</div>'
      + '<div style="font-size:11px;font-weight:700;color:' + shade(col, 0.45) + '">085 - 123 4567</div></div>';
    return '<div style="background:#fff">'
      + wrap(1, header, '44px') + wrap(2, hero, '118px') + wrap(3, services, '70px') + wrap(4, gallery, '80px') + wrap(5, contact, '40px') + '</div>';
  }
  function browserFrame(inner, url) {
    return '<div style="border-radius:8px;overflow:hidden;border:1px solid #E5E3DF;box-shadow:0 20px 50px rgba(26,26,26,0.13);background:#fff">'
      + '<div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#F1EFEB;border-bottom:1px solid #E5E3DF">'
      + '<div style="display:flex;gap:6px">' + ['#E06C5B', '#E6B84F', '#68B15A'].map(function (c) { return '<div style="width:10px;height:10px;border-radius:50%;background:' + c + '"></div>'; }).join('') + '</div>'
      + '<div style="flex:1;background:#fff;border:1px solid #E5E3DF;border-radius:5px;padding:4px 12px;font-size:11px;color:#8A8681;font-weight:600">' + esc(url) + '</div></div>'
      + inner + '</div>';
  }
  function phoneFrame(inner) {
    return '<div style="max-width:320px;width:100%;margin:0 auto;border-radius:34px;border:10px solid #1A1A1A;overflow:hidden;box-shadow:0 22px 50px rgba(26,26,26,0.22);background:#1A1A1A">'
      + '<div style="background:#1A1A1A;height:26px;display:flex;justify-content:center;align-items:center"><div style="width:90px;height:6px;border-radius:4px;background:#3a3a3a"></div></div>'
      + '<div style="border-radius:2px;overflow:hidden">' + inner + '</div></div>';
  }

  // ---------- autocomplete ----------
  var currentMatches = [];
  function suggestHTML(list, immediate) {
    currentMatches = list;
    return list.map(function (m, i) {
      return '<div class="sg" data-pick="' + i + '" data-imm="' + (immediate ? 1 : 0) + '" style="padding:13px 16px;cursor:pointer;' + (i < list.length - 1 ? 'border-bottom:1px solid #F1EFEB;' : '') + 'display:flex;justify-content:space-between;align-items:center;gap:12px">'
        + '<div><div style="font-size:16px;font-weight:700">' + esc(m.name) + '</div><div style="font-size:13px;color:#8A8681;margin-top:2px">' + esc(m.branche) + ' · ' + esc(m.place) + '</div></div>'
        + '<div style="font-size:12px;color:#12386B;font-weight:700;white-space:nowrap">Kies →</div></div>';
    }).join('');
  }
  function refreshSuggests() {
    var list = matches();
    var heroS = $('hero-suggest');
    if (heroS) {
      if (list.length) { heroS.innerHTML = suggestHTML(list, false); heroS.style.display = 'block'; }
      else heroS.style.display = 'none';
    }
    var slotS = $('slot-suggest');
    if (slotS) {
      if (list.length) { slotS.innerHTML = suggestHTML(list, true); slotS.style.display = 'block'; }
      else slotS.style.display = 'none';
    }
  }
  function onInput(v, from) {
    state.query = v; state.selected = null;
    var hi = $('hero-input'), si = $('slot-input');
    if (from !== 'hero' && hi) hi.value = v;
    if (from !== 'slot' && si) si.value = v;
    refreshSuggests();
  }

  // ---------- hero states ----------
  function renderHeroA() {
    var c0 = companies[0];
    $('gen-form').innerHTML = '<div style="position:relative;max-width:520px"><div style="display:flex;gap:10px;flex-wrap:wrap">'
      + '<div style="position:relative;flex:1 1 240px">'
      + '<input id="hero-input" value="' + esc(state.query) + '" placeholder="Typ je bedrijfsnaam…" style="width:100%;height:60px;padding:0 18px;font-size:17px;font-weight:500;background:#fff;border:1.5px solid #D8D5D0;border-radius:6px;outline:none;color:#1A1A1A">'
      + '<div id="hero-suggest" style="display:none;position:absolute;top:68px;left:0;right:0;background:#fff;border:1.5px solid #E5E3DF;border-radius:6px;box-shadow:0 12px 34px rgba(26,26,26,0.12);overflow:hidden;z-index:20"></div></div>'
      + '<button id="hero-btn" class="btn-primary" style="height:60px;padding:0 30px;background:#12386B;color:#fff;border:none;border-radius:6px;font-size:17px;font-weight:700;cursor:pointer;white-space:nowrap">Bekijk mijn voorbeeld →</button>'
      + '</div><p style="font-size:13px;color:#8A8681;margin:12px 2px 0;font-weight:500">Gratis. Je zit nergens aan vast.</p></div>';
    $('gen-preview').innerHTML = browserFrame(sitePreview(c0, 5, false), slug(c0.name));
    var hi = $('hero-input');
    hi.addEventListener('input', function () { onInput(this.value, 'hero'); });
    hi.addEventListener('focus', function () { refreshSuggests(); });
    hi.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); goToTool(state.query); } });
    $('hero-btn').addEventListener('click', function () { goToTool(state.query); });
  }

  // pick clicks (delegated, werkt voor hero- en slot-dropdown)
  document.addEventListener('click', function (e) {
    var el = e.target.closest ? e.target.closest('.sg') : null;
    if (!el) return;
    var idx = parseInt(el.getAttribute('data-pick'), 10);
    var imm = el.getAttribute('data-imm') === '1';
    var c = currentMatches[idx];
    if (!c) return;
    var hs = $('hero-suggest'); if (hs) hs.style.display = 'none';
    var ss = $('slot-suggest'); if (ss) ss.style.display = 'none';
    state.selected = c; state.query = c.name;
    var hi = $('hero-input'); if (hi) hi.value = c.name;
    var si = $('slot-input'); if (si) si.value = c.name;
  });
  document.addEventListener('click', function (e) {
    // sluit dropdowns bij klik buiten
    if (e.target.closest && (e.target.closest('#hero-suggest') || e.target.closest('#slot-suggest') || e.target.id === 'hero-input' || e.target.id === 'slot-input')) return;
    var hs = $('hero-suggest'); if (hs) hs.style.display = 'none';
    var ss = $('slot-suggest'); if (ss) ss.style.display = 'none';
  });

  // ---------- proof cards ----------
  function renderProof() {
    $('proof-cards').innerHTML = companies.map(function (c) {
      return '<div style="flex:0 0 auto;width:230px;background:#fff;border:1.5px solid #E5E3DF;border-radius:8px;overflow:hidden">'
        + '<div style="padding:10px 12px;background:' + c.color + ';color:#fff;display:flex;align-items:center;gap:8px">'
        + '<div style="width:20px;height:20px;border-radius:4px;background:' + shade(c.color, -0.2) + ';display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800">' + esc(initials(c.name)) + '</div>'
        + '<div style="font-size:11px;font-weight:700">' + esc(c.name) + '</div></div>'
        + '<div style="position:relative;height:92px;background:' + c.color + ';overflow:hidden">'
        + ((c.photos && c.photos[0]) ? '<img src="' + c.photos[0] + '" alt="" loading="lazy" onerror="this.style.display=\'none\'" style="width:100%;height:100%;object-fit:cover;display:block"><div style="position:absolute;inset:0;background:' + c.color + ';opacity:0.14;mix-blend-mode:multiply"></div>' : '')
        + '</div>'
        + '<div style="padding:14px 14px 16px"><div style="font-size:16px;font-weight:800;letter-spacing:-0.01em">' + esc(c.name) + '</div>'
        + '<div style="font-size:13px;color:#8A8681;margin-top:3px;font-weight:600">' + esc(c.branche) + ' · ' + esc(c.place) + '</div></div></div>';
    }).join('');
  }

  // ---------- reviews scroller ----------
  function renderReviews() {
    var section = document.querySelector('[data-reviews]');
    if (!section) return;
    // Geen echte reviews of geen echte score: sectie blijft weg. Zo kan er nooit
    // een half gevuld "Google-reviews"-blok live staan.
    var ready = reviews.length > 0 && reviewStats && reviewStats.score != null && reviewStats.count != null;
    if (!ready) { section.hidden = true; return; }
    section.hidden = false;

    $('rev-score').textContent = String(reviewStats.score).replace('.', ',');
    $('rev-count').textContent = reviewStats.count + (reviewStats.count === 1 ? ' review' : ' reviews');
    $('rev-stars').style.width = Math.max(0, Math.min(100, (reviewStats.score / 5) * 100)) + '%';

    var el = $('reviewscroll');
    el.innerHTML = reviews.concat(reviews).map(function (r, i) {
      return '<div style="flex:0 0 300px;padding:' + (i === 0 ? '6px 28px 6px 0' : '6px 28px') + ';border-left:' + (i === 0 ? 'none' : '1px solid #E5E3DF') + ';display:flex;flex-direction:column;gap:12px">'
        + starRow(r.rating, '15px')
        + '<p style="font-size:15px;line-height:1.55;color:#2E2C29;margin:0;display:-webkit-box;-webkit-line-clamp:6;-webkit-box-orient:vertical;overflow:hidden">' + esc(r.text) + '</p>'
        + '<div style="margin-top:auto;padding-top:4px;display:flex;align-items:center;gap:10px">'
        + '<div style="width:34px;height:34px;border-radius:50%;flex-shrink:0;background:#E8EAED;color:#3C4043;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px">' + esc((r.name || '?').trim().charAt(0).toUpperCase()) + '</div>'
        + '<div><div style="font-size:15px;font-weight:800;color:#1A1A1A">' + esc(r.name) + '</div>'
        + '<div style="font-size:13px;color:#8A8681;font-weight:600">' + esc(r.branche) + ' · ' + esc(r.place) + ' · op Google</div></div></div></div>';
    }).join('');
    setupReviewScroll(el);
  }
  function setupReviewScroll(el) {
    var down = false, startX = 0, startL = 0, paused = false, inView = true;
    el.scrollLeft = 0;
    el.addEventListener('mouseenter', function () { paused = true; });
    el.addEventListener('mouseleave', function () { paused = false; });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { es.forEach(function (e) { inView = e.isIntersecting; }); }, { threshold: 0.05 }).observe(el);
    el.addEventListener('pointerdown', function (e) { down = true; startX = e.clientX; startL = el.scrollLeft; paused = true; try { el.setPointerCapture(e.pointerId); } catch (x) {} el.style.cursor = 'grabbing'; });
    el.addEventListener('pointermove', function (e) { if (down) el.scrollLeft = startL - (e.clientX - startX); });
    el.addEventListener('pointerup', function () { down = false; paused = false; el.style.cursor = 'grab'; });
    if (reduceMotion) return;
    clearInterval(timers.rev);
    timers.rev = setInterval(function () {
      if (!paused && !down && inView !== false) { el.scrollLeft += 0.5; var half = el.scrollWidth / 2; if (el.scrollLeft >= half) el.scrollLeft -= half; }
    }, 16);
  }

  // ---------- groeidiamant ----------
  function renderDiamond() {
    var steps = diamondDefs.slice().reverse().map(function (d) {
      var filled = d.n <= state.diamondStep;
      var width = 40 + (d.n - 1) * 13;
      return '<div class="dstep" data-n="' + d.n + '" style="width:' + width + '%;background:' + (filled ? '#1D5DA0' : 'rgba(255,255,255,0.10)') + ';color:' + (filled ? '#fff' : '#C9D6E8') + ';padding:15px 18px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:background-color 0.35s ease,transform 0.2s ease,color 0.3s ease;border-top:1px solid rgba(11,42,80,0.5)">'
        + '<div style="width:26px;height:26px;background:' + (filled ? 'rgba(255,255,255,0.22)' : 'rgba(255,255,255,0.12)') + ';display:flex;align-items:center;justify-content:center;font-weight:900;font-size:14px;flex-shrink:0">' + d.n + '</div>'
        + '<div style="font-size:16px;font-weight:800;letter-spacing:-0.01em">' + esc(d.title) + '</div></div>';
    }).join('');
    $('diamond-steps').innerHTML = '<div style="display:flex;flex-direction:column">' + steps + '</div>';
    var dm = diamondDefs[state.diamondStep - 1];
    $('diamond-panel').innerHTML = '<div style="animation:' + (reduceMotion ? 'none' : 'bg-fadeup 0.4s ease') + '">'
      + '<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">'
      + '<div style="width:40px;height:40px;border-radius:6px;background:#1D5DA0;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:19px;flex-shrink:0">' + dm.n + '</div>'
      + '<h3 style="font-size:26px;font-weight:800;letter-spacing:-0.02em;margin:0;color:#fff">' + esc(dm.title) + '</h3></div>'
      + '<p style="font-size:18px;line-height:1.55;color:#DCE4F0;margin:0 0 22px">' + esc(dm.body) + '</p>'
      + '<div style="font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#9DBCE4;margin-bottom:6px">Voorbeeld uit de praktijk</div>'
      + '<p style="font-size:16px;line-height:1.5;margin:0;color:#B9C6DE">' + esc(dm.example) + '</p></div>';
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest ? e.target.closest('.dstep') : null;
    if (!el) return;
    state.diamondStep = parseInt(el.getAttribute('data-n'), 10);
    renderDiamond();
  });
  document.addEventListener('mouseover', function (e) {
    var el = e.target.closest ? e.target.closest('.dstep') : null;
    if (el) el.style.transform = 'translateX(8px)';
  });
  document.addEventListener('mouseout', function (e) {
    var el = e.target.closest ? e.target.closest('.dstep') : null;
    if (el) el.style.transform = 'none';
  });
  function setupDiamond() {
    var el = document.querySelector('[data-diamond]');
    if (!el || reduceMotion || !('IntersectionObserver' in window)) return;
    var played = false;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting && !played) {
          played = true;
          [1, 2, 3, 4, 5].forEach(function (n, i) { setTimeout(function () { state.diamondStep = n; renderDiamond(); }, i * 420); });
          setTimeout(function () { state.diamondStep = 1; renderDiamond(); }, 5 * 420 + 800);
          io.disconnect();
        }
      });
    }, { threshold: 0.4 });
    io.observe(el);
  }

  // ---------- prijzen ----------
  function renderPriceEl(key) {
    var p = pricing[key], monthly = state.priceMode === 'monthly';
    var big = 'font-size:40px;font-weight:900;letter-spacing:-0.03em;margin-bottom:6px';
    var body = monthly
      ? '<div style="' + big + '">' + p.monthly + '<span style="font-size:18px;font-weight:700;letter-spacing:0"> /mnd</span></div><div style="font-size:14px;color:#6B6864;margin-bottom:24px">Looptijd 24 maanden</div>'
      : '<div style="' + big + '">' + p.once + '</div><div style="font-size:14px;color:#6B6864;line-height:1.45;margin-bottom:24px">Eenmalig. Daarna ' + p.onceMonthly + ' per maand voor hosting en onderhoud, doorlopend.</div>';
    return '<div style="animation:' + (reduceMotion ? 'none' : 'bg-fadeup 0.35s ease') + '">' + body + '</div>';
  }
  function renderPrices() {
    var m = state.priceMode;
    function opt(mode, label) {
      return '<button class="ptog" data-mode="' + mode + '" style="background:none;border:none;cursor:pointer;font:inherit;padding:8px 2px;margin-right:28px;font-size:16px;font-weight:800;letter-spacing:-0.01em;color:' + (m === mode ? '#1A1A1A' : '#8A8681') + ';border-bottom:' + (m === mode ? '3px solid #12386B' : '3px solid transparent') + '">' + label + '</button>';
    }
    $('price-toggle').innerHTML = '<div style="display:flex;margin-bottom:28px">' + opt('once', 'In één keer') + opt('monthly', 'Per maand') + '</div>';
    $('price-start').innerHTML = renderPriceEl('start');
    $('price-groei').innerHTML = renderPriceEl('groei');
    $('price-compleet').innerHTML = renderPriceEl('compleet');
    $('btw-text').textContent = m === 'monthly'
      ? 'Alle bedragen zijn exclusief btw. Geen aanbetaling, je betaalt per maand.'
      : 'Alle bedragen zijn exclusief btw. De helft betaal je bij akkoord, de rest als je site live staat.';
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest ? e.target.closest('.ptog') : null;
    if (!el) return;
    state.priceMode = el.getAttribute('data-mode');
    renderPrices();
  });

  // ---------- slot carrousel ----------
  function renderSlot() {
    var sc = companies[state.slotIdx];
    $('slot-framed').innerHTML = '<div style="animation:' + (reduceMotion ? 'none' : 'bg-fadeup 0.6s ease') + '">' + browserFrame(sitePreview(sc, 5, false), slug(sc.name)) + '</div>';
    $('slot-meta').textContent = sc.name + ' · ' + sc.place;
  }
  function setupSlot() {
    var card = document.querySelector('[data-slotcard]');
    var paused = false, inView = true;
    if (card) {
      card.addEventListener('mouseenter', function () { paused = true; });
      card.addEventListener('mouseleave', function () { paused = false; });
      if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { es.forEach(function (e) { inView = e.isIntersecting; }); }, { threshold: 0.2 }).observe(card);
    }
    var si = $('slot-input');
    if (si) {
      si.addEventListener('input', function () { onInput(this.value, 'slot'); });
      si.addEventListener('focus', function () { refreshSuggests(); });
      si.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); goToTool(state.query); } });
    }
    var sb = $('slot-btn');
    if (sb) sb.addEventListener('click', function () { goToTool(state.query); });
    if (reduceMotion) return;
    clearInterval(timers.slot);
    timers.slot = setInterval(function () {
      if (paused || inView === false) return;
      state.slotIdx = (state.slotIdx + 1) % companies.length;
      renderSlot();
    }, 3500);
  }

  // ---------- init ----------
  wireStaticCtas();
  renderHeroA();
  renderProof();
  renderReviews();
  renderDiamond();
  renderPrices();
  renderSlot();
  setupDiamond();
  setupSlot();
})();
</script>

</body>
</html>
@endverbatim
