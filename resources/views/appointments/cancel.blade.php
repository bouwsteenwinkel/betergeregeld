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
        /* De gedeelde kalender (partials/slot-calendar) hangt aan deze variabelen in
           plaats van aan vaste kleuren. Deze pagina staat buiten de channel-layout en
           heeft dus geen thema-tokens; hier zetten we ze op het eigen palet.

           --c-primary is de donkerdere tint (de hover-kleur van .btn-primary) en niet
           #1685c4: de gekozen dag is wit-op-gevuld, en op #1685c4 haalt dat 4.05:1,
           net onder de 4.5 die tekst nodig heeft. De lichtere tint blijft wel het
           accent voor de stip, want die draagt geen tekst. */
        :root{--c-primary:#12719f;--c-on-primary:#fff;--c-accent:#1685c4;--c-ink:#0f172a;--c-muted:#64748b;--c-bg:#f7f9fb;--radius:8px}
        body{margin:0;font:16px/1.55 system-ui,-apple-system,'Segoe UI',sans-serif;color:#0f172a;background:#f7f9fb}
        .box{max-width:720px;margin:10vh auto 4rem;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:2rem;box-shadow:0 18px 40px -32px rgba(15,23,42,.4)}
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
        .muted{font-size:.85rem;color:#94a3b8}
        .actions{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-top:1.1rem}
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
                @include('partials.slot-calendar', [
                    'emptyText' => 'Er zijn nu geen vrije momenten. Mail ons, dan prikken we samen een datum.',
                    'metaTekst' => false,
                ])
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
            elChosen = form.querySelector('[data-chosen]'),
            elSubmit = form.querySelector('[data-submit]');

        // Zelfde component én dezelfde bron als de boekwidget (SlotEngine), zodat hier
        // nooit een moment staat dat het boeken daarna afkeurt.
        window.bgSlotCalendar(form.querySelector('[data-slotcal]'), {
            onPick: function (keuze) {
                at.value = keuze.waarde;
                elChosen.hidden = false;
                elChosen.textContent = 'Nieuw: ' + keuze.kort + ' om ' + keuze.time + ' uur';
                elSubmit.disabled = false;
            },
            onEmpty: function () { elSubmit.disabled = true; },
            onError: function () { elSubmit.disabled = true; }
        });
    })();
    </script>
    @endif
</body>
</html>
