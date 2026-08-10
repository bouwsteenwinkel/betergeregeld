@php
    /** @var \App\Support\ChannelSite $site */
    $t = $site->theme();
    $name = $site->name();
    $phone = $site->brand('phone');
    $email = $site->brand('email');
    $address = $site->brand('address', 'Brinklaan 12, Bussum');

    $treatments = [
        ['Knippen & stylen', 'Wassen, knippen, föhnen of stylen', '€ 39'],
        ['Kleuren', 'Uitgroei of volledige kleuring', 'vanaf € 55'],
        ['Highlights / Balayage', 'Natuurlijke, zongekuste tinten', 'vanaf € 95'],
        ['Föhnen & opsteken', 'Voor een gelegenheid of gewoon zin', '€ 29'],
        ['Bruidskapsel', 'Inclusief proefkapsel op afspraak', 'op aanvraag'],
        ['Knippen tot 12 jaar', 'Voor de kleintjes', '€ 22'],
    ];
    $gallery = ['Balayage', 'Coupe', 'Kleur', 'Opsteken', 'Krullen', 'Bruid'];
    $hours = [
        ['Maandag', 'Gesloten'], ['Dinsdag', '09:00 - 18:00'], ['Woensdag', '09:00 - 18:00'],
        ['Donderdag', '09:00 - 21:00'], ['Vrijdag', '09:00 - 18:00'], ['Zaterdag', '08:30 - 16:00'], ['Zondag', 'Gesloten'],
    ];
    $reviews = [
        ['Sinds jaren mijn vaste salon. Altijd een prachtige kleur en echt de tijd voor je.', 'Marloes'],
        ['De balayage is precies wat ik wilde, natuurlijk en stijlvol. Aanrader!', 'Yvonne'],
        ['Fijne sfeer, vakwerk en je kunt zó online een afspraak maken.', 'Sanne'],
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
        --primary:{{ $t['primary'] }}; --accent:{{ $t['accent'] }}; --ink:{{ $t['ink'] }};
        --muted:{{ $t['muted'] }}; --bg:{{ $t['bg'] }}; --surface:{{ $t['surface'] }}; --radius:{{ $t['radius'] }};
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:{!! $t['font'] !!};color:var(--ink);background:var(--bg);line-height:1.6;font-weight:300}
    h1,h2,h3{font-family:'Cormorant Garamond',Georgia,serif;font-weight:600;line-height:1.1;letter-spacing:.01em}
    .serif{font-family:'Cormorant Garamond',Georgia,serif}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:1080px;margin:0 auto;padding:0 24px}
    .btn{display:inline-block;background:var(--primary);color:#fff;padding:13px 26px;border-radius:999px;font-weight:500;font-size:14px;letter-spacing:.03em;transition:.2s;border:1px solid var(--primary)}
    .btn:hover{filter:brightness(.94)}
    .btn-ghost{background:transparent;color:var(--ink);border:1px solid rgba(0,0,0,.18)}
    .eyebrow{display:inline-block;text-transform:uppercase;letter-spacing:.28em;font-size:11px;color:var(--primary);font-weight:500;margin-bottom:18px}

    /* nav */
    nav{position:sticky;top:0;z-index:50;background:rgba(250,246,242,.86);backdrop-filter:blur(10px);border-bottom:1px solid rgba(0,0,0,.06)}
    nav .inner{display:flex;align-items:center;justify-content:space-between;height:70px}
    .logo{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;letter-spacing:.02em}
    .navlinks{display:flex;gap:30px;align-items:center}
    .navlinks a{font-size:14px;color:var(--muted);transition:.2s}
    .navlinks a:hover{color:var(--ink)}
    @media(max-width:780px){.navlinks a:not(.btn){display:none}}

    /* hero */
    .hero{position:relative;padding:96px 0 110px;text-align:center;overflow:hidden}
    .hero::before{content:"";position:absolute;inset:0;background:radial-gradient(900px 420px at 50% -10%,rgba(183,110,121,.18),transparent 70%),linear-gradient(180deg,#fff,var(--bg));z-index:-1}
    .hero h1{font-size:clamp(42px,7vw,76px);max-width:14ch;margin:0 auto 20px}
    .hero p.lead{font-size:19px;color:var(--muted);max-width:54ch;margin:0 auto 32px;font-weight:300}
    .hero .cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

    .usps{display:flex;justify-content:center;gap:50px;flex-wrap:wrap;padding:28px 0;border-top:1px solid rgba(0,0,0,.06);border-bottom:1px solid rgba(0,0,0,.06)}
    .usps div{display:flex;align-items:center;gap:10px;font-size:14px;color:var(--ink)}
    .usps svg{color:var(--primary)}

    section.block{padding:84px 0}
    .sec-head{text-align:center;max-width:60ch;margin:0 auto 48px}
    .sec-head h2{font-size:clamp(32px,4.5vw,46px);margin-bottom:12px}
    .sec-head p{color:var(--muted)}

    /* behandelingen */
    .price-list{max-width:680px;margin:0 auto;display:grid;gap:6px}
    .price-row{display:flex;align-items:baseline;gap:14px;padding:16px 4px;border-bottom:1px dashed rgba(0,0,0,.12)}
    .price-row .nm{font-family:'Cormorant Garamond',serif;font-size:23px;font-weight:600}
    .price-row .ds{color:var(--muted);font-size:14px;flex:1}
    .price-row .pr{font-weight:500;white-space:nowrap;color:var(--primary)}

    /* galerij */
    .gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    @media(max-width:680px){.gallery{grid-template-columns:repeat(2,1fr)}}
    .gallery .tile{position:relative;aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;background:linear-gradient(160deg,var(--primary),var(--accent))}
    .gallery .tile:nth-child(2n){background:linear-gradient(160deg,#c9a27e,#8a807a)}
    .gallery .tile:nth-child(3n){background:linear-gradient(160deg,#2b2724,var(--primary))}
    .gallery .tile span{padding:14px 16px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.35),transparent);width:100%}

    /* over */
    .about{display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}
    @media(max-width:820px){.about{grid-template-columns:1fr}}
    .about .visual{aspect-ratio:4/5;border-radius:var(--radius);background:linear-gradient(150deg,var(--primary),var(--accent));position:relative}
    .about .visual::after{content:"L";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:200px;color:rgba(255,255,255,.22)}
    .stats{display:flex;gap:36px;margin-top:26px}
    .stats .v{font-family:'Cormorant Garamond',serif;font-size:38px;color:var(--primary);line-height:1}
    .stats .l{font-size:13px;color:var(--muted)}

    /* reviews */
    .reviews{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
    @media(max-width:780px){.reviews{grid-template-columns:1fr}}
    .review{background:var(--surface);border:1px solid rgba(0,0,0,.07);border-radius:var(--radius);padding:26px}
    .review .stars{color:var(--accent);letter-spacing:3px;margin-bottom:12px}
    .review p{font-family:'Cormorant Garamond',serif;font-size:20px;line-height:1.4;margin-bottom:14px}
    .review .who{font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}

    /* contact */
    .contact{background:var(--ink);color:#fff;border-radius:calc(var(--radius)*1.6);padding:54px;display:grid;grid-template-columns:1fr 1fr;gap:48px}
    @media(max-width:780px){.contact{grid-template-columns:1fr;padding:36px}}
    .contact h2{color:#fff;font-size:40px}
    .contact a{color:#fff}
    .hours{display:grid;gap:6px}
    .hours .r{display:flex;justify-content:space-between;font-size:14px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.85)}
    .contact .info p{color:rgba(255,255,255,.8);margin-bottom:8px;font-size:15px}

    footer{text-align:center;padding:40px 0;color:var(--muted);font-size:13px}
    .demo-flag{position:fixed;bottom:14px;left:14px;z-index:60;background:var(--ink);color:#fff;font-size:11px;letter-spacing:.08em;padding:7px 12px;border-radius:999px;opacity:.78}
</style>
</head>
<body>

<nav><div class="wrap inner">
    <div class="logo">{{ $name }}</div>
    <div class="navlinks">
        <a href="#behandelingen">Behandelingen</a>
        <a href="#galerij">Galerij</a>
        <a href="#over">Over ons</a>
        <a href="#contact">Contact</a>
        <a href="#afspraak" class="btn">Afspraak maken</a>
    </div>
</div></nav>

<header class="hero"><div class="wrap">
    <span class="eyebrow">Dameskapper · Bussum</span>
    <h1>Jouw haar, met aandacht en oog voor detail</h1>
    <p class="lead">Knippen, kleuren en balayage in een warme, persoonlijke salon. Maak in een paar tikken zelf online een afspraak, wanneer het jou uitkomt.</p>
    <div class="cta">
        <a href="#afspraak" class="btn">Online afspraak maken</a>
        <a href="#behandelingen" class="btn btn-ghost">Bekijk behandelingen</a>
    </div>
</div></header>

<div class="usps"><div class="wrap" style="display:flex;justify-content:center;gap:50px;flex-wrap:wrap">
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Online boeken, 24/7</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg> Hartje Bussum</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7Z"/></svg> Kleur- & coupespecialisten</div>
</div></div>

<section class="block" id="behandelingen"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Behandelingen</span><h2>Vakwerk voor elke gelegenheid</h2><p>Een greep uit ons aanbod. Twijfel je wat past? We adviseren je graag.</p></div>
    <div class="price-list">
        @foreach ($treatments as $tr)
            <div class="price-row"><span class="nm">{{ $tr[0] }}</span><span class="ds">{{ $tr[1] }}</span><span class="pr">{{ $tr[2] }}</span></div>
        @endforeach
    </div>
</div></section>

<section class="block" id="galerij" style="background:var(--surface)"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Galerij</span><h2>Een indruk van ons werk</h2><p>Hier komen straks jouw eigen foto's: coupes, kleuren en opgestoken kapsels.</p></div>
    <div class="gallery">
        @foreach ($gallery as $g)<div class="tile"><span>{{ $g }}</span></div>@endforeach
    </div>
</div></section>

<section class="block" id="over"><div class="wrap about">
    <div>
        <span class="eyebrow">Over Salon Lumière</span>
        <h2 style="font-size:clamp(30px,4vw,44px);margin-bottom:16px">Een salon waar je tot rust komt</h2>
        <p style="color:var(--muted);margin-bottom:14px">Bij Salon Lumière nemen we de tijd voor je. We luisteren naar wat je wilt, denken mee en zorgen dat je met een goed gevoel de deur uitloopt, of het nu om een frisse coupe of een complete kleurmetamorfose gaat.</p>
        <p style="color:var(--muted)">Onze kapsters zijn gespecialiseerd in kleurtechnieken zoals balayage, en in stijlvolle opgestoken kapsels voor bruiloften en feesten.</p>
        <div class="stats">
            <div><div class="v">15+</div><div class="l">jaar ervaring</div></div>
            <div><div class="v">4,9</div><div class="l">gemiddelde review</div></div>
            <div><div class="v">3</div><div class="l">vaste kapsters</div></div>
        </div>
    </div>
    <div class="visual"></div>
</div></section>

<section class="block" style="background:var(--surface)"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Reviews</span><h2>Wat klanten zeggen</h2></div>
    <div class="reviews">
        @foreach ($reviews as $r)
            <div class="review"><div class="stars">★★★★★</div><p>“{{ $r[0] }}”</p><div class="who">{{ $r[1] }}</div></div>
        @endforeach
    </div>
</div></section>

<section class="block" id="contact"><div class="wrap" id="afspraak">
    <div class="contact">
        <div>
            <span class="eyebrow" style="color:var(--accent)">Afspraak & contact</span>
            <h2>Maak je afspraak</h2>
            <p style="color:rgba(255,255,255,.8);margin:14px 0 22px;max-width:38ch">Boek eenvoudig online, of bel ons even. We zien je graag bij Salon Lumière.</p>
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="btn">Bel {{ $phone }}</a>
            <div class="info" style="margin-top:24px">
                <p>📍 {{ $address }}</p>
                <p>✉ {{ $email }}</p>
            </div>
        </div>
        <div>
            <h3 style="color:#fff;font-size:24px;margin-bottom:14px">Openingstijden</h3>
            <div class="hours">
                @foreach ($hours as $hh)<div class="r"><span>{{ $hh[0] }}</span><span>{{ $hh[1] }}</span></div>@endforeach
            </div>
        </div>
    </div>
</div></section>

@include('channels.partials.lead-wizard')

<footer><div class="wrap">{{ $name }} · {{ $address }} · {{ $phone }} · © {{ date('Y') }}</div></footer>

<a href="#gratis-voorbeeld" class="demo-flag">Voorbeeld door Beter Geregeld · vraag je eigen voorbeeld aan →</a>
</body>
</html>
