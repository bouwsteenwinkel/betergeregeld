@php /** @var \App\Support\ChannelSite $site */ @endphp
<!DOCTYPE html>
<html lang="{{ $site->locale() }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="robots" content="@yield('robots', $site->isLive() ? 'index,follow,max-image-preview:large' : 'noindex,nofollow')">

	<title>@yield('title', $site->homeTitle()) · {{ $site->displayName() }}</title>
	<meta name="description" content="@yield('description', $site->metaDescription())">
	<link rel="canonical" href="@yield('canonical', $site->url(request()->path() === '/' ? '' : ltrim(str_replace('_site/'.$site->key, '', request()->path()), '/')))">

	<meta property="og:type" content="website">
	<meta property="og:site_name" content="{{ $site->displayName() }}">
	<meta property="og:title" content="@yield('title', $site->homeTitle())">
	<meta property="og:description" content="@yield('description', $site->metaDescription())">
	@php $ogImage = $site->image('hero'); @endphp
	@if ($ogImage)
		<meta property="og:image" content="{{ $ogImage }}">
		<meta property="og:image:alt" content="{{ $site->displayName() }}">
		<meta name="twitter:image" content="{{ $ogImage }}">
	@endif
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="@yield('title', $site->homeTitle())">
	<meta name="twitter:description" content="@yield('description', $site->metaDescription())">

	{{-- Themed favicon (data-URI): primary-badge met de eerste monogram-letter in het accent. --}}
	@php
		$ft = $site->theme();
		$favSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="7" fill="' . $ft['primary'] . '"/><text x="16" y="23" font-family="Arial,Helvetica,sans-serif" font-size="19" font-weight="800" text-anchor="middle" fill="' . $ft['accent'] . '">' . e(mb_substr($site->monogram(), 0, 1)) . '</text></svg>';
	@endphp
	<link rel="icon" href="data:image/svg+xml,{{ rawurlencode($favSvg) }}">
	<link rel="apple-touch-icon" href="data:image/svg+xml,{{ rawurlencode($favSvg) }}">

	{{-- Provider-agnostische event-laag: pusht naar dataLayer (GA4/GTM pikken het
	     op zodra gekoppeld). Meet CTA-kliks en formulier-submits funnel-breed. --}}
	<script>
	(function () {
		window.dataLayer = window.dataLayer || [];
		window.bgTrack = function (name, data) { window.dataLayer.push(Object.assign({ event: name }, data || {})); };
		document.addEventListener('click', function (e) {
			var a = e.target.closest('a.btn, .sticky-cta, .nav-drawer-cta');
			if (a) window.bgTrack('cta_click', { label: (a.textContent || '').trim().slice(0, 40), href: a.getAttribute('href') || '' });
		});
		document.addEventListener('submit', function (e) {
			var f = e.target;
			if (f && f.tagName === 'FORM' && /\/contact$/.test(f.getAttribute('action') || '')) {
				window.bgTrack('lead_submit', { form: f.classList.contains('lwz') ? 'wizard' : 'simpel' });
			}
		});
	})();
	</script>

	{{-- Betergeregeld CMP (cookie-consent). Toont de banner, logt consent en gate't
	     scripts uit categorie 'analytics'/'marketing'. LET OP: analytics/heatmap/GA4
	     NOOIT los in de layout laden — voeg ze toe als CMP-script (admin, categorie
	     analytics), anders draaien ze buiten de consent om. --}}
	<script src="{{ url('/cmp/loader.js') }}?tenant=channels&lang={{ $site->locale() }}" async></script>

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
		/* ankers niet onder de sticky nav laten verdwijnen */
		section[id],.scroll-anchor{scroll-margin-top:88px}
		/* toegankelijkheid: skip-link, zichtbare focus, reduced-motion */
		.skip-link{position:absolute;left:12px;top:-60px;z-index:100;background:var(--c-primary);color:#fff;padding:.7rem 1.1rem;border-radius:8px;font-weight:700;transition:top .15s}
		.skip-link:focus{top:12px;outline:2px solid var(--c-cta);outline-offset:2px}
		a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,summary:focus-visible,[tabindex]:focus-visible{outline:2px solid var(--c-cta);outline-offset:2px;border-radius:3px}
		@media (prefers-reduced-motion:reduce){
			*,*::before,*::after{animation-duration:.001ms!important;animation-iteration-count:1!important;transition-duration:.001ms!important;scroll-behavior:auto!important}
			.btn:hover,.feature-card:hover,.blog-card:hover,.team-card:hover .team-avatar{transform:none!important}
		}
		/* Primaire CTA: de opvallende CTA-kleur (standaard = accent, per thema los te zetten).
		   Scherpe randen (krapper dan de card-radius), display-font, subtiele getinte glow. */
		.btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;background:var(--c-cta);color:var(--c-on-cta);font-weight:700;font-size:1rem;letter-spacing:.01em;padding:.78rem 1.5rem;border:0;border-radius:6px;cursor:pointer;box-shadow:0 5px 16px -10px color-mix(in srgb,var(--c-cta) 60%,transparent);transition:transform .15s,filter .15s,box-shadow .2s}
		.btn:hover{transform:translateY(-1px);filter:brightness(1.05);box-shadow:0 10px 22px -10px color-mix(in srgb,var(--c-cta) 70%,transparent)}
		/* Secundaire actie: neutraal, mag niet met de primaire CTA concurreren. */
		.btn-secondary{background:var(--c-primary);color:#fff}
		.btn-ghost{background:transparent;color:var(--c-primary);border:2px solid var(--c-primary)}
		.eyebrow{display:inline-block;background:color-mix(in srgb,var(--c-accent) 18%,transparent);color:var(--c-primary);font-weight:700;font-size:.8rem;letter-spacing:.04em;text-transform:uppercase;padding:.35rem .8rem;border-radius:999px;margin-bottom:1rem}
		h1{font-family:var(--font-display);font-size:clamp(2rem,5vw,3.4rem);line-height:1.08;font-weight:700;letter-spacing:-.005em}
		h2{font-family:var(--font-display);font-size:clamp(1.5rem,3.5vw,2.3rem);font-weight:700;letter-spacing:0;margin-bottom:.4rem}
		h3{font-size:1.2rem;font-weight:700}
		section{padding:64px 0}
		.muted{color:var(--c-muted)}
		.card{background:var(--c-surface);border-radius:var(--radius);padding:1.6rem;box-shadow:0 10px 30px -18px rgba(0,0,0,.25)}
		/* sectie-kicker met lijntje */
		.kicker{display:inline-flex;align-items:center;gap:.6rem;font-size:.78rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:var(--c-cta);margin-bottom:.85rem}
		.kicker-line{display:block;width:30px;height:2px;border-radius:2px;background:var(--c-cta)}
		.section-lead{margin-bottom:2.4rem;font-size:1.05rem;max-width:54ch}
		/* feature-cards met lijn-accenten */
		.feature-grid{gap:1.4rem}
		.feature-card{position:relative;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);border-radius:calc(var(--radius) + 2px);padding:1.7rem 1.5rem;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
		.feature-card::before{content:"";position:absolute;left:0;top:0;width:100%;height:3px;background:linear-gradient(90deg,var(--c-accent),color-mix(in srgb,var(--c-accent) 25%,transparent));transform:scaleX(0);transform-origin:left;transition:transform .3s ease}
		.feature-card:hover{transform:translateY(-4px);box-shadow:0 24px 44px -26px rgba(0,0,0,.45);border-color:color-mix(in srgb,var(--c-accent) 38%,transparent)}
		.feature-card:hover::before{transform:scaleX(1)}
		.feature-ico{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:13px;background:color-mix(in srgb,var(--c-accent) 13%,transparent);color:var(--c-accent);margin-bottom:1.05rem}
		.feature-ico svg{width:24px;height:24px}
		.feature-card h3{font-size:1.12rem;line-height:1.25}
		.feature-rule{display:block;width:26px;height:2px;border-radius:2px;background:var(--c-accent);margin:.7rem 0 .85rem}
		.feature-card p{font-size:.95rem;margin:0}
		/* stappen met verbindende tijdlijn */
		.steps{display:grid;gap:1.6rem;margin-top:2rem}
		@media(min-width:760px){.steps{grid-template-columns:repeat(3,1fr);gap:2.2rem}}
		.step{position:relative}
		.step-num{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:var(--c-primary);color:#fff;font-weight:700;font-size:1.15rem;margin-bottom:1rem;position:relative;z-index:1}
		.step h3{margin-bottom:.3rem}
		@media(min-width:760px){.step:not(:last-child)::after{content:"";position:absolute;top:24px;left:62px;right:-2.2rem;height:2px;background:linear-gradient(90deg,color-mix(in srgb,var(--c-accent) 65%,transparent),color-mix(in srgb,var(--c-accent) 18%,transparent))}}
		/* pull-statement (proof) */
		.pullquote{max-width:780px;margin:0 auto;text-align:center;position:relative;padding:.5rem 1rem}
		.pullquote-mark{display:block;font-size:3.4rem;line-height:.7;color:var(--c-accent);margin-bottom:.3rem}
		.pullquote blockquote{margin:0;font-weight:600;font-size:clamp(1.3rem,2.6vw,1.7rem);line-height:1.35;color:var(--c-ink)}
		/* reviews (WebwinkelKeur/Google-gevoel, in de huisstijl) */
		.rev-stars{color:var(--c-accent);letter-spacing:.12em;line-height:1;font-size:1rem}
		.reviews-head{display:grid;gap:1.4rem;margin-bottom:2rem}
		@media(min-width:760px){.reviews-head{grid-template-columns:1fr auto;align-items:end}}
		.reviews-summary .rev-stars{font-size:1.25rem;margin-bottom:.3rem}
		@media(min-width:760px){.reviews-summary{text-align:right}}
		.rev-score{font-weight:800;font-size:1.7rem;line-height:1;color:var(--c-ink)}
		.rev-score span{font-weight:600;font-size:1.05rem;color:var(--c-muted)}
		.rev-count{font-size:.85rem;color:var(--c-muted);margin-top:.3rem}
		.reviews-rail{display:flex;gap:1.4rem;overflow-x:auto;scroll-snap-type:x mandatory;padding:.3rem .2rem 1.3rem;margin:0 -.2rem;scrollbar-width:thin}
		.reviews-rail::-webkit-scrollbar{height:8px}
		.reviews-rail::-webkit-scrollbar-thumb{background:color-mix(in srgb,var(--c-ink) 16%,transparent);border-radius:999px}
		.rev-card{flex:0 0 clamp(258px,80vw,320px);scroll-snap-align:start;display:flex;flex-direction:column;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);border-radius:calc(var(--radius) + 2px);padding:1.5rem;box-shadow:0 14px 34px -26px rgba(0,0,0,.4)}
		.rev-title{font-size:1.02rem;font-weight:700;margin:.7rem 0 .35rem;line-height:1.3}
		.rev-body{margin:.5rem 0 0;font-size:.95rem;line-height:1.55;color:color-mix(in srgb,var(--c-ink) 88%,transparent)}
		.rev-foot{margin-top:1.2rem;padding-top:1rem;border-top:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);display:flex;align-items:center;justify-content:space-between;gap:.8rem}
		.rev-who{display:flex;flex-direction:column;line-height:1.3;min-width:0}
		.rev-who strong{font-size:.95rem}
		.rev-who span{font-size:.8rem;color:var(--c-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
		.rev-verified{flex:0 0 auto;display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--c-accent)}
		.rev-verified svg{width:14px;height:14px}
		.reviews-summary-line{margin-top:1.4rem;font-size:.92rem;color:var(--c-muted)}
		.reviews-summary-line a{color:var(--c-cta);font-weight:600}
		/* showcase (detail-beeld + tekst) */
		.showcase-inner{display:grid;gap:2rem;align-items:center}
		@media(min-width:760px){.showcase-inner{grid-template-columns:46% 1fr;gap:3rem}}
		.showcase-media{border-radius:calc(var(--radius) + 4px);overflow:hidden;box-shadow:0 30px 60px -30px rgba(0,0,0,.5);aspect-ratio:4/3}
		.showcase-media img{width:100%;height:100%;object-fit:cover;display:block}
		.showcase-text h2{margin:.3rem 0 .6rem}
		.showcase-text p{max-width:46ch}
		/* compacte variant: minder hoogte, kleiner beeld, kop bescheidener */
		.showcase--compact{padding:38px 0}
		.showcase--compact .showcase-media{aspect-ratio:16/10}
		.showcase--compact .showcase-text h2{font-size:clamp(1.4rem,3vw,2rem)}
		@media(min-width:760px){.showcase--compact .showcase-inner{grid-template-columns:40% 1fr;gap:2.6rem}}
		/* herhaalde CTA-band: eigen ruimte zodat de kaart nooit tegen de sectiegrens plakt */
		.cta-band{padding:40px 0}
		.cta-band-inner{background:var(--c-primary);color:#fff;border-radius:calc(var(--radius) + 4px);padding:2rem clamp(1.4rem,4vw,3rem);display:grid;gap:1.4rem;align-items:center;box-shadow:0 26px 50px -28px rgba(0,0,0,.55)}
		@media(min-width:760px){.cta-band-inner{grid-template-columns:1fr auto}}
		.cta-band h2{margin-bottom:.3rem}
		.cta-band p{color:rgba(255,255,255,.82);max-width:46ch;margin:0}
		.cta-band .btn{white-space:nowrap}
		/* sticky mobiele CTA */
		.sticky-cta{position:fixed;left:0;right:0;bottom:0;z-index:60;display:none;align-items:center;justify-content:center;text-align:center;background:var(--c-cta);color:var(--c-on-cta);font-weight:700;padding:1.05rem 1rem;border-radius:0;box-shadow:0 -6px 24px -8px rgba(0,0,0,.35);transform:translateY(140%);transition:transform .3s ease}
		.sticky-cta.show{transform:translateY(0)}
		@media(max-width:859px){.sticky-cta{display:flex}}
		/* blog-overzicht */
		.blog-grid{gap:1.4rem;margin-top:1rem}
		.blog-card{display:flex;flex-direction:column;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);border-radius:calc(var(--radius) + 2px);padding:1.6rem;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
		.blog-card:hover{transform:translateY(-4px);box-shadow:0 24px 44px -26px rgba(0,0,0,.45);border-color:color-mix(in srgb,var(--c-accent) 38%,transparent)}
		.blog-meta{font-size:.76rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--c-accent);margin-bottom:.6rem}
		.blog-card h3{font-size:1.18rem;line-height:1.25;margin-bottom:.5rem}
		.blog-card p{font-size:.95rem;margin:0 0 1.1rem}
		.blog-more{margin-top:auto;display:inline-flex;align-items:center;gap:.4rem;font-weight:600;font-size:.95rem;color:var(--c-cta)}
		.blog-more svg{width:16px;height:16px;transition:transform .2s ease}
		.blog-card:hover .blog-more svg{transform:translateX(3px)}
		.blog-card-img{display:block;margin:-1.6rem -1.6rem 1.1rem;border-radius:calc(var(--radius) + 2px) calc(var(--radius) + 2px) 0 0;overflow:hidden;aspect-ratio:16/10;background:color-mix(in srgb,var(--c-ink) 6%,transparent)}
		.blog-card-img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s ease}
		.blog-card:hover .blog-card-img img{transform:scale(1.04)}
		.article-cover{width:100%;aspect-ratio:16/9;object-fit:cover;display:block;border-radius:var(--radius);box-shadow:0 24px 60px -30px rgba(0,0,0,.4)}
		.pager{display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap}
		.pager-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.1rem;border-radius:8px;font-weight:600;font-size:.95rem;border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:var(--c-surface);color:var(--c-ink);text-decoration:none;transition:border-color .15s,color .15s}
		.pager-btn:hover{border-color:var(--c-primary);color:var(--c-primary)}
		.pager-btn.is-disabled{opacity:.4;pointer-events:none}
		.pager-info{color:var(--c-muted);font-size:.9rem;font-weight:600}
		.empty-state{text-align:center;max-width:540px;margin:1rem auto;padding:2.6rem 1.6rem;background:var(--c-surface);border:1px dashed color-mix(in srgb,var(--c-ink) 18%,transparent);border-radius:calc(var(--radius) + 2px)}
		.empty-state h3{font-size:1.3rem;margin-bottom:.5rem}
		.empty-state p{margin:0 auto 1.5rem;max-width:42ch}
		/* artikel */
		.article-back{display:inline-flex;align-items:center;gap:.4rem;font-weight:600;font-size:.92rem;color:var(--c-cta)}
		.article-back svg{width:16px;height:16px;transition:transform .2s ease}
		.article-back:hover svg{transform:translateX(-3px)}
		/* team */
		.team-grid{display:grid;gap:1.8rem 1.4rem;margin-top:2rem;grid-template-columns:repeat(2,1fr)}
		@media(min-width:680px){.team-grid{grid-template-columns:repeat(3,1fr)}}
		.team-card{text-align:center}
		.team-avatar{width:108px;height:108px;margin:0 auto .9rem;border-radius:50%;overflow:hidden;box-shadow:0 14px 30px -16px rgba(0,0,0,.45);outline:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);transition:transform .2s ease}
		.team-card:hover .team-avatar{transform:translateY(-4px)}
		.avatar-svg{display:block;width:100%;height:100%}
		.team-avatar img{width:100%;height:100%;object-fit:cover;display:block}
		.team-card h3{font-size:1.12rem;line-height:1.2;margin-bottom:.15rem}
		.team-role{display:inline-block;font-size:.76rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--c-accent);margin-bottom:.5rem}
		.team-card p{font-size:.9rem;max-width:30ch;margin:0 auto}
		.grid{display:grid;gap:1.2rem}
		@media(min-width:760px){.cols-3{grid-template-columns:repeat(3,1fr)}.cols-4{grid-template-columns:repeat(4,1fr)}.cols-2{grid-template-columns:1fr 1fr}}
		/* FAQ (accordeon) */
		.faq{display:grid;gap:.7rem;max-width:760px}
		.faq details{background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);border-radius:var(--radius);overflow:hidden}
		.faq summary{list-style:none;cursor:pointer;padding:1rem 1.2rem;font-weight:700;display:flex;justify-content:space-between;gap:1rem;align-items:center}
		.faq summary::-webkit-details-marker{display:none}
		.faq summary::after{content:"+";color:var(--c-cta);font-size:1.4rem;line-height:1;transition:transform .2s}
		.faq details[open] summary::after{transform:rotate(45deg)}
		.faq details p{padding:0 1.2rem 1.1rem;color:var(--c-muted)}
		/* trust-balk boven de nav */
		.topbar{background:var(--c-ink);color:rgba(255,255,255,.78);font-size:.82rem;letter-spacing:.01em}
		.topbar-inner{display:flex;align-items:center;justify-content:space-between;min-height:42px;gap:1rem}
		.topbar a{color:rgba(255,255,255,.78);display:inline-flex;align-items:center;gap:.4rem;transition:color .15s}
		.topbar a:hover{color:#fff}
		.topbar .ic{width:14px;height:14px;flex:0 0 auto}
		.topbar-trust{display:inline-flex;align-items:center;gap:.5rem;font-weight:500;min-width:0;color:rgba(255,255,255,.9)}
		.topbar-trust .ic{color:var(--c-cta);width:15px;height:15px}
		.topbar-contact{display:none;gap:1.5rem;flex:0 0 auto}
		.topbar-contact .ic{opacity:.7}
		@media(min-width:680px){.topbar-contact{display:inline-flex}}
		/* nav */
		.nav{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--c-bg) 92%,transparent);backdrop-filter:blur(12px) saturate(1.2);border-bottom:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);box-shadow:0 6px 24px -22px rgba(0,0,0,.7)}
		.nav-inner{display:flex;align-items:center;justify-content:space-between;height:74px;gap:1.5rem}
		.nav-actions{display:flex;align-items:center;gap:1rem;flex:0 0 auto}
		/* Op mobiel (burger zichtbaar) de header-CTA verbergen zodat de burger nooit
		   van het scherm geduwd wordt; de sticky-CTA onderaan dekt de conversie. */
		@media(max-width:859px){.nav-actions .btn{display:none}}
		.logo{display:inline-flex;align-items:center;gap:.6rem;color:var(--c-ink);line-height:1}
		.logo-mark{display:inline-flex;flex:0 0 auto}
		.logo-img{height:44px;width:auto;display:block}
		@media(max-width:560px){.logo-img{height:36px}}
		.logo-text{display:inline-flex;flex-direction:column;gap:.16rem;min-width:0}
		.logo-word{font-family:var(--font-display);font-weight:700;font-size:1.34rem;letter-spacing:.01em}
		.logo-word span{color:var(--c-accent)}
		.logo-tag{font-size:.6rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;opacity:.6}
		.nav-links{display:none;gap:1.9rem;align-items:center}
		.nav-links a{font-weight:600;font-size:.98rem;letter-spacing:.02em;color:var(--c-ink);position:relative;padding:.35rem 0;transition:color .15s}
		.nav-links a::after{content:"";position:absolute;left:0;right:100%;bottom:0;height:2px;border-radius:2px;background:var(--c-cta);transition:right .22s ease}
		.nav-links a:hover{color:var(--c-cta)}
		.nav-links a:hover::after{right:0}
		@media(min-width:860px){.nav-links{display:flex}}
		/* hamburger + mobiel uitklapmenu */
		.nav-toggle{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;margin-right:-8px;border:0;background:transparent;cursor:pointer;color:var(--c-ink)}
		.nav-toggle-bars{position:relative;width:22px;height:16px}
		.nav-toggle-bars span{position:absolute;left:0;right:0;top:50%;height:2px;border-radius:2px;background:currentColor;transition:transform .25s ease,opacity .2s ease}
		.nav-toggle-bars span:nth-child(1){transform:translateY(-7px)}
		.nav-toggle-bars span:nth-child(3){transform:translateY(7px)}
		.nav.open .nav-toggle-bars span:nth-child(1){transform:translateY(0) rotate(45deg)}
		.nav.open .nav-toggle-bars span:nth-child(2){opacity:0}
		.nav.open .nav-toggle-bars span:nth-child(3){transform:translateY(0) rotate(-45deg)}
		@media(min-width:860px){.nav-toggle{display:none}}
		.nav-drawer{display:none;flex-direction:column;position:absolute;top:100%;left:0;right:0;background:var(--c-surface);border-top:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);box-shadow:0 22px 44px -22px rgba(0,0,0,.45);padding:.4rem 0 1.1rem;max-height:calc(100vh - 116px);overflow-y:auto}
		.nav.open .nav-drawer{display:flex}
		@media(min-width:860px){.nav.open .nav-drawer{display:none}}
		.nav-drawer>a{font-weight:600;font-size:1.08rem;color:var(--c-ink);padding:.9rem 22px;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 6%,transparent)}
		.nav-drawer>a:hover{color:var(--c-cta);background:color-mix(in srgb,var(--c-cta) 5%,transparent)}
		.nav-drawer-contact{display:flex;flex-direction:column;gap:.1rem;padding:.7rem 22px .2rem}
		.nav-drawer-contact a{display:inline-flex;align-items:center;gap:.55rem;padding:.45rem 0;font-weight:600;color:var(--c-ink)}
		.nav-drawer-contact a:hover{color:var(--c-cta)}
		.nav-drawer-contact .ic{width:16px;height:16px;color:var(--c-cta);flex:0 0 auto}
		.nav-drawer-cta{margin:.8rem 22px 0;text-align:center}
		/* compacter logo/CTA op kleine schermen zodat de balk niet propt */
		@media(max-width:560px){.logo-tag{display:none}.logo-word{font-size:1.16rem}.nav-actions .btn{padding:.7rem 1.05rem;font-size:.92rem}.nav-inner{gap:.8rem;height:64px}}
		/* hero */
		.hero{padding:72px 0 56px;background:linear-gradient(180deg,color-mix(in srgb,var(--c-accent) 10%,transparent),transparent)}
		.hero p.lead{font-size:1.2rem;max-width:38ch;margin:1rem 0 1.6rem}
		.hero-usps{list-style:none;margin:1.4rem 0 0;display:grid;gap:.5rem}
		.hero-usps li{padding-left:1.7rem;position:relative;font-weight:600}
		.hero-usps li:before{content:"✓";position:absolute;left:0;color:var(--c-primary);font-weight:800}
		/* Mobiel: minder top-ruimte onder de sticky header + hero-CTA's vol breedte,
		   gestapeld met ruimte ertussen (inline margin-left van de 2e knop overriden). */
		@media(max-width:640px){
			section{padding:38px 0}
			.hero{padding:34px 0 40px}
			.hero .btn{display:flex;width:100%;margin-left:0!important}
			.hero .btn+.btn{margin-top:.7rem}
		}
		/* Full-width hero-beeld: edge-to-edge foto met leesbare overlay, tekst eroverheen. */
		.hero--shot{position:relative;isolation:isolate;padding:0;min-height:clamp(460px,74vh,660px);display:flex;align-items:center;overflow:hidden;background:var(--c-ink)}
		.hero--shot .hero-bg{position:absolute;inset:0;z-index:-2}
		.hero--shot .hero-bg img{width:100%;height:100%;object-fit:cover;max-width:none}
		.hero--shot::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(90deg,rgba(8,10,16,.94) 0%,rgba(8,10,16,.74) 50%,rgba(8,10,16,.38) 100%)}
		.hero--shot .wrap{padding-top:2rem;padding-bottom:2rem}
		.hero--shot h1{max-width:15ch}
		.hero--shot h1,.hero--shot p.lead,.hero--shot .muted,.hero--shot .hero-usps li{text-shadow:0 2px 18px rgba(0,0,0,.6)}
		.hero--shot h1,.hero--shot p.lead{color:#fff}
		.hero--shot p.lead{color:rgba(255,255,255,.92)}
		.hero--shot .muted{color:rgba(255,255,255,.82)!important}
		.hero--shot .hero-usps li{color:#fff}
		.hero--shot .hero-usps li:before{color:#fff}
		/* Split-hero: tekst links, zwevend product-mockup rechts. */
		.hero--shot .wrap{display:grid;grid-template-columns:1fr;gap:2.4rem;align-items:center}
		@media(min-width:980px){.hero--shot.has-widget .wrap{grid-template-columns:1.04fr .96fr}}
		.hero-text{min-width:0}
		.hero-aside{display:none}
		@media(min-width:980px){.hero--shot.has-widget .hero-aside{display:block}}
		/* afspraak-planner mockup (illustratief) */
		.hero-mock{background:#fff;color:var(--c-ink);border-radius:calc(var(--radius) + 8px);box-shadow:0 34px 80px -22px rgba(0,0,0,.65);padding:1.3rem;max-width:380px;margin-left:auto;transform:rotate(-1.3deg)}
		.hm-head{display:flex;align-items:center;gap:.5rem;font-size:.74rem;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.06em}
		.hm-head .dot{width:8px;height:8px;border-radius:50%;background:var(--c-cta);box-shadow:0 0 0 3px color-mix(in srgb,var(--c-cta) 22%,transparent)}
		.hm-title{font-weight:800;font-size:1.15rem;margin:.5rem 0 .9rem;text-shadow:none}
		.hm-days{display:flex;gap:.35rem;margin-bottom:.7rem}
		.hm-days span{flex:1;text-align:center;font-size:.72rem;font-weight:700;padding:.45rem 0;border-radius:8px;background:var(--c-bg);color:var(--c-muted)}
		.hm-days .on{background:var(--c-primary);color:#fff}
		.hm-slots{display:grid;grid-template-columns:repeat(4,1fr);gap:.35rem;margin-bottom:1rem}
		.hm-slots span{text-align:center;font-size:.78rem;font-weight:700;padding:.5rem 0;border-radius:8px;border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent)}
		.hm-slots .sel{background:color-mix(in srgb,var(--c-accent) 18%,transparent);border-color:var(--c-accent);color:var(--c-ink)}
		.hm-svc{display:grid;gap:.4rem;margin-bottom:1rem}
		.hm-svc div{display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;padding:.55rem .7rem;border-radius:8px;background:var(--c-bg)}
		.hm-svc .sel{background:var(--c-primary);color:#fff}
		.hm-btn{width:100%;background:var(--c-primary);color:#fff;font-weight:800;border:0;border-radius:10px;padding:.8rem;font-size:.95rem;cursor:pointer}
		/* footer */
		footer{background:var(--c-footer-bg,var(--c-ink));color:#fff;padding:48px 0 28px;margin-top:24px}
		footer a{color:rgba(255,255,255,.8)}footer a:hover{color:#fff}
		.foot-grid{display:grid;gap:1.6rem;margin-bottom:1.8rem}
		@media(min-width:760px){.foot-grid{grid-template-columns:2fr 1fr 1.6fr 1fr}}
		.foot-bottom{border-top:1px solid rgba(255,255,255,.15);padding-top:1rem;font-size:.85rem;color:rgba(255,255,255,.6);display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem;align-items:center}
		.endorsement{display:inline-flex;align-items:center;gap:.55rem}
		.endorsement a{color:rgba(255,255,255,.75)}.endorsement a:hover{color:#fff}
		.endorsement .diamond{color:var(--c-accent);font-size:.7rem}
		.endorse-label{color:rgba(255,255,255,.55);font-size:.8rem}
		.endorse-badge{display:inline-flex;align-items:center;background:#fff;border-radius:10px;padding:.45rem .7rem;box-shadow:0 6px 18px -10px rgba(0,0,0,.6);transition:transform .15s}
		.endorse-badge:hover{transform:translateY(-1px)}
		.endorse-badge img{height:34px;width:auto;display:block}
		.foot-endorse{display:inline-flex;align-items:center;gap:.65rem;text-decoration:none}
		.foot-endorse-label{color:rgba(255,255,255,.6);font-size:.82rem}
		.foot-endorse-badge{display:inline-flex;background:#fff;border-radius:12px;padding:.3rem .45rem;box-shadow:0 8px 22px -10px rgba(0,0,0,.7);transition:transform .15s ease,box-shadow .2s ease}
		.foot-endorse:hover .foot-endorse-badge{transform:translateY(-2px);box-shadow:0 13px 30px -10px rgba(0,0,0,.85)}
		.foot-endorse-badge img{height:64px;width:auto;display:block;border-radius:4px}
		.foot-legal{display:inline-flex;gap:1rem;flex-wrap:wrap}
		.foot-legal a{color:rgba(255,255,255,.6);font-size:.82rem}.foot-legal a:hover{color:#fff}
		.foot-links{display:flex;gap:.6rem 1.4rem;flex-wrap:wrap;padding:1.2rem 0;margin-bottom:.4rem;border-top:1px solid rgba(255,255,255,.12)}
		.foot-links a{color:rgba(255,255,255,.8);font-size:.9rem;font-weight:600}.foot-links a:hover{color:#fff}
		/* form */
		.field{margin-bottom:.9rem}
		.field label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.3rem}
		.field input,.field textarea{width:100%;padding:.7rem .9rem;border:1px solid color-mix(in srgb,var(--c-ink) 18%,transparent);border-radius:10px;font:inherit;background:#fff}
		.field input:focus,.field textarea:focus{outline:2px solid var(--c-primary);border-color:transparent}
		.hp{position:absolute;left:-9999px}
		.errors{background:#fef2f2;color:#b91c1c;border-radius:10px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.9rem}
		.prose p{margin:0 0 1rem}.prose h2{margin:1.6rem 0 .6rem}.prose h3{margin:1.2rem 0 .4rem}.prose ul{margin:0 0 1rem 1.2rem}
	</style>
	{!! $site->jsonLd() !!}
	@stack('head')
</head>
<body>
	<a class="skip-link" href="#main">Naar inhoud</a>
	@if (! $site->isLive())
		<div style="background:#1c1917;color:#fbbf24;text-align:center;font-size:.8rem;padding:.4rem">
			PREVIEW · concept "{{ $site->key }}" · nog niet live (geen domein gekoppeld)
		</div>
	@endif

	@include('channels.partials.nav')

	<main id="main">
		@yield('content')
	</main>

	@include('channels.partials.footer')

	{{-- Sticky mobiele CTA: verschijnt na het scrollen voorbij de hero. --}}
	<a href="#contact" class="sticky-cta">Gratis voorbeeld aanvragen</a>
	<script>
	(function () {
		var el = document.querySelector('.sticky-cta');
		if (el) {
			var upd = function () { el.classList.toggle('show', window.scrollY > 560); };
			window.addEventListener('scroll', upd, { passive: true });
			upd();
		}

		// Sectie-zichtbaarheid meten (scroll-diepte): één event per sectie zodra
		// hij 40% in beeld komt. Voedt de funnel-analyse (waar haakt men af).
		if ('IntersectionObserver' in window && window.bgTrack) {
			var seen = {};
			var obs = new IntersectionObserver(function (entries) {
				entries.forEach(function (e) {
					if (!e.isIntersecting) return;
					var name = e.target.getAttribute('data-section');
					if (name && !seen[name]) { seen[name] = 1; window.bgTrack('section_view', { section: name }); }
				});
			}, { threshold: 0.4 });
			document.querySelectorAll('[data-section]').forEach(function (s) { obs.observe(s); });
		}
	})();
	</script>
</body>
</html>
