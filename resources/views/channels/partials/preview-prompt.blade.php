@php
    /** @var \App\Support\ChannelSite $site */
    // Conversie-prompt op een gegenereerde preview. Verschijnt pas als de bezoeker
    // echt gekeken heeft (scroll-diepte of tijd op de pagina), want de balk bovenaan
    // scrollt weg en daarna is er niets meer dat naar ons terugleidt.
    // Bewust GEEN schermvullende modal: de preview moet als de eigen site van de
    // ondernemer blijven voelen (zie de one-pager-keuze in de tool).
@endphp

<div class="pv-prompt" id="pv-prompt" hidden role="dialog" aria-label="Plan een gesprek over je website">
    <button type="button" class="pv-prompt-x" data-pv-prompt-close aria-label="Sluiten">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <p class="pv-prompt-title">Blij met wat je ziet?</p>
    <p class="pv-prompt-body">Dit is nog maar een voorbeeld. In tien minuten aan de telefoon maken we hem samen af.</p>
    <div class="pv-prompt-actions">
        <button type="button" class="pv-prompt-cta" data-pv-appt-open>Plan een gesprek</button>
        <button type="button" class="pv-prompt-alt" data-pv-save-open>Bewaar dit voorbeeld</button>
    </div>
</div>

<style>
    .pv-prompt {
        position: fixed; right: 1rem; bottom: 1rem; z-index: 70; width: min(330px, calc(100vw - 2rem));
        background: #fff; color: #1a1a1a; border: 1px solid rgba(0, 0, 0, .1); border-radius: 10px;
        box-shadow: 0 24px 60px -18px rgba(0, 0, 0, .45); padding: 1.1rem 1.15rem 1.15rem;
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        transform: translateY(140%); transition: transform .35s ease, opacity .35s ease; opacity: 0;
    }
    .pv-prompt.show { transform: translateY(0); opacity: 1; }
    .pv-prompt-x { position: absolute; top: .5rem; right: .5rem; width: 30px; height: 30px; display: grid; place-items: center; background: none; border: 0; border-radius: 6px; color: #8a8681; cursor: pointer; }
    .pv-prompt-x:hover { background: rgba(0, 0, 0, .06); color: #1a1a1a; }
    .pv-prompt-title { margin: 0 0 .3rem; font-size: 1.02rem; font-weight: 800; letter-spacing: -.01em; padding-right: 1.5rem; }
    .pv-prompt-body { margin: 0 0 .9rem; font-size: .86rem; line-height: 1.5; color: #4a4844; }
    .pv-prompt-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .pv-prompt-cta, .pv-prompt-alt {
        flex: 1 1 auto; text-align: center; padding: .62rem .8rem; border-radius: 6px;
        font-size: .86rem; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap;
    }
    .pv-prompt-cta { background: var(--c-cta, #1685c4); color: var(--c-on-cta, #fff); border: 1.5px solid transparent; }
    .pv-prompt-alt { background: #fff; color: #1a1a1a; border: 1.5px solid rgba(0, 0, 0, .18); font: inherit; font-size: .86rem; font-weight: 700; }
    .pv-prompt-alt:hover { border-color: rgba(0, 0, 0, .4); }
    /* Op mobiel staat de sticky-CTA van de site zelf al onderaan: daarboven blijven. */
    @media (max-width: 859px) {
        .pv-prompt { right: .6rem; left: .6rem; bottom: 4.6rem; width: auto; }
    }
    @media (prefers-reduced-motion: reduce) {
        .pv-prompt { transition: none; }
    }
</style>

<script>
(function () {
    var el = document.getElementById('pv-prompt');
    if (!el) return;

    // Eén keer per preview per sessie: wie hem wegklikt of al reageerde, krijgt
    // hem niet opnieuw. sessionStorage kan gooien (private mode), dus afgeschermd.
    var KEY = 'pv-prompt-' + {!! json_encode($site->key) !!};
    function seen() { try { return sessionStorage.getItem(KEY) === '1'; } catch (e) { return false; } }
    function markSeen() { try { sessionStorage.setItem(KEY, '1'); } catch (e) {} }
    if (seen()) return;

    var shown = false, timer = null;

    function show() {
        if (shown || seen()) return;
        shown = true;
        el.hidden = false;
        // hidden weghalen en pas daarna de klasse zetten, anders geen transitie
        requestAnimationFrame(function () { el.classList.add('show'); });
        window.removeEventListener('scroll', onScroll);
        if (timer) clearTimeout(timer);
        if (window.bgTrack) window.bgTrack('preview_prompt_shown', { site: {!! json_encode($site->key) !!} });
    }

    function hide(remember) {
        el.classList.remove('show');
        if (remember) markSeen();
        setTimeout(function () { el.hidden = true; }, 350);
    }

    // Verschijnt bij echte interesse: 45% doorgescrold, of 40s op de pagina.
    function onScroll() {
        var h = document.documentElement.scrollHeight - window.innerHeight;
        if (h > 0 && (window.scrollY / h) >= 0.45) show();
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    timer = setTimeout(show, 40000);

    el.addEventListener('click', function (e) {
        if (e.target.closest('[data-pv-prompt-close]')) { hide(true); return; }
        // Bij een keuze (afspraak of bewaren) opent de bijbehorende modal eroverheen;
        // de prompt blijft eronder staan, zodat de andere keuze bereikbaar blijft als
        // de bezoeker de modal sluit. We onthouden alleen dat de prompt z'n werk deed,
        // zodat hij niet bij elke pagina opnieuw opduikt.
        if (e.target.closest('[data-pv-appt-open]') || e.target.closest('[data-pv-save-open]')) { markSeen(); }
    });
})();
</script>
