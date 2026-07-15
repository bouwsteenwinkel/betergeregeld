{{-- "Bewaar dit voorbeeld"-modal voor preview-sites: maakt een klant-account
     (WebsiteLead) + koppelt de preview via POST /bewaren, en toont daarna de
     persoonlijke revisit-link. Alleen geladen op previews (zie layout.blade). --}}
<div class="pv-modal" id="pv-save-modal" role="dialog" aria-modal="true" aria-labelledby="pv-save-title" hidden>
    <div class="pv-modal-card">
        <button type="button" class="pv-close" data-pv-save-close aria-label="Sluiten">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>

        <div class="pv-step" data-pv-step="form">
            <h2 id="pv-save-title">Bewaar dit voorbeeld</h2>
            <p class="pv-sub">Laat je gegevens achter, dan mailen we je een persoonlijke link waarmee je dit voorbeeld altijd terugvindt.</p>
            <form id="pv-save-form" action="{{ $site->url('bewaren') }}" method="post" novalidate>
                @csrf
                <label class="pv-field">
                    <span>Je naam</span>
                    <input type="text" name="contact_name" maxlength="120" required autocomplete="name" placeholder="Voor- en achternaam">
                </label>
                <label class="pv-field">
                    <span>E-mailadres</span>
                    <input type="email" name="email" maxlength="190" required autocomplete="email" placeholder="jij@voorbeeld.nl">
                </label>
                <label class="pv-field">
                    <span>Telefoon <em>(optioneel)</em></span>
                    <input type="tel" name="phone" maxlength="60" autocomplete="tel" placeholder="06 12345678">
                </label>
                <label class="pv-consent">
                    <input type="checkbox" name="consent" value="1" checked>
                    <span>Ja, hou me per e-mail op de hoogte van dit voorbeeld. Je kunt je altijd afmelden.</span>
                </label>
                {{-- Honeypot --}}
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label>Laat leeg<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <button type="submit" class="pv-submit">Bewaar mijn voorbeeld</button>
                <p class="pv-error" id="pv-save-error" hidden></p>
            </form>
        </div>

        <div class="pv-step" data-pv-step="done" hidden>
            <div class="pv-check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h2>Opgeslagen</h2>
            <p class="pv-sub">We hebben je een e-mail gestuurd met je persoonlijke link. Je kunt je voorbeeld hier ook direct openen.</p>
            <a class="pv-submit" id="pv-save-revisit" href="#" target="_blank" rel="noopener">Naar mijn voorbeelden</a>
        </div>
    </div>
</div>

<style>
    .pv-modal{position:fixed;inset:0;z-index:200;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:1.2rem}
    .pv-modal[hidden]{display:none}
    .pv-modal-card{position:relative;background:#fff;color:#0f172a;width:100%;max-width:440px;border-radius:16px;padding:1.8rem;box-shadow:0 30px 60px -20px rgba(0,0,0,.5);font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
    .pv-modal-card h2{font-size:1.35rem;margin:0 0 .4rem}
    .pv-sub{color:#64748b;font-size:.92rem;margin:0 0 1.2rem}
    .pv-close{position:absolute;top:.9rem;right:.9rem;background:none;border:0;color:#94a3b8;cursor:pointer;padding:.2rem}
    .pv-close svg{width:22px;height:22px}
    .pv-field{display:block;margin-bottom:.85rem}
    .pv-field span{display:block;font-weight:600;font-size:.88rem;margin-bottom:.3rem}
    .pv-field em{color:#94a3b8;font-style:normal;font-weight:400}
    .pv-field input{width:100%;padding:.7rem .85rem;border:1px solid #cbd5e1;border-radius:9px;font:inherit;color:#0f172a}
    .pv-field input:focus{outline:2px solid #1685c4;outline-offset:1px;border-color:#1685c4}
    .pv-consent{display:flex;gap:.55rem;align-items:flex-start;font-size:.85rem;color:#475569;margin:.4rem 0 1.1rem}
    .pv-consent input{margin-top:.15rem}
    .pv-submit{display:block;width:100%;text-align:center;background:#1685c4;color:#fff;border:0;border-radius:9px;font:inherit;font-weight:700;font-size:1rem;padding:.85rem;cursor:pointer;text-decoration:none}
    .pv-submit:hover{filter:brightness(1.05)}
    .pv-error{color:#b91c1c;font-size:.88rem;margin:.7rem 0 0}
    .pv-check{width:56px;height:56px;border-radius:50%;background:#dcfce7;color:#16a34a;display:grid;place-items:center;margin:0 auto 1rem}
    .pv-check svg{width:30px;height:30px}
    .pv-step[data-pv-step="done"]{text-align:center}
</style>

<script>
(function () {
    var modal = document.getElementById('pv-save-modal');
    if (!modal) { return; }
    var form = document.getElementById('pv-save-form');
    var errorEl = document.getElementById('pv-save-error');
    var stepForm = modal.querySelector('[data-pv-step="form"]');
    var stepDone = modal.querySelector('[data-pv-step="done"]');
    var revisit = document.getElementById('pv-save-revisit');

    function open() { modal.hidden = false; document.body.style.overflow = 'hidden'; }
    function close() { modal.hidden = true; document.body.style.overflow = ''; }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-pv-save-open]')) { e.preventDefault(); open(); }
        else if (e.target.closest('[data-pv-save-close]') || e.target === modal) { close(); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) { close(); } });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.reportValidity()) { return; }
        errorEl.hidden = true;
        var submit = form.querySelector('.pv-submit');
        submit.disabled = true; submit.textContent = 'Bezig...';

        var meta = document.querySelector('meta[name="csrf-token"]');
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta ? meta.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
          .then(function (res) {
            if (!res.ok || !res.data || !res.data.ok) { throw new Error('mislukt'); }
            if (res.data.revisitUrl) { revisit.href = res.data.revisitUrl; }
            stepForm.hidden = true; stepDone.hidden = false;
            // Dit is het leadmoment: er ontstaat een WebsiteLead met e-mailadres.
            // Hier hangt straks de Ads-conversie aan.
            if (window.bgTrack) { window.bgTrack('preview_saved', { site: {!! json_encode($site->key) !!} }); }
        }).catch(function () {
            submit.disabled = false; submit.textContent = 'Bewaar mijn voorbeeld';
            errorEl.textContent = 'Het opslaan lukte net niet. Probeer het zo nog een keer.';
            errorEl.hidden = false;
        });
    });
})();
</script>
