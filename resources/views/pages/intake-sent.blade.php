@extends('layouts.app')

@section('title', 'Afspraak bevestigd, Beter Geregeld ICT')
@section('description', 'Je afspraak voor je nieuwe website staat genoteerd.')

@section('content')
<section class="section-dark relative overflow-hidden">
    <div class="absolute inset-0 grid-pattern opacity-40"></div>
    <div class="relative max-w-[700px] mx-auto px-6 py-20 text-center">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-white/10">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-white">Top, {{ $done['name'] }} — afspraak genoteerd!</h1>
        <p class="mt-4 text-[color:var(--color-on-dark-soft)] max-w-[52ch] mx-auto">
            We zien je <strong class="text-white">{{ $done['when'] }}</strong>,
            {{ $done['type'] === 'onsite' ? 'wij komen bij je langs' : 'online via Google Meet' }}.
            Je krijgt een bevestiging op <strong class="text-white">{{ $done['email'] }}</strong>.
        </p>
        <p class="mt-3 text-[color:var(--color-on-dark-muted)] max-w-[52ch] mx-auto text-sm">
            We bereiden alvast een voorbeeld-website voor je branche voor, zodat je bij het gesprek al iets concreets ziet.
        </p>
        <a href="{{ route('home') }}" class="inline-block mt-8 rounded-xl bg-white text-[#111] font-semibold px-6 py-3 text-sm hover:bg-white/90">Terug naar home</a>
    </div>
</section>
@endsection
