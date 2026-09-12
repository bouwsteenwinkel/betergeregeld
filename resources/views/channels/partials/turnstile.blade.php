{{--
    Cloudflare Turnstile: de mensencheck, als los blokje zodat elk formulier op de
    kanaalsites hem met één regel kan opnemen:

        @include('channels.partials.turnstile', ['locale' => $site->locale()])

    Zijn de sleutels niet gezet (TURNSTILE_SITE_KEY/TURNSTILE_SECRET in de .env), dan
    rendert dit NIETS en verandert er dus niets. Zo draaien lokaal en de tests door.
    Zelfde opzet als op het contactformulier van betergeregeld.com.

    Het script staat hier bewust inline en niet via @push('head'): dan blijft de
    widget één samenhangend blokje dat overal hetzelfde werkt. Cloudflare laadt het
    async, dus het houdt de pagina niet op.

    De widget zet zelf een verborgen veld 'cf-turnstile-response' in het formulier.
    Formulieren die met fetch versturen hoeven daar niets voor te doen zolang ze
    new FormData(form) gebruiken — dan gaat het veld automatisch mee.
--}}
@if (app(\App\Services\Security\Turnstile::class)->enabled())
    <div class="cf-turnstile" style="margin:1.1rem 0"
         data-sitekey="{{ app(\App\Services\Security\Turnstile::class)->siteKey() }}"
         data-language="{{ $locale ?? 'nl' }}"
         data-theme="light"></div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
