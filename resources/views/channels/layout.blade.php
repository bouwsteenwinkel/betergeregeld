@php /** @var \App\Support\ChannelSite $site */ @endphp
<!DOCTYPE html>
<html lang="{{ $site->locale() }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="robots" content="@yield('robots', $site->isLive() ? 'index,follow,max-image-preview:large' : 'noindex,nofollow')">

	<title>@yield('title', $site->homeTitle()) · {{ $site->name() }}</title>
	<meta name="description" content="@yield('description', $site->homeDescription())">
	<link rel="canonical" href="@yield('canonical', $site->url(request()->path() === '/' ? '' : ltrim(str_replace('_site/'.$site->key, '', request()->path()), '/')))">

	<meta property="og:type" content="website">
	<meta property="og:site_name" content="{{ $site->name() }}">
	<meta property="og:title" content="@yield('title', $site->homeTitle())">
	<meta property="og:description" content="@yield('description', $site->homeDescription())">

	@if ($site->theme()['font_url'])
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="{{ $site->theme()['font_url'] }}" rel="stylesheet">
	@endif

	<style>
		:root{ {!! $site->cssVars() !!} }
		*{box-sizing:border-box;margin:0;padding:0}
		html{scroll-behavior:smooth}
		body{font-family:var(--font);color:var(--c-ink);background:var(--c-bg);line-height:1.6;-webkit-font-smoothing:antialiased}
		a{color:inherit;text-decoration:none}
		img{max-width:100%;display:block}
		.wrap{max-width:1140px;margin:0 auto;padding:0 22px}
		.btn{display:inline-block;background:var(--c-primary);color:#fff;padding:.85rem 1.5rem;border-radius:var(--radius);font-weight:700;border:0;cursor:pointer;transition:transform .15s,filter .15s}
		.btn:hover{transform:translateY(-2px);filter:brightness(1.06)}
		.btn-ghost{background:transparent;color:var(--c-primary);border:2px solid var(--c-primary)}
		.eyebrow{display:inline-block;background:color-mix(in srgb,var(--c-accent) 18%,transparent);color:var(--c-primary);font-weight:700;font-size:.8rem;letter-spacing:.04em;text-transform:uppercase;padding:.35rem .8rem;border-radius:999px;margin-bottom:1rem}
		h1{font-size:clamp(2rem,5vw,3.3rem);line-height:1.1;font-weight:800;letter-spacing:-.02em}
		h2{font-size:clamp(1.5rem,3.5vw,2.3rem);font-weight:800;letter-spacing:-.01em;margin-bottom:.4rem}
		h3{font-size:1.2rem;font-weight:700}
		section{padding:64px 0}
		.muted{color:var(--c-muted)}
		.card{background:var(--c-surface);border-radius:var(--radius);padding:1.6rem;box-shadow:0 10px 30px -18px rgba(0,0,0,.25)}
		.grid{display:grid;gap:1.2rem}
		@media(min-width:760px){.cols-3{grid-template-columns:repeat(3,1fr)}.cols-4{grid-template-columns:repeat(4,1fr)}.cols-2{grid-template-columns:1fr 1fr}}
		/* nav */
		.nav{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--c-bg) 88%,transparent);backdrop-filter:blur(10px);border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent)}
		.nav-inner{display:flex;align-items:center;justify-content:space-between;height:68px}
		.logo{font-weight:800;font-size:1.3rem;color:var(--c-ink)}
		.logo span{color:var(--c-primary)}
		.nav-links{display:none;gap:1.6rem;align-items:center}
		.nav-links a{font-weight:600}
		.nav-links a:hover{color:var(--c-primary)}
		@media(min-width:860px){.nav-links{display:flex}}
		/* hero */
		.hero{padding:72px 0 56px;background:linear-gradient(180deg,color-mix(in srgb,var(--c-accent) 10%,transparent),transparent)}
		.hero p.lead{font-size:1.2rem;max-width:38ch;margin:1rem 0 1.6rem}
		.hero-usps{list-style:none;margin:1.4rem 0 0;display:grid;gap:.5rem}
		.hero-usps li{padding-left:1.7rem;position:relative;font-weight:600}
		.hero-usps li:before{content:"✓";position:absolute;left:0;color:var(--c-primary);font-weight:800}
		/* footer */
		footer{background:var(--c-footer-bg,var(--c-ink));color:#fff;padding:48px 0 28px;margin-top:24px}
		footer a{color:rgba(255,255,255,.8)}footer a:hover{color:#fff}
		.foot-grid{display:grid;gap:1.6rem;margin-bottom:1.8rem}
		@media(min-width:760px){.foot-grid{grid-template-columns:2fr 1fr 1fr}}
		.foot-bottom{border-top:1px solid rgba(255,255,255,.15);padding-top:1rem;font-size:.85rem;color:rgba(255,255,255,.6);display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem;align-items:center}
		.endorsement{display:inline-flex;align-items:center;gap:.4rem}
		.endorsement a{color:rgba(255,255,255,.75)}.endorsement a:hover{color:#fff}
		.endorsement .diamond{color:var(--c-accent);font-size:.7rem}
		/* form */
		.field{margin-bottom:.9rem}
		.field label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.3rem}
		.field input,.field textarea{width:100%;padding:.7rem .9rem;border:1px solid color-mix(in srgb,var(--c-ink) 18%,transparent);border-radius:10px;font:inherit;background:#fff}
		.field input:focus,.field textarea:focus{outline:2px solid var(--c-primary);border-color:transparent}
		.hp{position:absolute;left:-9999px}
		.errors{background:#fef2f2;color:#b91c1c;border-radius:10px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.9rem}
		.prose p{margin:0 0 1rem}.prose h2{margin:1.6rem 0 .6rem}.prose h3{margin:1.2rem 0 .4rem}.prose ul{margin:0 0 1rem 1.2rem}
	</style>
	@stack('head')
</head>
<body>
	@if (! $site->isLive())
		<div style="background:#1c1917;color:#fbbf24;text-align:center;font-size:.8rem;padding:.4rem">
			PREVIEW · concept "{{ $site->key }}" — nog niet live (geen domein gekoppeld)
		</div>
	@endif

	@include('channels.partials.nav')

	@yield('content')

	@include('channels.partials.footer')
</body>
</html>
