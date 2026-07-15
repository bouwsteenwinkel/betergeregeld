@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Maak in 60 seconden je voorbeeldwebsite')
@section('description', 'Vul je bedrijfsnaam, type bedrijf, kleur en doel in. Je ziet direct een voorbeeld van je eigen website, gratis en zonder verplichtingen.')
@section('robots', 'noindex,nofollow')

@push('head')
<style>
    .vm-wrap{max-width:760px}
    .vm-card{background:var(--c-surface);border:1px solid #e5e9f0;border-radius:var(--radius);padding:2rem 1.8rem;box-shadow:0 24px 60px -40px rgba(15,23,42,.35)}
    .vm-field{margin-bottom:1.5rem}
    .vm-field > label{display:block;font-weight:700;margin-bottom:.5rem;color:var(--c-ink)}
    .vm-hint{color:var(--c-muted);font-size:.85rem;margin:.15rem 0 .6rem}
    .vm-input{width:100%;padding:.85rem 1rem;border:1px solid #cbd5e1;border-radius:10px;font:inherit;color:var(--c-ink);background:#fff}
    .vm-input:focus{outline:2px solid var(--c-accent);outline-offset:1px;border-color:var(--c-accent)}
    /* Kleurkiezer */
    .vm-colors{display:flex;flex-wrap:wrap;gap:.55rem;align-items:center}
    .vm-swatch{width:38px;height:38px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px #cbd5e1;cursor:pointer;padding:0}
    .vm-swatch[aria-pressed="true"]{box-shadow:0 0 0 2px var(--c-ink);transform:scale(1.06)}
    .vm-color-native{width:44px;height:38px;padding:0;border:1px solid #cbd5e1;border-radius:10px;background:#fff;cursor:pointer}
    /* Doel-kaarten */
    .vm-goals{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem}
    .vm-goal{position:relative;border:1px solid #cbd5e1;border-radius:12px;padding:.9rem 1rem;cursor:pointer;display:flex;gap:.7rem;align-items:flex-start;transition:border-color .12s,background .12s}
    .vm-goal:hover{border-color:var(--c-accent)}
    .vm-goal input{position:absolute;opacity:0;pointer-events:none}
    .vm-goal .vm-tick{flex:0 0 20px;width:20px;height:20px;border-radius:50%;border:2px solid #cbd5e1;margin-top:.1rem;display:grid;place-items:center}
    .vm-goal .vm-tick svg{width:12px;height:12px;opacity:0;color:#fff}
    .vm-goal:has(input:checked){border-color:var(--c-accent);background:color-mix(in srgb,var(--c-accent) 7%,#fff)}
    .vm-goal:has(input:checked) .vm-tick{background:var(--c-accent);border-color:var(--c-accent)}
    .vm-goal:has(input:checked) .vm-tick svg{opacity:1}
    .vm-goal b{display:block;color:var(--c-ink);font-size:.95rem}
    .vm-goal span{color:var(--c-muted);font-size:.82rem}
    /* Sfeer-kaarten: 2-kleuren mini-swatch in de bestaande .vm-goal-kaart */
    .vm-sfeer-swatch{width:44px;height:32px;border-radius:8px;flex:0 0 auto;margin-top:.05rem;box-shadow:inset 0 0 0 1px rgba(15,23,42,.12)}
    /* Uitklapbare optionele secties (eigen kleur, persoonlijker maken) */
    .vm-more{margin-bottom:1.5rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff}
    .vm-more>summary{cursor:pointer;padding:.85rem 1rem;font-weight:600;color:var(--c-ink);list-style:none;display:flex;align-items:center;gap:.55rem}
    .vm-more>summary::-webkit-details-marker{display:none}
    .vm-more>summary::before{content:"";width:8px;height:8px;border-right:2px solid var(--c-muted);border-bottom:2px solid var(--c-muted);transform:rotate(-45deg);transition:transform .15s}
    .vm-more[open]>summary::before{transform:rotate(45deg)}
    .vm-more-body{padding:.1rem 1rem 1rem}
    .vm-more-body .vm-field{margin-bottom:1rem}
    .vm-more-body .vm-field:last-child{margin-bottom:.2rem}
    textarea.vm-input{resize:vertical;min-height:2.6rem}
    .vm-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
    .vm-error{display:none;margin-top:1rem;padding:.8rem 1rem;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.9rem}
    /* Laadscherm */
    .vm-overlay{position:fixed;inset:0;z-index:60;background:rgba(255,255,255,.97);display:none;place-items:center;text-align:center;padding:2rem}
    .vm-overlay.on{display:grid}
    .vm-spinner{width:56px;height:56px;border-radius:50%;border:5px solid #e2e8f0;border-top-color:var(--c-accent);animation:vmspin 1s linear infinite;margin:0 auto 1.6rem}
    @keyframes vmspin{to{transform:rotate(360deg)}}
    .vm-overlay h2{font-size:1.5rem;margin:0 0 .4rem;color:var(--c-ink)}
    .vm-step{color:var(--c-muted);min-height:1.4em;transition:opacity .3s}
    .vm-bar{width:min(320px,80vw);height:6px;border-radius:3px;background:#e2e8f0;overflow:hidden;margin:1.4rem auto 0}
    .vm-bar > span{display:block;height:100%;width:0;background:var(--c-accent);transition:width .6s ease}
    @media (prefers-reduced-motion:reduce){.vm-spinner{animation:none}}
</style>
@endpush

@section('content')
<section class="hero">
    <div class="wrap vm-wrap">
        <span class="kicker"><span class="kicker-line"></span> Gratis voorbeeld, klaar in een halve minuut</span>
        <h1>Zie hoe jouw website eruit kan zien</h1>
        <p class="lead">Vul vier dingen in. Wij zetten direct een voorbeeld van je eigen website neer, met jouw naam, jouw vak en jouw kleur. Geen account, geen verplichtingen.</p>
    </div>
</section>

<section style="padding-top:0">
    <div class="wrap vm-wrap">
        <form id="vm-form" class="vm-card" method="post" action="{{ $site->url('voorbeeld-maken') }}" novalidate>
            @csrf

            <div class="vm-field">
                <label for="vm-company">Wat is je bedrijfsnaam?</label>
                <input class="vm-input" type="text" id="vm-company" name="company" maxlength="120" required
                       value="{{ old('company', request('bedrijf')) }}"
                       placeholder="Bijvoorbeeld: Van Dijk Installatietechniek" autocomplete="organization">
            </div>

            <div class="vm-field">
                <label for="vm-type">Wat voor bedrijf is het?</label>
                <p class="vm-hint">Beschrijf je vak in een paar woorden, zoals je het zelf zou zeggen.</p>
                <input class="vm-input" type="text" id="vm-type" name="business_type" maxlength="120" required
                       placeholder="Bijvoorbeeld: loodgieter, kapsalon, hoveniersbedrijf, advocaat">
            </div>

            <div class="vm-field">
                <label>Welke uitstraling past bij je bedrijf?</label>
                <p class="vm-hint">Dit bepaalt het kleurenschema en de sfeer van je voorbeeld.</p>
                @php
                    $sfeerTitles = [
                        'fris-modern'          => ['Fris en modern', 'Helder en strak'],
                        'warm-persoonlijk'     => ['Warm en persoonlijk', 'Uitnodigend'],
                        'premium-rustig'       => ['Premium en rustig', 'Verzorgd'],
                        'speels-kleurrijk'     => ['Speels en kleurrijk', 'Energiek'],
                        'degelijk-betrouwbaar' => ['Degelijk en betrouwbaar', 'Nuchter'],
                        'natuurlijk-rustig'    => ['Natuurlijk en rustig', 'Groen en kalm'],
                    ];
                @endphp
                <div class="vm-goals">
                    @foreach ($sferen as $key => $s)
                        <label class="vm-goal">
                            <input type="radio" name="sfeer" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} required>
                            <span class="vm-tick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
                            <span class="vm-sfeer-swatch" aria-hidden="true" style="background:linear-gradient(120deg,{{ $s['primary'] }} 0 50%,{{ $s['accent2'] }} 50% 100%)"></span>
                            <span>
                                <b>{{ $sfeerTitles[$key][0] ?? $key }}</b>
                                <span>{{ $sfeerTitles[$key][1] ?? '' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="vm-field">
                <label>Wat wil je vooral bereiken?</label>
                @php $goalTitles = ['website' => 'Meer klanten', 'webshop' => 'Webshop']; @endphp
                <div class="vm-goals">
                    @foreach ($goals as $key => $label)
                        <label class="vm-goal">
                            <input type="radio" name="goal" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} required>
                            <span class="vm-tick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
                            <span>
                                <b>{{ $goalTitles[$key] ?? \Illuminate\Support\Str::ucfirst($key) }}</b>
                                <span>{{ $label }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <details class="vm-more" id="vm-color-details">
                <summary>Liever je eigen kleur? (optioneel)</summary>
                <div class="vm-more-body">
                    <p class="vm-hint">We passen de gekozen sfeer aan op jouw kleur.</p>
                    <div class="vm-colors" id="vm-colors">
                        @foreach (['#2563eb' => 'Blauw', '#0ea5e9' => 'Lichtblauw', '#059669' => 'Groen', '#ea580c' => 'Oranje', '#dc2626' => 'Rood', '#7c3aed' => 'Paars', '#0f172a' => 'Antraciet'] as $hex => $naam)
                            <button type="button" class="vm-swatch" data-color="{{ $hex }}" title="{{ $naam }}"
                                    style="background:{{ $hex }}" aria-pressed="false" aria-label="{{ $naam }}"></button>
                        @endforeach
                        <input type="color" id="vm-color-native" class="vm-color-native" value="#2563eb" aria-label="Eigen kleur kiezen">
                    </div>
                </div>
            </details>
            <input type="hidden" name="color" id="vm-color" value="">

            <details class="vm-more">
                <summary>Maak het persoonlijker (optioneel)</summary>
                <div class="vm-more-body">
                    <div class="vm-field">
                        <label for="vm-services">Waar ben je vooral goed in?</label>
                        <p class="vm-hint">Je belangrijkste diensten of producten, in je eigen woorden.</p>
                        <textarea class="vm-input" id="vm-services" name="key_services" maxlength="200" rows="2"
                                  placeholder="Bijvoorbeeld: cv-ketel vervangen, lekkage snel verhelpen, badkamer aanleggen"></textarea>
                    </div>
                    <div class="vm-field">
                        <label for="vm-place">In welke plaats of regio werk je?</label>
                        <input class="vm-input" type="text" id="vm-place" name="place" maxlength="80"
                               placeholder="Bijvoorbeeld: Zwolle en omgeving">
                    </div>
                    <div class="vm-field">
                        <label for="vm-usp">Wat maakt jullie anders?</label>
                        <input class="vm-input" type="text" id="vm-usp" name="usp" maxlength="160"
                               placeholder="Bijvoorbeeld: binnen 24 uur ter plaatse">
                    </div>
                </div>
            </details>

            <div class="vm-hp" aria-hidden="true">
                <label>Laat dit veld leeg<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn" style="width:100%;justify-content:center;font-size:1.05rem;padding:.95rem">
                Maak mijn voorbeeld
            </button>
            <p class="vm-hint" style="text-align:center;margin-top:.7rem">Klaar in ongeveer 30 seconden. Je hoeft niets in te vullen dat je niet wilt.</p>
            <div class="vm-error" id="vm-error"></div>
        </form>
    </div>
</section>

<div class="vm-overlay" id="vm-overlay" role="status" aria-live="polite">
    <div>
        <div class="vm-spinner"></div>
        <h2>We bouwen je voorbeeld</h2>
        <p class="vm-step" id="vm-step">Je kleur toepassen...</p>
        <div class="vm-bar"><span id="vm-progress"></span></div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('vm-form');
    var colorHidden = document.getElementById('vm-color');
    var native = document.getElementById('vm-color-native');
    var swatches = Array.prototype.slice.call(document.querySelectorAll('.vm-swatch'));
    var overlay = document.getElementById('vm-overlay');
    var stepEl = document.getElementById('vm-step');
    var progressEl = document.getElementById('vm-progress');
    var errorEl = document.getElementById('vm-error');

    function setColor(hex) {
        colorHidden.value = hex;
        native.value = hex;
        swatches.forEach(function (s) { s.setAttribute('aria-pressed', s.dataset.color.toLowerCase() === hex.toLowerCase() ? 'true' : 'false'); });
    }
    swatches.forEach(function (s) { s.addEventListener('click', function () { setColor(s.dataset.color); }); });
    native.addEventListener('input', function () { setColor(native.value); });

    // Eigen kleur is een OVERRIDE op het sfeer-palet. Dicht = geen override (leeg).
    // Open = kies een kleur; standaard de eerste swatch zodat er meteen iets staat.
    var colorDetails = document.getElementById('vm-color-details');
    if (colorDetails) {
        colorDetails.addEventListener('toggle', function () {
            if (colorDetails.open) {
                if (!colorHidden.value && swatches[0]) { setColor(swatches[0].dataset.color); }
            } else {
                colorHidden.value = '';
                swatches.forEach(function (s) { s.setAttribute('aria-pressed', 'false'); });
            }
        });
    }

    var steps = [
        'Je sfeer en kleuren toepassen...',
        'Teksten schrijven voor jouw vak...',
        'Een passend beeld maken voor je site...',
        'Diensten, tarieven en reviews...',
        'De laatste hand aan je pagina...'
    ];
    var timer = null;

    function runProgress() {
        var i = 0, pct = 6;
        stepEl.textContent = steps[0];
        progressEl.style.width = pct + '%';
        timer = setInterval(function () {
            i = Math.min(i + 1, steps.length - 1);
            pct = Math.min(pct + 17, 92);
            stepEl.style.opacity = 0;
            setTimeout(function () { stepEl.textContent = steps[i]; stepEl.style.opacity = 1; }, 250);
            progressEl.style.width = pct + '%';
        }, 4500);
    }
    function stopProgress() { if (timer) { clearInterval(timer); timer = null; } }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.reportValidity()) { return; }
        errorEl.style.display = 'none';
        overlay.classList.add('on');
        runProgress();

        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var jsonHeaders = { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };

        // Funnel-events. bgTrack bestaat altijd (channels/partials/analytics-head),
        // ook zonder container; dan blijven ze in de dataLayer staan.
        if (window.bgTrack) {
            window.bgTrack('preview_start', {
                business_type: (form.querySelector('[name="business_type"]') || {}).value || '',
                goal: (form.querySelector('[name="goal"]:checked') || form.querySelector('[name="goal"]') || {}).value || ''
            });
        }
        var t0 = Date.now();

        // De server stuurt bij een limiet (429) een eigen uitleg mee; die is
        // duidelijker dan de algemene melding, dus die tonen we dan.
        function fail(err) {
            stopProgress();
            overlay.classList.remove('on');
            errorEl.textContent = (err && err.userMessage)
                ? err.userMessage
                : 'Het maken van je voorbeeld lukte net niet. Probeer het zo nog een keer.';
            errorEl.style.display = 'block';
        }
        function post(url) {
            return fetch(url, { method: 'POST', headers: jsonHeaders })
                .then(function (r) { return r.json().catch(function () { return {}; }); })
                .catch(function () { return { ok: false }; });
        }

        // Fase 1: maak de site-key (snel, geen AI).
        fetch(form.action, {
            method: 'POST',
            headers: jsonHeaders,
            body: new FormData(form)
        }).then(function (r) {
            return r.json().then(function (data) { return { ok: r.ok, data: data }; });
        }).then(function (res) {
            if (!res.ok || !res.data || !res.data.contentUrl) {
                var e = new Error((res.data && res.data.error) || 'start');
                if (res.data && res.data.message) { e.userMessage = res.data.message; }
                throw e;
            }
            var d = res.data;

            // Fase 2: tekst en beeld(en) PARALLEL. Tekst is verplicht; de beelden zijn
            // best-effort en krijgen een tijdcap, zodat een trage beeld-call de
            // bezoeker niet eindeloos laat wachten (de preview toont anders een nette
            // kleur-/placeholder-variant). Bij een webshop komt er een productraster bij.
            var cap = function () { return new Promise(function (resolve) { setTimeout(function () { resolve({ capped: true }); }, 75000); }); };
            var content = post(d.contentUrl);
            var hero = Promise.race([post(d.heroUrl), cap()]);
            var products = d.productsUrl ? Promise.race([post(d.productsUrl), cap()]) : Promise.resolve(null);

            return content.then(function (c) {
                if (!c || !c.ok) { throw new Error((c && c.error) || 'content'); }
                return Promise.all([hero, products]).then(function () { return d.url; });
            });
        }).then(function (url) {
            stopProgress();
            progressEl.style.width = '100%';
            stepEl.textContent = 'Klaar. Je voorbeeld staat klaar.';
            // Het moment dat telt: de bezoeker krijgt echt een site te zien.
            if (window.bgTrack) {
                window.bgTrack('preview_ready', { seconds: Math.round((Date.now() - t0) / 1000) });
            }
            window.location.href = url;
        }).catch(function (err) {
            if (window.bgTrack) {
                window.bgTrack('preview_failed', { reason: (err && err.message) || 'onbekend' });
            }
            fail(err);
        });
    });
})();
</script>
@endsection
