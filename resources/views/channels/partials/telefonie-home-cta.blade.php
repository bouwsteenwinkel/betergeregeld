@php
    /** @var \App\Support\ChannelSite $site */
    // CTA naar de AI-telefoniepagina op de verkoop-homepage van een kanaal (16-09-2026,
    // eerst apotheek). Geen banner maar een "inkomend gesprek": links de belofte, rechts
    // een telefoonscherm waarop de assistent opneemt en een kort gesprek zich vanzelf
    // uittikt (uit config 'home_cta.gesprek'; zonder JavaScript of bij verminderde
    // beweging staat het hele gesprek er meteen). Alleen zichtbaar als het kanaal een
    // telefoniepagina heeft; de demoknop alleen als er een demonummer is.
    $tc = \App\Support\TelefonieConfig::for($site);
    $hc = (array) ($tc['home_cta'] ?? []);
@endphp
@if ($tc && $hc)
@php
    $demo    = trim((string) ($tc['demo_nummer'] ?? ''));
    $demoTel = preg_replace('/[^0-9+]/', '', $demo);
    $pad     = $site->url('ai-telefonie-' . $site->key);
    $lijnen  = array_values((array) ($hc['gesprek'] ?? []));
@endphp
<section class="belcta" data-section="ai-telefonie-cta" aria-labelledby="belcta-kop">
    <div class="wrap">
        <div class="belcta-grid">
            <div class="belcta-tekst">
                <span class="belcta-kicker"><span class="belcta-dot"></span> {{ $hc['kicker'] ?? 'Nieuw: AI-telefonie' }}</span>
                <h2 id="belcta-kop">{{ $hc['kop'] ?? 'De telefoon gaat. Niemand hoeft op te nemen.' }}</h2>
                <p class="belcta-lead">{{ $hc['lead'] ?? '' }}</p>
                <div class="belcta-knoppen">
                    @if ($demo)
                        <a href="tel:{{ $demoTel }}" class="btn belcta-bel"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.9 2z"/></svg> {{ $hc['bel_label'] ?? 'Bel de demo' }}: {{ $demo }}</a>
                    @endif
                    <a href="{{ $pad }}" class="btn belcta-meer">{{ $hc['meer_label'] ?? 'Zo werkt het' }} →</a>
                </div>
                @if (! empty($hc['noot']))<p class="belcta-noot">{{ $hc['noot'] }}</p>@endif
            </div>

            <div class="belcta-toestel" aria-hidden="true">
                <div class="tel">
                    <div class="tel-top"><span class="tel-tijd">{{ $hc['tijd'] ?? '08:12' }}</span><span class="tel-status">●●● ▲</span></div>
                    <div class="tel-bel">
                        <div class="tel-ring"><span></span><span></span><span></span>
                            <div class="tel-avatar"><svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.9 2z"/></svg></div>
                        </div>
                        <div class="tel-naam">{{ $hc['toestel_naam'] ?? ($tc['demo_naam'] ?? 'Digitale assistent') }}</div>
                        <div class="tel-sub"><span class="tel-live"></span> <span data-tel-sub>{{ $hc['toestel_sub'] ?? 'Inkomend gesprek · de assistent neemt op' }}</span></div>
                    </div>
                    <div class="tel-gesprek" data-tel-gesprek>
                        @foreach ($lijnen as $i => $l)
                            <div class="tel-bubbel {{ ($l[0] ?? 'a') === 'b' ? 'is-beller' : 'is-assistent' }}" data-tekst="{{ $l[1] ?? '' }}">{{ $l[1] ?? '' }}</div>
                        @endforeach
                    </div>
                    <div class="tel-onder"><span>Er wordt niet opgenomen · samenvatting per mail</span></div>
                </div>
            </div>
        </div>
    </div>
</section>
@once
<style>
.belcta{padding:56px 0;background:linear-gradient(135deg,var(--c-primary) 0%,color-mix(in srgb,var(--c-primary) 70%,#0b1020) 100%);color:#fff;overflow:hidden;position:relative}
.belcta::before{content:"";position:absolute;inset:-40% auto auto -10%;width:60%;aspect-ratio:1;border-radius:50%;background:radial-gradient(circle,color-mix(in srgb,var(--c-accent) 35%,transparent),transparent 65%);pointer-events:none}
.belcta-grid{display:grid;gap:2.4rem;align-items:center;position:relative}
@media(min-width:860px){.belcta-grid{grid-template-columns:1.15fr .85fr}}
.belcta-kicker{display:inline-flex;align-items:center;gap:.5rem;font-size:.78rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:color-mix(in srgb,var(--c-accent) 80%,#fff);margin-bottom:1rem}
.belcta-dot{width:9px;height:9px;border-radius:50%;background:var(--c-accent);box-shadow:0 0 0 0 color-mix(in srgb,var(--c-accent) 60%,transparent);animation:belcta-puls 2s ease-out infinite}
@keyframes belcta-puls{0%{box-shadow:0 0 0 0 color-mix(in srgb,var(--c-accent) 60%,transparent)}100%{box-shadow:0 0 0 12px transparent}}
.belcta h2{color:#fff;font-size:clamp(1.7rem,3.6vw,2.5rem);margin-bottom:.8rem}
.belcta-lead{font-size:1.08rem;color:rgba(255,255,255,.82);max-width:54ch;margin:0 0 1.5rem}
.belcta-knoppen{display:flex;flex-wrap:wrap;gap:.8rem}
.belcta-bel{background:var(--c-cta);color:var(--c-on-cta);font-size:1.05rem;padding:.9rem 1.5rem;gap:.55rem}
.belcta-meer{background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.35)}
.belcta-meer:hover{background:rgba(255,255,255,.2)}
.belcta-noot{margin:1rem 0 0;font-size:.88rem;color:rgba(255,255,255,.65)}
/* het toestel */
.belcta-toestel{display:flex;justify-content:center}
.tel{width:min(320px,100%);background:#0b0f1a;border-radius:34px;padding:14px 14px 16px;box-shadow:0 40px 80px -30px rgba(0,0,0,.8),inset 0 0 0 2px rgba(255,255,255,.08);transform:rotate(3deg);color:#e8ecf3;font-size:.9rem}
@media(min-width:860px){.tel{transform:rotate(4deg) translateY(6px)}}
.tel-top{display:flex;justify-content:space-between;font-size:.75rem;color:rgba(255,255,255,.6);padding:4px 12px 0}
.tel-bel{text-align:center;padding:22px 10px 14px}
.tel-ring{position:relative;width:84px;height:84px;margin:0 auto 12px}
.tel-ring span{position:absolute;inset:0;border-radius:50%;border:2px solid color-mix(in srgb,var(--c-accent) 70%,#fff);opacity:0;animation:tel-ring 2.4s ease-out infinite}
.tel-ring span:nth-child(2){animation-delay:.8s}.tel-ring span:nth-child(3){animation-delay:1.6s}
@keyframes tel-ring{0%{transform:scale(.7);opacity:.9}100%{transform:scale(1.9);opacity:0}}
.tel-avatar{position:absolute;inset:12px;border-radius:50%;background:var(--c-accent);color:var(--c-on-accent,#fff);display:flex;align-items:center;justify-content:center}
.tel-naam{font-weight:800;font-size:1.1rem;font-family:var(--font-display)}
.tel-sub{font-size:.8rem;color:rgba(255,255,255,.65);margin-top:.2rem;display:flex;align-items:center;justify-content:center;gap:.4rem}
.tel-live{width:8px;height:8px;border-radius:50%;background:#3ddc84;box-shadow:0 0 8px #3ddc84}
.tel-gesprek{display:flex;flex-direction:column;gap:7px;padding:6px 6px 10px;min-height:190px}
.tel-bubbel{max-width:86%;padding:.5rem .75rem;border-radius:14px;line-height:1.35;font-size:.86rem}
.tel-bubbel.is-beller{align-self:flex-end;background:#2b6cf6;color:#fff;border-bottom-right-radius:4px}
.tel-bubbel.is-assistent{align-self:flex-start;background:#1f2637;color:#e8ecf3;border-bottom-left-radius:4px}
.tel-bubbel.is-wacht{opacity:0;transform:translateY(6px)}
.tel-bubbel.is-typt::after{content:"…";animation:tel-typt 1s steps(3) infinite}
@keyframes tel-typt{to{opacity:.3}}
.tel-onder{text-align:center;font-size:.7rem;color:rgba(255,255,255,.45);padding-top:4px;border-top:1px solid rgba(255,255,255,.08)}
@media(prefers-reduced-motion:reduce){.belcta-dot,.tel-ring span{animation:none}.tel-ring span{opacity:.35;transform:scale(1.2)}.tel-bubbel.is-wacht{opacity:1;transform:none}}
</style>
<script>
(function(){
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var box=document.querySelector('[data-tel-gesprek]'); if(!box) return;
  var bubbels=Array.prototype.slice.call(box.querySelectorAll('.tel-bubbel')); if(!bubbels.length) return;
  var sub=document.querySelector('[data-tel-sub]'), subTekst=sub?sub.textContent:'';
  function reset(){ bubbels.forEach(function(b){ b.classList.add('is-wacht'); b.classList.remove('is-typt'); b.textContent=b.getAttribute('data-tekst'); }); if(sub) sub.textContent=subTekst; }
  function speel(){
    reset(); var i=0;
    function stap(){
      if(i>=bubbels.length){ if(sub) sub.textContent='Gesprek afgerond · samenvatting onderweg'; setTimeout(speel, 5200); return; }
      var b=bubbels[i], tekst=b.getAttribute('data-tekst'), assistent=b.classList.contains('is-assistent');
      b.classList.remove('is-wacht'); b.style.transition='opacity .25s,transform .25s';
      if(assistent){ b.textContent=''; b.classList.add('is-typt'); setTimeout(function(){ b.classList.remove('is-typt'); b.textContent=tekst; i++; setTimeout(stap, 1400); }, 900); }
      else { b.textContent=tekst; i++; setTimeout(stap, 1500); }
    }
    setTimeout(stap, 900);
  }
  var gestart=false;
  function start(){ if(gestart) return; gestart=true; speel(); }
  if('IntersectionObserver' in window){ var io=new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ start(); io.disconnect(); } }); },{threshold:.35}); io.observe(box); }
  else start();
})();
</script>
@endonce
@endif
