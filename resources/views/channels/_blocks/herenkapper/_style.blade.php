{{-- Bespoke Brink Barbers-stijl, gescoped onder .brink. Donker thema: de site
     gebruikt ink = lichte tekst + footer_bg apart (zie seeder). --}}
@once
@push('head')
<style>
.brink h1,.brink h2,.brink h3{font-family:'Oswald',Impact,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:.02em;line-height:1.05;color:#fff}
.brink .eyebrow{background:none;padding:0;border-radius:0;text-transform:uppercase;letter-spacing:.3em;font-size:11px;color:var(--c-primary);font-family:'Oswald',sans-serif;margin-bottom:16px}
.brink .btn{font-family:'Oswald',sans-serif;text-transform:uppercase;letter-spacing:.06em;border-radius:var(--radius)}
.brink-sec-head{text-align:center;max-width:60ch;margin:0 auto 46px}
.brink-sec-head h2{font-size:clamp(32px,5vw,52px);margin-bottom:12px}
.brink-sec-head p{color:var(--c-muted)}

.brink-hero{position:relative;padding:104px 0 116px;text-align:center;overflow:hidden;border-bottom:1px solid rgba(255,255,255,.08)}
.brink-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(800px 400px at 50% 0,color-mix(in srgb,var(--c-primary) 16%,transparent),transparent 70%);z-index:-1}
.brink-hero h1{font-size:clamp(46px,8vw,88px);max-width:16ch;margin:0 auto 16px}
.brink-hero p.lead{font-size:18px;color:#c5bdb0;max-width:50ch;margin:0 auto 30px}
.brink-hero .cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

.brink-price{max-width:680px;margin:0 auto;display:grid;gap:4px}
.brink-price .row{display:flex;align-items:baseline;gap:14px;padding:18px 4px;border-bottom:1px solid rgba(255,255,255,.1)}
.brink-price .nm{font-family:'Oswald',sans-serif;text-transform:uppercase;font-size:19px;letter-spacing:.02em;color:#fff}
.brink-price .ds{color:var(--c-muted);font-size:14px;flex:1}
.brink-price .pr{font-family:'Oswald',sans-serif;font-size:19px;color:var(--c-primary);white-space:nowrap}

.brink-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
@media(max-width:680px){.brink-gallery{grid-template-columns:repeat(2,1fr)}}
.brink-gallery .tile{position:relative;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;display:flex;align-items:flex-end;background:linear-gradient(160deg,#2a2419,#15120d);border:1px solid rgba(255,255,255,.08)}
.brink-gallery .tile:nth-child(3n){background:linear-gradient(160deg,var(--c-primary),#5a4516)}
.brink-gallery .tile span{padding:12px 14px;font-family:'Oswald',sans-serif;font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#fff;width:100%;background:linear-gradient(0deg,rgba(0,0,0,.5),transparent)}

.brink-about{display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}
@media(max-width:820px){.brink-about{grid-template-columns:1fr}}
.brink-about .visual{aspect-ratio:4/5;border-radius:var(--radius);background:linear-gradient(150deg,#2a2419,var(--c-primary));position:relative;border:1px solid rgba(255,255,255,.1)}
.brink-about .visual::after{content:"✂";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:120px;color:rgba(0,0,0,.3)}
.brink-stats{display:flex;gap:36px;margin-top:26px}
.brink-stats .v{font-family:'Oswald',sans-serif;font-size:40px;color:var(--c-primary);line-height:1}
.brink-stats .l{font-size:12px;color:var(--c-muted);text-transform:uppercase;letter-spacing:.08em}

.brink-contact{background:#0e0c08;border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:52px;display:grid;grid-template-columns:1fr 1fr;gap:46px}
@media(max-width:780px){.brink-contact{grid-template-columns:1fr;padding:34px}}
.brink-contact h2{font-size:40px}
.brink-contact .eyebrow{color:var(--c-primary)}
.brink-hours{display:grid;gap:4px}
.brink-hours .r{display:flex;justify-content:space-between;font-size:14px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.1);color:#c5bdb0}
</style>
@endpush
@endonce
