@php
    /** @var \App\Support\ChannelSite $site */
    $name   = $site->name();
    $phone  = $site->brand('phone');
    $email  = $site->brand('email');
    $action = $site->url('contact');
    $endorse = $site->endorsement();
    $endorseUrl = $site->endorsementUrl();
    $tel = $phone ? preg_replace('/\s+/', '', $phone) : '';
    // ASSET-PLACEHOLDERS — vervangen door echte content (zie asset-lijst).
    $faq = $site->faq() ?? [
        ['Wat kost een website?', 'Een complete website voor een installatiebedrijf start vanaf een vast, laag maandbedrag, inclusief hosting, onderhoud en updates. Je krijgt vooraf een heldere prijs, geen verrassingen achteraf.'],
        ['Hoe snel sta ik online?', 'Binnen 2 werkdagen zie je een gratis voorbeeld. Na je akkoord staat je site doorgaans binnen 2 weken live en vindbaar in Google.'],
        ['Moet ik zelf iets met techniek doen?', 'Nee. Wij regelen techniek, hosting, e-mail en de vindbaarheid. Jij levert wat foto\'s en info aan, de rest doen wij.'],
        ['Zit ik vast aan een lang contract?', 'Nee. Geen meerjarencontract en geen kleine lettertjes. Je betaalt een vast bedrag per maand en kunt maandelijks opzeggen.'],
        ['Ik heb al een (oude) website. Kan dat beter?', 'Ja. We kijken naar je huidige site en maken een voorbeeld van hoe het scherper en beter vindbaar kan, zonder dat je opnieuw hoeft te beginnen.'],
        ['Werken jullie ook in mijn regio?', 'We bouwen websites voor installateurs door heel Nederland. Je werkgebied stemmen we precies af, zodat je lokaal goed gevonden wordt.'],
    ];
    $regio = ['Amersfoort','Utrecht','Hilversum','Bussum','Amsterdam','Zeist','Soest','Almere'];
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $site->homeTitle() }}</title>
<meta name="description" content="{{ $site->homeDescription() }}">
<meta name="robots" content="noindex,nofollow">
{{-- OG / social share (pro-detail; og:image = placeholder tot er een echt beeld is) --}}
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $site->homeTitle() }}">
<meta property="og:description" content="{{ $site->homeDescription() }}">
<meta property="og:image" content="{{ $site->url('og-image.jpg') }}">
<meta name="twitter:card" content="summary_large_image">
{{-- JSON-LD: bedrijf + FAQ voor rich results in Google --}}
{!! $site->jsonLd() !!}
@php $faqLd = ['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($qa)=>['@type'=>'Question','name'=>$qa[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$qa[1]]], $faq)]; @endphp
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
{{-- favicon: inline SVG-mark --}}
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23163a52'/%3E%3Cpath d='M16 7c-3 4-5 6.5-5 9a5 5 0 0 0 10 0c0-2.5-2-5-5-9z' fill='%23ef6033'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
@verbatim
<style>
:root{
  --ink:#1c1a17; --primary:#163a52; --primary-2:#0f2a3d;
  --accent:#ef6033; --accent-2:#d94e22; --accent-soft:#fdeee6;
  --bg:#fbf6ef; --surface:#ffffff; --muted:#6f665b; --line:#ece2d4; --line-2:#ddd0bd;
  --gold:#f4b740;
  --display:'Bricolage Grotesque',system-ui,sans-serif;
  --body:'Hanken Grotesk',system-ui,-apple-system,sans-serif;
  --r:16px; --r-sm:10px;
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.6;-webkit-font-smoothing:antialiased}
/* craft: subtiele grain over de hele pagina */
body::after{content:"";position:fixed;inset:0;z-index:1;pointer-events:none;opacity:.035;mix-blend-mode:multiply;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
.wrap{width:100%;max-width:1140px;margin-inline:auto;padding-inline:22px;position:relative;z-index:2}
a{color:inherit;text-decoration:none}
h1,h2,h3{margin:0;font-family:var(--display);font-weight:600;letter-spacing:-.02em;line-height:1.04}
p{margin:0}

.btn{display:inline-flex;align-items:center;gap:.55rem;font-family:var(--body);font-weight:700;font-size:1rem;padding:.95rem 1.5rem;border-radius:999px;border:2px solid transparent;cursor:pointer;transition:transform .14s ease,box-shadow .14s ease,background .14s ease}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 8px 20px rgba(239,96,51,.28)}
.btn-primary:hover{background:var(--accent-2);transform:translateY(-2px);box-shadow:0 12px 28px rgba(239,96,51,.36)}
.btn-call{background:#fff;color:var(--primary);border-color:var(--line-2)}
.btn-call:hover{border-color:var(--primary);transform:translateY(-2px)}
.btn svg{width:18px;height:18px}

/* logo-mark */
.brand{display:inline-flex;align-items:center;gap:.55rem;font-family:var(--display);font-weight:700;font-size:1.3rem;letter-spacing:-.03em}
.brand .mark{width:30px;height:30px;flex:none;border-radius:9px;background:var(--primary);display:grid;place-items:center}
.brand .mark svg{width:17px;height:17px}
.brand span{color:var(--accent)}

header{position:sticky;top:0;z-index:30;background:rgba(251,246,239,.9);backdrop-filter:blur(8px);border-bottom:1px solid var(--line)}
.nav{display:flex;align-items:center;justify-content:space-between;height:70px;gap:1rem}
.nav-links{display:none;gap:1.7rem;font-weight:600}
.nav-links a:hover{color:var(--accent)}
.nav-right{display:flex;align-items:center;gap:.9rem}
.nav-phone{display:none;align-items:center;gap:.4rem;font-weight:700;color:var(--primary)}
.nav-phone svg{width:17px;height:17px}

.pill{display:inline-flex;align-items:center;gap:.5rem;background:var(--accent-soft);color:var(--accent-2);font-weight:700;font-size:.85rem;padding:.4rem .9rem;border-radius:999px}
.pill .dot{width:8px;height:8px;border-radius:50%;background:var(--accent);animation:pulse 2.4s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(239,96,51,.5)}70%{box-shadow:0 0 0 9px rgba(239,96,51,0)}100%{box-shadow:0 0 0 0 rgba(239,96,51,0)}}

.hero{padding:52px 0 20px}
.hero-grid{display:grid;gap:2.4rem;align-items:center;margin-top:1.3rem}
.hero h1{font-size:clamp(2.5rem,6.5vw,4.4rem);margin:1.1rem 0}
.hero h1 .u{color:var(--accent);position:relative;white-space:nowrap}
.hero h1 .u::after{content:"";position:absolute;left:0;right:0;bottom:.08em;height:.14em;background:var(--gold);opacity:.55;border-radius:2px;z-index:-1}
.hero-sub{font-size:clamp(1.1rem,2vw,1.3rem);color:var(--muted);max-width:48ch}
.hero-cta{display:flex;flex-wrap:wrap;gap:.8rem;margin:1.8rem 0 1.4rem}
.trust{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1.3rem;font-weight:600;font-size:.93rem;color:var(--muted)}
.stars{color:var(--gold);letter-spacing:.05em}
/* beeld-slot: de grootste pro-hefboom (nette placeholder) */
.shot{position:relative;border-radius:calc(var(--r) + 8px);overflow:hidden;aspect-ratio:4/5;background:
  repeating-linear-gradient(135deg,#eee5d6 0 14px,#e8ddcb 14px 28px);
  border:1px solid var(--line-2);display:grid;place-items:center;text-align:center}
.shot img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.shot .ph{color:#a8957a;font-weight:600;display:grid;gap:.6rem;justify-items:center;padding:1.4rem}
.shot .ph svg{width:42px;height:42px}
.shot .ph small{font-family:var(--body);font-size:.78rem}
.shot .chip{position:absolute;left:14px;bottom:14px;background:#fff;border:1px solid var(--line);border-radius:999px;padding:.45rem .85rem;font-weight:700;font-size:.85rem;box-shadow:0 8px 20px rgba(28,26,23,.12);display:flex;align-items:center;gap:.4rem}
.shot .chip .stars{font-size:.9rem}

/* keurmerk-strip */
.marks{margin-top:2.2rem;border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:1.2rem 0;display:flex;flex-wrap:wrap;align-items:center;gap:.7rem 1.4rem;justify-content:center}
.mk{display:inline-flex;align-items:center;gap:.45rem;font-weight:700;color:#8a8073;font-size:.9rem;letter-spacing:.01em}
.mk svg{width:18px;height:18px;color:var(--primary)}
.marks .cap{flex-basis:100%;text-align:center;font-size:.72rem;color:#b3a896;font-weight:500;letter-spacing:.04em}

section{padding-block:60px}
.alt{background:var(--surface)}
.sec-head{max-width:54ch;margin-bottom:2.2rem}
.sec-head .pill{margin-bottom:.9rem}
.sec-head h2{font-size:clamp(1.8rem,4vw,2.7rem);margin-bottom:.7rem}
.sec-head p{color:var(--muted);font-size:1.1rem}

.split{display:grid;gap:1.4rem}
.feat-lead{background:var(--primary);color:#fff;border-radius:calc(var(--r) + 6px);padding:2rem;position:relative;overflow:hidden}
.feat-lead h3{font-size:1.5rem;color:#fff;margin-bottom:.6rem}
.feat-lead p{color:rgba(255,255,255,.82)}
.feat-lead .tag{display:inline-block;font-weight:700;color:var(--gold);font-size:.85rem;margin-bottom:.8rem}
.minis{display:grid;gap:1.1rem}
.mini{display:flex;gap:1rem;background:var(--bg);border:1px solid var(--line);border-radius:var(--r);padding:1.3rem}
.mini .ic{flex:none;width:46px;height:46px;border-radius:13px;background:var(--accent-soft);color:var(--accent-2);display:grid;place-items:center}
.mini .ic svg{width:24px;height:24px}
.mini h4{margin:0 0 .25rem;font-family:var(--display);font-weight:600;font-size:1.1rem}
.mini p{color:var(--muted);font-size:.96rem}

.steps{display:grid;gap:1.4rem}
.step .n{font-family:var(--display);font-size:3rem;font-weight:700;color:var(--accent);line-height:1}
.step h3{font-size:1.25rem;margin:.5rem 0 .4rem}
.step p{color:var(--muted)}

/* editorial pull-quote breuk */
.pull{padding:18px 0}
.pull blockquote{margin:0;font-family:var(--display);font-weight:600;font-size:clamp(1.8rem,5vw,3.2rem);line-height:1.12;letter-spacing:-.02em;max-width:20ch}
.pull blockquote .q{color:var(--accent)}
.pull .by{margin-top:1.4rem;display:flex;align-items:center;gap:.8rem;font-weight:700;color:var(--muted)}
.pull .by .av{width:46px;height:46px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-family:var(--display);flex:none}
.statline{display:flex;flex-wrap:wrap;gap:1.6rem;margin-top:2rem}
.statline .s b{font-family:var(--display);font-size:1.9rem;display:block;color:var(--primary);line-height:1}
.statline .s span{color:var(--muted);font-weight:600;font-size:.9rem}

/* FAQ */
.faq{display:grid;gap:.7rem;max-width:760px}
.faq details{border:1px solid var(--line);border-radius:var(--r);background:var(--bg);overflow:hidden}
.faq summary{list-style:none;cursor:pointer;padding:1.1rem 1.3rem;font-family:var(--display);font-weight:600;font-size:1.08rem;display:flex;justify-content:space-between;gap:1rem;align-items:center}
.faq summary::-webkit-details-marker{display:none}
.faq summary::after{content:"+";color:var(--accent);font-size:1.5rem;line-height:1;transition:transform .2s}
.faq details[open] summary::after{transform:rotate(45deg)}
.faq details p{padding:0 1.3rem 1.2rem;color:var(--muted)}

.regio{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1.2rem}
.regio span{font-weight:600;font-size:.9rem;color:var(--primary);background:#fff;border:1px solid var(--line);border-radius:999px;padding:.4rem .9rem}
.regio .more{color:var(--muted);background:transparent;border-color:transparent}

.reassure{background:linear-gradient(180deg,var(--accent-soft),var(--bg));border:1px solid var(--line-2);border-radius:calc(var(--r) + 8px);padding:1.6rem 1.8rem;display:flex;gap:1rem;align-items:flex-start;margin-top:1.6rem}
.reassure svg{flex:none;width:26px;height:26px;color:var(--accent)}
.reassure b{color:var(--primary)}

.lead-grid{display:grid;gap:2rem;align-items:start}
.lead-side h2{font-size:clamp(1.9rem,4vw,2.7rem);margin-bottom:.7rem}
.lead-side p{color:var(--muted);font-size:1.08rem}
.lead-side ul{list-style:none;padding:0;margin:1.3rem 0 0}
.lead-side li{display:flex;gap:.6rem;align-items:flex-start;margin-bottom:.7rem;font-weight:600}
.lead-side li svg{flex:none;width:22px;height:22px;color:var(--accent);margin-top:.15rem}
.card-form{background:var(--surface);border:1px solid var(--line);border-radius:calc(var(--r) + 8px);padding:1.9rem;box-shadow:0 20px 50px rgba(28,26,23,.1)}
.f{margin-bottom:1rem}
.f label{display:block;font-weight:700;font-size:.92rem;margin-bottom:.35rem}
.f input,.f textarea{width:100%;font:inherit;padding:.85rem .95rem;border:1.5px solid var(--line-2);border-radius:var(--r-sm);background:#fff;transition:border-color .15s,box-shadow .15s}
.f input:focus,.f textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft)}
.two{display:grid;gap:1rem}
.hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
.formnote{font-size:.86rem;color:var(--muted);margin-top:.9rem;text-align:center}
a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,summary:focus-visible{outline:3px solid var(--accent);outline-offset:2px}

.ribbon{background:var(--gold);color:#4a3500;text-align:center;font-weight:700;font-size:.84rem;padding:.45rem 1rem;position:relative;z-index:2}

footer{background:var(--primary-2);color:rgba(255,255,255,.74);padding-block:46px 30px;position:relative;z-index:2}
.foot{display:grid;gap:1.5rem}
.foot .brand{color:#fff}
.foot a:hover{color:#fff}
.foot b{color:#fff;display:block;margin-bottom:.4rem}
.endorse{border-top:1px solid rgba(255,255,255,.14);margin-top:1.5rem;padding-top:1.3rem;font-size:.85rem;color:rgba(255,255,255,.55)}
.endorse a{color:rgba(255,255,255,.8);text-decoration:underline}

/* scroll-reveal met intentie */
.reveal{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease}
.reveal.in{opacity:1;transform:none}

@media (min-width:840px){
  .nav-links{display:flex}.nav-phone{display:inline-flex}
  .hero-grid{grid-template-columns:1.35fr .85fr}
  .split{grid-template-columns:1fr 1fr}
  .steps{grid-template-columns:repeat(3,1fr)}
  .pull-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:2rem;align-items:center}
  .lead-grid{grid-template-columns:1fr 1.05fr}
  .two{grid-template-columns:1fr 1fr}
  .foot{grid-template-columns:2fr 1fr 1fr}
}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}.reveal{opacity:1;transform:none}}
</style>
@endverbatim
</head>
<body>

@if(!$site->isLive())
<div class="ribbon">Voorbeeld, nog niet live — placeholder-foto's en keurmerken vervang je door je eigen materiaal</div>
@endif

@php
  $markSvg = '<svg viewBox="0 0 24 24" fill="none"><path d="M12 4c-2.4 3-4 5-4 7a4 4 0 0 0 8 0c0-2-1.6-4-4-7z" fill="#ef6033"/></svg>';
@endphp

<header>
  <div class="wrap nav">
    <a href="#top" class="brand"><span class="mark">{!! $markSvg !!}</span>{!! $site->brand('logo_text', $name) !!}</a>
    <nav class="nav-links" aria-label="Hoofdmenu">
      <a href="#voordelen">Wat je krijgt</a>
      <a href="#werkwijze">Hoe het werkt</a>
      <a href="#faq">Vragen</a>
      <a href="#aanvraag">Werkgebied</a>
    </nav>
    <div class="nav-right">
      @if($phone)<a class="nav-phone" href="tel:{{ $tel }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>{{ $phone }}</a>@endif
      <a class="btn btn-primary" href="#aanvraag">Gratis voorbeeld</a>
    </div>
  </div>
</header>

<main id="top">

  <section class="hero">
    <div class="wrap">
      <div class="hero-grid">
        <div>
          <h1>Meer klussen, ook als je <span class="u">middenin een klus</span> zit.</h1>
          <p class="hero-sub">Mensen met een lekkage of storing bellen de eerste vakman die ze online vertrouwen. Met een nette eigen website ben jij dat, in plaats van de concurrent.</p>
          <div class="hero-cta">
            <a class="btn btn-primary" href="#aanvraag">Gratis voorbeeld aanvragen</a>
            @if($phone)<a class="btn btn-call" href="tel:{{ $tel }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>Of bel ons direct</a>@endif
          </div>
          <div class="trust">
            <span><span class="stars">★★★★★</span> Vakmensen door heel NL</span>
            <span>Binnen 2 werkdagen een voorbeeld</span>
            <span>Geen verplichtingen</span>
          </div>
        </div>
        <div class="shot">
          @if($hero = $site->image('hero'))
            <img src="{{ $hero }}" srcset="{{ $site->imageSrcset('hero') }}" sizes="(max-width:840px) 100vw, 42vw" alt="Installateur aan het werk aan een cv-ketel" width="1024" height="1280" loading="eager" fetchpriority="high">
          @else
          <div class="ph">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            <div>Jouw foto hier<br><small>monteur aan het werk / de bus</small></div>
          </div>
          @endif
          <div class="chip"><span class="stars">★</span> 4,8 op Google <small style="color:var(--muted);font-weight:500">(voorbeeld)</small></div>
        </div>
      </div>
      <div class="marks">
        <span class="mk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg> Erkend Installateur</span>
        <span class="mk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg> Sterkin</span>
        <span class="mk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg> VCA-gecertificeerd</span>
        <span class="mk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg> KvK geregistreerd</span>
        <span class="cap">Voorbeeld, vervang door jouw echte keurmerken en leverancierslogo's</span>
      </div>
    </div>
  </section>

  <section id="voordelen" class="alt reveal">
    <div class="wrap">
      <div class="sec-head">
        <span class="pill">Wat je krijgt</span>
        <h2>Een website die nieuwe klanten naar je toe stuurt</h2>
        <p>Geen techniek of moeilijke woorden. Gewoon meer aanvragen, op het moment dat het telt.</p>
      </div>
      <div class="split">
        <div class="feat-lead">
          <span class="tag">★ Het belangrijkste</span>
          <h3>Bovenaan in Google, juist bij spoed</h3>
          <p>Iemand met een storing zoekt en belt wie hij als eerste vindt. Wij zorgen dat jij dat bent, voor "loodgieter spoed in jouw stad" en soortgelijke zoekopdrachten. Dat zijn de klussen die nu langs je heen lopen.</p>
        </div>
        <div class="minis">
          <div class="mini">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><h4>Aanvragen, dag en nacht</h4><p>Belknop en formulier die altijd aanstaan, ook buiten kantoortijd.</p></div>
          </div>
          <div class="mini">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6.6 6.6a1.5 1.5 0 0 0 2.1 2.1l6.6-6.6a4 4 0 0 0 5.4-5.4l-2.3 2.3-1.8-1.8z"/></svg></span>
            <div><h4>Vertrouwen vooraf</h4><p>Je diensten, werkgebied en reviews. Ze bellen met een gerust gevoel.</p></div>
          </div>
          <div class="mini">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <div><h4>Sterk in jouw regio</h4><p>Lokaal gericht, want jouw klanten zitten in de buurt.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="werkwijze" class="reveal">
    <div class="wrap">
      <div class="sec-head">
        <span class="pill">Hoe het werkt</span>
        <h2>In drie stappen online, jij hebt er geen werk aan</h2>
        <p>Wij regelen de techniek. Jij hoeft alleen je vak te doen.</p>
      </div>
      <div class="steps">
        <div class="step"><div class="n">1</div><h3>Gratis voorbeeld</h3><p>We zetten vooraf een voorbeeld van jóuw bedrijf klaar. Je ziet precies wat je krijgt, zonder iets te beslissen.</p></div>
        <div class="step"><div class="n">2</div><h3>Samen aanscherpen</h3><p>In één kort gesprek stemmen we je diensten, werkgebied en stijl af. Klaar in een half uur.</p></div>
        <div class="step"><div class="n">3</div><h3>Live binnen 2 weken</h3><p>Wij zetten alles live en zorgen dat Google je oppikt. Daarna stromen de aanvragen binnen.</p></div>
      </div>
    </div>
  </section>

  <section class="alt reveal">
    <div class="wrap pull-grid">
      <div class="pull">
        <blockquote><span class="q">&ldquo;</span>Eerste week al drie spoedklussen via de site. Voorheen liep dat allemaal langs me heen.<span class="q">&rdquo;</span></blockquote>
        <div class="by"><span class="av">M</span><div>Mark · installateur in Amersfoort<br><span style="font-weight:500;color:#a8957a;font-size:.85rem">(voorbeeldreview, vervang door je echte review)</span></div></div>
      </div>
      <div class="statline">
        <div class="s"><b>2 dagen</b><span>tot een voorbeeld</span></div>
        <div class="s"><b>2 weken</b><span>tot je site live is</span></div>
        <div class="s"><b>heel NL</b><span>ons werkgebied</span></div>
      </div>
    </div>
  </section>

  <section id="faq" class="reveal">
    <div class="wrap">
      <div class="sec-head">
        <span class="pill">Veelgestelde vragen</span>
        <h2>Wat installateurs ons meestal vragen</h2>
      </div>
      <div class="faq">
        @foreach($faq as $i => $qa)
        <details @if($i===0) open @endif>
          <summary>{{ $qa[0] }}</summary>
          <p>{{ $qa[1] }}</p>
        </details>
        @endforeach
      </div>
    </div>
  </section>

  <section id="aanvraag" class="alt reveal">
    <div class="wrap lead-grid">
      <div class="lead-side">
        <span class="pill">Gratis &amp; vrijblijvend</span>
        <h2 style="margin-top:.9rem">Vraag een gratis voorbeeld aan</h2>
        <p>Binnen 2 werkdagen zie je een voorbeeld van jóuw installatiebedrijf. Geen verplichtingen, geen kleine lettertjes.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Een voorbeeld op maat van jouw bedrijf</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Eén kort gesprek om af te stemmen</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Pas daarna beslis jij, rustig en zonder druk</li>
        </ul>
        <p style="margin-top:1.4rem;font-weight:600;color:var(--ink)">We werken o.a. in:</p>
        <div class="regio">
          @foreach($regio as $stad)<span>{{ $stad }}</span>@endforeach
          <span class="more">+ heel NL</span>
        </div>
      </div>
      <div class="card-form">
        <form method="POST" action="{{ $action }}">
          @csrf
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input type="hidden" name="facet" value="{{ $facet ?? 'website' }}">
          <div class="two">
            <div class="f"><label for="contact_name">Je naam</label><input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required></div>
            <div class="f"><label for="company">Bedrijfsnaam</label><input id="company" name="company" value="{{ old('company') }}" placeholder="optioneel"></div>
          </div>
          <div class="two">
            <div class="f"><label for="email">E-mailadres</label><input id="email" type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="f"><label for="phone">Telefoon</label><input id="phone" name="phone" value="{{ old('phone') }}" required></div>
          </div>
          <div class="f"><label for="city">Plaats / werkgebied</label><input id="city" name="city" value="{{ old('city') }}" placeholder="bijv. Amersfoort"></div>
          <div class="f"><label for="message">Wat doe je precies? <span style="color:var(--muted);font-weight:500">(optioneel)</span></label><textarea id="message" name="message" rows="2" placeholder="loodgieter, elektra, cv, spoed...">{{ old('message') }}</textarea></div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Stuur mijn aanvraag</button>
          <p class="formnote">We bellen of mailen alleen over je voorbeeld. Geen spam, beloofd.</p>
        </form>
      </div>
    </div>
  </section>

</main>

<footer>
  <div class="wrap foot">
    <div>
      <a href="#top" class="brand"><span class="mark">{!! $markSvg !!}</span>{!! $site->brand('logo_text', $name) !!}</a>
      <p style="margin-top:.6rem;max-width:34ch">Websites voor installatiebedrijven die gevonden willen worden, juist als het spoed is.</p>
    </div>
    <div>
      <b>Contact</b>
      @if($phone)<a href="tel:{{ $tel }}">{{ $phone }}</a><br>@endif
      @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@endif
    </div>
    <div>
      <b>Op deze site</b>
      <a href="#voordelen">Wat je krijgt</a><br>
      <a href="#werkwijze">Hoe het werkt</a><br>
      <a href="#faq">Vragen</a><br>
      <a href="#aanvraag">Gratis voorbeeld</a>
    </div>
  </div>
  @if($endorse)
  <div class="wrap"><div class="endorse">@if($endorseUrl)<a href="{{ $endorseUrl }}" rel="nofollow">{{ $endorse }}</a>@else{{ $endorse }}@endif</div></div>
  @endif
</footer>

@verbatim
<script>
document.querySelectorAll('a[href^="#"]').forEach(function(a){a.addEventListener('click',function(e){var id=a.getAttribute('href');if(id.length>1){var el=document.querySelector(id);if(el){e.preventDefault();el.scrollIntoView({behavior:'smooth',block:'start'});}}});});
if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){
  var io=new IntersectionObserver(function(es){es.forEach(function(en){if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target);}});},{threshold:.12});
  document.querySelectorAll('.reveal').forEach(function(el){io.observe(el);});
}else{document.querySelectorAll('.reveal').forEach(function(el){el.classList.add('in');});}
</script>
@endverbatim
</body>
</html>
