@php
    /** @var \App\Support\ChannelSite $site */
    $legal = (array) config('legal', []);
    $phone = $site->brand('phone');
    $email = $site->brand('email') ?: ($legal['email'] ?? null);
    $tel   = $phone ? preg_replace('/\s+/', '', (string) $phone) : null;
@endphp
@extends('channels.layout')

@section('title', 'Contact')
@section('description', 'Neem rechtstreeks contact op: bel of mail ons over een website, webshop of slimme tools voor je bedrijf. Een gratis voorbeeld vraag je zo aan.')

@section('content')
    @include('channels.partials.breadcrumb', ['items' => [['label' => 'Home', 'url' => $site->url('')], ['label' => 'Contact']]])
    <section class="hero hero--slim">
        <div class="wrap">
            <span class="kicker"><span class="kicker-line"></span> Contact</span>
            <h1>Even rechtstreeks contact?</h1>
            <p class="lead" style="max-width:56ch">Liever eerst je vraag stellen of sparren over wat er mogelijk is? Bel of mail ons gerust. Je krijgt een mens aan de lijn die met je meedenkt, geen keuzemenu.</p>
        </div>
    </section>

    <section style="padding-top:1rem">
        <div class="wrap">
            <div class="grid cols-3 feature-grid">
                @if ($phone)
                    <a class="feature-card" href="tel:{{ $tel }}" style="text-decoration:none;color:inherit;display:block">
                        <svg class="ic" style="width:26px;height:26px;color:var(--c-cta)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                        <h3 style="margin:.6rem 0 .2rem">Bel ons</h3>
                        <span class="feature-rule"></span>
                        <p style="font-weight:700;color:var(--c-ink)">{{ $phone }}</p>
                        <p>Op werkdagen tussen 9.00 en 17.00 uur.</p>
                    </a>
                @endif
                @if ($email)
                    <a class="feature-card" href="mailto:{{ $email }}" style="text-decoration:none;color:inherit;display:block">
                        <svg class="ic" style="width:26px;height:26px;color:var(--c-cta)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <h3 style="margin:.6rem 0 .2rem">Mail ons</h3>
                        <span class="feature-rule"></span>
                        <p style="font-weight:700;color:var(--c-ink);word-break:break-word">{{ $email }}</p>
                        <p>We reageren meestal binnen één werkdag.</p>
                    </a>
                @endif
                <div class="feature-card">
                    <svg class="ic" style="width:26px;height:26px;color:var(--c-cta)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                    <h3 style="margin:.6rem 0 .2rem">Liever een voorbeeld?</h3>
                    <span class="feature-rule"></span>
                    <p>Vraag gratis en vrijblijvend een voorbeeld van jouw bedrijf aan. Dan zie je meteen wat je krijgt, zonder dat je iets hoeft af te spreken.</p>
                </div>
            </div>

            <div style="margin-top:2.2rem">
                <a href="{{ $site->url() }}#gratis-voorbeeld" class="btn">Gratis voorbeeld aanvragen</a>
            </div>
        </div>
    </section>

    @if (! empty(array_filter([$legal['legal_name'] ?? '', $legal['address'] ?? '', $legal['kvk'] ?? '', $legal['btw'] ?? ''])))
        <section style="background:var(--c-surface)">
            <div class="wrap" style="max-width:760px">
                <span class="kicker"><span class="kicker-line"></span> Bedrijfsgegevens</span>
                <h2>Wie je aan de lijn krijgt</h2>
                <div class="prose" style="margin-top:1rem">
                    @if (! empty($legal['operator']))<p><strong>{{ $legal['operator'] }}</strong></p>@endif
                    @if (! empty($legal['address']))<p>{{ $legal['address'] }}</p>@endif
                    <p>
                        @if (! empty($legal['kvk']))KvK {{ $legal['kvk'] }}@endif
                        @if (! empty($legal['btw'])) &middot; BTW {{ $legal['btw'] }}@endif
                    </p>
                </div>
            </div>
        </section>
    @endif

    @include('channels.partials.sales-trust', ['site' => $site, 'ctaTitle' => 'Klaar om te kijken wat er voor jou mogelijk is?', 'ctaHref' => $site->url() . '#gratis-voorbeeld'])
@endsection
