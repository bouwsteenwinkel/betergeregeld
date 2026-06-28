@extends('layouts.app')

@section('title', 'Website laten maken, Beter Geregeld ICT')
@section('description', 'Vraag in 2 minuten je nieuwe website aan. We plannen direct een afspraak (bezoek binnen 50 km van Bussum, anders Google Meet) en zetten alvast een voorbeeld-website klaar.')

@php
    $inp = 'w-full rounded-lg border border-[#dcdce0] bg-white px-3 py-2.5 text-sm focus:border-[#111] focus:ring-2 focus:ring-[#111]/10 outline-none';
    $lbl = 'block text-sm font-medium text-[#333] mb-1.5';
@endphp

@section('content')
<section class="section-dark relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern opacity-40"></div>
    <div class="relative max-w-[820px] mx-auto px-6 pt-16 pb-10">
        <span class="pill pill-dark mb-5">Wij bouwen wat werkt</span>
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-white">Je nieuwe website — geregeld</h1>
        <p class="mt-3 text-[color:var(--color-on-dark-soft)] max-w-[60ch]">
            Vul kort wat in over je bedrijf en plan een afspraak. We bereiden alvast een <strong class="text-white">voorbeeld-website</strong> voor,
            zodat je bij het gesprek al iets concreets ziet. Bezoek binnen {{ $radiusKm }} km van Bussum, anders via Google Meet.
        </p>
    </div>
</section>

<section class="bg-[#f7f7f8]">
    <div class="max-w-[820px] mx-auto px-6 py-10">

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                Controleer de gemarkeerde velden hieronder.
            </div>
        @endif

        <form method="POST" action="{{ route('intake.store', ['locale' => app()->getLocale()]) }}" class="space-y-6">
            @csrf
            {{-- honeypot --}}
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            {{-- Bedrijf & branche --}}
            <div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
                <h2 class="text-lg font-semibold mb-4">Je bedrijf</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $lbl }}">Bedrijfsnaam *</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="{{ $inp }}" required>
                        @error('company')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Branche *</label>
                        <select name="branche" id="branche" class="{{ $inp }}" required>
                            <option value="">— kies je branche —</option>
                            @foreach ($branches as $bk => $bl)
                                <option value="{{ $bk }}" @selected(old('branche', $branchePreset) === $bk)>{{ $bl }}</option>
                            @endforeach
                        </select>
                        @error('branche')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Huidige website (indien)</label>
                        <input type="text" name="current_website" value="{{ old('current_website') }}" placeholder="https://" class="{{ $inp }}">
                    </div>
                </div>
            </div>

            {{-- Branche-specifiek: gewenste functies + extra vragen --}}
            @foreach ($branchesDef as $bkey => $def)
                <div class="rounded-2xl border border-[#e7e7ea] bg-white p-6 hidden" data-branche-block="{{ $bkey }}">
                    <h2 class="text-lg font-semibold mb-1">Wat wil je op je site?</h2>
                    <p class="text-sm text-[#666] mb-4">Kies wat past — dit bepaalt de opzet van je voorbeeld-website.</p>

                    @if (!empty($def['features']))
                        <div class="grid sm:grid-cols-2 gap-2 mb-5">
                            @foreach ($def['features'] as $fk => $fl)
                                <label class="flex items-center gap-2.5 rounded-lg border border-[#e3e3e6] px-3 py-2.5 cursor-pointer hover:border-[#111]/30">
                                    <input type="checkbox" name="features[]" value="{{ $fk }}" @checked(in_array($fk, old('features', []))) class="rounded border-[#bbb] text-[#111] focus:ring-[#111]/20">
                                    <span class="text-sm text-[#222]">{{ $fl }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($def['questions']))
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach ($def['questions'] as $q)
                                @include('pages.partials.intake-question', ['q' => $q])
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Algemene vragen --}}
            <div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
                <h2 class="text-lg font-semibold mb-4">Over de website</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach ($common as $q)
                        @include('pages.partials.intake-question', ['q' => $q])
                    @endforeach
                    <div class="sm:col-span-2">
                        <label class="{{ $lbl }}">Iets anders dat we moeten weten?</label>
                        <textarea name="message" rows="2" class="{{ $inp }}">{{ old('message') }}</textarea>
                    </div>
                </div>
            </div>

            @include('pages.partials.intake-contact')
            @include('pages.partials.intake-slots')

            <div class="flex items-center justify-between gap-4">
                <p class="text-xs text-[#888]">Je gegevens komen alleen bij ons binnen voor deze aanvraag.</p>
                <button type="submit" class="rounded-xl bg-[#111] text-white font-semibold px-6 py-3 text-sm hover:bg-black">
                    Afspraak bevestigen →
                </button>
            </div>
        </form>
    </div>
</section>

<script>
(function () {
    var sel = document.getElementById('branche');
    var blocks = document.querySelectorAll('[data-branche-block]');
    function sync() {
        var v = sel.value;
        blocks.forEach(function (b) {
            var on = b.getAttribute('data-branche-block') === v;
            b.classList.toggle('hidden', !on);
            // velden in verborgen blokken niet meesturen
            b.querySelectorAll('input,select,textarea').forEach(function (f) { f.disabled = !on; });
        });
    }
    if (sel) { sel.addEventListener('change', sync); sync(); }
})();
</script>
@endsection
