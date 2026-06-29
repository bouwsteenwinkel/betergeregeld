{{-- Bespoke Salon Lumière-stijl, gescoped onder .lum zodat het niet botst met de
     generieke layout-CSS. Eénmalig in de <head> gepusht. Kleuren/rondingen komen
     uit de site-thema-tokens (--c-*), de serif-kop uit het thema-font_url. --}}
@once
@push('head')
<style>
.lum h1,.lum h2,.lum h3{font-family:'Cormorant Garamond',Georgia,serif;font-weight:600;letter-spacing:.01em;line-height:1.1}
.lum .eyebrow{background:none;padding:0;text-transform:uppercase;letter-spacing:.28em;font-size:11px;color:var(--c-primary);font-weight:500;margin-bottom:14px;border-radius:0}
.lum .btn{border-radius:999px;font-weight:500;letter-spacing:.03em}
.lum-sec-head{text-align:center;max-width:60ch;margin:0 auto 46px}
.lum-sec-head h2{font-size:clamp(32px,4.5vw,46px);margin-bottom:12px}
.lum-sec-head p{color:var(--c-muted)}

.lum-hero{position:relative;padding:92px 0 104px;text-align:center;overflow:hidden}
.lum-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(900px 420px at 50% -10%,color-mix(in srgb,var(--c-primary) 18%,transparent),transparent 70%),linear-gradient(180deg,#fff,var(--c-bg));z-index:-1}
.lum-hero h1{font-size:clamp(42px,7vw,72px);max-width:14ch;margin:0 auto 18px}
.lum-hero p.lead{font-size:19px;color:var(--c-muted);max-width:54ch;margin:0 auto 30px;font-weight:300}
.lum-hero .cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

.lum-price{max-width:680px;margin:0 auto;display:grid;gap:6px}
.lum-price .row{display:flex;align-items:baseline;gap:14px;padding:16px 4px;border-bottom:1px dashed color-mix(in srgb,var(--c-ink) 12%,transparent)}
.lum-price .nm{font-family:'Cormorant Garamond',serif;font-size:23px;font-weight:600}
.lum-price .ds{color:var(--c-muted);font-size:14px;flex:1}
.lum-price .pr{font-weight:500;white-space:nowrap;color:var(--c-primary)}

.lum-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:680px){.lum-gallery{grid-template-columns:repeat(2,1fr)}}
.lum-gallery .tile{position:relative;aspect-ratio:3/4;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;color:#fff;background:linear-gradient(160deg,var(--c-primary),var(--c-accent))}
.lum-gallery .tile:nth-child(3n){background:linear-gradient(160deg,var(--c-ink),var(--c-primary))}
.lum-gallery .tile span{padding:14px 16px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;background:linear-gradient(0deg,rgba(0,0,0,.35),transparent);width:100%}

.lum-about{display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}
@media(max-width:820px){.lum-about{grid-template-columns:1fr}}
.lum-about .visual{aspect-ratio:4/5;border-radius:var(--radius);background:linear-gradient(150deg,var(--c-primary),var(--c-accent));position:relative}
.lum-about .visual::after{content:"L";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:200px;color:rgba(255,255,255,.22)}
.lum-stats{display:flex;gap:36px;margin-top:26px}
.lum-stats .v{font-family:'Cormorant Garamond',serif;font-size:38px;color:var(--c-primary);line-height:1}
.lum-stats .l{font-size:13px;color:var(--c-muted)}

.lum-contact{background:var(--c-ink);color:#fff;border-radius:calc(var(--radius)*1.6);padding:52px;display:grid;grid-template-columns:1fr 1fr;gap:46px}
@media(max-width:780px){.lum-contact{grid-template-columns:1fr;padding:34px}}
.lum-contact h2{color:#fff;font-size:38px}
.lum-contact a{color:#fff}
.lum-hours{display:grid;gap:6px}
.lum-hours .r{display:flex;justify-content:space-between;font-size:14px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.85)}
</style>
@endpush
@endonce
