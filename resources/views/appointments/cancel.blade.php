@php
    // locale('nl') expliciet: deze pagina hangt bewust buiten de locale-prefix (schone
    // mail-link), dus SetLocale draait hier niet en app.locale staat op 'en'.
    $start = \Carbon\Carbon::parse($appt->starts_at)->setTimezone($tz)->locale('nl');
@endphp
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Je afspraak · Beter Geregeld</title>
    <style>
        body{margin:0;font:16px/1.55 system-ui,-apple-system,'Segoe UI',sans-serif;color:#0f172a;background:#f7f9fb}
        .box{max-width:560px;margin:10vh auto 4rem;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:2rem;box-shadow:0 18px 40px -32px rgba(15,23,42,.4)}
        h1{font-size:1.35rem;margin:0 0 .5rem}
        h2{font-size:1rem;margin:0 0 .75rem}
        p{color:#64748b;margin:.3rem 0}
        a{color:#1685c4}
        .details{width:100%;border-collapse:collapse;margin:1.25rem 0;font-size:.95rem}
        .details td{padding:.5rem 0;border-bottom:1px solid #f1f5f9}
        .details td:first-child{color:#64748b;width:110px}
        .details td:last-child{font-weight:600}
        .note{display:flex;gap:.6rem;align-items:flex-start;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:.8rem 1rem;margin:0 0 1.25rem}
        .note.is-err{background:#fef2f2;border-color:#fecaca}
        .note svg{flex:none;width:20px;height:20px;color:#1685c4;margin-top:.1rem}
        .note.is-err svg{color:#dc2626}
        .note p{margin:0;color:#334155}
        .sep{border:0;border-top:1px solid #e2e8f0;margin:1.5rem 0}
        .btn{display:inline-block;border:0;border-radius:8px;padding:.7rem 1.2rem;font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
        .btn-danger{background:#fff;color:#b91c1c;border:1px solid #fecaca}
        .btn-danger:hover{background:#fef2f2}
        .btn-primary{background:#1685c4;color:#fff}
        .btn-primary:hover{background:#12719f}
        .btn:disabled{opacity:.45;cursor:not-allowed}
        .days,.times{display:flex;flex-wrap:wrap;gap:.4rem;margin:0 0 .75rem}
        .day,.time{border:1px solid #e2e8f0;background:#fff;border-radius:8px;padding:.45rem .7rem;font:inherit;font-size:.9rem;cursor:pointer;line-height:1.2}
        .day{text-align:center;min-width:56px}
        .day small{display:block;color:#94a3b8;font-size:.7rem}
        .day b{display:block;font-size:1.05rem}
        .day.is-sel,.time.is-sel{border-color:#1685c4;background:#eff8fd;color:#0f172a}
        .day:hover,.time:hover{border-color:#94a3b8}
        .muted{font-size:.85rem;color:#94a3b8}
        .actions{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center}
    </style>
</head>
<body>
    <div class="box">
        @if (session('appointment_cancelled'))
            <div class="note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p>Je afspraak is geannuleerd. Je hoeft verder niets te doen, we hebben je afzegging verwerkt.</p>
            </div>
        @endif

        @if (session('appointment_rescheduled'))
            <div class="note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p>Je afspraak is verzet. De nieuwe bevestiging staat in je mailbox.</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="note is-err">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        @if ($state === 'cancelled')
            <h1>Deze afspraak is geannuleerd</h1>
            <p>Het moment hieronder staat niet meer in de agenda. Wil je alsnog een gesprek?
                <a href="/afspraak">Plan een nieuw moment</a>.</p>
        @elseif ($state === 'past')
            <h1>Deze afspraak is geweest</h1>
            <p>Dit moment ligt in het verleden, annuleren of verzetten kan niet meer. Wil je een
                vervolggesprek? <a href="/afspraak">Plan een nieuw moment</a>.</p>
        @else
            <h1>Je afspraak</h1>
            <p>Hoi {{ $appt->name }}, hieronder staat je afspraak. Je kunt hem hier verzetten of annuleren.</p>
        @endif

        <table class="details">
            <tr><td>Datum</td><td>{{ $start->translatedFormat('l j F Y') }}</td></tr>
            <tr><td>Tijd</td><td>{{ $start->format('H:i') }} uur</td></tr>
            <tr><td>Vorm</td><td>Online via Google Meet</td></tr>
        </table>

        @if ($state === 'open')
            <hr class="sep">

            <h2>Even verzetten?</h2>
            <p class="muted" style="margin:0 0 .9rem">Liever een ander moment? Kies hieronder, dan schuiven we je afspraak op. Je oude moment vervalt pas als het nieuwe vaststaat.</p>

            <form method="POST" action="/afspraak/annuleren/{{ $token }}/verzetten" data-move>
                @csrf
                <input type="hidden" name="starts_at" value="">
                <p class="muted" data-loading>Momenten laden...</p>
                <p class="muted" data-empty hidden>Er zijn nu geen vrije momenten. Mail ons, dan prikken we samen een datum.</p>
                <div data-body hidden>
                    <div class="days" data-days></div>
                    <div class="times" data-times></div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary" data-submit disabled>Verzet mijn afspraak</button>
                    <span class="muted" data-chosen hidden></span>
                </div>
            </form>

            <hr class="sep">

            <h2>Toch niet?</h2>
            <p class="muted" style="margin:0 0 .9rem">Zeg gerust af, liever dat dan een leeg gesprek. Je kunt daarna altijd een nieuw moment prikken.</p>
            <form method="POST" action="/afspraak/annuleren/{{ $token }}"
                  onsubmit="return confirm('Weet je zeker dat je deze afspraak wilt annuleren?')">
                @csrf
                <button type="submit" class="btn btn-danger">Annuleer mijn afspraak</button>
            </form>
        @endif

        <hr class="sep">
        <p class="muted">Vragen? Mail ons op <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>
    </div>

    @if ($state === 'open')
    <script>
    (function () {
        var form = document.querySelector('[data-move]');
        if (!form) return;
        var at = form.querySelector('input[name="starts_at"]'),
            elLoad = form.querySelector('[data-loading]'), elEmpty = form.querySelector('[data-empty]'),
            elBody = form.querySelector('[data-body]'), elDays = form.querySelector('[data-days]'),
            elTimes = form.querySelector('[data-times]'), elChosen = form.querySelector('[data-chosen]'),
            elSubmit = form.querySelector('[data-submit]');
        var days = {};
        var WD = ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
            MO = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
        function lab(d) { var p = d.split('-'), x = new Date(+p[0], +p[1] - 1, +p[2]); return { wd: WD[x.getDay()], dm: x.getDate() + ' ' + MO[x.getMonth()] }; }

        // Zelfde bron als de boekwidget (SlotEngine), zodat hier nooit een moment
        // staat dat het boeken daarna afkeurt.
        fetch('/afspraak/beschikbaarheid', { headers: { 'Accept': 'application/json' } })
            // Een 503 (agenda onbereikbaar) laat fetch niet struikelen; zonder deze
            // controle lijkt het alsof er geen enkel moment vrij is. De parse zit achter
            // de ok-controle: een 502/503 van IIS is HTML, en die mag de bezoeker geen
            // rauwe parse-fout opleveren.
            .then(function (r) {
                if (r.ok) { return r.json(); }
                return r.json().catch(function () { return {}; }).then(function (b) {
                    throw new Error(b && b.message ? b.message : 'onbereikbaar');
                });
            })
            .then(function (j) {
                days = j.days || {}; elLoad.hidden = true;
                var keys = Object.keys(days);
                if (!keys.length) { elEmpty.hidden = false; return; }
                elBody.hidden = false;
                keys.forEach(function (date, i) {
                    var l = lab(date), b = document.createElement('button');
                    b.type = 'button'; b.className = 'day'; b.dataset.date = date;
                    b.innerHTML = '<small>' + l.wd + '</small><b>' + l.dm.split(' ')[0] + '</b>' + l.dm.split(' ')[1];
                    b.addEventListener('click', function () { selDay(date, b); });
                    elDays.appendChild(b); if (i === 0) selDay(date, b);
                });
            }).catch(function (e) {
                elLoad.hidden = false;
                elLoad.textContent = (e && e.message && e.message !== 'onbereikbaar') ? e.message : 'Kon de momenten niet laden. Ververs de pagina of mail ons.';
            });

        function selDay(date, btn) {
            elDays.querySelectorAll('.day').forEach(function (d) { d.classList.remove('is-sel'); });
            btn.classList.add('is-sel'); elTimes.innerHTML = '';
            (days[date] || []).forEach(function (time) {
                var t = document.createElement('button');
                t.type = 'button'; t.className = 'time'; t.textContent = time;
                t.addEventListener('click', function () { pick(date, time, t); });
                elTimes.appendChild(t);
            });
        }
        function pick(date, time, btn) {
            elTimes.querySelectorAll('.time').forEach(function (t) { t.classList.remove('is-sel'); });
            btn.classList.add('is-sel'); at.value = date + ' ' + time;
            var l = lab(date);
            elChosen.hidden = false; elChosen.textContent = 'Nieuw: ' + l.wd + ' ' + l.dm + ' om ' + time + ' uur';
            elSubmit.disabled = false;
        }
    })();
    </script>
    @endif
</body>
</html>
