@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Vraag je gratis voorbeeldwebsite aan')
@section('description', 'Vertel in een paar vragen wat je doet. Je krijgt ' . $levertijd . ' een voorbeeld van je eigen website te zien. Gratis, vrijblijvend, geen account.')
@section('robots', 'noindex,nofollow')

@push('head')
<style>
    .va-wrap{max-width:760px}
    .va-card{background:var(--c-surface);border:1px solid #e5e9f0;border-radius:var(--radius);padding:2rem 1.8rem;box-shadow:0 24px 60px -40px rgba(15,23,42,.35)}
    .va-field{margin-bottom:1.5rem}
    .va-field > label{display:block;font-weight:700;margin-bottom:.5rem;color:var(--c-ink)}
    .va-hint{color:var(--c-muted);font-size:.85rem;margin:.15rem 0 .6rem}
    .va-input{width:100%;padding:.85rem 1rem;border:1px solid #cbd5e1;border-radius:10px;font:inherit;color:var(--c-ink);background:#fff}
    .va-input:focus{outline:2px solid var(--c-accent);outline-offset:1px;border-color:var(--c-accent)}
    .va-rij{display:grid;gap:1rem;grid-template-columns:1fr 1fr}
    @media (max-width:640px){.va-rij{grid-template-columns:1fr}}
    .va-keuze{display:grid;gap:.6rem;grid-template-columns:repeat(auto-fit,minmax(190px,1fr))}
    .va-keuze label{display:flex;gap:.6rem;align-items:flex-start;padding:.8rem .9rem;border:1px solid #cbd5e1;border-radius:10px;cursor:pointer;font-weight:500;background:#fff}
    .va-keuze input{margin-top:.2rem}
    .va-keuze label:has(input:checked){border-color:var(--c-accent);box-shadow:inset 0 0 0 1px var(--c-accent)}
    .va-stappen{display:grid;gap:.8rem;margin:1.6rem 0 0;padding:0;list-style:none}
    .va-stappen li{display:flex;gap:.7rem;align-items:flex-start;color:var(--c-muted);font-size:.95rem}
    .va-stappen b{color:var(--c-ink)}
    .va-nr{flex:0 0 1.6rem;height:1.6rem;border-radius:50%;background:var(--c-accent);color:#fff;font-size:.8rem;font-weight:700;display:grid;place-items:center}
    .va-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
    .va-fout{background:#fee2e2;color:#991b1b;padding:.8rem 1rem;border-radius:10px;margin-bottom:1.2rem;font-size:.9rem}
</style>
@endpush

@section('content')
<section class="hero">
    <div class="wrap va-wrap">
        <span class="kicker"><span class="kicker-line"></span> Gratis en vrijblijvend</span>
        <h1>Zie hoe jouw website eruit kan zien</h1>
        <p class="lead">
            Beantwoord een paar korte vragen over je bedrijf. Wij maken daar een echt voorbeeld van,
            met jouw naam, jouw vak en jouw regio, en je krijgt het {{ $levertijd }} te zien.
            Geen account, geen kosten, je zit nergens aan vast.
        </p>
    </div>
</section>

<section style="padding-top:0">
    <div class="wrap va-wrap">

        @if ($errors->any())
            <div class="va-fout">
                Er ontbreekt nog iets: {{ implode(' ', $errors->all()) }}
            </div>
        @endif

        <form class="va-card" method="post" action="{{ $site->url('voorbeeld-aanvragen') }}">
            @csrf

            {{-- Honeypot: bots vullen dit, mensen zien het niet. --}}
            <div class="va-hp" aria-hidden="true">
                <label for="va-website">Website</label>
                <input type="text" id="va-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="va-field">
                <label for="va-company">Wat is je bedrijfsnaam?</label>
                <input class="va-input" type="text" id="va-company" name="company" maxlength="120" required
                       value="{{ old('company', request('bedrijf')) }}"
                       placeholder="Bijvoorbeeld: Van Dijk Installatietechniek" autocomplete="organization">
            </div>

            <div class="va-field">
                <label for="va-type">Wat voor bedrijf is het?</label>
                <p class="va-hint">In een paar woorden, zoals je het zelf zou zeggen.</p>
                <input class="va-input" type="text" id="va-type" name="business_type" maxlength="120" required
                       value="{{ old('business_type') }}"
                       placeholder="Bijvoorbeeld: loodgieter, kapsalon, hoveniersbedrijf, advocaat">
            </div>

            <div class="va-rij va-field">
                <div>
                    <label for="va-place">In welke plaats zit je?</label>
                    <p class="va-hint">Zo maken we het voorbeeld meteen lokaal kloppend.</p>
                    <input class="va-input" type="text" id="va-place" name="place" maxlength="80" required
                           value="{{ old('place') }}" placeholder="Bijvoorbeeld: Bussum">
                </div>
                <div>
                    <label for="va-site">Heb je nu al een website?</label>
                    <p class="va-hint">Laat leeg als je die nog niet hebt.</p>
                    <input class="va-input" type="text" id="va-site" name="current_site" maxlength="190"
                           value="{{ old('current_site') }}" placeholder="www.jouwbedrijf.nl">
                </div>
            </div>

            <div class="va-field">
                <label>Wat moet de website vooral opleveren?</label>
                <div class="va-keuze">
                    @foreach ([
                        'gebeld worden'        => 'Dat mensen bellen',
                        'offerteaanvragen'     => 'Offerteaanvragen',
                        'afspraken/boekingen'  => 'Afspraken of boekingen',
                        'gevonden worden'      => 'Beter gevonden worden',
                    ] as $waarde => $tekst)
                        <label>
                            <input type="radio" name="goal" value="{{ $waarde }}" @checked(old('goal') === $waarde)>
                            <span>{{ $tekst }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="va-field">
                <label>Welke uitstraling past bij je?</label>
                <div class="va-keuze">
                    @foreach ([
                        'strak en zakelijk'      => 'Strak en zakelijk',
                        'warm en persoonlijk'    => 'Warm en persoonlijk',
                        'stoer en robuust'       => 'Stoer en robuust',
                        'licht en fris'          => 'Licht en fris',
                    ] as $waarde => $tekst)
                        <label>
                            <input type="radio" name="sfeer" value="{{ $waarde }}" @checked(old('sfeer') === $waarde)>
                            <span>{{ $tekst }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="va-field">
                <label for="va-usp">Wat moeten we zeker weten?</label>
                <p class="va-hint">Waar je goed in bent, wat je juist níét doet, een werkgebied: alles helpt. Mag ook leeg.</p>
                <textarea class="va-input" id="va-usp" name="usp" rows="3" maxlength="400"
                          placeholder="Bijvoorbeeld: alleen particulieren, 24-uurs storingsdienst, werkgebied Gooi en Vechtstreek">{{ old('usp') }}</textarea>
            </div>

            <div class="va-rij va-field">
                <div>
                    <label for="va-name">Je naam</label>
                    <input class="va-input" type="text" id="va-name" name="contact_name" maxlength="120" required
                           value="{{ old('contact_name') }}" autocomplete="name">
                </div>
                <div>
                    <label for="va-phone">Telefoonnummer</label>
                    <input class="va-input" type="tel" id="va-phone" name="phone" maxlength="60" required
                           value="{{ old('phone') }}" autocomplete="tel">
                </div>
            </div>

            <div class="va-field">
                <label for="va-email">E-mailadres</label>
                <input class="va-input" type="email" id="va-email" name="email" maxlength="190" required
                       value="{{ old('email') }}" autocomplete="email">
            </div>

            <button class="btn" type="submit" style="width:100%">Vraag mijn gratis voorbeeld aan</button>

            <p class="va-hint" style="margin-top:.9rem;text-align:center">
                We gebruiken je gegevens alleen om je voorbeeld te maken en het met je door te nemen.
                Is iets ons niet helemaal duidelijk, dan bellen we je even kort.
            </p>
        </form>

        <ol class="va-stappen">
            <li><span class="va-nr">1</span><span><b>Je stuurt dit formulier.</b> Daarmee weten we wie je bent en wat je doet.</span></li>
            <li><span class="va-nr">2</span><span><b>We bellen je kort.</b> Een paar minuten om de laatste dingen scherp te krijgen: foto's, diensten, wat jou anders maakt.</span></li>
            <li><span class="va-nr">3</span><span><b>{{ ucfirst($levertijd) }} staat je voorbeeld klaar.</b> Bevalt het niet, dan hoor je nooit meer iets van ons.</span></li>
        </ol>

        <p class="va-hint" style="margin-top:1.4rem">
            Liever meteen iemand spreken? <a href="{{ $site->url('afspraak') }}">Plan een gesprek in</a>,
            dat kan ook zonder voorbeeld.
        </p>
    </div>
</section>
@endsection
