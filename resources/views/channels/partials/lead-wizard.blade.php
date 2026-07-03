@php
    /** @var \App\Support\ChannelSite $site */
    // Conversational "gratis voorbeeld"-lead-flow als HERBRUIKBAAR bouwblok.
    // Zelfstandig: eigen scoped CSS + vanilla JS, past zich aan het thema van de
    // host-pagina aan via --primary (licht thema) of --gold (donker thema).
    // De vragen/opties komen branche-specifiek uit config/promo.php; de rest
    // (doel/timing/afspraak) is universeel. Antwoorden POSTen naar leadStore.
    $ch       = (array) config('promo.channels.' . $site->key, []);
    $zaakNaam = $site->name();

    // Stijl-opties uit de branche-config (general → 'style'); anders een fallback.
    $styleOpts = [];
    foreach (($ch['questions']['general'] ?? []) as $q) {
        if (($q['key'] ?? '') === 'style' && ! empty($q['options'])) {
            $styleOpts = $q['options'];
        }
    }
    if (! $styleOpts) {
        $styleOpts = ['warm' => 'Warm & persoonlijk', 'modern' => 'Strak & modern', 'luxe' => 'Luxe', 'stoer' => 'Stoer'];
    }

    // Feature-checklist (branche-specifiek). Key => label.
    $featureOpts = (array) ($ch['features'] ?? []);

    // Doel → bepaalt de Groeidiamant-fase (facet) waarin de lead instapt.
    $goals = [
        ['value' => 'meer_klanten',  'label' => 'Meer klanten & afspraken',   'icon' => 'chart',  'facet' => 'website'],
        ['value' => 'professioneel', 'label' => 'Professioneler overkomen',    'icon' => 'spark',  'facet' => 'website'],
        ['value' => 'vindbaar',      'label' => 'Beter vindbaar in Google',    'icon' => 'search', 'facet' => 'website'],
        ['value' => 'webshop',       'label' => 'Online verkopen (webshop)',   'icon' => 'cart',   'facet' => 'webshop'],
    ];

    $timings = [
        ['value' => 'asap',       'label' => 'Zo snel mogelijk'],
        ['value' => 'maand',      'label' => 'Binnen een maand'],
        ['value' => 'kwartaal',   'label' => 'Dit kwartaal'],
        ['value' => 'orienteren', 'label' => 'Ik oriënteer me nog'],
    ];

    $totalSteps = 5;
@endphp

<section id="gratis-voorbeeld" class="lwz-band" data-section="formulier">
    <div class="lwz-wrap">
        <div class="lwz-head">
            <span class="lwz-eyebrow">Zelf ook zo'n website?</span>
            <h2 class="lwz-title">Vraag je gratis voorbeeld aan</h2>
            <p class="lwz-sub">Dit is een voorbeeld. Beantwoord een paar korte vragen en wij zetten een
                voorbeeld van <strong>jouw</strong> zaak klaar, klaar bij je afspraak, vaak al binnen 1&nbsp;à&nbsp;2 dagen.
                <span class="lwz-reassure">Gratis &middot; vrijblijvend &middot; geen verplichtingen</span>
            </p>
        </div>

        @if ($errors->any())
            <div class="lwz-errors">Controleer even: {{ implode(' ', $errors->all()) }}</div>
        @endif

        <form class="lwz" method="POST" action="{{ $site->url('contact') }}" novalidate>
            @csrf
            <input type="text" name="website" class="lwz-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
            {{-- verborgen velden die de keuze-stappen vullen --}}
            <input type="hidden" name="facet"            value="{{ $facet ?? '' }}">
            <input type="hidden" name="goal"             value="{{ old('goal') }}">
            <input type="hidden" name="style"            value="{{ old('style') }}">
            <input type="hidden" name="timing"           value="{{ old('timing') }}">
            <input type="hidden" name="appointment_type" value="{{ old('appointment_type') }}">
            {{-- Plaats-prefill (bv. vanaf een /plaatsen-pagina), zodat de lead getagd wordt. --}}
            <input type="hidden" name="city"             value="{{ old('city', $placePrefill ?? '') }}">

            <div class="lwz-progress"><span class="lwz-bar"></span></div>

            {{-- 1 ─ Doel (opener, bepaalt de groeifase) --}}
            <div class="lwz-step is-active" data-step="1">
                <p class="lwz-q">Wat wil je vooral bereiken?</p>
                <div class="lwz-opts" data-choice="goal" data-required data-advance>
                    @foreach ($goals as $g)
                        <button type="button" class="lwz-opt" data-value="{{ $g['value'] }}" data-facet="{{ $g['facet'] }}">
                            <span class="lwz-opt-ic">@include('channels.partials.icon', ['name' => $g['icon']])</span>{{ $g['label'] }}
                        </button>
                    @endforeach
                </div>
                <div class="lwz-nav"><span></span><span></span></div>
            </div>

            {{-- 2 ─ Contactgegevens (vroeg vangen: ook bij afhaken hebben we een lead) --}}
            <div class="lwz-step" data-step="2">
                <p class="lwz-q">Waar mogen we je voorbeeld naartoe sturen?</p>
                <p class="lwz-help">We sturen je vrijblijvend een voorbeeld van jouw zaak, geen verplichtingen.</p>
                <label class="lwz-label" for="lwz-name">Je naam</label>
                <input id="lwz-name" class="lwz-input" type="text" name="contact_name" value="{{ old('contact_name') }}" placeholder="Je naam" autocomplete="name" data-required>
                <label class="lwz-label" for="lwz-email">E-mailadres</label>
                <input id="lwz-email" class="lwz-input" type="email" name="email" value="{{ old('email') }}" placeholder="naam@voorbeeld.nl" autocomplete="email" data-required>
                <label class="lwz-label" for="lwz-phone">Telefoonnummer</label>
                <input id="lwz-phone" class="lwz-input" type="tel" name="phone" value="{{ old('phone') }}" placeholder="06 12 34 56 78" autocomplete="tel" data-required>
                <div class="lwz-nav"><button type="button" class="lwz-back">← Terug</button><button type="button" class="lwz-next">Volgende →</button></div>
            </div>

            {{-- 3 ─ Zaak + huidige website (samengevoegd) --}}
            <div class="lwz-step" data-step="3">
                <p class="lwz-q">Vertel kort over je zaak</p>
                <label class="lwz-label" for="lwz-company">Naam van je zaak</label>
                <input id="lwz-company" class="lwz-input" type="text" name="company" value="{{ old('company') }}"
                       placeholder="bijv. {{ $zaakNaam }}" autocomplete="organization" data-required>
                <label class="lwz-label" for="lwz-website">Huidige website <span class="lwz-opt-note">(optioneel)</span></label>
                <input id="lwz-website" class="lwz-input" type="text" name="current_website" value="{{ old('current_website') }}"
                       placeholder="Plak 'm hier, of typ: nee">
                <div class="lwz-nav"><button type="button" class="lwz-back">← Terug</button><button type="button" class="lwz-next">Volgende →</button></div>
            </div>

            {{-- 4 ─ Stijl --}}
            <div class="lwz-step" data-step="4">
                <p class="lwz-q">Welke uitstraling past bij je zaak?</p>
                <div class="lwz-opts" data-choice="style" data-required data-advance>
                    @foreach ($styleOpts as $val => $label)
                        <button type="button" class="lwz-opt" data-value="{{ $val }}">{{ $label }}</button>
                    @endforeach
                </div>
                <div class="lwz-nav"><button type="button" class="lwz-back">← Terug</button><span></span></div>
            </div>

            {{-- 5 ─ Afspraak + verzenden --}}
            <div class="lwz-step" data-step="5">
                <p class="lwz-q">Hoe bespreken we je voorbeeld?</p>
                <div class="lwz-opts" data-choice="appointment_type" data-required>
                    <button type="button" class="lwz-opt" data-value="onsite"><span class="lwz-opt-ic">@include('channels.partials.icon', ['name' => 'coffee'])</span>Liever langskomen <small>(omgeving Bussum)</small></button>
                    <button type="button" class="lwz-opt" data-value="meet"><span class="lwz-opt-ic">@include('channels.partials.icon', ['name' => 'laptop'])</span>Online via Google&nbsp;Meet</button>
                </div>
                <p class="lwz-help" style="margin-top:14px">Wanneer komt het je uit? (optioneel)</p>
                <textarea class="lwz-input" name="appointment_note" rows="2" placeholder="bijv. doordeweeks na 17:00, of dinsdagochtend">{{ old('appointment_note') }}</textarea>
                <div class="lwz-nav">
                    <button type="button" class="lwz-back">← Terug</button>
                    <button type="submit" class="lwz-submit">Gratis voorbeeld aanvragen</button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
    /* Accent + surface komen uit het host-thema, in welke conventie dan ook:
       --primary (demo licht) / --gold (demo donker) / --c-primary (generieke layout). */
    .lwz-band{
        --lwz-accent:var(--c-cta,var(--primary,var(--gold,var(--c-primary,#b76e79))));
        --lwz-surface:var(--surface,var(--c-surface,#fff));
        padding:84px 0;background:color-mix(in srgb,var(--lwz-accent) 7%,var(--lwz-surface));
        border-top:1px solid color-mix(in srgb,currentColor 10%,transparent)}
    .lwz-wrap{max-width:840px;margin:0 auto;padding:0 24px}
    .lwz-head{text-align:center;margin-bottom:34px}
    .lwz-eyebrow{display:inline-block;text-transform:uppercase;letter-spacing:.22em;font-size:11px;font-weight:600;color:var(--lwz-accent);margin-bottom:12px}
    .lwz-title{font-size:clamp(30px,5vw,46px);margin:0 0 12px;color:inherit}
    .lwz-sub{color:color-mix(in srgb,currentColor 70%,transparent);font-size:16px;max-width:52ch;margin:0 auto}
    .lwz-reassure{display:block;margin-top:12px;font-size:13px;letter-spacing:.04em;color:var(--lwz-accent);font-weight:600}

    .lwz{position:relative;background:var(--lwz-surface,#fff);border:1px solid color-mix(in srgb,currentColor 12%,transparent);
        border-radius:calc(var(--radius,16px) * 1.2);padding:30px clamp(20px,4vw,40px) 26px;
        box-shadow:0 24px 60px color-mix(in srgb,var(--lwz-accent) 16%,transparent)}
    .lwz-hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}

    .lwz-progress{height:5px;border-radius:999px;background:color-mix(in srgb,currentColor 12%,transparent);overflow:hidden;margin-bottom:26px}
    .lwz-bar{display:block;height:100%;width:20%;background:var(--lwz-accent);border-radius:999px;transition:width .35s ease}

    .lwz-step{display:none;animation:lwz-in .3s ease}
    .lwz-step.is-active{display:block}
    @keyframes lwz-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}

    .lwz-q{font-size:clamp(20px,3.4vw,27px);font-weight:600;line-height:1.25;margin:0 0 6px;color:inherit}
    .lwz-help{color:color-mix(in srgb,currentColor 60%,transparent);font-size:14px;margin:0 0 16px}

    .lwz-input{width:100%;margin-top:14px;padding:14px 16px;font:inherit;font-size:16px;color:inherit;
        background:color-mix(in srgb,currentColor 4%,transparent);
        border:1px solid color-mix(in srgb,currentColor 22%,transparent);border-radius:var(--radius,14px);outline:none;transition:.18s}
    .lwz-input:first-of-type{margin-top:18px}
    .lwz-label{display:block;font-size:13px;font-weight:600;margin:16px 0 5px;color:color-mix(in srgb,currentColor 80%,transparent)}
    .lwz-label:first-of-type{margin-top:20px}
    .lwz-label+.lwz-input,.lwz-label+.lwz-input:first-of-type{margin-top:0}
    .lwz-opt-note{font-weight:400;color:color-mix(in srgb,currentColor 55%,transparent)}
    .lwz-input::placeholder{color:color-mix(in srgb,currentColor 45%,transparent)}
    .lwz-input:focus{border-color:var(--lwz-accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--lwz-accent) 22%,transparent)}
    .lwz-input.lwz-invalid{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.18)}
    textarea.lwz-input{resize:vertical;min-height:60px}

    .lwz-opts{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;margin-top:18px}
    .lwz-opt{display:flex;align-items:center;gap:10px;text-align:left;font:inherit;font-size:16px;color:inherit;cursor:pointer;
        padding:16px 18px;background:color-mix(in srgb,currentColor 3%,transparent);
        border:1.5px solid color-mix(in srgb,currentColor 18%,transparent);border-radius:var(--radius,14px);transition:.16s}
    .lwz-opt:hover{border-color:var(--lwz-accent);background:color-mix(in srgb,var(--lwz-accent) 8%,transparent)}
    .lwz-opt.is-sel{border-color:var(--lwz-accent);background:color-mix(in srgb,var(--lwz-accent) 16%,transparent);
        box-shadow:0 0 0 3px color-mix(in srgb,var(--lwz-accent) 16%,transparent)}
    .lwz-opt-ic{color:var(--c-accent);display:inline-flex;align-items:center}.lwz-opt-ic svg{width:20px;height:20px}
    .lwz-opt small{color:color-mix(in srgb,currentColor 55%,transparent);font-size:12px}

    .lwz-checks{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;margin-top:18px}
    .lwz-check{display:flex;align-items:center;gap:11px;padding:13px 15px;cursor:pointer;
        background:color-mix(in srgb,currentColor 3%,transparent);
        border:1.5px solid color-mix(in srgb,currentColor 16%,transparent);border-radius:var(--radius,14px);transition:.16s;font-size:15px}
    .lwz-check:hover{border-color:var(--lwz-accent)}
    .lwz-check input{width:18px;height:18px;accent-color:var(--lwz-accent);flex:none}
    .lwz-check:has(input:checked){border-color:var(--lwz-accent);background:color-mix(in srgb,var(--lwz-accent) 12%,transparent)}

    .lwz-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:26px}
    .lwz-back{font:inherit;font-size:14px;color:color-mix(in srgb,currentColor 60%,transparent);background:none;border:0;cursor:pointer;padding:8px 2px}
    .lwz-back:hover{color:inherit}
    .lwz-next,.lwz-submit{font-family:inherit;font-size:15px;font-weight:700;letter-spacing:.01em;cursor:pointer;color:var(--c-on-cta,#fff);
        background:var(--lwz-accent);border:1px solid var(--lwz-accent);border-radius:6px;padding:13px 26px;transition:.16s}
    .lwz-next:hover,.lwz-submit:hover{filter:brightness(1.04)}
    .lwz-submit{width:100%}

    .lwz-errors{max-width:840px;margin:0 auto 18px;padding:14px 18px;border-radius:var(--radius,14px);
        background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.4);color:#b91c1c;font-size:14px}
</style>

<script>
(function () {
    var form = document.currentScript.parentNode.querySelector('.lwz');
    if (!form || form.dataset.lwzInit) return;
    form.dataset.lwzInit = '1';

    var steps = Array.prototype.slice.call(form.querySelectorAll('.lwz-step'));
    var bar   = form.querySelector('.lwz-bar');
    var total = steps.length;
    var idx   = 0;

    function show(i) {
        idx = Math.max(0, Math.min(i, total - 1));
        steps.forEach(function (s, n) { s.classList.toggle('is-active', n === idx); });
        if (bar) bar.style.width = ((idx + 1) / total * 100) + '%';
        if (window.bgTrack) window.bgTrack('wizard_step', { step: idx + 1, total: total });
        var f = steps[idx].querySelector('input:not([type=hidden]),textarea');
        if (f) { try { f.focus({ preventScroll: true }); } catch (e) {} }
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Verplichte velden in de huidige stap valideren.
    function valid(step) {
        var ok = true;
        step.querySelectorAll('[data-required]').forEach(function (el) {
            if (el.classList.contains('lwz-opts')) {
                var hid = form.querySelector('input[name="' + el.dataset.choice + '"]');
                if (!hid || !hid.value) ok = false;
            } else if (!el.value.trim()) {
                el.classList.add('lwz-invalid'); ok = false;
            } else {
                el.classList.remove('lwz-invalid');
            }
        });
        return ok;
    }

    // Keuze-knoppen (single choice).
    form.querySelectorAll('.lwz-opts[data-choice]').forEach(function (group) {
        var hid = form.querySelector('input[name="' + group.dataset.choice + '"]');
        group.querySelectorAll('.lwz-opt').forEach(function (btn) {
            if (hid && hid.value === btn.dataset.value) btn.classList.add('is-sel');
            btn.addEventListener('click', function () {
                group.querySelectorAll('.lwz-opt').forEach(function (b) { b.classList.remove('is-sel'); });
                btn.classList.add('is-sel');
                if (hid) hid.value = btn.dataset.value;
                // Doel stuurt de Groeidiamant-fase.
                if (btn.dataset.facet) {
                    var fc = form.querySelector('input[name="facet"]'); if (fc) fc.value = btn.dataset.facet;
                }
                if (group.hasAttribute('data-advance')) setTimeout(function () { show(idx + 1); }, 180);
            });
        });
    });

    form.querySelectorAll('.lwz-next').forEach(function (b) {
        b.addEventListener('click', function () { if (valid(steps[idx])) show(idx + 1); });
    });
    form.querySelectorAll('.lwz-back').forEach(function (b) {
        b.addEventListener('click', function () { show(idx - 1); });
    });

    // Enter in een tekstveld = volgende (niet submitten), behalve op de laatste stap.
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.matches('.lwz-input:not(textarea)')) {
            e.preventDefault();
            if (valid(steps[idx])) show(idx + 1);
        }
    });

    form.addEventListener('submit', function (e) {
        if (!valid(steps[idx])) { e.preventDefault(); }
    });

    show(0);
})();
</script>
