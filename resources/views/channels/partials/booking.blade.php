@php
    /** @var \App\Support\ChannelSite $site */
    $duur = (int) config('scheduling.meeting_minutes', 60);
@endphp
{{-- Zelfstandige afspraken-widget: haalt vrije sloten op en boekt (same-origin
     /afspraak/*). Platform-breed, dezelfde gedeelde agenda op elke trigger-site.

     De dagkiezer is een echte maandkalender en geen horizontale strip meer. Die strip
     toonde op een telefoon 3,5 van de 21 dagen: wie over twee weken wilde, scrollde
     blind. Een maandraster geeft in één oogopslag waar ruimte is, en dat is precies de
     vraag die de bezoeker hier heeft. --}}
<section class="bkg" data-section="afspraak" id="afspraak">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Direct plannen</span>
        <h2>Plan zelf een online kennismaking</h2>
        <p class="muted" style="max-width:56ch;margin-top:.3rem">Kies een moment dat jou uitkomt. Je krijgt een korte videoafspraak via Google Meet, gratis en vrijblijvend.</p>

        <div class="bkg-card" data-booking data-site="{{ $site->key }}" data-duur="{{ $duur }}">
            <div class="bkg-loading">Beschikbare momenten laden…</div>

            <div class="bkg-body" hidden>
                {{-- Eén tik naar het eerstvolgende moment. Wie "gewoon zo snel mogelijk"
                     wil, hoeft dan niet eerst een datum te kiezen. --}}
                <button type="button" class="bkg-soonest" hidden></button>

                <div class="bkg-grid">
                    <div class="bkg-cal">
                        <div class="bkg-cal-head">
                            <button type="button" class="bkg-nav bkg-prev" aria-label="Vorige maand">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                            </button>
                            <strong class="bkg-month" aria-live="polite"></strong>
                            <button type="button" class="bkg-nav bkg-next" aria-label="Volgende maand">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                            </button>
                        </div>
                        <div class="bkg-wd" aria-hidden="true">
                            <span>ma</span><span>di</span><span>wo</span><span>do</span><span>vr</span><span>za</span><span>zo</span>
                        </div>
                        <div class="bkg-dates" role="group" aria-label="Kies een dag"></div>
                        <p class="bkg-legend"><span class="bkg-dot" aria-hidden="true"></span> Dagen met vrije momenten</p>
                    </div>

                    <div class="bkg-slots">
                        <p class="bkg-slots-head"></p>
                        <div class="bkg-times" aria-live="polite"></div>
                    </div>
                </div>
            </div>

            <form class="bkg-form" hidden>
                <p class="bkg-chosen"></p>
                <input type="text" name="website" class="bkg-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="bkg-fields">
                    <label>Je naam<input type="text" name="name" required autocomplete="name"></label>
                    <label>E-mailadres<input type="email" name="email" required autocomplete="email"></label>
                    <label>Telefoon <span class="bkg-opt">(optioneel)</span><input type="tel" name="phone" autocomplete="tel"></label>
                </div>
                <p class="bkg-error" role="alert" hidden></p>
                <div class="bkg-actions">
                    <button type="button" class="bkg-back">Ander moment kiezen</button>
                    <button type="submit" class="btn bkg-submit">Afspraak bevestigen</button>
                </div>
            </form>

            <div class="bkg-done" hidden></div>
            <div class="bkg-empty" hidden>Er zijn op dit moment geen vrije momenten. Vraag gerust een gratis voorbeeld aan, dan plannen we samen iets in.</div>

            <p class="bkg-meta">{{ $duur }} minuten, online via Google Meet. Alle tijden zijn Nederlandse tijd.</p>
        </div>
    </div>
</section>

<style>
    .bkg-card{margin-top:1.4rem;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);
        border-radius:calc(var(--radius) + 6px);padding:1.4rem clamp(1rem,3vw,1.6rem);box-shadow:0 24px 60px -34px rgba(0,0,0,.4)}
    .bkg-loading{color:var(--c-muted)}

    /* Kalender links, tijden rechts: de tijden staan naast de datum in plaats van
       eronder, zodat de keuze niet onder de vouw verdwijnt zodra je een dag aantikt.
       De kalenderkolom krijgt zijn eigen breedte (auto + max-width op .bkg-cal): op
       1fr rekten de vierkante cellen mee tot 75px en waaierde het raster uit tot een
       leeg veld dat de hele kaart opslokte. */
    .bkg-grid{display:grid;grid-template-columns:minmax(320px,368px) minmax(0,1fr);gap:clamp(1rem,3vw,2.2rem);align-items:start}
    .bkg-cal{width:100%}
    @media(max-width:720px){.bkg-grid{grid-template-columns:1fr;gap:1.2rem}}

    .bkg-cal-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.9rem}
    .bkg-month{font-size:1.05rem;text-transform:capitalize}
    .bkg-nav{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;cursor:pointer;
        border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:var(--c-bg);border-radius:10px;color:inherit}
    .bkg-nav svg{width:18px;height:18px}
    .bkg-nav:hover:not(:disabled){border-color:var(--c-primary)}
    .bkg-nav:disabled{opacity:.35;cursor:default}

    .bkg-wd{display:grid;grid-template-columns:repeat(7,1fr);gap:.25rem;margin-bottom:.35rem}
    .bkg-wd span{text-align:center;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);font-weight:700}

    .bkg-dates{display:grid;grid-template-columns:repeat(7,1fr);gap:.25rem}
    .bkg-date{position:relative;aspect-ratio:1;display:flex;align-items:center;justify-content:center;
        min-height:42px;border:0;background:none;font:inherit;font-weight:600;color:var(--c-ink);
        border-radius:10px;cursor:pointer}
    .bkg-date:disabled{color:color-mix(in srgb,var(--c-muted) 45%,transparent);cursor:default;font-weight:500}
    .bkg-date.is-blank{visibility:hidden}
    .bkg-date:not(:disabled):hover{background:color-mix(in srgb,var(--c-primary) 9%,transparent)}
    /* Het oranje accent doet hier één ding, en dat is genoeg: aangeven waar ruimte is. */
    .bkg-date[data-vrij]::after{content:"";position:absolute;bottom:6px;left:50%;transform:translateX(-50%);
        width:5px;height:5px;border-radius:50%;background:var(--c-accent)}
    .bkg-date.is-today{box-shadow:inset 0 0 0 1.5px color-mix(in srgb,var(--c-ink) 30%,transparent)}
    /* Gevuld in de merkkleur met witte tekst: dezelfde combinatie als .btn-secondary,
       dus gegarandeerd leesbaar op elk kanaalthema. Het oude "12% van primary" gaf
       grijsblauw op wit en las als uitgeschakeld in plaats van gekozen. */
    .bkg-date.is-sel{background:var(--c-primary);color:#fff;box-shadow:none}
    .bkg-date.is-sel::after{background:#fff}
    .bkg-date:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}

    .bkg-legend{display:flex;align-items:center;gap:.4rem;margin-top:.8rem;font-size:.78rem;color:var(--c-muted)}
    .bkg-dot{width:5px;height:5px;border-radius:50%;background:var(--c-accent);flex:0 0 auto}

    .bkg-slots-head{font-weight:700;margin:0 0 .7rem}
    .bkg-times{display:grid;grid-template-columns:repeat(auto-fill,minmax(76px,1fr));gap:.45rem}
    .bkg-dagdeel{grid-column:1/-1;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--c-muted);
        font-weight:700;margin:.5rem 0 -.1rem}
    .bkg-dagdeel:first-child{margin-top:0}
    .bkg-time{cursor:pointer;border:1.5px solid color-mix(in srgb,var(--c-ink) 16%,transparent);background:var(--c-bg);
        border-radius:10px;padding:.7rem .4rem;font:inherit;font-weight:700;color:inherit;min-height:44px}
    .bkg-time:hover{border-color:var(--c-primary);background:color-mix(in srgb,var(--c-primary) 8%,transparent)}
    .bkg-time:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}
    .bkg-slots-leeg{color:var(--c-muted);font-size:.9rem}

    .bkg-soonest{display:block;width:100%;text-align:left;margin-bottom:1.1rem;cursor:pointer;font:inherit;
        border:1.5px solid color-mix(in srgb,var(--c-accent) 45%,transparent);
        background:color-mix(in srgb,var(--c-accent) 8%,transparent);
        border-radius:12px;padding:.75rem .9rem;color:inherit;min-height:44px}
    .bkg-soonest:hover{border-color:var(--c-accent);background:color-mix(in srgb,var(--c-accent) 14%,transparent)}
    .bkg-soonest:focus-visible{outline:2px solid var(--c-primary);outline-offset:2px}
    .bkg-soonest b{font-weight:700}
    .bkg-soonest span{color:var(--c-muted);font-size:.85rem}

    .bkg-chosen{font-weight:700;margin:0 0 1rem}
    .bkg-fields{display:grid;gap:.8rem}
    .bkg-fields label{display:block;font-size:.85rem;font-weight:600;color:color-mix(in srgb,var(--c-ink) 80%,transparent)}
    .bkg-opt{font-weight:400;color:var(--c-muted)}
    .bkg-fields input{width:100%;margin-top:.3rem;padding:.75rem .9rem;font:inherit;font-size:16px;
        border:1px solid color-mix(in srgb,var(--c-ink) 22%,transparent);border-radius:10px;background:var(--c-bg);color:inherit}
    .bkg-error{margin-top:.9rem;padding:.7rem .9rem;border-radius:10px;font-size:.9rem;font-weight:600;
        background:color-mix(in srgb,#d64545 10%,transparent);border:1px solid color-mix(in srgb,#d64545 35%,transparent)}
    .bkg-actions{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1.2rem}
    .bkg-back{background:none;border:0;color:var(--c-muted);font:inherit;cursor:pointer;text-decoration:underline;min-height:44px}
    .bkg-hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
    .bkg-done{background:color-mix(in srgb,var(--c-primary) 10%,transparent);border:1px solid color-mix(in srgb,var(--c-primary) 30%,transparent);
        border-radius:12px;padding:1.1rem 1.2rem;font-weight:600}
    .bkg-empty{color:var(--c-muted)}
    .bkg-meta{margin-top:1.1rem;padding-top:.9rem;border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);
        font-size:.8rem;color:var(--c-muted)}
    @media(max-width:560px){.bkg-actions{flex-direction:column-reverse;align-items:stretch}.bkg-submit{width:100%}}
</style>

<script>
(function () {
    var root = document.querySelector('[data-booking]');
    if (!root) return;
    var token = document.querySelector('meta[name=csrf-token]');
    token = token ? token.getAttribute('content') : '';
    var site = root.getAttribute('data-site') || '';

    var elLoading = root.querySelector('.bkg-loading'),
        elBody = root.querySelector('.bkg-body'),
        elMonth = root.querySelector('.bkg-month'),
        elPrev = root.querySelector('.bkg-prev'),
        elNext = root.querySelector('.bkg-next'),
        elDates = root.querySelector('.bkg-dates'),
        elSlotsHead = root.querySelector('.bkg-slots-head'),
        elTimes = root.querySelector('.bkg-times'),
        elSoonest = root.querySelector('.bkg-soonest'),
        elForm = root.querySelector('.bkg-form'),
        elChosen = root.querySelector('.bkg-chosen'),
        elError = root.querySelector('.bkg-error'),
        elDone = root.querySelector('.bkg-done'),
        elEmpty = root.querySelector('.bkg-empty');

    var days = {}, picked = null, selDate = null, cursor = null, maanden = [];

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
    function label(s) { var d = toDate(s); return { wd: WD[d.getDay()], dm: d.getDate() + ' ' + MO[d.getMonth()] }; }
    function langLabel(s) { var d = toDate(s); return WDVOL[d.getDay()] + ' ' + d.getDate() + ' ' + MOVOL[d.getMonth()]; }

    fetch('/afspraak/beschikbaarheid', { headers: { 'Accept': 'application/json' } })
        // Een 503 laat fetch niet struikelen, dus zonder deze controle liep een
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
        })
        .then(function (j) {
            days = j.days || {};
            elLoading.hidden = true;
            var keys = Object.keys(days).sort();
            if (!keys.length) { elEmpty.hidden = false; return; }

            elBody.hidden = false;
            maanden = maandenUit(keys);
            cursor = new Date(toDate(keys[0]).getFullYear(), toDate(keys[0]).getMonth(), 1);
            toonSoonest(keys[0]);
            renderMaand();
            kiesDag(keys[0]);
        })
        .catch(function (e) {
            elLoading.hidden = false;
            elLoading.textContent = (e && e.message && e.message !== 'onbereikbaar')
                ? e.message
                : 'Kon de beschikbaarheid niet laden. Probeer het later opnieuw.';
        });

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
        var t = (days[eersteDatum] || [])[0];
        if (!t) return;
        elSoonest.innerHTML = '<b>Eerste vrije moment: ' + langLabel(eersteDatum) + ' om ' + t + ' uur</b><br><span>Tik om dit moment te kiezen</span>';
        elSoonest.hidden = false;
        elSoonest.addEventListener('click', function () { kiesDag(eersteDatum); kies(eersteDatum, t); });
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
            leeg.type = 'button'; leeg.className = 'bkg-date is-blank'; leeg.disabled = true; leeg.tabIndex = -1;
            leeg.setAttribute('aria-hidden', 'true');
            elDates.appendChild(leeg);
        }

        for (var n = 1; n <= aantal; n++) {
            var d = new Date(cursor.getFullYear(), cursor.getMonth(), n);
            var k = key(d);
            var vrij = (days[k] || []).length;
            var cel = document.createElement('button');
            cel.type = 'button';
            cel.className = 'bkg-date';
            cel.textContent = n;
            cel.dataset.date = k;
            if (k === vandaag) cel.classList.add('is-today');
            if (vrij) {
                cel.dataset.vrij = vrij;
                cel.setAttribute('aria-label', langLabel(k) + ', ' + vrij + (vrij === 1 ? ' moment vrij' : ' momenten vrij'));
                cel.addEventListener('click', (function (dd) { return function () { kiesDag(dd); }; })(k));
            } else {
                cel.disabled = true;
                cel.setAttribute('aria-label', langLabel(k) + ', geen momenten vrij');
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
        if (cursor.getFullYear() !== d.getFullYear() || cursor.getMonth() !== d.getMonth()) {
            cursor = new Date(d.getFullYear(), d.getMonth(), 1);
        }
        renderMaand();

        elSlotsHead.textContent = langLabel(date);
        elTimes.innerHTML = '';

        var tijden = days[date] || [];
        if (!tijden.length) {
            var leeg = document.createElement('p');
            leeg.className = 'bkg-slots-leeg';
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
            h.className = 'bkg-dagdeel';
            h.textContent = titel;
            elTimes.appendChild(h);
        }
        tijden.forEach(function (time) {
            var t = document.createElement('button');
            t.type = 'button'; t.className = 'bkg-time'; t.textContent = time;
            t.setAttribute('aria-label', langLabel(date) + ' om ' + time);
            t.addEventListener('click', function () { kies(date, time); });
            elTimes.appendChild(t);
        });
    }

    function kies(date, time) {
        picked = date + ' ' + time;
        var l = label(date);
        elChosen.textContent = 'Gekozen: ' + l.wd + ' ' + l.dm + ' om ' + time + ' uur (online via Google Meet)';
        elBody.hidden = true;
        elForm.hidden = false;
        elError.hidden = true;
        elForm.querySelector('input[name=name]').focus();
    }

    root.querySelector('.bkg-back').addEventListener('click', function () {
        elForm.hidden = true; elBody.hidden = false; picked = null;
    });

    elForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!picked) return;
        var btn = elForm.querySelector('.bkg-submit');
        btn.disabled = true; btn.textContent = 'Bezig…';
        elError.hidden = true;
        var body = {
            name: elForm.name.value, email: elForm.email.value, phone: elForm.phone.value,
            website: elForm.website.value, starts_at: picked, source_site: site
        };
        fetch('/afspraak/boeken', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(body)
        }).then(function (r) {
            // Net als bij de beschikbaarheid: een 502/503 van IIS is HTML, geen JSON.
            return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; });
        }).then(function (res) {
            if (!res.ok || !res.j.ok) {
                btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
                // Geen alert(): die valt buiten de pagina, is op mobiel een systeemdialoog
                // en verdwijnt zonder spoor. De melding hoort bij het formulier.
                toonFout(res.j.message || 'Er ging iets mis. Probeer een ander moment.');
                if (res.j && res.j.message && res.j.message.indexOf('bezet') > -1) { herlaadSloten(); }
                return;
            }
            elForm.hidden = true;
            elDone.hidden = false;
            elDone.textContent = res.j.message;
            if (window.bgTrack) window.bgTrack('appointment_booked', { site: site });
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
            toonFout('Er ging iets mis. Probeer het later opnieuw.');
        });
    });

    function toonFout(tekst) {
        elError.textContent = tekst;
        elError.hidden = false;
    }

    /* Slot net vergeven: alleen de agenda verversen in plaats van location.reload().
       Een harde herlaadbeurt gooide de ingevulde naam en het e-mailadres weg, en dan
       moet iemand die net wilde boeken alles opnieuw typen. */
    function herlaadSloten() {
        fetch('/afspraak/beschikbaarheid', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) {
                if (!j) return;
                days = j.days || {};
                var keys = Object.keys(days).sort();
                if (!keys.length) { elForm.hidden = true; elEmpty.hidden = false; return; }
                maanden = maandenUit(keys);
                elForm.hidden = true;
                elBody.hidden = false;
                picked = null;
                kiesDag(days[selDate] && days[selDate].length ? selDate : keys[0]);
            })
            .catch(function () {});
    }
})();
</script>
