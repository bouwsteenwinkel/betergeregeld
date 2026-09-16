@php
    // Rekenhulp "wat levert AI-telefonie op" op de telefoniepagina van een kanaal (16-09-2026,
    // eerst alleen bakkerij). Verwacht $c = TelefonieConfig::for($site); cijfers en teksten
    // staan in $c['rekenhulp'] (basis + kanaal). De eerste stand wordt hier in PHP
    // uitgerekend zodat de pagina zonder JavaScript al een compleet voorbeeld toont;
    // de schuiven rekenen daarna in de browser met dezelfde formule (zie script onderaan).
    //
    // WAT ER BEWUST NIET IN ZIT: winst uit gemiste bestellingen (we tonen omzet en zeggen
    // dat), en een claim dat de assistent personeel vervangt. De uitkomst heet "vrij"
    // en "misgelopen", nooit "besparing op personeel".
    $rk   = (array) ($c['rekenhulp'] ?? []);
    $st   = (array) ($rk['standaard'] ?? []);
    $tk   = (array) ($rk['teksten'] ?? []);
    $maand = (float) ($rk['maandprijs'] ?? 89);
    $inbeg = (int) ($rk['inbegrepen_gesprekken'] ?? 200);
    $extra = (float) ($rk['extra_per_gesprek'] ?? 0.45);

    $perDag   = (int) ($st['per_dag'] ?? 20);
    $dagen    = (int) ($st['dagen'] ?? 6);
    $minuten  = (float) ($st['minuten'] ?? 2);
    $uurloon  = (float) ($st['uurloon'] ?? 22);
    $oppakken = (float) ($st['oppakken'] ?? 3);
    $gemistWk = (int) ($st['gemist_per_week'] ?? 8);
    $bestPct  = (int) ($st['bestelling_pct'] ?? 25);
    $bestWrd  = (float) ($st['bestelwaarde'] ?? 18);
    $afhPct   = (int) ($st['afhandel_pct'] ?? 70);

    $weken      = 4.33;
    $perMaand   = $perDag * $dagen * $weken;
    $afgevangen = $perMaand * $afhPct / 100;
    $urenTel    = $afgevangen * $minuten / 60;
    $urenOnderb = $afgevangen * $oppakken / 60;
    $eurTijd    = ($urenTel + $urenOnderb) * $uurloon;
    $eurOmzet   = $gemistWk * $weken * $bestPct / 100 * $bestWrd;
    $gesprAI    = $perMaand + $gemistWk * $weken;
    $eurKosten  = $maand + max(0, $gesprAI - $inbeg) * $extra;
    $eur = fn ($x) => '€ ' . number_format($x, 0, ',', '.');
    // Bereik van de waardeschuif volgt het vak: € 18 voor een taart, € 6.000 voor een architect.
    $wrdMax  = max(120, (int) ceil($bestWrd * 3));
    $wrdStep = $wrdMax > 1000 ? 50 : ($wrdMax > 300 ? 5 : 1);
    $eenmalig = preg_replace('/ voor .*/', '', (string) ($c['prijs']['eenmalig'] ?? '€ 295'));
@endphp
<section data-section="rekenhulp">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Wat het je oplevert</span>
        <h2>Reken het na met je eigen cijfers</h2>
        <p class="lead muted" style="max-width:62ch">{{ $tk['lead'] ?? 'Een rekenvoorbeeld, geen belofte.' }}</p>

        <form class="rk" id="rk" onsubmit="return false">
            <div class="rk-in">
                <div class="rk-groep">
                    <h3>De telefoon nu</h3>
                    <label for="rk_per_dag">Telefoontjes per werkdag <output for="rk_per_dag" id="rk_per_dag_o">{{ $perDag }}</output></label>
                    <input type="range" id="rk_per_dag" name="per_dag" min="2" max="80" step="1" value="{{ $perDag }}">
                    <label for="rk_dagen">Werkdagen per week <output id="rk_dagen_o">{{ $dagen }}</output></label>
                    <input type="range" id="rk_dagen" name="dagen" min="3" max="7" step="1" value="{{ $dagen }}">
                    <label for="rk_minuten">Minuten per gesprek <output id="rk_minuten_o">{{ $minuten }}</output></label>
                    <input type="range" id="rk_minuten" name="minuten" min="0.5" max="6" step="0.5" value="{{ $minuten }}">
                    <label for="rk_uurloon">Loonkosten per uur, alles erin <output id="rk_uurloon_o">€ {{ $uurloon }}</output></label>
                    <input type="range" id="rk_uurloon" name="uurloon" min="14" max="45" step="1" value="{{ $uurloon }}">
                </div>
                <div class="rk-groep">
                    <h3>Wat het stoort en wat je mist</h3>
                    <label for="rk_oppakken">Minuten om de draad weer op te pakken, per onderbreking <output id="rk_oppakken_o">{{ $oppakken }}</output></label>
                    <input type="range" id="rk_oppakken" name="oppakken" min="0" max="10" step="0.5" value="{{ $oppakken }}">
                    <label for="rk_gemist">Telefoontjes per week die niemand opneemt <output id="rk_gemist_o">{{ $gemistWk }}</output></label>
                    <input type="range" id="rk_gemist" name="gemist" min="0" max="50" step="1" value="{{ $gemistWk }}">
                    <label for="rk_best_pct">{{ $tk['deel_label'] ?? 'Deel daarvan dat een opdracht was' }} <output id="rk_best_pct_o">{{ $bestPct }}%</output></label>
                    <input type="range" id="rk_best_pct" name="best_pct" min="0" max="100" step="5" value="{{ $bestPct }}">
                    <label for="rk_best_wrd">{{ $tk['waarde_label'] ?? 'Gemiddelde waarde van een opdracht' }} <output id="rk_best_wrd_o">€ {{ $bestWrd }}</output></label>
                    <input type="range" id="rk_best_wrd" name="best_wrd" min="0" max="{{ $wrdMax }}" step="{{ $wrdStep }}" value="{{ $bestWrd }}">
                    <label for="rk_afh">Deel van de gesprekken dat de assistent zelf afrondt <output id="rk_afh_o">{{ $afhPct }}%</output></label>
                    <input type="range" id="rk_afh" name="afh" min="30" max="95" step="5" value="{{ $afhPct }}">
                </div>
            </div>

            <div class="rk-uit card" aria-live="polite">
                <div class="rk-tegels">
                    <div><b id="rk_u_uren">{{ number_format($urenTel + $urenOnderb, 0, ',', '.') }} uur</b><span>{{ $tk['uren_tegel'] ?? 'per maand niet meer aan de telefoon of erdoor onderbroken' }}</span></div>
                    <div><b id="rk_u_tijd">{{ $eur($eurTijd) }}</b><span>{{ $tk['tijd_tegel'] ?? 'aan loonkosten die vrijkomen voor het werk zelf' }}</span></div>
                    <div><b id="rk_u_omzet">{{ $eur($eurOmzet) }}</b><span>{{ $tk['omzet_tegel'] ?? 'omzet per maand in telefoontjes die nu niemand opneemt' }}</span></div>
                    <div><b id="rk_u_kosten">{{ $eur($eurKosten) }}</b><span>kost de assistent per maand bij <em id="rk_u_gespr">{{ number_format($gesprAI, 0, ',', '.') }}</em> gesprekken</span></div>
                </div>
                <p class="rk-som">Per maand: <strong id="rk_u_saldo">{{ $eur($eurTijd + $eurOmzet - $eurKosten) }}</strong> <span id="rk_u_saldo_t">aan vrijgekomen tijd en niet-gemiste omzet, na aftrek van de assistent.</span></p>
                <p class="muted rk-klein">Zo rekenen we: de assistent rondt <span id="rk_u_afh">{{ $afhPct }}</span>% van je gesprekken zelf af; die tijd plus het weer oppakken van je werk telt tegen je uurloon. Gemiste telefoontjes tellen als omzet, niet als winst. Gesprekken boven de {{ $inbeg }} per maand kosten € {{ str_replace('.', ',', number_format($extra, 2)) }} per stuk. De uitkomst is een schatting met jouw aannames; de eenmalige inrichting van {{ $eenmalig }} zit er niet in.</p>
            </div>
        </form>
    </div>
</section>
<style>
.rk{display:grid;gap:1.4rem;margin-top:1.6rem}
@media(min-width:900px){.rk{grid-template-columns:1.15fr .85fr;align-items:start}}
.rk-in{display:grid;gap:1.2rem}
@media(min-width:640px){.rk-in{grid-template-columns:1fr 1fr}}
.rk-groep h3{font-size:1rem;margin:0 0 .6rem}
.rk label{display:flex;justify-content:space-between;gap:.8rem;font-size:.9rem;font-weight:600;margin-top:.7rem;line-height:1.3}
.rk label output{flex:0 0 auto;font-variant-numeric:tabular-nums;color:var(--c-primary);font-weight:700}
.rk input[type=range]{width:100%;accent-color:var(--c-cta);margin:.35rem 0 0;cursor:pointer}
.rk input[type=range]:focus-visible{outline:2px solid var(--c-primary);outline-offset:3px}
.rk-uit{position:sticky;top:84px}
.rk-tegels{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.rk-tegels div{background:var(--c-tint,var(--c-bg));border-radius:calc(var(--radius) - 2px);padding:.9rem 1rem}
.rk-tegels b{display:block;font-family:var(--font-display);font-size:1.45rem;line-height:1.1;font-variant-numeric:tabular-nums;color:var(--c-ink)}
.rk-tegels span{display:block;font-size:.82rem;color:var(--c-muted);margin-top:.3rem;line-height:1.35}
.rk-tegels em{font-style:normal;font-weight:700}
.rk-som{margin:1.1rem 0 .4rem;font-size:1.05rem;line-height:1.45}
.rk-som strong{font-family:var(--font-display);font-size:1.6rem;color:var(--c-primary);font-variant-numeric:tabular-nums}
.rk-som strong.neg{color:var(--c-muted)}
.rk-klein{font-size:.82rem;line-height:1.5;margin:0}
@media(max-width:480px){.rk-tegels{grid-template-columns:1fr}.rk-uit{position:static}}
</style>
<script>
(function () {
    var f = document.getElementById('rk'); if (!f) return;
    var K = {maand: {{ json_encode($maand) }}, inbeg: {{ json_encode($inbeg) }}, extra: {{ json_encode($extra) }}, weken: 4.33};
    function $(id) { return document.getElementById(id); }
    function eur(x) { return '€ ' + Math.round(x).toLocaleString('nl-NL'); }
    function reken() {
        var perDag = +$('rk_per_dag').value, dagen = +$('rk_dagen').value, minuten = +$('rk_minuten').value,
            uurloon = +$('rk_uurloon').value, oppakken = +$('rk_oppakken').value, gemist = +$('rk_gemist').value,
            bestPct = +$('rk_best_pct').value, bestWrd = +$('rk_best_wrd').value, afh = +$('rk_afh').value;
        $('rk_per_dag_o').textContent = perDag; $('rk_dagen_o').textContent = dagen;
        $('rk_minuten_o').textContent = String(minuten).replace('.', ','); $('rk_uurloon_o').textContent = '€ ' + uurloon;
        $('rk_oppakken_o').textContent = String(oppakken).replace('.', ','); $('rk_gemist_o').textContent = gemist;
        $('rk_best_pct_o').textContent = bestPct + '%'; $('rk_best_wrd_o').textContent = '€ ' + bestWrd; $('rk_afh_o').textContent = afh + '%';
        // Dezelfde formule als in PHP hierboven.
        var perMaand = perDag * dagen * K.weken, afgevangen = perMaand * afh / 100;
        var uren = afgevangen * (minuten + oppakken) / 60;
        var eurTijd = uren * uurloon;
        var eurOmzet = gemist * K.weken * bestPct / 100 * bestWrd;
        var gesprAI = perMaand + gemist * K.weken;
        var eurKosten = K.maand + Math.max(0, gesprAI - K.inbeg) * K.extra;
        var saldo = eurTijd + eurOmzet - eurKosten;
        $('rk_u_uren').textContent = Math.round(uren) + ' uur';
        $('rk_u_tijd').textContent = eur(eurTijd);
        $('rk_u_omzet').textContent = eur(eurOmzet);
        $('rk_u_kosten').textContent = eur(eurKosten);
        $('rk_u_gespr').textContent = Math.round(gesprAI).toLocaleString('nl-NL');
        $('rk_u_afh').textContent = afh;
        var s = $('rk_u_saldo');
        s.textContent = (saldo < 0 ? '– ' : '') + eur(Math.abs(saldo));
        s.classList.toggle('neg', saldo < 0);
        $('rk_u_saldo_t').textContent = saldo < 0
            ? 'komt de assistent bij deze cijfers duurder uit dan wat hij oplevert. Dan is hij vooral gemak: altijd opgenomen, ook als jij bakt.'
            : 'aan vrijgekomen tijd en niet-gemiste omzet, na aftrek van de assistent.';
    }
    f.addEventListener('input', reken);
    reken();
})();
</script>
