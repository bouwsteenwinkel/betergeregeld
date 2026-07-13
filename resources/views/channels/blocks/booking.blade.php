@php
    /** @var \App\Models\Channel\Block $block */
    $services = array_values(array_filter((array) $block->c('services', []), fn ($s) => is_string($s) && $s !== ''));
    $deposit  = $block->c('deposit');                 // bedrag in hele euro's, of null
    $phone    = $site->brand('phone');
    $cfg = [
        'services' => $services,
        'deposit'  => $deposit ? (int) $deposit : null,
        'openDays' => [1, 2, 3, 4, 5, 6],             // ma-za (0 = zo dicht)
        'slots'    => ['09:30', '10:15', '11:00', '11:45', '13:15', '14:00', '14:45', '15:30', '16:15'],
        'preview'  => (bool) $site->get('meta.preview.is_preview'),
    ];
@endphp
<section data-block="booking" id="contact" class="booking">
    <div class="wrap">
        <div class="bk-head">
            <span class="kicker" style="justify-content:center"><span class="kicker-line"></span> Online afspraak</span>
            <h2>{{ $block->c('heading', 'Maak een afspraak') }}</h2>
            @if ($block->c('sub'))<p class="muted bk-sub">{{ $block->c('sub') }}</p>@endif
        </div>

        <div class="bk-card" id="bk-widget">
            {{-- Voortgang --}}
            <ol class="bk-steps" aria-hidden="true">
                <li class="is-active" data-step-ind="1"><span>1</span> Datum</li>
                <li data-step-ind="2"><span>2</span> Tijd</li>
                <li data-step-ind="3"><span>3</span> Gegevens</li>
                @if ($cfg['deposit'])<li data-step-ind="4"><span>4</span> Aanbetaling</li>@endif
            </ol>

            {{-- Stap 1: datum --}}
            <div class="bk-pane" data-step="1">
                <div class="bk-cal-head">
                    <button type="button" class="bk-nav" data-cal-prev aria-label="Vorige maand">‹</button>
                    <strong data-cal-title></strong>
                    <button type="button" class="bk-nav" data-cal-next aria-label="Volgende maand">›</button>
                </div>
                <div class="bk-dow"><span>ma</span><span>di</span><span>wo</span><span>do</span><span>vr</span><span>za</span><span>zo</span></div>
                <div class="bk-grid" data-cal-grid></div>
            </div>

            {{-- Stap 2: tijd --}}
            <div class="bk-pane" data-step="2" hidden>
                <p class="bk-chosen" data-chosen-date></p>
                <div class="bk-slots" data-slots></div>
                <button type="button" class="bk-back" data-back="1">← andere datum</button>
            </div>

            {{-- Stap 3: gegevens --}}
            <div class="bk-pane" data-step="3" hidden>
                <p class="bk-chosen" data-chosen-when></p>
                <div class="bk-fields">
                    @if ($cfg['services'])
                        <label>Behandeling
                            <select data-f="service">
                                @foreach ($cfg['services'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                            </select>
                        </label>
                    @endif
                    <label>Naam <input type="text" data-f="name" autocomplete="name" required></label>
                    <label>Telefoon <input type="tel" data-f="phone" autocomplete="tel" required></label>
                    <label>E-mail <input type="email" data-f="email" autocomplete="email" required></label>
                    <label class="bk-full">Opmerking (optioneel) <textarea data-f="note" rows="2"></textarea></label>
                </div>
                <div class="bk-actions">
                    <button type="button" class="bk-back" data-back="2">← terug</button>
                    <button type="button" class="btn" data-next-3>{{ $cfg['deposit'] ? 'Naar aanbetaling' : 'Afspraak bevestigen' }}</button>
                </div>
            </div>

            @if ($cfg['deposit'])
                {{-- Stap 4: aanbetaling (Mollie/iDEAL) --}}
                <div class="bk-pane" data-step="4" hidden>
                    <div class="bk-summary" data-summary></div>
                    <div class="bk-deposit">
                        <div>
                            <strong>Aanbetaling &euro;{{ $cfg['deposit'] }}</strong>
                            <p class="muted">Je betaalt nu een kleine aanbetaling om je plek vast te zetten. Het restbedrag reken je in de salon af.</p>
                        </div>
                        <span class="bk-ideal">iDEAL</span>
                    </div>
                    <div class="bk-actions">
                        <button type="button" class="bk-back" data-back="3">← terug</button>
                        <button type="button" class="btn bk-pay" data-pay>Betaal &euro;{{ $cfg['deposit'] }} en bevestig</button>
                    </div>
                    <p class="bk-secure">Beveiligd betalen via Mollie</p>
                </div>
            @endif

            {{-- Succes --}}
            <div class="bk-pane bk-done" data-step="done" hidden>
                <div class="bk-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></div>
                <h3>Afspraak aangevraagd</h3>
                <p class="muted" data-done-text></p>
                @if ($phone)<p class="muted">Liever bellen? <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>@endif
            </div>
        </div>
    </div>
</section>

<style>
    .booking .bk-head{text-align:center;max-width:640px;margin:0 auto 1.1rem}
    .booking .bk-head h2{margin:.25rem 0 0;font-size:clamp(1.55rem,3.4vw,2rem)}
    .booking .bk-sub{margin-top:.3rem}
    .booking .bk-card{max-width:700px;margin:0 auto;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);border-radius:8px;box-shadow:0 20px 50px -34px rgba(0,0,0,.5);padding:1.4rem}
    .booking .bk-steps{list-style:none;display:flex;gap:.4rem;margin:0 0 1rem;padding:0;flex-wrap:wrap}
    .booking .bk-steps li{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:var(--c-muted);flex:1;min-width:110px}
    .booking .bk-steps li span{width:22px;height:22px;border-radius:5px;display:grid;place-items:center;background:color-mix(in srgb,var(--c-ink) 10%,transparent);color:var(--c-muted);font-size:.78rem;flex:0 0 auto}
    .booking .bk-steps li.is-active{color:var(--c-ink)}
    .booking .bk-steps li.is-active span,.booking .bk-steps li.is-done span{background:var(--c-accent);color:var(--c-on-accent,#fff)}
    .booking .bk-cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem}
    .booking .bk-cal-head strong{font-size:1rem;text-transform:capitalize}
    .booking .bk-nav{width:32px;height:32px;border-radius:6px;border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:#fff;cursor:pointer;font-size:1.1rem;line-height:1;color:var(--c-ink)}
    .booking .bk-nav:hover{border-color:var(--c-accent);color:var(--c-accent)}
    .booking .bk-dow,.booking .bk-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.35rem}
    .booking .bk-dow{margin-bottom:.35rem}
    .booking .bk-dow span{text-align:center;font-size:.72rem;font-weight:700;color:var(--c-muted);text-transform:uppercase}
    .booking .bk-day{padding:.72rem 0;min-height:0;border:1px solid transparent;border-radius:6px;background:color-mix(in srgb,var(--c-ink) 5%,transparent);cursor:pointer;font-weight:600;color:var(--c-ink);font-size:.95rem}
    .booking .bk-day:hover:not(:disabled){border-color:var(--c-accent);background:color-mix(in srgb,var(--c-accent) 10%,transparent)}
    .booking .bk-day.is-sel{background:var(--c-accent);color:var(--c-on-accent,#fff)}
    .booking .bk-day:disabled{opacity:.32;cursor:default;background:transparent}
    .booking .bk-day.is-empty{background:transparent;border:0;cursor:default}
    .booking .bk-chosen{font-weight:700;color:var(--c-ink);margin-bottom:1rem;text-transform:capitalize}
    .booking .bk-slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(88px,1fr));gap:.5rem}
    .booking .bk-slots button{padding:.6rem;border-radius:6px;border:1px solid color-mix(in srgb,var(--c-ink) 14%,transparent);background:#fff;cursor:pointer;font-weight:600;color:var(--c-ink)}
    .booking .bk-slots button:hover{border-color:var(--c-accent);color:var(--c-accent)}
    .booking .bk-fields{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
    .booking .bk-fields label{display:flex;flex-direction:column;gap:.3rem;font-size:.85rem;font-weight:600;color:var(--c-ink)}
    .booking .bk-fields .bk-full{grid-column:1/-1}
    .booking .bk-fields input,.booking .bk-fields select,.booking .bk-fields textarea{padding:.7rem .8rem;border:1px solid color-mix(in srgb,var(--c-ink) 18%,transparent);border-radius:6px;font:inherit;color:var(--c-ink);background:#fff;font-weight:400}
    .booking .bk-actions{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1.3rem}
    .booking .bk-back{background:none;border:0;color:var(--c-muted);cursor:pointer;font-weight:600;font-size:.9rem;padding:.4rem 0}
    .booking .bk-back:hover{color:var(--c-ink)}
    .booking .bk-summary{background:color-mix(in srgb,var(--c-ink) 5%,transparent);border-radius:6px;padding:1rem 1.1rem;margin-bottom:1rem;font-size:.92rem;line-height:1.7}
    .booking .bk-deposit{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid color-mix(in srgb,var(--c-accent) 35%,transparent);border-radius:6px;padding:1rem 1.1rem}
    .booking .bk-deposit p{margin:.25rem 0 0;font-size:.85rem}
    .booking .bk-ideal{flex:0 0 auto;font-weight:800;font-size:.8rem;letter-spacing:.03em;color:#cc0066;background:#fff;border:1px solid #ffd0e6;border-radius:5px;padding:.4rem .6rem}
    .booking .bk-pay{background:var(--c-accent);color:var(--c-on-accent,#fff)}
    .booking .bk-secure{text-align:center;color:var(--c-muted);font-size:.78rem;margin:.8rem 0 0}
    .booking .bk-done{text-align:center;padding:1.4rem 0}
    .booking .bk-check{width:64px;height:64px;border-radius:50%;background:color-mix(in srgb,var(--c-accent) 15%,transparent);color:var(--c-accent);display:grid;place-items:center;margin:0 auto 1rem}
    .booking .bk-check svg{width:32px;height:32px}
    @media(max-width:560px){.booking .bk-fields{grid-template-columns:1fr}.booking .bk-card{padding:1.2rem}}
</style>

<script>
(function () {
    var root = document.getElementById('bk-widget');
    if (!root || root.dataset.init) return;
    root.dataset.init = '1';
    var cfg = @json($cfg);
    var MONTHS = ['januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'];
    var DAYS = ['zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag'];
    var state = { date: null, time: null };
    var today = new Date(); today.setHours(0,0,0,0);
    var view = new Date(today.getFullYear(), today.getMonth(), 1);
    var maxMonth = today.getMonth() + 3;

    function q(sel){ return root.querySelector(sel); }
    function qa(sel){ return Array.prototype.slice.call(root.querySelectorAll(sel)); }

    function setStep(n) {
        qa('.bk-pane').forEach(function (p) { p.hidden = p.getAttribute('data-step') !== String(n); });
        qa('.bk-steps li').forEach(function (li) {
            var s = Number(li.getAttribute('data-step-ind'));
            li.classList.toggle('is-active', s === n);
            li.classList.toggle('is-done', typeof n === 'number' && s < n);
        });
    }

    function renderCal() {
        q('[data-cal-title]').textContent = MONTHS[view.getMonth()] + ' ' + view.getFullYear();
        var grid = q('[data-cal-grid]'); grid.innerHTML = '';
        var first = new Date(view.getFullYear(), view.getMonth(), 1);
        var offset = (first.getDay() + 6) % 7; // maandag = 0
        var daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        for (var i = 0; i < offset; i++) { var e = document.createElement('div'); e.className = 'bk-day is-empty'; grid.appendChild(e); }
        for (var d = 1; d <= daysInMonth; d++) {
            var dt = new Date(view.getFullYear(), view.getMonth(), d);
            var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'bk-day'; btn.textContent = d;
            var closed = cfg.openDays.indexOf(dt.getDay()) === -1;
            if (dt < today || closed) { btn.disabled = true; }
            else { btn.addEventListener('click', function (dt) { return function () { pickDate(dt); }; }(dt)); }
            if (state.date && dt.getTime() === state.date.getTime()) btn.classList.add('is-sel');
            grid.appendChild(btn);
        }
        q('[data-cal-prev]').disabled = (view.getFullYear() === today.getFullYear() && view.getMonth() === today.getMonth());
        q('[data-cal-next]').disabled = (view.getMonth() >= maxMonth % 12 && view.getFullYear() >= today.getFullYear() + Math.floor(maxMonth / 12));
    }

    function fmtDate(dt) { return DAYS[dt.getDay()] + ' ' + dt.getDate() + ' ' + MONTHS[dt.getMonth()]; }

    function pickDate(dt) {
        state.date = dt;
        q('[data-chosen-date]').textContent = fmtDate(dt);
        var wrap = q('[data-slots]'); wrap.innerHTML = '';
        cfg.slots.forEach(function (t) {
            var b = document.createElement('button'); b.type = 'button'; b.textContent = t;
            b.addEventListener('click', function () { pickTime(t); });
            wrap.appendChild(b);
        });
        setStep(2);
    }

    function pickTime(t) {
        state.time = t;
        q('[data-chosen-when]').textContent = fmtDate(state.date) + ', ' + t + ' uur';
        setStep(3);
    }

    q('[data-cal-prev]').addEventListener('click', function () { view.setMonth(view.getMonth() - 1); renderCal(); });
    q('[data-cal-next]').addEventListener('click', function () { view.setMonth(view.getMonth() + 1); renderCal(); });
    qa('[data-back]').forEach(function (b) { b.addEventListener('click', function () { setStep(Number(b.getAttribute('data-back'))); }); });

    function collect() {
        var g = {};
        qa('[data-f]').forEach(function (el) { g[el.getAttribute('data-f')] = el.value.trim(); });
        return g;
    }
    function validate() {
        var ok = true;
        qa('[data-f]').forEach(function (el) {
            if (el.hasAttribute('required') && !el.value.trim()) { el.style.borderColor = '#dc2626'; ok = false; }
            else { el.style.borderColor = ''; }
        });
        return ok;
    }

    var next3 = q('[data-next-3]');
    if (next3) next3.addEventListener('click', function () {
        if (!validate()) return;
        if (cfg.deposit) {
            var g = collect();
            var lines = [];
            if (g.service) lines.push('<strong>' + g.service + '</strong>');
            lines.push(fmtDate(state.date) + ', ' + state.time + ' uur');
            lines.push(g.name + ' &middot; ' + g.phone);
            q('[data-summary]').innerHTML = lines.join('<br>');
            setStep(4);
        } else { finish(); }
    });

    var pay = q('[data-pay]');
    if (pay) pay.addEventListener('click', function () {
        pay.disabled = true; pay.textContent = 'Bezig met betalen...';
        setTimeout(finish, 900); // preview: simuleert de Mollie/iDEAL-redirect
    });

    function finish() {
        var g = collect();
        var txt = 'Bedankt ' + (g.name || '') + '. We hebben je aanvraag voor ' + fmtDate(state.date) + ' om ' + state.time + ' uur ontvangen'
            + (cfg.deposit ? ' en je aanbetaling is gelukt' : '') + '. Je krijgt een bevestiging per e-mail.';
        q('[data-done-text]').innerHTML = txt;
        qa('.bk-steps li').forEach(function (li) { li.classList.add('is-done'); li.classList.remove('is-active'); });
        setStep('done');
        root.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    renderCal();
})();
</script>
