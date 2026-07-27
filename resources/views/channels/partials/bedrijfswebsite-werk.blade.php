{{--
  Etalage "Dit hebben we gemaakt": horizontale schuifrij met echte sites.

  Elke kaart toont minimaal drie schermafdrukken van dezelfde site die om de paar seconden
  overvloeien. Inhoud staat in config/bedrijfswebsite_portfolio.php; de beelden komen uit
  public/channel-media/bedrijfswebsite/ (zie scripts/site-screenshots.mjs).

  Zonder JavaScript of met "verminderde beweging" aan blijft het eerste beeld gewoon staan —
  de kaart is dan nog steeds compleet. De rij zelf schuift met de muis, veegt op mobiel en is
  met tab/pijltjes te bedienen.
--}}
@php
    $werk = (array) config('bedrijfswebsite_portfolio.items', []);
    $werkTitel = (string) config('bedrijfswebsite_portfolio.titel', 'Dit hebben we gemaakt');
    $werkIntro = (string) config('bedrijfswebsite_portfolio.intro', '');
    $werkMap = '/channel-media/bedrijfswebsite/';
@endphp
@if ($werk)
<section id="werk" aria-labelledby="werk-titel" style="padding: 72px 0; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; border-top: 1px solid #E5E3DF;">
  <div style="max-width: 1120px; margin: 0 auto; padding: 0 24px;">
    <h2 id="werk-titel" style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 14px;">{{ $werkTitel }}</h2>
    @if ($werkIntro !== '')
      <p style="font-size: 18px; line-height: 1.5; color: #4A4844; margin: 0 0 32px; max-width: 52ch;">{{ $werkIntro }}</p>
    @endif
  </div>

  <div data-werk-rij style="display: flex; gap: 24px; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin; padding: 4px 24px 20px; max-width: 1120px; margin: 0 auto;">
    @foreach ($werk as $item)
      @php $beelden = (array) ($item['beelden'] ?? []); @endphp
      @continue (! $beelden)
      {{-- Kaartbreedte: op de brede rij passen er drieënhalf naast elkaar, zodat je meteen ziet dat
           er nog meer naast staat. (100% - 3 tussenruimtes) / 3.5. Op smalle schermen zou die som
           een postzegel opleveren, vandaar de ondergrens van 280px. --}}
      <article data-werk-kaart style="flex: 0 0 max(280px, min(86vw, calc((100% - 72px) / 3.5))); scroll-snap-align: start; background: #fff; border: 1px solid #E5E3DF; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;">
        {{-- Kader is platter (16:9) dan de afdruk (16:10) en het beeld hangt bovenaan: de onderste
             strook valt weg. Dat scheelt de cookie-icoontjes die op sommige sites vastgeplakt
             onderin blijven staan. --}}
        <div style="position: relative; aspect-ratio: 16 / 9; background: #F1EFEB; overflow: hidden;">
          @foreach ($beelden as $i => $beeld)
            @php $slot = $werkMap . $beeld['slot']; @endphp
            <img data-werk-beeld
                 class="{{ $i === 0 ? 'werk-aan' : '' }}"
                 src="{{ $slot }}-800.webp"
                 srcset="{{ $slot }}-480.webp 480w, {{ $slot }}-800.webp 800w, {{ $slot }}-1200.webp 1200w"
                 sizes="(max-width: 600px) 280px, 290px"
                 alt="{{ $beeld['alt'] ?? ($item['naam'] ?? '') }}"
                 width="1280" height="800"
                 loading="lazy" decoding="async"
                 style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: top center; opacity: {{ $i === 0 ? '1' : '0' }}; transition: opacity .5s ease;">
          @endforeach
          <div aria-hidden="true" style="position: absolute; right: 12px; bottom: 12px; display: flex; gap: 6px;">
            @foreach ($beelden as $i => $beeld)
              <span data-werk-punt class="{{ $i === 0 ? 'werk-aan' : '' }}" style="width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,.55); box-shadow: 0 0 0 1px rgba(0,0,0,.18); transition: background .4s ease;"></span>
            @endforeach
          </div>
        </div>
        <div style="padding: 14px 16px 18px;">
          <div style="font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #12386B; margin-bottom: 5px;">{{ $item['soort'] ?? '' }}</div>
          <h3 style="font-size: 19px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 7px;">{{ $item['naam'] ?? '' }}</h3>
          <p style="font-size: 15px; line-height: 1.5; color: #4A4844; margin: 0;">{{ $item['tekst'] ?? '' }}</p>
          @if (! empty($item['url']))
            <a href="{{ $item['url'] }}" target="_blank" rel="noopener" style="display: inline-block; margin-top: 10px; font-size: 14px; font-weight: 700; color: #12386B; text-decoration: underline; text-underline-offset: 3px;">Bekijk {{ $item['naam'] ?? 'de site' }} &rarr;</a>
          @endif
        </div>
      </article>
    @endforeach
  </div>
</section>

@verbatim
<style>
  [data-werk-rij]::-webkit-scrollbar { height: 8px; }
  [data-werk-rij]::-webkit-scrollbar-thumb { background: #D8D5D0; border-radius: 4px; }
  [data-werk-punt].werk-aan { background: #fff; }
  @media (prefers-reduced-motion: reduce) {
    [data-werk-beeld] { transition: none; }
  }
</style>
<script>
(function () {
  var rustig = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (rustig) return;   // dan blijft het eerste beeld staan

  document.querySelectorAll('[data-werk-kaart]').forEach(function (kaart, n) {
    var beelden = kaart.querySelectorAll('[data-werk-beeld]');
    var punten  = kaart.querySelectorAll('[data-werk-punt]');
    if (beelden.length < 2) return;

    var i = 0;
    var toon = function (nieuw) {
      beelden[i].style.opacity = '0';
      if (punten[i]) punten[i].classList.remove('werk-aan');
      i = nieuw;
      beelden[i].style.opacity = '1';
      if (punten[i]) punten[i].classList.add('werk-aan');
    };

    // Om de twee seconden het volgende beeld: startpagina, overzicht, detailpagina, weer van voren.
    // Elke kaart begint iets later, anders wisselt de hele rij tegelijk en gaat het knipperen.
    setTimeout(function () {
      setInterval(function () {
        if (document.hidden) return;   // niets doen op een tabblad dat niemand ziet
        toon((i + 1) % beelden.length);
      }, 2000);
    }, n * 400);
  });
})();
</script>
@endverbatim
@endif
