@php /** @var \App\Support\ChannelSite $site */ @endphp
{{-- Zelfstandige afspraken-widget: kalender + formulier, boekt same-origin via
     /afspraak/boeken. Platform-breed, dezelfde gedeelde agenda op elke trigger-site.

     De kalender zelf komt uit partials/slot-calendar: die staat ook op de verzetpagina
     en in de lead-wizard. --}}
<section class="bkg" data-section="afspraak" id="afspraak">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Direct plannen</span>
        <h2>Plan zelf een online kennismaking</h2>
        <p class="muted" style="max-width:56ch;margin-top:.3rem">Kies een moment dat jou uitkomt. Je krijgt een korte videoafspraak via Google Meet, gratis en vrijblijvend.</p>

        <div class="bkg-card" data-booking data-site="{{ $site->key }}">
            <div class="bkg-kalender">
                @include('partials.slot-calendar', [
                    'emptyText' => 'Er zijn op dit moment geen vrije momenten. Vraag gerust een gratis voorbeeld aan, dan plannen we samen iets in.',
                ])
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
        </div>
    </div>
</section>

<style>
    .bkg-card{margin-top:1.4rem;background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);
        border-radius:calc(var(--radius) + 6px);padding:1.4rem clamp(1rem,3vw,1.6rem);box-shadow:0 24px 60px -34px rgba(0,0,0,.4)}
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
    @media(max-width:560px){.bkg-actions{flex-direction:column-reverse;align-items:stretch}.bkg-submit{width:100%}}
</style>

<script>
(function () {
    var root = document.querySelector('[data-booking]');
    if (!root) return;
    var token = document.querySelector('meta[name=csrf-token]');
    token = token ? token.getAttribute('content') : '';
    var site = root.getAttribute('data-site') || '';

    var elKalender = root.querySelector('.bkg-kalender'),
        elForm = root.querySelector('.bkg-form'),
        elChosen = root.querySelector('.bkg-chosen'),
        elError = root.querySelector('.bkg-error'),
        elDone = root.querySelector('.bkg-done');

    var picked = null;

    var cal = window.bgSlotCalendar(root.querySelector('[data-slotcal]'), {
        onPick: function (keuze) {
            picked = keuze.waarde;
            elChosen.textContent = 'Gekozen: ' + keuze.kort + ' om ' + keuze.time + ' uur (online via Google Meet)';
            elKalender.hidden = true;
            elForm.hidden = false;
            elError.hidden = true;
            elForm.querySelector('input[name=name]').focus();
        }
    });

    root.querySelector('.bkg-back').addEventListener('click', function () {
        elForm.hidden = true; elKalender.hidden = false; picked = null;
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
                // Slot net vergeven: alleen de agenda verversen. Een herlaadbeurt gooide
                // de ingevulde naam en het e-mailadres weg.
                if (res.j && res.j.message && res.j.message.indexOf('bezet') > -1) {
                    cal.refresh().then(function () { elForm.hidden = true; elKalender.hidden = false; picked = null; });
                }
                return;
            }
            // Naar een aparte bevestigings-URL i.p.v. een inline melding: een echte pageview op
            // /afspraak-bevestigd is meetbaar als ads-conversie. De details staan in de mail; de
            // conversie-tracking + eenmalig-tellen zit op die pagina (server-flash).
            window.location.href = '/afspraak-bevestigd';
        }).catch(function () {
            btn.disabled = false; btn.textContent = 'Afspraak bevestigen';
            toonFout('Er ging iets mis. Probeer het later opnieuw.');
        });
    });

    function toonFout(tekst) {
        elError.textContent = tekst;
        elError.hidden = false;
    }
})();
</script>
