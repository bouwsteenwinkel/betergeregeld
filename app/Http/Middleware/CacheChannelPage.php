<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paginacache voor de kanaalsites.
 *
 * Deze pagina's zijn voor elke bezoeker gelijk — een plaatspagina wordt volledig
 * uit de database en config opgebouwd — maar werden bij elke aanroep opnieuw
 * gerenderd. Gemeten op jouw-bedrijfswebsite.nl: 0,8 tot 1,6 seconde tot de
 * eerste byte. Google haalt van zo'n site minder pagina's op, en met 984
 * plaatspagina's tegelijk in de sitemap leverde dat honderden URL's op die wel
 * gevonden maar nooit gecrawld werden.
 *
 * Wat er NIET in de cache mag:
 *   - alles behalve een gewone GET zonder query-parameters;
 *   - ingelogde bezoekers (die zien een andere kop) en previews (/_site/…),
 *     want die moeten meteen tonen wat er net gewijzigd is;
 *   - antwoorden die geen HTTP 200 + HTML zijn, of die een flash-melding of
 *     validatiefouten in de sessie hebben staan.
 *
 * Het CSRF-token wordt vóór opslag vervangen door een merkteken en bij het
 * uitserveren teruggezet met het token van deze bezoeker. Zonder die stap zou
 * elke bezoeker het token van de eerste bezoeker krijgen en zou de
 * afsprakenplanner met een 419 stuklopen.
 */
class CacheChannelPage
{
    /** Merkteken dat de plek van het CSRF-token in de opgeslagen HTML markeert. */
    private const CSRF_PLACEHOLDER = '__BG_CSRF_TOKEN__';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->cacheable($request)) {
            return $next($request);
        }

        $key = $this->key($request);
        $ttl = (int) config('channel_cache.ttl', 3600);

        $cached = Cache::get($key);
        if (is_string($cached) && $cached !== '') {
            return response($this->withToken($cached))
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Page-Cache', 'HIT');
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->storable($request, $response)) {
            Cache::put($key, $this->withPlaceholder((string) $response->getContent()), $ttl);
        }
        $response->headers->set('X-Page-Cache', 'MISS');

        return $response;
    }

    /** Mag dit verzoek überhaupt uit de cache komen? */
    private function cacheable(Request $request): bool
    {
        if (! $request->isMethod('GET')) return false;
        if ($request->getQueryString() !== null) return false;      // ?utm=… krijgt eigen HTML? nee: gewoon niet cachen
        if ($request->is('_site/*')) return false;                   // preview van een concept-kanaal
        if (! config('channel_cache.enabled', true)) return false;

        // Ingelogd = mogelijk andere inhoud; nooit uit een gedeelde cache.
        try {
            if ($request->user() !== null) return false;
        } catch (\Throwable $e) { /* geen auth-context: dan is het een gast */ }

        return true;
    }

    /** Mag dit antwoord worden opgeslagen? */
    private function storable(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200) return false;
        $type = (string) $response->headers->get('Content-Type');
        if (stripos($type, 'text/html') === false) return false;

        // Een pagina met een flash-melding of formulierfouten is persoonlijk.
        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->has('errors') || $session->has('status') || $session->has('flash')) return false;
        }

        return $response->getContent() !== '' && $response->getContent() !== false;
    }

    private function key(Request $request): string
    {
        return 'chpage:v' . static::generation() . ':' . sha1($request->getHost() . '|' . $request->getPathInfo());
    }

    /**
     * Volgnummer dat in elke sleutel meegaat. Ophogen = de hele paginacache in
     * één klap ongeldig maken, zonder dat we een lijst met sleutels hoeven bij
     * te houden. Nodig omdat de file- en database-cache geen tags kennen; met
     * `Cache::flush()` zouden we ook alle andere caches slopen.
     */
    public static function generation(): int
    {
        return (int) Cache::get('chpage:generation', 1);
    }

    /** Gooit de paginacache weg (zie `php artisan channel:cache-clear`). */
    public static function flush(): int
    {
        $next = static::generation() + 1;
        Cache::forever('chpage:generation', $next);

        return $next;
    }

    /** Token eruit vóór opslag. */
    private function withPlaceholder(string $html): string
    {
        $token = csrf_token();
        if ($token === '' || $token === null) return $html;

        return str_replace($token, self::CSRF_PLACEHOLDER, $html);
    }

    /** Token van déze bezoeker erin bij het uitserveren. */
    private function withToken(string $html): string
    {
        return str_replace(self::CSRF_PLACEHOLDER, (string) csrf_token(), $html);
    }
}
