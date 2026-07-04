@php /** @var \App\Support\ChannelSite $site */ @endphp
{{-- Zelfstandige afspraken-widget: haalt vrije sloten op en boekt (same-origin
     /afspraak/*). Platform-breed, dezelfde gedeelde agenda op elke trigger-site. --}}
<section class="bkg" data-section="afspraak" id="afspraak">
    <div class="wrap" style="max-width:820px">
        <span class="kicker"><span class="kicker-line"></span> Direct plannen</span>
        <h2>Plan zelf een online kennismaking</h2>
        <p class="muted" style="max-width:56ch;margin-top:.3rem">Kies een moment dat jou uitkomt. Je krijgt een korte videoafspraak via Google Meet, gratis en vrijblijvend.</p>

        <div class="bkg-card" data-booking data-site="{{ $site->key }}">
            <div class="bkg-loading">Beschikbare momenten laden…</div>
            <div class="bkg-body" hidden>
                <div class="bkg-days" role="tablist" aria-label="Kies een dag"></div>
                <div class="bkg-times" aria-live="polite"></div>
            </div>
            <form class="bkg-form" hidden>
                <p class="bkg-chosen"></p>
                <input type="text" name="company" class="bkg-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="bkg-fields">
                    <label>Je naam<input type="text" name="name" required autocomplete="name"></label>
                    <label>E-mailadres<input type="email" name="email" required autocomplete="email"></label>
                    <label>Telefoon <span class="bkg-opt">(optioneel)</span><input type="tel" name="phone" autocomplete="tel"></label>
                </div>
                <div class="bkg-actions">
                    <button type="button" class="bkg-back">← Ander moment</button>
                    <button type="submit" class="btn bkg-submit">Afspraak bevestigen</button>
                </div>
            </form>
            <div class="bkg-done" hidden></div>
            <div class="bkg-empty" hidden>Er zijn op dit moment geen vrije momenten. Vraag gerust een gratis voorbeeld aan, dan plannen we samen iets in.</div>
        </div>
    </div>
</section>

<style>
    .bkg-card{margin-top:1.4rem;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);
        border-radius:calc(var(--radius) + 6px);padding:1.4rem clamp(1rem,3vw,1.6rem);box-shadow:0 24px 60px -34px rgba(0,0,0,.4)}
    .bkg-loading{color:var(--c-muted)}
    .bkg-days{display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.5rem}
    .bkg-day{flex:0 0 auto;min-width:82px;text-align:center;cursor:pointer;border:1.5px solid color-mix(in srgb,var(--c-ink) 14%,transparent);
        background:var(--c-bg);border-radius:12px;padding:.6rem .5rem;font:inherit;color:inherit;line-height:1.2}
    .bkg-day small{display:block;color:var(--c-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em}
    .bkg-day b{display:block;font-size:1.15rem;margin-top:.15rem}
    .bkg-day.is-sel{border-color:var(--c-primary);background:color-mix(in srgb,var(--c-primary) 12%,transparent)}
    .bkg-times{display:grid;grid-template-columns:repeat(auto-fill,minmax(84px,1fr));gap:.5rem;margin-top:1rem}
    .bkg-time{cursor:pointer;border:1.5px solid color-mix(in srgb,var(--c-ink) 16%,transparent);background:var(--c-bg);
        border-radius:10px;padding:.7rem .4rem;font:inherit;font-weight:700;color:inherit}
    .bkg-time:hover{border-color:var(--c-primary);background:color-mix(in srgb,var(--c-primary) 8%,transparent)}
    .bkg-chosen{font-weight:700;margin:0 0 1rem}
    .bkg-fields{display:grid;gap:.8rem}
    .bkg-fields label{display:block;font-size:.85rem;font-weight:600;color:color-mix(in srgb,var(--c-ink) 80%,transparent)}
    .bkg-opt{font-weight:400;color:var(--c-muted)}
    .bkg-fields input{width:100%;margin-top:.3rem;padding:.75rem .9rem;font:inherit;font-size:16px;
        border:1px solid color-mix(in srgb,var(--c-ink) 22%,transparent);border-radius:10px;background:var(--c-bg);color:inherit}
    .bkg-actions{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1.2rem}
    .bkg-back{background:none;border:0;color:var(--c-muted);font:inherit;cursor:pointer}
    .bkg-hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
    .bkg-done{background:color-mix(in srgb,var(--c-primary) 10%,transparent);border:1px solid color-mix(in srgb,var(--c-primary) 30%,transparent);
        border-radius:12px;padding:1.1rem 1.2rem;font-weight:600}
    .bkg-empty{color:var(--c-muted)}
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
        elDays = root.querySelector('.bkg-days'),
        elTimes = root.querySelector('.bkg-times'),
        elForm = root.querySelector('.bkg-form'),
        elChosen = root.querySelector('.bkg-chosen'),
        elDone = root.querySelector('.bkg-done'),
        elEmpty = root.querySelector('.bkg-empty');
    var days = {}, picked = null;

    var WD = ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'];
    var MO = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

    function label(dateStr) {
        var p = dateStr.split('-'), d = new Date(+p[0], +p[1] - 1, +p[2]);
        return { wd: WD[d.getDay()], dm: d.getDate() + ' ' + MO[d.getMonth()] };
    }

    fetch('/afspraak/beschikbaarheid', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            days = j.days || {};
            elLoading.hidden = true;
            var keys = Object.keys(days);
            if (!keys.length) { elEmpty.hidden = false; return; }
            elBody.hidden = false;
            keys.forEach(function (date, i) {
                var l = label(date);
                var b = document.createElement('button');
                b.type = 'button'; b.className = 'bkg-day'; b.dataset.date = date;
                b.innerHTML = '<small>' + l.wd + '</small><b>' + l.dm.split(' ')[0] + '</b>' + l.dm.split(' ')[1];
                b.addEventListener('click', function () { selectDay(date, b); });
                elDays.appendChild(b);
                if (i === 0) selectDay(date, b);
            });
        })
        .catch(function () { elLoading.textContent = 'Kon de beschikbaarheid niet laden. Probeer het later opnieuw.'; });

    function selectDay(date, btn) {
        elDays.querySelectorAll('.bkg-day').forEach(function (d) { d.classList.remove('is-sel'); });
        btn.classList.add('is-sel');
        elTimes.innerHTML = '';
        (days[date] || []).forEach(function (time) {
            var t = document.createElement('button');
            t.type = 'button'; t.className = 'bkg-time'; t.textContent = time;
            t.addEventListener('click', function () { choose(date, time); });
            elTimes.appendChild(t);
        });
    }

    function choose(date, time) {
        picked = date + ' ' + time;
        var l = label(date);
        elChosen.textContent = 'Gekozen: ' + l.wd + ' ' + l.dm + ' om ' + time + ' uur (online via Google Meet)';
        elBody.hidden = true;
        elForm.hidden = false;
    }

    root.querySelector('.bkg-back').addEventListener('click', function () {
        elForm.hidden = true; elBody.hidden = false; picked = null;
    });

    elForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!picked) return;
        var btn = elForm.querySelector('.bkg-submit');
        btn.disabled = true; btn.textContent = 'Bezig…';
        var body = {
            name: elForm.name.value, email: elForm.email.value, phone: elForm.phone.value,
            company: elForm.company.value, starts_at: picked, source_site: site
        };
        fetch('/afspraak/boeken', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (res) {
            if (!res.ok || !res.j.ok) {
                btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
                alert(res.j.message || 'Er ging iets mis. Probeer een ander moment.');
                if (res.j && res.j.message && res.j.message.indexOf('bezet') > -1) { location.reload(); }
                return;
            }
            elForm.hidden = true;
            elDone.hidden = false;
            elDone.textContent = res.j.message;
            if (window.bgTrack) window.bgTrack('appointment_booked', { site: site });
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
            alert('Er ging iets mis. Probeer het later opnieuw.');
        });
    });
})();
</script>
