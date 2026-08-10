@php
    /** @var \App\Support\ChannelSite $site */
    $t = $site->theme();
    $name = $site->name();
    $phone = $site->brand('phone');
    $tel = preg_replace('/\s+/', '', (string) $phone);
    $email = $site->brand('email');
    $address = $site->brand('address', 'Brinklaan 24, Bussum');
    $booking = $site->brand('booking_url');
    $heroImg = $site->image('hero');

    $treatments = [
        ['Gezichtsbehandeling klassiek', 'Diepe reiniging, peeling, masker en een ontspannende gezichtsmassage. Je huid straalt weer.', '€ 65 · 60 min', true],
        ['Huidverbetering op maat', 'Gerichte aanpak van acne, pigment of een doffe huid, met persoonlijk advies en een plan.', 'vanaf € 75', false],
        ['Wenkbrauwen epileren & verven', 'Strak in model, perfect passend bij je gezicht.', '€ 22', false],
        ['Wimperlifting', 'Je eigen wimpers gekruld en geaccentueerd, weken mooi zonder mascara.', '€ 45', false],
        ['Harsen', 'Benen, bikinilijn of oksels, langdurig glad en verzorgd.', 'vanaf € 18', false],
        ['Ontspanningsmassage', 'Rug, nek en schouders, even helemaal tot rust komen.', '€ 55 · 45 min', false],
    ];
    $why = [
        ['heart', 'Persoonlijk huidadvies', 'We kijken eerst goed naar jouw huid en geven eerlijk advies, zonder dure pakketten op te dringen.'],
        ['spark', 'Zichtbaar resultaat', 'Behandelingen die werken, met producten die we zelf vertrouwen. Je ziet en voelt het verschil.'],
        ['leaf', 'Rust & aandacht', 'Een serene studio waar je echt even tot rust komt. Geen haast, alle tijd voor jou.'],
        ['phone', 'Zo geboekt', 'Online of telefonisch een afspraak in een minuut. Binnen 1 werkdag bevestigd.'],
    ];
    $gallery = ['Gezichtsbehandeling', 'Huidverbetering', 'Wenkbrauwen', 'Wimpers', 'Massage', 'Sfeer'];
    $team = [
        ['Esmée', 'Eigenaar & huidspecialist', '#d4628a'],
        ['Noor', 'Wenkbrauw- & wimperstyliste', '#e0457e'],
        ['Lisa', 'Huidtherapie & massage', '#c97ba0'],
    ];
    $hours = [
        ['Maandag', 'Gesloten'], ['Dinsdag', '09:00 - 18:00'], ['Woensdag', '09:00 - 18:00'],
        ['Donderdag', '09:00 - 21:00'], ['Vrijdag', '09:00 - 18:00'], ['Zaterdag', '09:00 - 16:00'], ['Zondag', 'Gesloten'],
    ];
    $reviews = [
        ['Mijn huid is echt rustiger geworden na de behandelingen. Fijn advies en een heerlijke sfeer.', 'Annemiek', 'Gezichtsbehandeling'],
        ['Heerlijk ontspannen en altijd persoonlijke aandacht. Ik kom hier al jaren.', 'Petra', 'Vaste klant'],
        ['Wenkbrauwen perfect in model en een gezichtsbehandeling om van te genieten.', 'Iris', 'Wenkbrauwen'],
        ['In één behandeling al verschil gezien bij mijn acne. Eindelijk iemand die meedenkt.', 'Sanne', 'Huidverbetering'],
        ['De wimperlifting is geweldig, weken wakker worden met mooie wimpers. Aanrader!', 'Demi', 'Wimperlifting'],
        ['Zo makkelijk online geboekt en super geholpen. Voelt echt als een momentje voor jezelf.', 'Yara', 'Massage'],
    ];
    $faq = [
        ['Hoe maak ik een afspraak?', 'Online via de knop hierboven, telefonisch, of laat je gegevens achter in het formulier. We bevestigen binnen 1 werkdag.'],
        ['Moet ik vooraf betalen?', 'Nee, je betaalt gewoon na de behandeling in de salon. Geen aanbetaling nodig.'],
        ['Ik kom voor het eerst, wat kan ik verwachten?', 'We beginnen met een kort, gratis huidadvies. Samen kijken we wat bij jouw huid past, daarna start de behandeling.'],
        ['Kan ik een afspraak verzetten of annuleren?', 'Natuurlijk, kosteloos tot 24 uur van tevoren. Even bellen of mailen is genoeg.'],
        ['Welke producten gebruiken jullie?', 'Professionele, huidvriendelijke merken die we zelf vertrouwen. We vertellen je graag wat we gebruiken en waarom.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', $site->homeTitle())</title>
<meta name="description" content="{{ $site->homeDescription() }}">
<meta name="robots" content="noindex,nofollow">
@if (!empty($t['font_url']))<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="{{ $t['font_url'] }}" rel="stylesheet">@endif
<style>
    :root{
        --primary:{{ $t['primary'] }}; --accent:{{ $t['accent'] }}; --cta:{{ $t['cta'] ?? $t['primary'] }};
        --ink:{{ $t['ink'] }}; --muted:{{ $t['muted'] }}; --bg:{{ $t['bg'] }}; --surface:{{ $t['surface'] }}; --radius:{{ $t['radius'] }};
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:{!! $t['font'] !!};color:var(--ink);background:var(--bg);line-height:1.65;font-weight:400;-webkit-font-smoothing:antialiased}
    h1,h2,h3{font-family:'Cormorant Garamond',Georgia,serif;font-weight:600;line-height:1.12;letter-spacing:.01em}
    a{color:inherit;text-decoration:none}
    img{max-width:100%;display:block}
    .wrap{max-width:1100px;margin:0 auto;padding:0 24px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--cta);color:#fff;padding:14px 28px;border-radius:999px;font-weight:500;font-size:14px;letter-spacing:.03em;transition:.2s;border:1px solid var(--cta);cursor:pointer;box-shadow:0 10px 26px -12px color-mix(in srgb,var(--cta) 70%,transparent)}
    .btn:hover{transform:translateY(-2px);filter:brightness(1.04)}
    .btn-ghost{background:transparent;color:var(--ink);border-color:rgba(0,0,0,.16);box-shadow:none}
    .btn-light{background:#fff;color:var(--cta);border-color:#fff;box-shadow:none}
    .eyebrow{display:inline-block;text-transform:uppercase;letter-spacing:.26em;font-size:11px;color:var(--cta);font-weight:600;margin-bottom:16px}
    .stars{color:#e9a23b;letter-spacing:2px}

    /* aankondigings-/offer-balk */
    .promobar{background:color-mix(in srgb,var(--primary) 78%,#2a1018);color:#fff;text-align:center;font-size:13.5px;font-weight:500;padding:10px 16px;letter-spacing:.02em}
    .promobar strong{font-weight:700}
    .promobar a{text-decoration:underline;text-underline-offset:2px}

    nav{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--bg) 88%,transparent);backdrop-filter:blur(10px);border-bottom:1px solid rgba(0,0,0,.06)}
    nav .inner{display:flex;align-items:center;justify-content:space-between;height:72px}
    .logo{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:700;letter-spacing:.02em;color:var(--primary)}
    .navlinks{display:flex;gap:28px;align-items:center}
    .navlinks a{font-size:14px;color:var(--muted);transition:.2s}
    .navlinks a:hover{color:var(--ink)}
    @media(max-width:880px){.navlinks a:not(.btn){display:none}}

    .hero{position:relative;min-height:min(80vh,660px);display:flex;align-items:center;overflow:hidden;color:#fff}
    .hero-bg{position:absolute;inset:0;z-index:-2}
    .hero-bg img{width:100%;height:100%;object-fit:cover}
    .hero::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(100deg,color-mix(in srgb,var(--primary) 78%,#2a1620),color-mix(in srgb,var(--primary) 30%,transparent) 60%,transparent)}
    .hero .wrap{padding:60px 24px}
    .hero .eyebrow{color:#fff}
    .hero .trust{display:inline-flex;align-items:center;gap:9px;font-size:14px;margin-bottom:18px;background:rgba(255,255,255,.16);padding:7px 14px;border-radius:999px}
    .hero h1{font-size:clamp(42px,6.6vw,76px);max-width:16ch;margin-bottom:18px;color:#fff;font-weight:700}
    .hero p.lead{font-size:19px;max-width:50ch;margin-bottom:26px;color:rgba(255,255,255,.94);font-weight:300}
    .hero .cta{display:flex;gap:14px;flex-wrap:wrap}
    .hero .reassure{margin-top:18px;font-size:13px;color:rgba(255,255,255,.85);display:flex;gap:16px;flex-wrap:wrap}
    .hero .reassure span{display:inline-flex;align-items:center;gap:6px}
    .hero.no-img{color:var(--ink);min-height:auto;padding:80px 0 50px;text-align:center}
    .hero.no-img::after{background:radial-gradient(800px 380px at 50% -10%,color-mix(in srgb,var(--primary) 18%,transparent),transparent 70%)}
    .hero.no-img h1,.hero.no-img .eyebrow{color:var(--ink)}.hero.no-img p.lead{color:var(--muted)}.hero.no-img .cta,.hero.no-img .reassure{justify-content:center}.hero.no-img .trust{background:color-mix(in srgb,var(--primary) 10%,transparent);color:var(--ink)}

    .usps{padding:24px 0;border-bottom:1px solid rgba(0,0,0,.06);background:var(--surface)}
    .usps .inner{display:flex;justify-content:center;gap:46px;flex-wrap:wrap}
    .usps div{display:flex;align-items:center;gap:10px;font-size:14px;color:var(--ink)}
    .usps svg{color:var(--cta);flex:none}

    section.block{padding:80px 0}
    .tint{background:color-mix(in srgb,var(--primary) 5%,var(--bg))}
    .sec-head{text-align:center;max-width:60ch;margin:0 auto 44px}
    .sec-head h2{font-size:clamp(32px,4.6vw,48px);margin-bottom:12px}
    .sec-head p{color:var(--muted)}

    .price-list{max-width:740px;margin:0 auto 34px;display:grid;gap:2px}
    .price-row{display:grid;grid-template-columns:1fr auto;gap:5px 18px;padding:18px 6px;border-bottom:1px dashed rgba(0,0,0,.12)}
    .price-row .nm{font-family:'Cormorant Garamond',serif;font-size:23px;font-weight:600;display:flex;align-items:center;gap:10px}
    .price-row .pop{font-family:var(--font);font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#fff;background:var(--cta);padding:3px 8px;border-radius:999px}
    .price-row .pr{font-weight:500;white-space:nowrap;color:var(--cta);align-self:center}
    .price-row .ds{grid-column:1/-1;color:var(--muted);font-size:14px;max-width:62ch}
    .center{text-align:center}

    .why{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
    @media(max-width:880px){.why{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:520px){.why{grid-template-columns:1fr}}
    .why-card{background:var(--surface);border:1px solid rgba(0,0,0,.05);border-radius:var(--radius);padding:30px 24px;text-align:center;box-shadow:0 16px 36px -26px rgba(60,30,40,.4);transition:transform .2s ease,box-shadow .2s ease}
    .why-card:hover{transform:translateY(-4px);box-shadow:0 24px 44px -26px rgba(60,30,40,.45)}
    .why-ico{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;margin:0 auto 16px;background:color-mix(in srgb,var(--primary) 14%,transparent);color:var(--cta)}
    .why-card h3{font-size:24px;margin-bottom:8px}
    .why-card p{color:var(--muted);font-size:15px}

    .gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    @media(max-width:680px){.gallery{grid-template-columns:repeat(2,1fr)}}
    .gallery .tile{position:relative;aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;background:linear-gradient(160deg,var(--primary),var(--accent))}
    .gallery .tile:nth-child(3n){background:linear-gradient(160deg,var(--cta),var(--primary))}
    .gallery .tile img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
    .gallery .tile span{position:relative;z-index:1;padding:14px 16px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.45),transparent);width:100%}

    .about{display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}
    @media(max-width:820px){.about{grid-template-columns:1fr}}
    .about .visual{aspect-ratio:4/5;border-radius:var(--radius);overflow:hidden;background:linear-gradient(150deg,var(--primary),var(--accent));position:relative}
    .about .visual img{width:100%;height:100%;object-fit:cover}
    .stats{display:flex;gap:38px;margin-top:28px}
    .stats .v{font-family:'Cormorant Garamond',serif;font-size:42px;color:var(--cta);line-height:1}
    .stats .l{font-size:13px;color:var(--muted)}

    .team{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
    @media(max-width:780px){.team{grid-template-columns:1fr}}
    .team-card{text-align:center}
    .team-av{width:128px;height:128px;border-radius:50%;margin:0 auto 16px;display:grid;place-items:center;overflow:hidden;color:#fff;font-family:'Cormorant Garamond',serif;font-size:48px;box-shadow:0 16px 34px -16px rgba(60,30,40,.45);transition:transform .2s ease}
    .team-card:hover .team-av{transform:translateY(-4px)}
    .team-av img{width:100%;height:100%;object-fit:cover}
    .team-card h3{font-size:24px}
    .team-card .role{font-size:12.5px;text-transform:uppercase;letter-spacing:.1em;color:var(--cta);font-weight:500}

    /* afspraak in 3 stappen */
    .steps3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    @media(max-width:780px){.steps3{grid-template-columns:1fr}}
    .step3{text-align:center}
    .step3 .n{width:54px;height:54px;border-radius:50%;background:var(--cta);color:#fff;font-family:'Cormorant Garamond',serif;font-size:26px;display:grid;place-items:center;margin:0 auto 14px;box-shadow:0 12px 28px -12px color-mix(in srgb,var(--cta) 70%,transparent)}
    .step3 h3{font-size:24px;margin-bottom:6px}
    .step3 p{color:var(--muted);font-size:14.5px;max-width:32ch;margin:0 auto}

    /* cadeaubon */
    .gift{display:grid;grid-template-columns:1fr 1fr;border-radius:calc(var(--radius)*1.4);overflow:hidden;border:1px solid rgba(0,0,0,.06);background:var(--surface)}
    @media(max-width:780px){.gift{grid-template-columns:1fr}}
    .gift .visual{background:linear-gradient(150deg,var(--primary),var(--cta));min-height:250px;display:grid;place-items:center;color:#fff;padding:30px}
    .gift .voucher{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.4);border-radius:14px;padding:24px 34px;text-align:center;transform:rotate(-3deg)}
    .gift .voucher .lbl{font-size:11px;letter-spacing:.2em;text-transform:uppercase;opacity:.9}
    .gift .voucher .big{font-family:'Cormorant Garamond',serif;font-size:42px;line-height:1.05;margin:6px 0}
    .gift .body{padding:42px}
    .gift .body h2{font-size:clamp(28px,3.6vw,40px);margin-bottom:10px}
    .gift .body p{color:var(--muted);margin-bottom:20px;max-width:42ch}

    .reviews-head{display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:34px;flex-wrap:wrap}
    .reviews-head .score{font-family:'Cormorant Garamond',serif;font-size:40px;color:var(--ink);line-height:1}
    .reviews{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
    @media(max-width:820px){.reviews{grid-template-columns:1fr}}
    .review{background:var(--surface);border:1px solid rgba(0,0,0,.05);border-radius:var(--radius);padding:28px;box-shadow:0 16px 36px -26px rgba(60,30,40,.4);transition:transform .2s ease,box-shadow .2s ease}
    .review:hover{transform:translateY(-4px);box-shadow:0 24px 44px -26px rgba(60,30,40,.45)}
    .review .stars{letter-spacing:3px;margin-bottom:12px}
    .review p{font-family:'Cormorant Garamond',serif;font-size:20px;line-height:1.45;margin-bottom:16px;color:var(--ink)}
    .review .who{font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
    .review .who b{color:var(--ink);font-weight:600}

    /* offer-band */
    .offer{background:linear-gradient(120deg,var(--primary),var(--cta));color:#fff;border-radius:calc(var(--radius)*1.6);padding:44px;text-align:center}
    .offer h2{color:#fff;font-size:clamp(30px,4vw,44px);margin-bottom:10px}
    .offer p{color:rgba(255,255,255,.9);max-width:50ch;margin:0 auto 22px}

    /* faq */
    .faq{max-width:760px;margin:0 auto;display:grid;gap:10px}
    .faq details{background:var(--surface);border:1px solid rgba(0,0,0,.08);border-radius:var(--radius);overflow:hidden}
    .faq summary{list-style:none;cursor:pointer;padding:18px 22px;font-family:'Cormorant Garamond',serif;font-size:21px;font-weight:600;display:flex;justify-content:space-between;gap:14px;align-items:center}
    .faq summary::-webkit-details-marker{display:none}
    .faq summary::after{content:"+";color:var(--cta);font-size:24px;line-height:1;transition:transform .2s}
    .faq details[open] summary::after{transform:rotate(45deg)}
    .faq details p{padding:0 22px 18px;color:var(--muted)}

    .contact{background:var(--ink);color:#fff;border-radius:calc(var(--radius)*1.6);padding:54px;display:grid;grid-template-columns:1fr 1fr;gap:48px}
    @media(max-width:860px){.contact{grid-template-columns:1fr;padding:34px}}
    .contact h2{color:#fff;font-size:40px}
    .contact a{color:#fff}
    .contact .eyebrow{color:var(--accent)}
    .contact .info p{color:rgba(255,255,255,.82);margin-bottom:8px;font-size:15px;display:flex;gap:9px;align-items:center}
    .contact .info svg{color:var(--accent);flex:none}
    .hours{display:grid;gap:4px;margin-top:8px}
    .hours .r{display:flex;justify-content:space-between;font-size:14px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.85)}
    .afspraak-actions{display:flex;gap:12px;flex-wrap:wrap;margin:18px 0 16px}
    .reassure-line{font-size:12.5px;color:rgba(255,255,255,.7);display:flex;gap:14px;flex-wrap:wrap}
    .reassure-line span{display:inline-flex;align-items:center;gap:6px}
    .afform{display:grid;gap:12px}
    .afform .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:520px){.afform .row{grid-template-columns:1fr}}
    .afform label{display:block;font-size:12.5px;color:rgba(255,255,255,.7);margin-bottom:5px}
    .afform input,.afform select,.afform textarea{width:100%;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);color:#fff;font:inherit;font-size:15px}
    .afform input::placeholder,.afform textarea::placeholder{color:rgba(255,255,255,.45)}
    .afform input:focus,.afform select:focus,.afform textarea:focus{outline:2px solid var(--accent);border-color:transparent}
    .afform option{color:#111}
    .afform .btn{width:100%;margin-top:4px}
    .afform-note{font-size:12px;color:rgba(255,255,255,.6);text-align:center}
    .afform-done{display:none;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:22px;text-align:center}
    .afform-done h3{color:#fff;font-size:24px;margin-bottom:8px}
    .afform-done p{color:rgba(255,255,255,.82);font-size:15px}

    footer{text-align:center;padding:42px 0;color:var(--muted);font-size:13px}
    footer .fname{font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--primary);display:block;margin-bottom:6px}

    /* sticky CTA */
    .sticky-cta{position:fixed;left:50%;transform:translateX(-50%) translateY(140%);bottom:18px;z-index:60;transition:transform .3s ease}
    .sticky-cta.show{transform:translateX(-50%) translateY(0)}
    .sticky-cta .btn{box-shadow:0 14px 34px -10px color-mix(in srgb,var(--cta) 75%,transparent)}
</style>
</head>
<body>

<div class="promobar">✨ <strong>Nieuw bij {{ $name }}?</strong> Gratis huidadvies (t.w.v. € 15) bij je eerste afspraak. <a href="#afspraak">Plan nu →</a></div>

<nav><div class="wrap inner">
    <div class="logo">{{ $name }}</div>
    <div class="navlinks">
        <a href="#behandelingen">Behandelingen</a>
        <a href="#waarom">Waarom wij</a>
        <a href="#reviews">Reviews</a>
        <a href="#contact">Contact</a>
        <a href="#afspraak" class="btn">Afspraak maken</a>
    </div>
</div></nav>

<header class="hero {{ $heroImg ? '' : 'no-img' }}">
    @if ($heroImg)
        <div class="hero-bg" aria-hidden="true"><img src="{{ $heroImg }}" srcset="{{ $site->imageSrcset('hero') }}" sizes="100vw" alt="" loading="eager" fetchpriority="high"></div>
    @endif
    <div class="wrap">
        <span class="trust"><span class="stars">★★★★★</span> 4,9 uit 180+ reviews</span>
        <h1>Stralende huid, en een moment helemaal voor jou</h1>
        <p class="lead">Gezichtsbehandelingen, huidverbetering en ontspanning in een gezellige studio in Bussum. Met persoonlijk advies en aandacht voor jouw huid.</p>
        <div class="cta">
            <a href="#afspraak" class="btn">Maak een afspraak</a>
            <a href="#behandelingen" class="btn btn-ghost" style="{{ $heroImg ? 'color:#fff;border-color:rgba(255,255,255,.5)' : '' }}">Bekijk behandelingen</a>
        </div>
        <div class="reassure">
            <span>✓ Binnen 1 werkdag bevestigd</span><span>✓ Geen aanbetaling</span><span>✓ Gratis annuleren tot 24 uur vooraf</span>
        </div>
    </div>
</header>

<div class="usps"><div class="wrap inner">
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Online & telefonisch boeken</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg> Hartje Bussum</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 2 3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7Z"/></svg> Persoonlijk huidadvies</div>
</div></div>

<section class="block" id="behandelingen"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Behandelingen</span><h2>Wat we voor je doen</h2><p>Een greep uit ons aanbod. Niet zeker wat past bij jouw huid? We kijken samen tijdens een gratis huidadvies.</p></div>
    <div class="price-list">
        @foreach ($treatments as $tr)
            <div class="price-row">
                <span class="nm">{{ $tr[0] }}@if ($tr[3])<span class="pop">Populair</span>@endif</span>
                <span class="pr">{{ $tr[2] }}</span>
                <span class="ds">{{ $tr[1] }}</span>
            </div>
        @endforeach
    </div>
    <div class="center"><a href="#afspraak" class="btn">Plan je behandeling</a></div>
</div></section>

<section class="block tint" id="waarom"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Waarom {{ $name }}</span><h2>Waarom klanten voor ons kiezen</h2></div>
    <div class="why">
        @foreach ($why as $w)
            <div class="why-card">
                <span class="why-ico">
                    @switch($w[0])
                        @case('heart')<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>@break
                        @case('spark')<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v6M12 15v6M3 12h6M15 12h6"/></svg>@break
                        @case('leaf')<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 20A7 7 0 0 1 4 13c0-6 7-9 16-9 0 9-3 16-9 16Z"/><path d="M4 20c3-4 6-6 10-7"/></svg>@break
                        @default<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3 5.2 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.7c.1.9.4 1.9.7 2.8a2 2 0 0 1-.5 2.1L9 10.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.8.7A2 2 0 0 1 22 16.9Z"/></svg>
                    @endswitch
                </span>
                <h3>{{ $w[1] }}</h3>
                <p>{{ $w[2] }}</p>
            </div>
        @endforeach
    </div>
</div></section>

<section class="block" id="stappen"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Zo werkt het</span><h2>In 3 stappen geboekt</h2><p>Een afspraak maken is zo gepiept.</p></div>
    <div class="steps3">
        <div class="step3"><div class="n">1</div><h3>Kies je behandeling</h3><p>Weet je het nog niet? Vraag een gratis huidadvies aan, we denken met je mee.</p></div>
        <div class="step3"><div class="n">2</div><h3>Plan je moment</h3><p>Boek online of bel ons. Je krijgt binnen 1 werkdag een bevestiging.</p></div>
        <div class="step3"><div class="n">3</div><h3>Geniet & straal</h3><p>Kom langs, ontspan, en ga met een stralende huid de deur uit.</p></div>
    </div>
    <div class="center" style="margin-top:30px"><a href="#afspraak" class="btn">Maak je afspraak</a></div>
</div></section>

<section class="block" id="galerij"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Sfeer & werk</span><h2>Een indruk van de studio</h2></div>
    <div class="gallery">
        @foreach ($gallery as $i => $g)
            @php $gi = $site->image('gallery' . ($i + 1)); @endphp
            <div class="tile">
                @if ($gi)<img src="{{ $gi }}" srcset="{{ $site->imageSrcset('gallery' . ($i + 1)) }}" sizes="(min-width:680px) 33vw, 50vw" alt="{{ $g }}" loading="lazy">@endif
                <span>{{ $g }}</span>
            </div>
        @endforeach
    </div>
</div></section>

<section class="block tint" id="over"><div class="wrap about">
    <div>
        <span class="eyebrow">Over {{ $name }}</span>
        <h2 style="font-size:clamp(30px,4vw,44px);margin-bottom:16px">Een plek om tot rust te komen</h2>
        <p style="color:var(--muted);margin-bottom:14px">Bij {{ $name }} draait het om jouw huid én om even ontspannen. We nemen de tijd, kijken goed naar wat je huid nodig heeft en geven eerlijk advies, zonder dure pakketten op te dringen.</p>
        <p style="color:var(--muted)">Of je nu komt voor een ontspannende gezichtsbehandeling, gerichte huidverbetering of strakke wenkbrauwen, je gaat ontspannen en met een stralende huid de deur uit.</p>
        <div class="stats">
            <div><div class="v">12+</div><div class="l">jaar ervaring</div></div>
            <div><div class="v">4,9</div><div class="l">gemiddelde review</div></div>
            <div><div class="v">3.000+</div><div class="l">tevreden klanten</div></div>
        </div>
    </div>
    <div class="visual">@if ($site->image('detail'))<img src="{{ $site->image('detail') }}" alt="Behandeling bij {{ $name }}" loading="lazy">@endif</div>
</div></section>

<section class="block"><div class="wrap">
    <div class="gift">
        <div class="visual">
            <div class="voucher"><div class="lbl">Cadeaubon</div><div class="big">{{ $name }}</div><div class="lbl">stralende huid cadeau</div></div>
        </div>
        <div class="body">
            <span class="eyebrow">Cadeautip</span>
            <h2>Verras iemand met stralende huid</h2>
            <p>Een cadeaubon van {{ $name }}, voor een specifieke behandeling of een bedrag naar keuze. Altijd goed, voor een verjaardag, moederdag of zomaar.</p>
            <a href="#afspraak" class="btn">Cadeaubon aanvragen</a>
        </div>
    </div>
</div></section>

<section class="block"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Het team</span><h2>De specialisten die je verwennen</h2></div>
    <div class="team">
        @foreach ($team as $i => $m)
            @php $tp = $site->image('team' . ($i + 1)); @endphp
            <div class="team-card">
                <div class="team-av" style="background:linear-gradient(150deg,{{ $m[2] }},var(--accent))">
                    @if ($tp)<img src="{{ $tp }}" srcset="{{ $site->imageSrcset('team' . ($i + 1)) }}" sizes="128px" alt="{{ $m[0] }}, {{ $m[1] }}" loading="lazy">@else{{ mb_substr($m[0], 0, 1) }}@endif
                </div>
                <h3>{{ $m[0] }}</h3>
                <div class="role">{{ $m[1] }}</div>
            </div>
        @endforeach
    </div>
</div></section>

<section class="block tint" id="reviews"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Reviews</span><h2>Wat klanten zeggen</h2></div>
    <div class="reviews-head"><span class="stars" style="font-size:22px">★★★★★</span><span class="score">4,9</span><span style="color:var(--muted)">uit 180+ reviews</span></div>
    <div class="reviews">
        @foreach ($reviews as $r)
            <div class="review"><div class="stars">★★★★★</div><p>“{{ $r[0] }}”</p><div class="who"><b>{{ $r[1] }}</b> · {{ $r[2] }}</div></div>
        @endforeach
    </div>
</div></section>

<section class="block"><div class="wrap">
    <div class="offer">
        <span class="eyebrow" style="color:rgba(255,255,255,.9)">Welkomstactie</span>
        <h2>Gratis huidadvies bij je eerste afspraak</h2>
        <p>Nieuw bij {{ $name }}? Start met een persoonlijk huidadvies (t.w.v. € 15), helemaal gratis. Zo weet je precies welke behandeling bij jou past.</p>
        <a href="#afspraak" class="btn btn-light">Claim je gratis huidadvies</a>
    </div>
</div></section>

<section class="block tint"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Goed om te weten</span><h2>Veelgestelde vragen</h2></div>
    <div class="faq">
        @foreach ($faq as $i => $qa)
            <details @if($i === 0) open @endif><summary>{{ $qa[0] }}</summary><p>{{ $qa[1] }}</p></details>
        @endforeach
    </div>
</div></section>

<section class="block" id="contact"><div class="wrap" id="afspraak">
    <div class="contact">
        <div>
            <span class="eyebrow">Afspraak & contact</span>
            <h2>Maak een afspraak</h2>
            <p style="color:rgba(255,255,255,.82);margin:14px 0 0;max-width:40ch">Boek online of bel ons. Laat anders je gegevens achter, dan bellen we je terug om een moment te plannen.</p>
            <div class="afspraak-actions">
                @if ($booking)<a href="{{ $booking }}" target="_blank" rel="noopener" class="btn btn-light">Online boeken</a>@endif
                <a href="tel:{{ $tel }}" class="btn {{ $booking ? 'btn-ghost' : 'btn-light' }}" style="{{ $booking ? 'color:#fff;border-color:rgba(255,255,255,.5)' : '' }}">Bel {{ $phone }}</a>
            </div>
            <p class="reassure-line"><span>✓ Geen aanbetaling</span><span>✓ Binnen 1 werkdag bevestigd</span></p>
            <div class="info" style="margin-top:18px">
                <p><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>{{ $address }}</p>
                <p><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><a href="mailto:{{ $email }}">{{ $email }}</a></p>
            </div>
            <h3 style="color:#fff;font-size:21px;margin:20px 0 8px">Openingstijden</h3>
            <div class="hours">
                @foreach ($hours as $hh)<div class="r"><span>{{ $hh[0] }}</span><span>{{ $hh[1] }}</span></div>@endforeach
            </div>
        </div>
        <div>
            <form class="afform" id="afform" novalidate>
                <h3 style="color:#fff;font-size:22px;margin-bottom:2px">Vraag een afspraak aan</h3>
                <div class="row">
                    <div><label for="af-naam">Naam</label><input id="af-naam" type="text" placeholder="Je naam" required></div>
                    <div><label for="af-tel">Telefoon</label><input id="af-tel" type="tel" placeholder="06 …" required></div>
                </div>
                <div><label for="af-mail">E-mail</label><input id="af-mail" type="email" placeholder="naam@voorbeeld.nl"></div>
                <div><label for="af-beh">Behandeling</label>
                    <select id="af-beh">
                        @foreach ($treatments as $tr)<option>{{ $tr[0] }}</option>@endforeach
                        <option>Gratis huidadvies / weet ik nog niet</option>
                    </select>
                </div>
                <div><label for="af-voorkeur">Voorkeur dag/tijd (optioneel)</label><textarea id="af-voorkeur" rows="2" placeholder="bv. doordeweeks na 17:00, of zaterdagochtend"></textarea></div>
                <button type="submit" class="btn btn-light">Afspraak aanvragen</button>
                <p class="afform-note">Vrijblijvend, je zit nergens aan vast.</p>
            </form>
            <div class="afform-done" id="afdone">
                <h3>Bedankt!</h3>
                <p>We hebben je aanvraag ontvangen en bellen je binnen 1 werkdag om je afspraak te bevestigen.</p>
            </div>
        </div>
    </div>
</div></section>

<footer><div class="wrap">
    <span class="fname">{{ $name }}</span>
    {{ $address }} · <a href="tel:{{ $tel }}">{{ $phone }}</a> · © {{ date('Y') }}
</div></footer>

<div class="sticky-cta"><a href="#afspraak" class="btn">Maak een afspraak</a></div>

<script>
(function () {
    var form = document.getElementById('afform'), done = document.getElementById('afdone');
    if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var naam = document.getElementById('af-naam').value.trim();
        var tel = document.getElementById('af-tel').value.trim();
        if (!naam || !tel) { (naam ? document.getElementById('af-tel') : document.getElementById('af-naam')).focus(); return; }
        var d = done.querySelector('p');
        if (d) d.textContent = 'Bedankt ' + naam + ', we hebben je aanvraag ontvangen en bellen je binnen 1 werkdag om je afspraak te bevestigen.';
        form.style.display = 'none'; done.style.display = 'block';
        done.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    var sticky = document.querySelector('.sticky-cta');
    if (sticky) {
        var upd = function () { sticky.classList.toggle('show', window.scrollY > 700); };
        window.addEventListener('scroll', upd, { passive: true }); upd();
    }
})();
</script>
</body>
</html>
