@php
    /** @var \App\Support\ChannelSite $site */
    // De afspraak boekt op naam van het BRON-channel (meta.preview.source_channel, bv.
    // bedrijfswebsite), niet de vluchtige preview-key — zo klopt de attributie met de andere
    // boekingen. De bedrijfsnaam uit het voorbeeld gaat als note mee, als context voor het team.
    $apptSite = $site->get('meta.preview.source_channel') ?: $site->key;
    $apptCompany = (string) (data_get($site->get('meta.preview.input', []), 'company') ?: $site->name());
@endphp
{{-- "Wil je zo'n website?"-modal: opent de afsprakenplanner met de GEDEELDE kalender
     (partials.slot-calendar), zelfde /afspraak-keten als de losse widget incl. Google Meet.
     Voorvult naam/e-mail/telefoon uit een eerdere modal-invoer (localStorage 'bg_contact').
     Alleen geladen op previews (zie layout.blade). --}}
<div class="pv-modal" id="pv-appt-modal" role="dialog" aria-modal="true" aria-labelledby="pv-appt-title" hidden>
    <div class="pv-modal-card pv-appt-card" data-appt data-site="{{ $apptSite }}" data-company="{{ $apptCompany }}">
        <button type="button" class="pv-close" data-pv-appt-close aria-label="Sluiten">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>

        <div class="pv-step" data-appt-step="cal">
            <h2 id="pv-appt-title">Plan een online kennismaking</h2>
            <p class="pv-sub">Kies een moment dat jou uitkomt. Een korte videoafspraak via Google&nbsp;Meet — gratis en vrijblijvend.</p>
            @include('partials.slot-calendar', [
                'emptyText' => 'Er zijn nu geen vrije momenten. Bewaar het voorbeeld, dan plannen we samen iets in.',
            ])
        </div>

        <div class="pv-step" data-appt-step="form" hidden>
            <p class="pv-appt-chosen"></p>
            <form class="pv-appt-form" novalidate>
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label>Laat leeg<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <label class="pv-field"><span>Je naam</span>
                    <input type="text" name="name" maxlength="120" required autocomplete="name" placeholder="Voor- en achternaam"></label>
                <label class="pv-field"><span>E-mailadres</span>
                    <input type="email" name="email" maxlength="190" required autocomplete="email" placeholder="jij@voorbeeld.nl"></label>
                <label class="pv-field"><span>Telefoon <em>(optioneel)</em></span>
                    <input type="tel" name="phone" maxlength="60" autocomplete="tel" placeholder="06 12345678"></label>
                <p class="pv-error" id="pv-appt-error" hidden></p>
                <div class="pv-appt-actions">
                    <button type="button" class="pv-appt-back">← Ander moment</button>
                    <button type="submit" class="pv-submit pv-appt-submit">Afspraak bevestigen</button>
                </div>
            </form>
        </div>

        <div class="pv-step" data-appt-step="done" hidden>
            <div class="pv-check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h2>Afspraak bevestigd</h2>
            <p class="pv-sub pv-appt-done-msg"></p>
        </div>
    </div>
</div>

<style>
    /* Leunt op .pv-modal / .pv-field / .pv-submit / .pv-check uit preview-save. De kalender uit
       slot-calendar hangt aan --c-*-variabelen; binnen de witte modal zetten we die op het
       modal-palet (blauw/wit), zodat 'ie bij de kaart past i.p.v. de channel-huisstijl te erven. */
    .pv-appt-card{max-width:660px;--c-primary:#1685c4;--c-on-primary:#fff;--c-accent:#1685c4;--c-ink:#0f172a;--c-muted:#64748b;--c-bg:#f8fafc;--c-surface:#fff;--radius:10px}
    .pv-appt-card .slotcal-meta{border-color:#e2e8f0}
    .pv-appt-chosen{font-weight:700;margin:0 0 1rem;color:#0f172a}
    .pv-appt-actions{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1.1rem}
    .pv-appt-back{background:none;border:0;color:#64748b;font:inherit;cursor:pointer;text-decoration:underline}
    .pv-appt-submit{width:auto;padding-left:1.4rem;padding-right:1.4rem}
    .pv-step[data-appt-step="done"]{text-align:center}
    @media(max-width:560px){.pv-appt-actions{flex-direction:column-reverse;align-items:stretch}.pv-appt-submit{width:100%}}
</style>

<script>
(function () {
    var modal = document.getElementById('pv-appt-modal');
    if (!modal) { return; }
    var card    = modal.querySelector('[data-appt]');
    var site    = card.getAttribute('data-site') || '';
    var company = card.getAttribute('data-company') || '';
    var stepCal  = modal.querySelector('[data-appt-step="cal"]'),
        stepForm = modal.querySelector('[data-appt-step="form"]'),
        stepDone = modal.querySelector('[data-appt-step="done"]'),
        elForm   = modal.querySelector('.pv-appt-form'),
        elChosen = modal.querySelector('.pv-appt-chosen'),
        elDoneMsg= modal.querySelector('.pv-appt-done-msg'),
        elError  = document.getElementById('pv-appt-error');
    var picked = null, cal = null;

    function csrf(){ var m=document.querySelector('meta[name="csrf-token"]'); return m?m.getAttribute('content'):''; }

    // Gedeeld met de bewaar-modal: wat de bezoeker eerder invulde staat hier voor.
    function prefill(){
        try {
            var c = JSON.parse(localStorage.getItem('bg_contact') || '{}');
            if (c.name  && !elForm.name.value)  elForm.name.value  = c.name;
            if (c.email && !elForm.email.value) elForm.email.value = c.email;
            if (c.phone && !elForm.phone.value) elForm.phone.value = c.phone;
        } catch (e) {}
    }
    function remember(){
        try { localStorage.setItem('bg_contact', JSON.stringify({name:elForm.name.value,email:elForm.email.value,phone:elForm.phone.value})); } catch (e) {}
    }

    function toCal(){ stepForm.hidden=true; stepDone.hidden=true; stepCal.hidden=false; picked=null; }

    function open(){
        modal.hidden=false; document.body.style.overflow='hidden';
        // Planner geopend = contact-intentie (mapt naar Meta 'Contact', consent-gated).
        if (window.bgTrack) window.bgTrack('planner_opened', { site: site });
        // De gedeelde kalender pas bij openen laden (autoload:false), niet bij page-load.
        if (!cal) {
            cal = window.bgSlotCalendar(modal.querySelector('[data-slotcal]'), {
                autoload: false,
                onPick: function (keuze) {
                    picked = keuze.waarde;
                    elChosen.textContent = 'Gekozen: ' + keuze.lang + ' om ' + keuze.time + ' uur (online via Google Meet)';
                    stepCal.hidden = true; stepForm.hidden = false; elError.hidden = true; prefill();
                    elForm.name.focus();
                }
            });
        }
        if (cal) cal.load();
    }
    function close(){ modal.hidden=true; document.body.style.overflow=''; }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-pv-appt-open]')) { e.preventDefault(); open(); }
        else if (e.target.closest('[data-pv-appt-close]') || e.target === modal) { close(); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { close(); } });

    modal.querySelector('.pv-appt-back').addEventListener('click', toCal);

    elForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!picked || !elForm.reportValidity()) { return; }
        elError.hidden = true;
        var btn = modal.querySelector('.pv-appt-submit');
        btn.disabled = true; btn.textContent = 'Bezig…';
        remember();
        var note = company ? ('Aangevraagd vanuit voorbeeld voor: ' + company) : null;
        fetch('/afspraak/boeken', {
            method: 'POST',
            headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf() },
            body: JSON.stringify({ name:elForm.name.value, email:elForm.email.value, phone:elForm.phone.value,
                website:elForm.website.value, starts_at:picked, source_site:site, note:note })
        }).then(function (r){ return r.json().catch(function(){ return {}; }).then(function (j){ return { ok:r.ok, j:j }; }); })
          .then(function (res){
            if (!res.ok || !res.j.ok) {
                btn.disabled=false; btn.textContent='Afspraak bevestigen';
                elError.textContent = res.j.message || 'Er ging iets mis. Probeer een ander moment.';
                elError.hidden = false;
                // Slot net vergeven: kalender verversen en terug, zonder de velden te verliezen.
                if (res.j && res.j.message && res.j.message.indexOf('bezet') > -1 && cal) {
                    cal.refresh().then(toCal);
                }
                return;
            }
            stepForm.hidden=true; stepDone.hidden=false;
            elDoneMsg.textContent = res.j.message || 'Je ontvangt een bevestiging met de Google Meet-link per e-mail.';
            if (window.bgTrack) window.bgTrack('appointment_booked', { site: site });
        }).catch(function(){
            btn.disabled=false; btn.textContent='Afspraak bevestigen';
            elError.textContent='Er ging iets mis. Probeer het later opnieuw.'; elError.hidden=false;
        });
    });
})();
</script>
