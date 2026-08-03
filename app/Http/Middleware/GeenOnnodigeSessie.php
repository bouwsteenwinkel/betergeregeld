<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Geeft geen sessiebestand waar er geen nodig is.
 *
 * Elke bezoeker die hier binnenkomt krijgt een sessie, ook als hij niets doet:
 * Laravel start er een om het CSRF-token in te bewaren, en de file-driver zet
 * daar een bestand voor neer dat 30 dagen blijft staan. Voor een mens die een
 * formulier gaat invullen is dat precies de bedoeling. Voor Googlebot, die in
 * één ronde 525 plaatspagina's per site langsgaat, zijn het duizenden bestanden
 * die niemand ooit opvraagt.
 *
 * Die berg maakte het opruimen zo traag dat het merkbaar werd in de wachttijd
 * (zie sessions:prune). Dit haalt de bron weg in plaats van de gevolgen.
 *
 * De aanpak: vóór StartSession de driver op 'array' zetten. De sessie werkt dan
 * gewoon binnen dat ene verzoek — het CSRF-token wordt netjes gerenderd — maar
 * er wordt niets weggeschreven.
 *
 * Drie grendels, zodat een mens dit nooit merkt:
 *   - alleen GET en HEAD; een POST heeft een geldig token nodig
 *   - alleen als er nog géén sessiecookie is; wie er al een heeft houdt hem
 *   - alleen bij een user-agent die zichzelf als crawler aankondigt
 *
 * Een crawler die zich voordoet als browser krijgt dus gewoon een sessie. Dat is
 * de goede kant om op te falen: liever een bestand te veel dan een bezoeker met
 * een formulier dat niet verzendt.
 */
class GeenOnnodigeSessie
{
    /**
     * Vaste, herkenbare crawlers. Bewust een lijst en geen slimme heuristiek:
     * "bot" in de user-agent zit ook in browsers van mensen (bv. "Botswana"), en
     * fout raden kost hier een kapot formulier.
     */
    private const CRAWLERS = [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
        'applebot', 'facebookexternalhit', 'twitterbot', 'linkedinbot', 'pinterest',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'petalbot', 'bytespider',
        'gptbot', 'ccbot', 'claudebot', 'perplexitybot', 'google-extended',
        'chatgpt-user', 'amazonbot', 'seznambot', 'screaming frog',
        'lighthouse', 'pagespeed', 'uptimerobot', 'pingdom',
    ];

    /**
     * Adressen die per definitie geen sessie nodig hebben: scripts, feeds en
     * machine-endpoints. Ze zitten in de web-groep omdat ze cookies of CSRF nodig
     * hebben op ándere onderdelen, en kregen daardoor allemaal een sessiebestand.
     *
     * Uit de meting op de productieserver (342.341 bestanden, steekproef 2.000):
     * /cmp 18,8%, /cron 8,9%, robots.txt 1,6% en sitemap.xml 0,7%. Bij elkaar
     * bijna een derde van de berg, en geen enkele daarvan hoort bij een bezoeker.
     */
    private const PADEN_ZONDER_SESSIE = [
        'cmp/loader.js',
        'cmp/scripts.js',
        'cron/',
        'robots.txt',
        'sitemap.xml',
        'llms.txt',
        '.well-known/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->magiZonderSessie($request)) {
            // De array-driver houdt de sessie binnen dit verzoek en schrijft niets
            // naar schijf. StartSession leest de driver pas als hij aan de beurt is,
            // dus dit moet ervóór staan — zie bootstrap/app.php.
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }

    private function magiZonderSessie(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        // Scripts, feeds en machine-endpoints: nooit een sessie, ook niet als er
        // toevallig een cookie meekomt. Deze adressen worden door een browser als
        // bijvangst opgehaald (de cookiebanner-loader op élke paginaweergave) of
        // door onze eigen cron, en horen geen spoor achter te laten.
        $pad = ltrim($request->path(), '/');
        foreach (self::PADEN_ZONDER_SESSIE as $vast) {
            if (str_ends_with($vast, '/') ? str_starts_with($pad, $vast) : $pad === $vast) {
                return true;
            }
        }

        // Heeft hij al een sessie, dan is die er niet voor niets.
        if ($request->cookies->has(config('session.cookie'))) {
            return false;
        }

        $agent = mb_strtolower((string) $request->userAgent());
        if ($agent === '') {
            return false;
        }

        foreach (self::CRAWLERS as $crawler) {
            if (str_contains($agent, $crawler)) {
                return true;
            }
        }

        return false;
    }
}
