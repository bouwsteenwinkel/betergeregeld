@php
    /** @var \App\Support\ChannelSite $site */
    $t = $site->theme();
    $name = $site->name();
    $phone = $site->brand('phone');
    $email = $site->brand('email');
    $address = $site->brand('address', 'Brinklaan 48, Bussum');

    $services = [
        ['Knippen', 'Wassen, knippen en stylen', '€ 27'],
        ['Knippen + baard', 'De complete beurt', '€ 39'],
        ['Baard trimmen', 'Strak in model', '€ 18'],
        ['Scheren met het mes', 'Klassiek, met hete handdoek', '€ 30'],
        ['Contouren / snor', 'Even bijwerken', '€ 12'],
        ['Knippen tot 12 jaar', 'Voor de jonge mannen', '€ 20'],
    ];
    $gallery = ['Fade', 'Baard', 'Classic', 'Scheren', 'Crop', 'Pompadour'];
    $hours = [
        ['Maandag', 'Gesloten'], ['Dinsdag', '09:00 – 18:00'], ['Woensdag', '09:00 – 18:00'],
        ['Donderdag', '09:00 – 21:00'], ['Vrijdag', '09:00 – 18:00'], ['Zaterdag', '08:00 – 16:00'], ['Zondag', 'Gesloten'],
    ];
    $reviews = [
        ['Strakke fade en een scheerbeurt met het mes. Echt vakwerk.', 'Dave'],
        ['Vaste zaak geworden. Goeie sfeer en je zit zo binnen via de app.', 'Rachid'],
        ['Mijn baard is nog nooit zo netjes geweest. Top.', 'Mark'],
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
    :root{ --gold:{{ $t['primary'] }}; --gold2:{{ $t['accent'] }}; --bg:{{ $t['bg'] }}; --surface:{{ $t['surface'] }}; --muted:{{ $t['muted'] }}; --radius:{{ $t['radius'] }}; }
    *{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:{!! $t['font'] !!};color:#ece7df;background:var(--bg);line-height:1.6;font-weight:300}
    h1,h2,h3{font-family:'Oswald',Impact,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.02em;line-height:1.05;color:#fff}
    a{color:inherit;text-decoration:none}
    .wrap{max-width:1080px;margin:0 auto;padding:0 24px}
    .btn{display:inline-block;background:var(--gold);color:#15120d;padding:13px 28px;border-radius:var(--radius);font-family:'Oswald',sans-serif;text-transform:uppercase;letter-spacing:.06em;font-size:14px;transition:.2s;border:1px solid var(--gold)}
    .btn:hover{background:var(--gold2)}
    .btn-ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.25)}
    .eyebrow{display:inline-block;text-transform:uppercase;letter-spacing:.3em;font-size:11px;color:var(--gold);font-family:'Oswald',sans-serif;margin-bottom:16px}

    nav{position:sticky;top:0;z-index:50;background:rgba(21,18,13,.9);backdrop-filter:blur(8px);border-bottom:1px solid rgba(255,255,255,.08)}
    nav .inner{display:flex;align-items:center;justify-content:space-between;height:70px}
    .logo{font-family:'Oswald',sans-serif;font-size:24px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#fff}
    .navlinks{display:flex;gap:28px;align-items:center}
    .navlinks a{font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#b8b1a6;transition:.2s}
    .navlinks a:hover{color:#fff}
    @media(max-width:780px){.navlinks a:not(.btn){display:none}}

    .hero{position:relative;padding:110px 0 120px;text-align:center;overflow:hidden;border-bottom:1px solid rgba(255,255,255,.08)}
    .hero::before{content:"";position:absolute;inset:0;background:radial-gradient(800px 400px at 50% 0,rgba(199,154,58,.16),transparent 70%);z-index:-1}
    .hero h1{font-size:clamp(46px,8vw,92px);max-width:16ch;margin:0 auto 18px}
    .hero p.lead{font-size:18px;color:#c5bdb0;max-width:50ch;margin:0 auto 32px}
    .hero .cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

    .usps{display:flex;justify-content:center;gap:48px;flex-wrap:wrap;padding:24px;border-bottom:1px solid rgba(255,255,255,.08)}
    .usps div{display:flex;align-items:center;gap:10px;font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#d8d1c5;font-family:'Oswald',sans-serif}
    .usps svg{color:var(--gold)}

    section.block{padding:84px 0}
    .sec-head{text-align:center;max-width:60ch;margin:0 auto 46px}
    .sec-head h2{font-size:clamp(32px,5vw,52px);margin-bottom:12px}
    .sec-head p{color:var(--muted)}

    .price-list{max-width:680px;margin:0 auto;display:grid;gap:4px}
    .price-row{display:flex;align-items:baseline;gap:14px;padding:18px 4px;border-bottom:1px solid rgba(255,255,255,.1)}
    .price-row .nm{font-family:'Oswald',sans-serif;text-transform:uppercase;font-size:19px;letter-spacing:.02em;color:#fff}
    .price-row .ds{color:var(--muted);font-size:14px;flex:1}
    .price-row .pr{font-family:'Oswald',sans-serif;font-size:19px;color:var(--gold);white-space:nowrap}

    .gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
    @media(max-width:680px){.gallery{grid-template-columns:repeat(2,1fr)}}
    .gallery .tile{position:relative;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;background:linear-gradient(160deg,#2a2419,#15120d);border:1px solid rgba(255,255,255,.08)}
    .gallery .tile:nth-child(3n){background:linear-gradient(160deg,var(--gold),#5a4516)}
    .gallery .tile span{padding:12px 14px;font-family:'Oswald',sans-serif;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#fff;width:100%;background:linear-gradient(0deg,rgba(0,0,0,.5),transparent)}

    .about{display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}
    @media(max-width:820px){.about{grid-template-columns:1fr}}
    .about .visual{aspect-ratio:4/5;border-radius:var(--radius);background:linear-gradient(150deg,#2a2419,var(--gold));position:relative;border:1px solid rgba(255,255,255,.1)}
    .about .visual::after{content:"✂";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:120px;color:rgba(0,0,0,.3)}
    .stats{display:flex;gap:36px;margin-top:26px}
    .stats .v{font-family:'Oswald',sans-serif;font-size:40px;color:var(--gold);line-height:1}
    .stats .l{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}

    .reviews{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
    @media(max-width:780px){.reviews{grid-template-columns:1fr}}
    .review{background:var(--surface);border:1px solid rgba(255,255,255,.08);border-radius:var(--radius);padding:26px}
    .review .stars{color:var(--gold);letter-spacing:3px;margin-bottom:12px}
    .review p{font-size:17px;line-height:1.5;margin-bottom:14px;color:#e7e1d6}
    .review .who{font-family:'Oswald',sans-serif;font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}

    .contact{background:#0e0c08;border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:54px;display:grid;grid-template-columns:1fr 1fr;gap:48px}
    @media(max-width:780px){.contact{grid-template-columns:1fr;padding:34px}}
    .contact h2{font-size:42px}
    .hours{display:grid;gap:4px}
    .hours .r{display:flex;justify-content:space-between;font-size:14px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.1);color:#c5bdb0}
    .info p{color:#c5bdb0;margin-bottom:8px;font-size:15px}

    footer{text-align:center;padding:38px 0;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;border-top:1px solid rgba(255,255,255,.08)}
    .demo-flag{position:fixed;bottom:14px;left:14px;z-index:60;background:var(--gold);color:#15120d;font-family:'Oswald',sans-serif;font-size:11px;letter-spacing:.08em;text-transform:uppercase;padding:7px 12px;border-radius:999px}
</style>
</head>
<body>

<nav><div class="wrap inner">
    <div class="logo">{{ $name }}</div>
    <div class="navlinks">
        <a href="#diensten">Diensten</a>
        <a href="#galerij">Galerij</a>
        <a href="#over">Over</a>
        <a href="#contact">Contact</a>
        <a href="#afspraak" class="btn">Afspraak</a>
    </div>
</div></nav>

<header class="hero"><div class="wrap">
    <span class="eyebrow">Barbershop · Bussum</span>
    <h1>Scherp geknipt, strak in de baard</h1>
    <p class="lead">Klassiek vakmanschap, moderne coupes en scheren met het mes. Boek je stoel zo online — of loop binnen.</p>
    <div class="cta">
        <a href="#afspraak" class="btn">Online afspraak</a>
        <a href="#diensten" class="btn btn-ghost">Bekijk diensten</a>
    </div>
</div></header>

<div class="usps"><div class="wrap" style="display:flex;justify-content:center;gap:48px;flex-wrap:wrap">
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Walk-in & online</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9a6 6 0 0 0 12 0"/><path d="M6 9V4m12 5V4"/></svg> Scheren met het mes</div>
    <div><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg> Hartje Bussum</div>
</div></div>

<section class="block" id="diensten"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Diensten</span><h2>Wat we voor je doen</h2><p>Vakwerk voor knippen, baard en scheren. Twijfel je? We adviseren je in de stoel.</p></div>
    <div class="price-list">
        @foreach ($services as $s)<div class="price-row"><span class="nm">{{ $s[0] }}</span><span class="ds">{{ $s[1] }}</span><span class="pr">{{ $s[2] }}</span></div>@endforeach
    </div>
</div></section>

<section class="block" id="galerij" style="background:var(--surface)"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Galerij</span><h2>Ons werk</h2><p>Hier komen straks jouw eigen foto's — fades, baarden en classics.</p></div>
    <div class="gallery">@foreach ($gallery as $g)<div class="tile"><span>{{ $g }}</span></div>@endforeach</div>
</div></section>

<section class="block" id="over"><div class="wrap about">
    <div>
        <span class="eyebrow">Over Brink Barbers</span>
        <h2 style="font-size:clamp(30px,4.5vw,48px);margin-bottom:16px">Een barbershop met karakter</h2>
        <p style="color:var(--muted);margin-bottom:14px">Bij Brink Barbers draait alles om vakmanschap en een goeie sfeer. Of je nu komt voor een strakke fade, een nette baard of een klassieke scheerbeurt met het mes — we nemen de tijd en zorgen dat je er scherp uitloopt.</p>
        <p style="color:var(--muted)">Onze barbers zijn opgeleid in zowel klassieke als moderne technieken.</p>
        <div class="stats">
            <div><div class="v">10+</div><div class="l">jaar ervaring</div></div>
            <div><div class="v">4,9</div><div class="l">review</div></div>
            <div><div class="v">3</div><div class="l">barbers</div></div>
        </div>
    </div>
    <div class="visual"></div>
</div></section>

<section class="block" style="background:var(--surface)"><div class="wrap">
    <div class="sec-head"><span class="eyebrow">Reviews</span><h2>Wat klanten zeggen</h2></div>
    <div class="reviews">@foreach ($reviews as $r)<div class="review"><div class="stars">★★★★★</div><p>“{{ $r[0] }}”</p><div class="who">{{ $r[1] }}</div></div>@endforeach</div>
</div></section>

<section class="block" id="contact"><div class="wrap" id="afspraak">
    <div class="contact">
        <div>
            <span class="eyebrow">Afspraak & contact</span>
            <h2>Boek je stoel</h2>
            <p style="color:#c5bdb0;margin:14px 0 22px;max-width:38ch">Online boeken of even bellen — je bent welkom bij Brink Barbers.</p>
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="btn">Bel {{ $phone }}</a>
            <div class="info" style="margin-top:24px">
                <p>📍 {{ $address }}</p>
                <p>✉ {{ $email }}</p>
            </div>
        </div>
        <div>
            <h3 style="font-size:24px;margin-bottom:14px">Openingstijden</h3>
            <div class="hours">@foreach ($hours as $hh)<div class="r"><span>{{ $hh[0] }}</span><span>{{ $hh[1] }}</span></div>@endforeach</div>
        </div>
    </div>
</div></section>

@include('channels.partials.lead-wizard')

<footer><div class="wrap">{{ $name }} · {{ $address }} · {{ $phone }} — © {{ date('Y') }}</div></footer>

<a href="#gratis-voorbeeld" class="demo-flag">Voorbeeld door Beter Geregeld · vraag je eigen voorbeeld aan →</a>
</body>
</html>
