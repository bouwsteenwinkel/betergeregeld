@php
    /**
     * Gedeelde afspraak-kalender: maandraster + tijden, gevoed door
     * /afspraak/beschikbaarheid.
     *
     * Bestaat omdat deze kalender op drie plekken staat (de boekwidget, de verzetpagina
     * achter de mail-link en de lead-wizard) en drie keer los was gekopieerd. Elke fix
     * moest daardoor drie keer, en in de praktijk gebeurde dat niet: de widget kreeg een
     * nette 503-afhandeling, de andere twee bleven "geen momenten beschikbaar" tonen bij
     * een storing.
     *
     * De opmaak hangt aan CSS-variabelen, niet aan vaste kleuren, zodat elke pagina hem
     * in de eigen huisstijl kan zetten. Verplicht op een voorouder:
     *   --c-primary     merkkleur (gevulde selectie)
     *   --c-on-primary  tekst óp die merkkleur (default #fff)
     *   --c-accent      markeert dagen met ruimte
     *   --c-ink         tekstkleur
     *   --c-muted       gedempte tekst
     *   --c-bg          achtergrond van de knoppen (iets anders dan de kaart eronder)
     *   --radius        hoekstraal
     *
     * Parameters:
     *   $soonest    toon de "eerste vrije moment"-snelkeuze (default true)
     *   $emptyText  tekst als er geen enkel moment vrij is
     *   $metaTekst  regel onder de kalender; geef false om hem weg te laten
     */
    $soonest    = $soonest ?? true;
    $emptyText  = $emptyText ?? 'Er zijn op dit moment geen vrije momenten.';
    $duur       = (int) config('scheduling.meeting_minutes', 60);
    $metaTekst  = $metaTekst ?? ($duur . ' minuten, online via Google Meet. Alle tijden zijn Nederlandse tijd.');
@endphp

<div class="slotcal" data-slotcal>
    <div class="slotcal-loading">Beschikbare momenten laden…</div>

    <div class="slotcal-body" hidden>
        @if ($soonest)
            {{-- Eén tik naar het eerstvolgende moment. Wie "gewoon zo snel mogelijk" wil,
                 hoeft dan niet eerst een datum te zoeken. --}}
            <button type="button" class="slotcal-soonest" hidden></button>
        @endif

        <div class="slotcal-grid">
            <div class="slotcal-cal">
                <div class="slotcal-head">
                    <button type="button" class="slotcal-nav slotcal-prev" aria-label="Vorige maand">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <strong class="slotcal-month" aria-live="polite"></strong>
                    <button type="button" class="slotcal-nav slotcal-next" aria-label="Volgende maand">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
                <div class="slotcal-wd" aria-hidden="true">
                    <span>ma</span><span>di</span><span>wo</span><span>do</span><span>vr</span><span>za</span><span>zo</span>
                </div>
                <div class="slotcal-dates" role="group" aria-label="Kies een dag"></div>
                <p class="slotcal-legend"><span class="slotcal-dot" aria-hidden="true"></span> Dagen met vrije momenten</p>
            </div>

            <div class="slotcal-slots">
                <p class="slotcal-slots-head"></p>
                <div class="slotcal-times" aria-live="polite"></div>
            </div>
        </div>
    </div>

    <div class="slotcal-empty" hidden>{{ $emptyText }}</div>

    @if ($metaTekst)
        <p class="slotcal-meta">{{ $metaTekst }}</p>
    @endif
</div>

@once
<style>
    .slotcal-loading,.slotcal-empty{color:var(--c-muted)}

    /* Kalender links, tijden rechts: de tijden staan naast de datum in plaats van
       eronder, zodat de keuze niet onder de vouw verdwijnt zodra je een dag aantikt.
       De kalenderkolom krijgt een eigen breedte: op 1fr rekten de vierkante cellen mee
       tot 75px en waaierde het raster uit tot een leeg veld. */
    .slotcal-grid{display:grid;grid-template-columns:minmax(320px,368px) minmax(0,1fr);gap:clamp(1rem,3vw,2.2rem);align-items:start}
    .slotcal-cal{width:100%}
    /* Eén kolom op mobiel. minmax(0,1fr) plus min-width:0 op de kinderen is hier geen
       franje: op 1fr krimpt de kolom niet onder zijn min-content (7 datumcellen van 42px),
       waardoor het raster en de tijdsloten ~36px buiten de kaart staken. Body heeft
       overflow-x:clip, dus dat viel niet op als paginabrede overflow, het werd stilletjes
       afgesneden: de zondagkolom, de volgende-maand-knop en de rechterkolom tijden. */
    @media(max-width:720px){
        .slotcal-grid{grid-template-columns:minmax(0,1fr);gap:1.2rem}
        .slotcal-grid > *{min-width:0}
        .slotcal-times{grid-template-columns:repeat(auto-fill,minmax(64px,1fr))}
    }

    .slotcal-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.9rem}
    .slotcal-month{font-size:1.05rem;text-transform:capitalize;color:var(--c-ink)}
    .slotcal-nav{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;cursor:pointer;
        border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:var(--c-bg);border-radius:10px;color:var(--c-ink)}
    .slotcal-nav svg{width:18px;height:18px}
    .slotcal-nav:hover:not(:disabled){border-color:var(--c-primary)}
    .slotcal-nav:disabled{opacity:.35;cursor:default}

    .slotcal-wd{display:grid;grid-template-columns:repeat(7,1fr);gap:.25rem;margin-bottom:.35rem}
    .slotcal-wd span{text-align:center;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700}

    .slotcal-dates{display:grid;grid-template-columns:repeat(7,1fr);gap:.25rem}
    .slotcal-date{position:relative;aspect-ratio:1;display:flex;align-items:center;justify-content:center;
        min-height:42px;border:0;background:none;font:inherit;font-weight:600;color:var(--c-ink);
        border-radius:10px;cursor:pointer}
    .slotcal-date:disabled{color:color-mix(in srgb,var(--c-muted) 45%,transparent);cursor:default;font-weight:500}
    .slotcal-date.is-blank{visibility:hidden}
    .slotcal-date:not(:disabled):hover{background:color-mix(in srgb,var(--c-primary) 9%,transparent)}
    /* Het accent doet hier één ding, en dat is genoeg: aangeven waar ruimte is. */
    .slotcal-date[data-vrij]::after{content:"";position:absolute;bottom:6px;left:50%;transform:translateX(-50%);
        width:5px;height:5px;border-radius:50%;background:var(--c-accent)}
    .slotcal-date.is-today{box-shadow:inset 0 0 0 1.5px color-mix(in srgb,var(--c-ink) 30%,transparent)}
    /* Gevuld in de merkkleur. Een lichte tint daarvan (het oude "12% van primary") gaf
       grijsblauw op wit en las als uitgeschakeld, niet als gekozen. De tekstkleur is een
       variabele: elk thema weet zelf wat er leesbaar op zijn merkkleur staat. */
    .slotcal-date.is-sel{background:var(--c-primary);color:var(--c-on-primary,#fff);box-shadow:none}
    .slotcal-date.is-sel::after{background:var(--c-on-primary,#fff)}
    .slotcal-date:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}

    .slotcal-legend{display:flex;align-items:center;gap:.4rem;margin:.8rem 0 0;font-size:.78rem;color:var(--c-muted)}
    .slotcal-dot{width:5px;height:5px;border-radius:50%;background:var(--c-accent);flex:0 0 auto}

    .slotcal-slots-head{font-weight:700;margin:0 0 .7rem;color:var(--c-ink)}
    .slotcal-times{display:grid;grid-template-columns:repeat(auto-fill,minmax(76px,1fr));gap:.45rem}
    .slotcal-dagdeel{grid-column:1/-1;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);
        font-weight:700;margin:.5rem 0 -.1rem}
    .slotcal-dagdeel:first-child{margin-top:0}
    .slotcal-time{cursor:pointer;border:1.5px solid color-mix(in srgb,var(--c-ink) 16%,transparent);background:var(--c-bg);
        border-radius:10px;padding:.7rem .4rem;font:inherit;font-weight:700;color:var(--c-ink);min-height:44px}
    .slotcal-time:hover{border-color:var(--c-primary);background:color-mix(in srgb,var(--c-primary) 8%,transparent)}
    .slotcal-time.is-sel{background:var(--c-primary);color:var(--c-on-primary,#fff);border-color:var(--c-primary)}
    .slotcal-time:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}
    .slotcal-leeg{color:var(--c-muted);font-size:.9rem}

    .slotcal-soonest{display:block;width:100%;text-align:left;margin-bottom:1.1rem;cursor:pointer;font:inherit;
        border:1.5px solid color-mix(in srgb,var(--c-accent) 45%,transparent);
        background:color-mix(in srgb,var(--c-accent) 8%,transparent);
        border-radius:12px;padding:.75rem .9rem;color:var(--c-ink);min-height:44px}
    .slotcal-soonest:hover{border-color:var(--c-accent);background:color-mix(in srgb,var(--c-accent) 14%,transparent)}
    .slotcal-soonest:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}
    .slotcal-soonest b{font-weight:700}
    .slotcal-soonest span{color:var(--c-muted);font-size:.85rem}

    .slotcal-meta{margin:1.1rem 0 0;padding-top:.9rem;border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);
        font-size:.8rem;color:var(--c-muted)}

    /* Mobiele bijstellingen die ná de basisregels moeten staan, anders wint op gelijke
       specificiteit gewoon de laatste regel in het bestand. */
    @media(max-width:720px){
        /* De enige manier om van maand te wisselen, en alleen een pijl als houvast:
           minstens 44px tikdoel. Het icoon blijft 18px. */
        .slotcal-nav{width:44px;height:44px}
        .slotcal-dagdeel{font-size:.8rem}
    }
</style>

<script>
/**
 * bgSlotCalendar(root, opts) -> { load, refresh, kies, reset }
 *
 * opts.onPick(keuze)  keuze = {date:'2026-07-20', time:'11:00', waarde:'2026-07-20 11:00',
 *                              kort:'ma 20 jul', lang:'maandag 20 juli'}
 * opts.onEmpty()      geen enkel moment vrij
 * opts.onError(msg)   agenda onbereikbaar of laadfout
 */
window.bgSlotCalendar = function (root, opts) {
    opts = opts || {};
    if (!root) return null;

    var elLoading = root.querySelector('.slotcal-loading'),
        elBody = root.querySelector('.slotcal-body'),
        elMonth = root.querySelector('.slotcal-month'),
        elPrev = root.querySelector('.slotcal-prev'),
        elNext = root.querySelector('.slotcal-next'),
        elDates = root.querySelector('.slotcal-dates'),
        elSlotsHead = root.querySelector('.slotcal-slots-head'),
        elTimes = root.querySelector('.slotcal-times'),
        elSoonest = root.querySelector('.slotcal-soonest'),
        elEmpty = root.querySelector('.slotcal-empty');

    var days = {}, selDate = null, selTime = null, cursor = null, maanden = [], geladen = false;

    var WD = ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'];
    var WDVOL = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];
    var MO = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
    var MOVOL = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

    /* Datums altijd als lokale kalenderdag ontleden. new Date('2026-07-17') leest de
       ISO-vorm als UTC, en ten westen van Greenwich schuift dat een dag terug. */
    function toDate(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function key(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function kort(s) { var d = toDate(s); return WD[d.getDay()] + ' ' + d.getDate() + ' ' + MO[d.getMonth()]; }
    function lang(s) { var d = toDate(s); return WDVOL[d.getDay()] + ' ' + d.getDate() + ' ' + MOVOL[d.getMonth()]; }

    function haal() {
        return fetch('/afspraak/beschikbaarheid', { headers: { 'Accept': 'application/json' } })
            // Een 503 laat fetch niet struikelen, dus zonder deze controle loopt een
            // onbereikbare agenda door als een lege lijst: "geen momenten beschikbaar",
            // terwijl de agenda in werkelijkheid gewoon vol had kunnen zitten of leeg.
            // De json()-parse zit achter de ok-controle en in een catch: een 502/503 van
            // IIS is een HTML-pagina, en die zou de bezoeker anders een rauwe
            // "Unexpected token '<'" voorschotelen.
            .then(function (r) {
                if (r.ok) { return r.json(); }
                return r.json().catch(function () { return {}; }).then(function (b) {
                    throw new Error(b && b.message ? b.message : 'onbereikbaar');
                });
            });
    }

    function load() {
        if (geladen) return;
        geladen = true;
        haal().then(function (j) {
            days = j.days || {};
            elLoading.hidden = true;
            var keys = Object.keys(days).sort();
            if (!keys.length) {
                elEmpty.hidden = false;
                if (opts.onEmpty) opts.onEmpty();
                return;
            }
            elBody.hidden = false;
            maanden = maandenUit(keys);
            var d0 = toDate(keys[0]);
            cursor = new Date(d0.getFullYear(), d0.getMonth(), 1);
            toonSoonest(keys[0]);
            kiesDag(keys[0]);
        }).catch(function (e) {
            geladen = false;
            elLoading.hidden = false;
            var msg = (e && e.message && e.message !== 'onbereikbaar')
                ? e.message
                : 'Kon de beschikbaarheid niet laden. Probeer het later opnieuw.';
            elLoading.textContent = msg;
            if (opts.onError) opts.onError(msg);
        });
    }

    /* Opnieuw ophalen zonder de pagina te herladen: nodig als een slot net vergeven
       blijkt. Een harde herlaadbeurt gooit ingevulde velden weg. */
    function refresh() {
        return haal().then(function (j) {
            days = j.days || {};
            var keys = Object.keys(days).sort();
            if (!keys.length) {
                elBody.hidden = true; elEmpty.hidden = false;
                if (opts.onEmpty) opts.onEmpty();
                return;
            }
            elEmpty.hidden = true; elBody.hidden = false;
            maanden = maandenUit(keys);
            selTime = null;
            kiesDag(days[selDate] && days[selDate].length ? selDate : keys[0]);
        }).catch(function () {});
    }

    /* De maanden waartussen navigeren zin heeft: van de eerste tot de laatste dag met
       ruimte. Verder bladeren levert alleen lege rasters op. */
    function maandenUit(keys) {
        var eerste = toDate(keys[0]), laatste = toDate(keys[keys.length - 1]), uit = [];
        var d = new Date(eerste.getFullYear(), eerste.getMonth(), 1);
        var eind = new Date(laatste.getFullYear(), laatste.getMonth(), 1);
        while (d <= eind) { uit.push(d.getFullYear() + '-' + d.getMonth()); d = new Date(d.getFullYear(), d.getMonth() + 1, 1); }
        return uit;
    }

    function maandIndex() { return maanden.indexOf(cursor.getFullYear() + '-' + cursor.getMonth()); }

    function toonSoonest(eersteDatum) {
        if (!elSoonest) return;
        var t = (days[eersteDatum] || [])[0];
        if (!t) return;
        elSoonest.innerHTML = '<b>Eerste vrije moment: ' + lang(eersteDatum) + ' om ' + t + ' uur</b><br><span>Tik om dit moment te kiezen</span>';
        elSoonest.hidden = false;
        elSoonest.onclick = function () { kiesDag(eersteDatum); kies(eersteDatum, t); };
    }

    function renderMaand() {
        elMonth.textContent = MOVOL[cursor.getMonth()] + ' ' + cursor.getFullYear();
        var i = maandIndex();
        elPrev.disabled = i <= 0;
        elNext.disabled = i >= maanden.length - 1;

        elDates.innerHTML = '';
        var eersteVanMaand = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
        // Maandag = eerste kolom: getDay() geeft 0 voor zondag, dus zondag wordt 6.
        var offset = (eersteVanMaand.getDay() + 6) % 7;
        var aantal = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0).getDate();
        var vandaag = key(new Date());

        for (var b = 0; b < offset; b++) {
            var leeg = document.createElement('button');
            leeg.type = 'button'; leeg.className = 'slotcal-date is-blank'; leeg.disabled = true; leeg.tabIndex = -1;
            leeg.setAttribute('aria-hidden', 'true');
            elDates.appendChild(leeg);
        }

        for (var n = 1; n <= aantal; n++) {
            var d = new Date(cursor.getFullYear(), cursor.getMonth(), n);
            var k = key(d);
            var vrij = (days[k] || []).length;
            var cel = document.createElement('button');
            cel.type = 'button';
            cel.className = 'slotcal-date';
            cel.textContent = n;
            cel.dataset.date = k;
            if (k === vandaag) cel.classList.add('is-today');
            if (vrij) {
                cel.dataset.vrij = vrij;
                cel.setAttribute('aria-label', lang(k) + ', ' + vrij + (vrij === 1 ? ' moment vrij' : ' momenten vrij'));
                cel.addEventListener('click', (function (dd) { return function () { kiesDag(dd); }; })(k));
            } else {
                cel.disabled = true;
                cel.setAttribute('aria-label', lang(k) + ', geen momenten vrij');
            }
            if (k === selDate) cel.classList.add('is-sel');
            elDates.appendChild(cel);
        }
    }

    elPrev.addEventListener('click', function () { stapMaand(-1); });
    elNext.addEventListener('click', function () { stapMaand(1); });

    function stapMaand(richting) {
        cursor = new Date(cursor.getFullYear(), cursor.getMonth() + richting, 1);
        renderMaand();
    }

    function kiesDag(date) {
        selDate = date;

        // Springt de keuze naar een andere maand (via het eerste vrije moment), dan moet
        // het raster mee, anders staat de selectie in een maand die niet in beeld is.
        var d = toDate(date);
        if (!cursor || cursor.getFullYear() !== d.getFullYear() || cursor.getMonth() !== d.getMonth()) {
            cursor = new Date(d.getFullYear(), d.getMonth(), 1);
        }
        renderMaand();

        elSlotsHead.textContent = lang(date);
        elTimes.innerHTML = '';

        var tijden = days[date] || [];
        if (!tijden.length) {
            var leeg = document.createElement('p');
            leeg.className = 'slotcal-leeg';
            leeg.textContent = 'Geen vrije momenten op deze dag.';
            elTimes.appendChild(leeg);
            return;
        }

        var ochtend = tijden.filter(function (t) { return parseInt(t, 10) < 12; });
        var middag = tijden.filter(function (t) { return parseInt(t, 10) >= 12; });
        // Alleen kopjes zetten als er écht twee dagdelen zijn; anders is "Middag" boven
        // de enige groep alleen ruis.
        if (ochtend.length && middag.length) {
            groep('Ochtend', ochtend, date);
            groep('Middag', middag, date);
        } else {
            groep(null, tijden, date);
        }
    }

    function groep(titel, tijden, date) {
        if (titel) {
            var h = document.createElement('p');
            h.className = 'slotcal-dagdeel';
            h.textContent = titel;
            elTimes.appendChild(h);
        }
        tijden.forEach(function (time) {
            var t = document.createElement('button');
            t.type = 'button'; t.className = 'slotcal-time'; t.textContent = time;
            t.dataset.time = time;
            t.setAttribute('aria-label', lang(date) + ' om ' + time);
            if (date === selDate && time === selTime) t.classList.add('is-sel');
            t.addEventListener('click', function () { kies(date, time); });
            elTimes.appendChild(t);
        });
    }

    function kies(date, time) {
        selDate = date; selTime = time;
        elTimes.querySelectorAll('.slotcal-time').forEach(function (t) {
            t.classList.toggle('is-sel', t.dataset.time === time);
        });
        if (opts.onPick) {
            opts.onPick({
                date: date, time: time, waarde: date + ' ' + time,
                kort: kort(date), lang: lang(date)
            });
        }
    }

    function reset() { selTime = null; if (selDate) kiesDag(selDate); }

    if (opts.autoload !== false) load();

    return { load: load, refresh: refresh, kies: kies, reset: reset };
};
</script>
@endonce
