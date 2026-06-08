<div class="space-y-5 text-sm leading-relaxed text-gray-700 dark:text-gray-300">

    <p>
        De beveiligingsagent is een kleine WordPress-plugin die dagelijks de
        <strong>core-, plugin- en thema-versies</strong> (incl. beschikbare updates) van de site naar
        het platform stuurt. Wij matchen die tegen de Wordfence-kwetsbaarhedenfeed en signaleren
        verouderde en kwetsbare software.
    </p>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
        <p class="font-semibold text-gray-900 dark:text-white">Per-site token (geheim — alleen voor deze site):</p>
        <p class="mt-1 select-all break-all font-mono text-primary-600 dark:text-primary-400">{{ $token }}</p>
    </div>

    <div>
        <p class="mb-2 font-semibold text-gray-900 dark:text-white">Installatie (eenmalig)</p>
        <ol class="list-decimal space-y-1 pl-5">
            <li><a href="{{ $download }}" class="text-primary-600 underline dark:text-primary-400">Download de plugin</a> (<span class="font-mono">beter-geregeld-monitor.php</span>).</li>
            <li>Plaats het bestand in <span class="font-mono">wp-content/plugins/</span> op de site.</li>
            <li>Open het bestand en vul bij <span class="font-mono">BG_MONITOR_TOKEN</span> de token hierboven in (of zet <span class="font-mono">define('BG_MONITOR_TOKEN','…')</span> in <span class="font-mono">wp-config.php</span>).</li>
            <li>Activeer de plugin in <strong>WordPress → Plugins</strong>. Hij pusht meteen en daarna dagelijks.</li>
        </ol>
    </div>

    <div>
        <p class="font-semibold text-gray-900 dark:text-white">Endpoint (ter info)</p>
        <p class="mt-1 font-mono text-xs">{{ $endpoint }}/<span class="text-gray-400">{token}</span></p>
    </div>

    <p class="text-gray-500 dark:text-gray-400">
        Geen plugin-toegang? Dezelfde data kan ook door een ontwikkelaar naar het endpoint worden
        gepost met de token in de URL. Zonder agent blijven de externe checks (malware/blacklist,
        mixed content, broken links) gewoon werken.
    </p>
</div>
