@php
    /** @var \App\Support\ChannelSite $site */
    // De drie ingesproken luisterdemo's (telefonie.betergeregeld.com/demo/*), herbruikbaar:
    //   'kaarten' (standaard) = eigen sectie met drie kaarten, de demo die het dichtst bij dít
    //                           vak ligt vooraan en gemarkeerd; voor telefoniepagina en Groeidiamant.
    //   'strook'              = één compacte band met de dichtstbijzijnde demo en twee kleine links
    //                           naar de andere twee; voor de channel-homes zonder eigen telefonieband.
    // Data: config/telefonie_basis.php (luisterdemos + luister_voorkeur); een kanaal kan met
    // 'luister_demo' in zijn telefonie-config een andere voorkeur opgeven.
    $modus  = $modus ?? 'kaarten';
    $basis  = (array) config('telefonie_basis', []);
    $demos  = (array) ($basis['luisterdemos'] ?? []);
    $telKey = config('channel_telefonie.' . $site->key);
    $eigen  = $telKey ? (string) (config($telKey . '.luister_demo') ?? '') : '';
    $voorkeur = $eigen !== '' && isset($demos[$eigen]) ? $eigen : (string) ($basis['luister_voorkeur'][$site->key] ?? 'apotheek');
    if (! isset($demos[$voorkeur])) { $voorkeur = (string) array_key_first($demos); }
    // Voorkeur vooraan, de rest in vaste volgorde.
    $volgorde = array_merge([$voorkeur], array_values(array_diff(array_keys($demos), [$voorkeur])));
    $uid = 'ld' . substr(md5($site->key . $modus), 0, 6);
@endphp
@if ($demos)
@once
@push('head')
<style>
/* Luisterdemo's: kaarten + strook. Thema-bewust via --c-*; golfjes zijn puur CSS. */
.ld-kaarten{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:1.6rem}
@media(max-width:900px){.ld-kaarten{grid-template-columns:1fr}}
.ld-kaart{position:relative;display:flex;flex-direction:column;gap:.7rem;padding:1.3rem 1.3rem 1.1rem;border-radius:calc(var(--radius) + 4px);background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-ink) 9%,transparent);color:inherit;text-decoration:none;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.ld-kaart:hover,.ld-kaart:focus-visible{transform:translateY(-3px);box-shadow:0 22px 44px -26px rgba(0,0,0,.45);border-color:color-mix(in srgb,var(--c-accent) 55%,transparent)}
.ld-kaart.is-dichtbij{border-color:color-mix(in srgb,var(--c-primary) 55%,transparent);box-shadow:0 18px 40px -26px color-mix(in srgb,var(--c-primary) 60%,transparent)}
.ld-badge{position:absolute;top:-.7rem;left:1.1rem;background:var(--c-primary);color:#fff;font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:.28rem .65rem;border-radius:999px}
.ld-kop{display:flex;align-items:center;gap:.8rem}
.ld-play{flex:none;width:48px;height:48px;border-radius:50%;background:var(--c-cta);color:var(--c-on-cta);display:grid;place-items:center;box-shadow:0 10px 22px -10px color-mix(in srgb,var(--c-cta) 70%,transparent);transition:transform .18s}
.ld-kaart:hover .ld-play{transform:scale(1.08)}
.ld-play svg{width:20px;height:20px;margin-left:2px}
.ld-vak{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c-cta)}
.ld-bedrijf{font-weight:800;font-size:1.08rem;line-height:1.2;color:var(--c-ink)}
.ld-golf{display:flex;align-items:center;gap:2px;height:30px}
.ld-golf i{flex:1;display:block;border-radius:2px;background:color-mix(in srgb,var(--c-primary) 35%,transparent);height:var(--h,30%)}
.ld-kaart:hover .ld-golf i{background:var(--c-primary)}
.ld-waarom{font-size:.92rem;color:var(--c-muted);margin:0}
.ld-hs{margin:0;padding:0;list-style:none;display:grid;gap:.25rem;font-size:.86rem}
.ld-hs li{display:flex;gap:.55rem;align-items:baseline}
.ld-hs b{flex:none;width:1.25rem;height:1.25rem;border-radius:50%;background:color-mix(in srgb,var(--c-accent) 20%,transparent);color:var(--c-primary);font-size:.7rem;font-weight:800;display:grid;place-items:center}
.ld-voet{display:flex;justify-content:space-between;align-items:center;gap:.6rem;margin-top:auto;padding-top:.5rem;border-top:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);font-size:.88rem}
.ld-voet strong{color:var(--c-primary)}
.ld-voet strong::after{content:" →"}
.ld-voet .duur{color:var(--c-muted)}
/* strook */
.ld-strook{margin:2.4rem 0 0;padding:1.1rem 1.3rem;border-radius:calc(var(--radius) + 6px);background:var(--c-ink);color:#fff;display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:.3rem 1.2rem;align-items:center;text-decoration:none;transition:transform .18s}
.ld-strook:hover{transform:translateY(-2px)}
.ld-strook .ld-play{grid-column:1;grid-row:1/span 2;width:54px;height:54px}
.ld-strook .ld-tekst{grid-column:2;grid-row:1;display:flex;flex-wrap:wrap;gap:.2rem .7rem;align-items:baseline;line-height:1.3}
.ld-strook .ld-tekst strong{font-size:1.05rem}
.ld-strook .ld-tekst span{font-size:.88rem;color:rgba(255,255,255,.7)}
.ld-strook .ld-golf{grid-column:2;grid-row:2;height:26px;max-width:560px}
.ld-strook .ld-golf i{background:rgba(255,255,255,.35)}
.ld-strook:hover .ld-golf i{background:var(--c-accent)}
.ld-strook .ld-duur{grid-column:3;grid-row:1/span 2;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:.4rem}
.ld-strook .ld-duur svg{width:18px;height:18px}
.ld-anderen{margin-top:.7rem;font-size:.88rem;color:var(--c-muted)}
.ld-anderen a{color:var(--c-primary);font-weight:700}
@media(max-width:640px){.ld-strook{grid-template-columns:auto minmax(0,1fr)}.ld-strook .ld-play{grid-row:1}.ld-strook .ld-duur{grid-row:2;grid-column:2}.ld-strook .ld-golf{display:none}}
@media(prefers-reduced-motion:reduce){.ld-kaart,.ld-strook,.ld-play{transition:none}}
</style>
@endpush
@endonce

@if ($modus === 'strook')
    @php $d = $demos[$voorkeur]; @endphp
    <a class="ld-strook" href="{{ $d['url'] }}" aria-label="Luister mee met {{ $d['bedrijf'] }}: {{ $d['duur'] }}">
        <span class="ld-play" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
        <span class="ld-tekst"><strong>Liever eerst luisteren?</strong><span>Vijf ingesproken gesprekken met {{ $d['bedrijf'] }}, naast het portaal dat live meebeweegt.</span></span>
        <span class="ld-duur">{{ $d['duur'] }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
        <span class="ld-golf" aria-hidden="true">@for ($i = 0; $i < 56; $i++)<i style="--h:{{ 18 + (int) (abs(sin($i * .9) * 60) + abs(cos($i * 2.3) * 20)) }}%"></i>@endfor</span>
    </a>
    <p class="ld-anderen">Ook te beluisteren: @foreach (array_slice($volgorde, 1) as $k)<a href="{{ $demos[$k]['url'] }}">{{ $demos[$k]['bedrijf'] }}</a>{{ $loop->last ? '.' : ' en ' }}@endforeach</p>
@else
    <section data-section="luisterdemos" id="luisteren" style="background:color-mix(in srgb,var(--c-accent) 6%,transparent)">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Luister mee</span>
            <h2>{{ $titel ?? 'Hoor hoe het klinkt, voordat je belt' }}</h2>
            <p class="section-lead muted">{{ $lead ?? 'Drie verzonnen bedrijven, drie echte assistenten. Per demo vijf ingesproken gesprekken, met ernaast het portaal dat live meebeweegt: je ziet wat de ondernemer ervan terugziet.' }}</p>
            <div class="ld-kaarten">
                @foreach ($volgorde as $n => $k)
                    @php $d = $demos[$k]; @endphp
                    <a class="ld-kaart {{ $n === 0 ? 'is-dichtbij' : '' }}" href="{{ $d['url'] }}">
                        @if ($n === 0)<span class="ld-badge">Dichtst bij jouw vak</span>@endif
                        <span class="ld-kop">
                            <span class="ld-play" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                            <span><span class="ld-vak">{{ $d['vak'] }} · demo</span><br><span class="ld-bedrijf">{{ $d['bedrijf'] }}</span></span>
                        </span>
                        <span class="ld-golf" aria-hidden="true">@for ($i = 0; $i < 40; $i++)<i style="--h:{{ 18 + (int) (abs(sin($i * 1.1 + $n) * 60) + abs(cos($i * 2.1) * 20)) }}%"></i>@endfor</span>
                        <p class="ld-waarom">{{ $d['waarom'] }}</p>
                        <ol class="ld-hs">
                            @foreach ((array) $d['hoofdstukken'] as $i => $h)<li><b>{{ $i + 1 }}</b><span>{{ $h }}</span></li>@endforeach
                        </ol>
                        <span class="ld-voet"><strong>Luister mee</strong><span class="duur">{{ $d['duur'] }} · zelf bellen: {{ $d['nummer'] }}</span></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endif
