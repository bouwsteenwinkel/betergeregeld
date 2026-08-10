@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', 'Je aanvraag is binnen')
@section('robots', 'noindex,nofollow')

@push('head')
<style>
    .vb-wrap{max-width:680px}
    .vb-card{background:var(--c-surface);border:1px solid #e5e9f0;border-radius:var(--radius);padding:2rem 1.8rem;box-shadow:0 24px 60px -40px rgba(15,23,42,.35)}
    .vb-stappen{display:grid;gap:1rem;margin:1.6rem 0 0;padding:0;list-style:none}
    .vb-stappen li{display:flex;gap:.8rem;align-items:flex-start}
    .vb-nr{flex:0 0 1.8rem;height:1.8rem;border-radius:50%;background:var(--c-accent);color:#fff;font-size:.85rem;font-weight:700;display:grid;place-items:center}
    .vb-stappen b{display:block;color:var(--c-ink)}
    .vb-stappen span.tekst{color:var(--c-muted);font-size:.95rem}
</style>
@endpush

@section('content')
<section class="hero">
    <div class="wrap vb-wrap">
        <span class="kicker"><span class="kicker-line"></span> Aanvraag ontvangen</span>
        <h1>Dank je, we gaan ermee aan de slag</h1>
        <p class="lead">Je krijgt {{ $levertijd }} een voorbeeld van je eigen website te zien. Hieronder lees je wat er nu gebeurt.</p>
    </div>
</section>

<section style="padding-top:0">
    <div class="wrap vb-wrap">
        <div class="vb-card">
            <ol class="vb-stappen">
                <li>
                    <span class="vb-nr">1</span>
                    <span><b>We bellen je kort</b><span class="tekst">Een paar minuten, meestal nog vandaag. We vragen door op de dingen die een formulier niet vangt: je mooiste klussen, welke diensten voorop moeten, of je al foto's hebt. Daar wordt het voorbeeld echt beter van.</span></span>
                </li>
                <li>
                    <span class="vb-nr">2</span>
                    <span><b>Wij maken het voorbeeld</b><span class="tekst">Met jouw naam, jouw vak en jouw regio. Geen sjabloon met een logo erop, maar een site zoals die van jou zou kunnen zijn.</span></span>
                </li>
                <li>
                    <span class="vb-nr">3</span>
                    <span><b>Je krijgt een link, {{ $levertijd }}</b><span class="tekst">Rustig bekijken, met iemand anders overleggen, en dan pas beslissen. Bevalt het niet, dan laat je het weten en hoor je niets meer van ons.</span></span>
                </li>
            </ol>

            <p style="margin:1.8rem 0 0;color:var(--c-muted);font-size:.95rem">
                Iets vergeten te vertellen, of wil je liever direct een gesprek?
                <a href="{{ $site->url('afspraak') }}">Plan een moment in</a> dat jou uitkomt.
            </p>
        </div>
    </div>
</section>
@endsection
