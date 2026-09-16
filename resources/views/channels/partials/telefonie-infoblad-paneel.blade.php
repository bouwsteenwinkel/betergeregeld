@php
    // Het spiekbriefje als paneel op de pagina zelf (16-09-2026): op desktop een lade aan de
    // rechterkant (over de hero-afbeelding heen), op mobiel een sheet van onderen. Zelfde
    // inhoud als de PDF (config/telefonie_infobladen.php), gerenderd als HTML in de kleuren
    // van het kanaal, met onderin "Bel de demo" en "Download als PDF". Native <dialog>:
    // Escape, focus en achtergrond regelt de browser. Verwacht $blad, $url (PDF), $demo,
    // $demoTel. De knop (telefonie-infoblad-knop) opent dit paneel; zonder JavaScript opent
    // de knop gewoon de PDF.
@endphp
<dialog class="spiek-paneel" id="spiek-paneel" aria-labelledby="spiek-titel">
    <div class="sp-kop">
        <div>
            <div class="sp-kicker">Spiekbriefje bij de demo</div>
            <h2 id="spiek-titel">{{ $blad['titel'] }}</h2>
        </div>
        <button type="button" class="sp-sluit" data-spiek-sluit aria-label="Sluiten"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    </div>
    <div class="sp-body">
        <p class="sp-intro">{{ $blad['intro'] }}</p>

        <h3>Goed om te weten</h3>
        <dl class="sp-feiten">
            @foreach ($blad['feiten'] as $f)<dt>{{ $f[0] }}</dt><dd>{{ $f[1] }}</dd>@endforeach
        </dl>

        <h3>{{ $blad['data_titel'] }}</h3>
        <div class="sp-scroll"><table class="sp-tabel">
            <tr>@foreach ($blad['data_kop'] as $k)<th>{{ $k }}</th>@endforeach</tr>
            @foreach ($blad['data'] as $r)<tr>@foreach ($r as $i => $cel)<td class="{{ $i === 0 ? 'sp-sleutel' : '' }}">{{ $cel }}</td>@endforeach</tr>@endforeach
        </table></div>

        <h3>Probeer dit, en dit hoor je dan</h3>
        <ol class="sp-probeer">
            @foreach ($blad['probeer'] as $p)<li><strong>{{ $p[0] }}</strong><span>{{ $p[1] }}</span></li>@endforeach
        </ol>

        <div class="sp-twee">
            <div class="sp-box sp-niet"><h3>Wat hij bewust níét doet</h3><ul>@foreach ($blad['niet'] as $n)<li>{{ $n }}</li>@endforeach</ul></div>
            <div class="sp-box sp-tip"><h3>Tips voor een goed gesprek</h3><ul>@foreach ($blad['tips'] as $t)<li>{{ $t }}</li>@endforeach</ul></div>
        </div>
        <p class="sp-klein">Er wordt niet opgenomen; er wordt een uitgeschreven samenvatting gemaakt. {{ $blad['bedrijf'] }} en de genoemde personen zijn verzonnen.</p>
    </div>
    <div class="sp-voet">
        <a href="tel:{{ $demoTel }}" class="btn">Bel de demo: {{ $demo }}</a>
        <a href="{{ $url }}" class="btn btn-ghost" target="_blank" rel="noopener">Download als PDF</a>
    </div>
</dialog>
<style>
.spiek-paneel{position:fixed;inset:0 0 0 auto;width:min(560px,100vw);max-width:100vw;height:100dvh;max-height:100dvh;margin:0;padding:0;border:0;background:var(--c-surface);color:var(--c-ink);box-shadow:-24px 0 60px -20px rgba(0,0,0,.45);display:none;flex-direction:column;overflow:hidden;font-size:.95rem;line-height:1.5}
.spiek-paneel[open]{display:flex;animation:sp-in .32s cubic-bezier(.2,.8,.2,1)}
.spiek-paneel::backdrop{background:color-mix(in srgb,var(--c-ink) 45%,transparent);backdrop-filter:blur(2px)}
@keyframes sp-in{from{transform:translateX(40px);opacity:0}to{transform:none;opacity:1}}
.sp-kop{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.3rem 1.5rem 1rem;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);background:#fff8c2;color:#1c1a12}
.sp-kicker{font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:color-mix(in srgb,var(--c-cta) 70%,#000);margin-bottom:.25rem}
.sp-kop h2{font-size:1.15rem;line-height:1.25;margin:0;font-family:var(--font-display)}
.sp-sluit{flex:0 0 auto;width:40px;height:40px;border-radius:50%;border:0;background:color-mix(in srgb,#000 8%,transparent);color:#1c1a12;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
.sp-sluit:hover,.sp-sluit:focus-visible{background:color-mix(in srgb,#000 16%,transparent);outline:none}
.sp-body{overflow-y:auto;padding:1.1rem 1.5rem 1.4rem;flex:1 1 auto;-webkit-overflow-scrolling:touch}
.sp-body h3{font-size:.76rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--c-primary);margin:1.4rem 0 .5rem}
.sp-intro{margin:0;color:color-mix(in srgb,var(--c-ink) 80%,transparent)}
.sp-feiten{display:grid;grid-template-columns:max-content 1fr;gap:.35rem .9rem;margin:0;font-size:.92rem}
.sp-feiten dt{font-weight:700;color:color-mix(in srgb,var(--c-ink) 70%,transparent)}
.sp-feiten dd{margin:0}
.sp-scroll{overflow-x:auto}
.sp-tabel{border-collapse:collapse;width:100%;font-size:.88rem}
.sp-tabel th{text-align:left;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:color-mix(in srgb,var(--c-ink) 55%,transparent);padding:0 .6rem .35rem 0;border-bottom:2px solid var(--c-ink)}
.sp-tabel td{padding:.45rem .6rem .45rem 0;vertical-align:top;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent)}
.sp-tabel td.sp-sleutel{font-weight:700;white-space:nowrap}
.sp-probeer{list-style:none;margin:0;padding:0;counter-reset:sp}
.sp-probeer li{display:grid;grid-template-columns:1.6rem 1fr;gap:.2rem .5rem;padding:.55rem 0;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);counter-increment:sp}
.sp-probeer li::before{content:counter(sp);grid-row:1/3;font-weight:800;color:var(--c-cta);font-family:var(--font-display)}
.sp-probeer strong{grid-column:2}
.sp-probeer span{grid-column:2;color:color-mix(in srgb,var(--c-ink) 72%,transparent);font-size:.9rem}
.sp-twee{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-top:1.2rem}
.sp-box{background:var(--c-tint,var(--c-bg));border-radius:calc(var(--radius) - 2px);padding:.8rem 1rem;font-size:.88rem}
.sp-box h3{margin-top:0}
.sp-niet{border-left:3px solid #b03a2e}.sp-niet h3{color:#b03a2e}
.sp-tip{border-left:3px solid var(--c-cta)}
.sp-box ul{margin:0;padding-left:1.1rem}.sp-box li{margin:.2rem 0}
.sp-klein{font-size:.8rem;color:color-mix(in srgb,var(--c-ink) 55%,transparent);margin:1rem 0 0}
.sp-voet{display:flex;flex-wrap:wrap;gap:.6rem;padding:.9rem 1.5rem calc(.9rem + env(safe-area-inset-bottom,0px));border-top:1px solid color-mix(in srgb,var(--c-ink) 10%,transparent);background:var(--c-surface)}
.sp-voet .btn{flex:1 1 auto;justify-content:center}
@media (max-width:760px){
  .spiek-paneel{inset:auto 0 0 0;width:100vw;height:auto;max-height:92dvh;border-radius:18px 18px 0 0;box-shadow:0 -24px 60px -20px rgba(0,0,0,.5)}
  .spiek-paneel[open]{animation:sp-up .34s cubic-bezier(.2,.8,.2,1)}
  .sp-kop{padding-top:1.5rem}
  .sp-kop::before{content:"";position:absolute;left:50%;top:.55rem;width:44px;height:5px;margin-left:-22px;border-radius:3px;background:color-mix(in srgb,#000 22%,transparent)}
  .spiek-paneel{position:fixed}.sp-kop{position:relative}
  .sp-twee{grid-template-columns:1fr}
  .sp-feiten{grid-template-columns:1fr;gap:.1rem 0}.sp-feiten dd{margin-bottom:.45rem}
}
@keyframes sp-up{from{transform:translateY(40px);opacity:0}to{transform:none;opacity:1}}
@media (prefers-reduced-motion:reduce){.spiek-paneel[open]{animation:none!important}}
</style>
<script>
(function(){
  var d=document.getElementById('spiek-paneel'); if(!d||!d.showModal) return;
  document.querySelectorAll('a.spiek').forEach(function(a){
    a.addEventListener('click',function(e){ e.preventDefault(); d.showModal(); d.querySelector('.sp-body').scrollTop=0; });
  });
  d.querySelectorAll('[data-spiek-sluit]').forEach(function(b){ b.addEventListener('click',function(){ d.close(); }); });
  // Klik op de achtergrond sluit; een klik ín het paneel niet.
  d.addEventListener('click',function(e){ if(e.target===d) d.close(); });
})();
</script>
