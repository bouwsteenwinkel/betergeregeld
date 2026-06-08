@php($sa = $serviceAccount ?? null)

<div class="space-y-6 text-sm leading-relaxed text-gray-700 dark:text-gray-300">

    <div>
        <p>
            Op deze pagina koppel je een website aan <strong>Google Search Console (GSC)</strong>, zodat we de
            zoekdata (klikken, vertoningen, posities, zoekwoorden) importeren en in het klant-dashboard tonen.
            Eén rij = één website. De data halen we op met ons gedeelde service-account; de klant hoeft alleen
            dat account éénmalig toegang te geven.
        </p>
    </div>

    {{-- Service-account --}}
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
        <p class="font-semibold text-gray-900 dark:text-white">Ons service-account (dit deelt de klant toegang mee):</p>
        @if ($sa)
            <p class="mt-1 select-all font-mono text-primary-600 dark:text-primary-400">{{ $sa }}</p>
        @else
            <p class="mt-1 text-danger-600 dark:text-danger-400">
                Service-account JSON ontbreekt op deze omgeving (<code>storage/app/google-api.json</code>).
            </p>
        @endif
    </div>

    {{-- Stappen klant --}}
    <div>
        <p class="mb-2 font-semibold text-gray-900 dark:text-white">
            1. Wat de klant doet (eenmalig, per website)
        </p>
        <ol class="list-decimal space-y-1 pl-5">
            <li>Inloggen op <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="text-primary-600 underline dark:text-primary-400">Google Search Console</a> met het account dat eigenaar is van de site.</li>
            <li>Bovenaan links de juiste <strong>property</strong> kiezen.</li>
            <li>Linksonder naar <strong>Instellingen</strong> (tandwiel) → <strong>Gebruikers en machtigingen</strong>.</li>
            <li>Klik <strong>Gebruiker toevoegen</strong>.</li>
            <li>E-mailadres: <span class="font-mono">{{ $sa ?? 'ons service-account' }}</span></li>
            <li>Machtiging <strong>“Beperkt”</strong> (alleen lezen) volstaat.</li>
            <li>Opslaan. Bij meerdere sites: per property herhalen.</li>
        </ol>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            Zonder deze stap kunnen we géén zoekdata ophalen (de import geeft dan een 403).
        </p>
    </div>

    {{-- Stappen admin --}}
    <div>
        <p class="mb-2 font-semibold text-gray-900 dark:text-white">
            2. Wat jij hier instelt
        </p>
        <ol class="list-decimal space-y-1 pl-5">
            <li>
                Klik <strong>Nieuwe GSC-property</strong> en vul de <strong>site-URL</strong> exact zoals in Search Console:
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    <li><span class="font-mono">sc-domain:klant.nl</span> — domein-property (aanbevolen; vangt alle subdomeinen + http/https).</li>
                    <li><span class="font-mono">https://klant.nl/</span> — URL-prefix-property (let op de afsluitende slash).</li>
                </ul>
            </li>
            <li>Kies de <strong>tenant</strong> (de klant). Leeg laten = platform/eigen site.</li>
            <li>Opslaan.</li>
        </ol>
    </div>

    {{-- Stappen verifiëren --}}
    <div>
        <p class="mb-2 font-semibold text-gray-900 dark:text-white">
            3. Verifiëren &amp; eerste import
        </p>
        <ol class="list-decimal space-y-1 pl-5">
            <li>
                Klik in de lijst op <strong>Toegang testen</strong>:
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    <li><strong>Groen</strong> = de klant heeft ons account toegang gegeven, je bent klaar.</li>
                    <li><strong>Nog geen toegang</strong> = de melding toont welke properties ons account wél mag lezen — handig om de exacte <span class="font-mono">site-URL</span> te kiezen.</li>
                </ul>
            </li>
            <li>Klik <strong>Nu importeren</strong> voor de eerste 30 dagen historie.</li>
            <li>Daarna draait de <strong>dagelijkse import</strong> automatisch verder — niets meer te doen.</li>
        </ol>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            Tip: weet je de juiste vorm van de site-URL niet zeker? Maak de rij aan met je beste gok en klik
            “Toegang testen” — de lijst met toegankelijke properties laat exact zien welke vorm je moet gebruiken.
        </p>
    </div>

</div>
