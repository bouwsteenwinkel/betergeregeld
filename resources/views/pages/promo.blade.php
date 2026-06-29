@extends('layouts.app')

@section('title', $channel['title'] . ', Beter Geregeld ICT')
@section('description', $channel['intro'] ?? 'Vraag je nieuwe website aan en plan direct een afspraak.')

@php
    $inp = 'w-full rounded-lg border border-[#dcdce0] bg-white px-3 py-2.5 text-sm focus:border-[#111] focus:ring-2 focus:ring-[#111]/10 outline-none';
    $lbl = 'block text-sm font-medium text-[#333] mb-1.5';
    $questions = array_merge($channel['questions']['general'] ?? [], $channel['questions']['specific'] ?? []);
@endphp

@section('content')
<section class="section-dark relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern opacity-40"></div>
    <div class="relative max-w-[820px] mx-auto px-6 pt-16 pb-10">
        <div class="flex items-center gap-2 mb-5 flex-wrap">
            @if (!empty($channel['pill']))<span class="pill pill-dark">{{ $channel['pill'] }}</span>@endif
            @if (!empty($facet))<span class="pill pill-dark">{{ $facet['icon'] ?? '' }} Groeifase: {{ $facet['label'] ?? '' }}</span>@endif
        </div>
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-white">{{ $channel['title'] }}</h1>
        @if (!empty($channel['intro']))
            <p class="mt-3 text-[color:var(--color-on-dark-soft)] max-w-[60ch]">{{ $channel['intro'] }}</p>
        @endif
        @if (!empty($demoUrl))
            <a href="{{ $demoUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-5 rounded-xl border border-white/30 text-white font-semibold px-5 py-2.5 text-sm hover:bg-white/10">
                Bekijk een voorbeeld-website <span aria-hidden="true">↗</span>
            </a>
        @endif
        <p class="mt-4 text-[color:var(--color-on-dark-muted)] text-sm">Afspraak binnen 1–2 werkdagen · bezoek binnen {{ $radiusKm }} km van Bussum, anders Google Meet.</p>
    </div>
</section>

<section class="bg-[#f7f7f8]">
    <div class="max-w-[820px] mx-auto px-6 py-10">
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">Controleer de gemarkeerde velden hieronder.</div>
        @endif

        <form method="POST" action="{{ route('promo.store', ['locale' => app()->getLocale(), 'channel' => $channelKey]) }}" class="space-y-6">
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            {{-- Bedrijf --}}
            <div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
                <h2 class="text-lg font-semibold mb-4">Je bedrijf</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $lbl }}">Bedrijfsnaam *</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="{{ $inp }}" required>
                        @error('company')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Huidige website (indien)</label>
                        <input type="text" name="current_website" value="{{ old('current_website') }}" placeholder="https://" class="{{ $inp }}">
                    </div>
                </div>
            </div>

            {{-- Gewenste functies + vragen (kanaal-specifiek) --}}
            <div class="rounded-2xl border border-[#e7e7ea] bg-white p-6">
                <h2 class="text-lg font-semibold mb-1">Wat wil je op je site?</h2>
                <p class="text-sm text-[#666] mb-4">Kies wat past — dit bepaalt de opzet van je voorbeeld-website.</p>

                @if (!empty($channel['features']))
                    <div class="grid sm:grid-cols-2 gap-2 mb-5">
                        @foreach ($channel['features'] as $fk => $fl)
                            <label class="flex items-center gap-2.5 rounded-lg border border-[#e3e3e6] px-3 py-2.5 cursor-pointer hover:border-[#111]/30">
                                <input type="checkbox" name="features[]" value="{{ $fk }}" @checked(in_array($fk, old('features', []))) class="rounded border-[#bbb] text-[#111] focus:ring-[#111]/20">
                                <span class="text-sm text-[#222]">{{ $fl }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @if (!empty($questions))
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach ($questions as $q)
                            @include('pages.partials.intake-question', ['q' => $q])
                        @endforeach
                        <div class="sm:col-span-2">
                            <label class="{{ $lbl }}">Iets anders dat we moeten weten?</label>
                            <textarea name="message" rows="2" class="{{ $inp }}">{{ old('message') }}</textarea>
                        </div>
                    </div>
                @endif
            </div>

            @include('pages.partials.intake-contact')
            @include('pages.partials.intake-slots')

            <div class="flex items-center justify-between gap-4">
                <p class="text-xs text-[#888]">Je gegevens komen alleen bij ons binnen voor deze aanvraag.</p>
                <button type="submit" class="rounded-xl bg-[#111] text-white font-semibold px-6 py-3 text-sm hover:bg-black">Afspraak bevestigen →</button>
            </div>
        </form>
    </div>
</section>
@endsection
