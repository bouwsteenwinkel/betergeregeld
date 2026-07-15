<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vangt de klik-herkomst (gclid/gbraid/wbraid + utm_*) op de landingspagina en draagt
 * die mee tot het moment waarop er een lead ontstaat.
 *
 * WAAROM DIT MOET: het verkoopmodel sluit telefonisch af, dus de conversie gebeurt
 * buiten de website. Zonder de gclid op de lead kan er later geen offline conversion
 * import naar Google Ads en leert Ads dus nooit welke zoekwoorden klanten opleveren.
 *
 * TWEE DRAGERS, EN DAT IS EEN JURIDISCHE KEUZE:
 *
 *   1. De SESSIE, altijd. De sessie-cookie staat er al en is strikt noodzakelijk voor
 *      de werking van de site (CSRF), dus er wordt geen extra informatie op het apparaat
 *      geplaatst. Wat we er server-side aan koppelen is een gewone verwerking onder de
 *      AVG (grondslag: gerechtvaardigd belang, vastleggen waar een lead vandaan komt).
 *      Dit dekt het gros van de conversies: landen, voorbeeld maken, bewaren gebeurt
 *      binnen één sessie.
 *
 *   2. Een eigen COOKIE van 30 dagen, ALLEEN met toestemming voor marketing. Die rekt de
 *      herkomst op tot voorbij de sessie, zodat ook de bezoeker die via een reminder-mail
 *      op dag 1 of dag 4 terugkomt nog aan zijn klik hangt.
 *
 * WAAROM DIE TWEEDE ACHTER TOESTEMMING ZIT: art. 11.7a Tw (ePrivacy art. 5(3)) stelt een
 * eigen toestemmingseis aan het PLAATSEN van of toegang krijgen tot informatie op het
 * apparaat. Die eis staat los van de AVG-grondslag: "gerechtvaardigd belang" is een
 * antwoord op de vraag of je de gegevens mag verwerken, niet op de vraag of je de cookie
 * mag zetten. De uitzondering geldt alleen bij strikt noodzakelijk, en een klik-identifier
 * voor advertentiemeting is dat naar zijn aard niet. Dit project vraagt de bezoeker via de
 * eigen CMP expliciet om toestemming voor advertentiecookies; die dan negeren maakt de
 * consent-administratie aantoonbaar in strijd met het eigen gedrag.
 *
 * De prijs is bekend en aanvaard: van wie marketing weigert houden we alleen de herkomst
 * binnen de sessie, en zijn we die kwijt als hij dagen later via een reminder terugkomt.
 * Dat is precies de afweging die de wet maakt.
 *
 * Dataminimalisatie: alleen de acht parameters hieronder, geen landing_url/referrer_url,
 * geen IP of user-agent, en 30 dagen in plaats van het 90-daagse Ads-venster. Zodra de
 * lead bestaat staat de herkomst in de database en heeft de cookie geen functie meer.
 */
class CaptureAdAttribution
{
    /** Draagt de herkomst van landingspagina naar lead-moment. */
    public const COOKIE = 'bg_attr';

    /**
     * 30 dagen. Ruim genoeg voor de hele funnel (48u preview + reminders op dag 1 en 4
     * + een week bedenktijd), maar bewust korter dan het 90-daagse Ads-klikvenster:
     * langer bewaren levert geen extra leads op en is dus niet te verdedigen.
     */
    public const DAYS = 30;

    /**
     * gclid = gewone zoek-/displayklik. gbraid/wbraid komen in de plaats van de gclid
     * bij iOS-verkeer zonder app-tracking-toestemming; zonder die twee mist juist het
     * iPhone-verkeer volledig in de rapportage.
     *
     * De maxlengtes zijn gelijk aan de kolommen op website_leads, zodat een te lange
     * (of gemanipuleerde) parameter hier wordt afgekapt en niet pas bij de insert stuk gaat.
     */
    public const PARAMS = [
        'gclid'        => 120,
        'gbraid'       => 120,
        'wbraid'       => 120,
        'utm_source'   => 120,
        'utm_medium'   => 120,
        'utm_campaign' => 200,
        'utm_term'     => 200,
        'utm_content'  => 200,
    ];

    /** Sleutel in de sessie; zie de klassedoc voor waarom de sessie de basisdrager is. */
    public const SESSION_KEY = 'bg_attr';

    public function handle(Request $request, Closure $next): Response
    {
        // Alleen echte paginabezoeken. Een POST draagt de parameters niet in de URL,
        // die leest de herkomst via fromRequest() uit de sessie of de cookie.
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $params = $this->fromParams($request);

        // First touch wint: een bestaande herkomst wordt nooit overschreven. De klik die
        // iemand de funnel in bracht is wat Joshua wil weten ("welk zoekwoord levert
        // klanten op"); klikt dezelfde bezoeker later nog eens op een advertentie, dan
        // is dat een tweede aanraking en niet de bron. Wil je ooit naar last touch (het
        // model dat Ads zelf hanteert), dan is dit de enige plek die dat bepaalt.
        $bekend = $this->uitCookie($request) ?: (array) $request->session()->get(self::SESSION_KEY, []);

        if (! $params && ! $bekend) {
            return $next($request);
        }

        $herkomst = $bekend ?: $params;

        // De sessie mag altijd: die cookie staat er al en is strikt noodzakelijk.
        if ($request->hasSession() && $herkomst) {
            $request->session()->put(self::SESSION_KEY, $herkomst);
        }

        // De 30-dagen-cookie alleen met toestemming voor marketing. Zonder toestemming
        // reikt de herkomst dus niet verder dan deze sessie; dat is de bedoelde prijs.
        // Accepteert de bezoeker later alsnog, dan promoveert een volgend paginabezoek
        // de herkomst uit de sessie naar de cookie: vandaar dat dit ook draait als er
        // geen klik-parameters in de URL staan.
        if ($herkomst && ! $this->uitCookie($request) && $this->magMarketingCookie($request)) {
            Cookie::queue(Cookie::make(
                self::COOKIE,
                (string) json_encode($herkomst),
                self::DAYS * 24 * 60,
                '/',
                null,
                null,   // secure: null erft session.secure (SESSION_SECURE_COOKIE), net als de sessie-cookie
                true,   // httpOnly: geen enkel script hoeft hierbij, dus ook geen enkel script mag erbij
                false,
                'lax',  // de klik komt als top-level navigatie vanaf google.com binnen; 'strict' zou de eerste hit missen
            ));
        }

        return $next($request);
    }

    /**
     * Heeft de bezoeker toestemming gegeven voor advertentiecookies? De keuze staat in
     * cmp_consents; de browser draagt alleen het consent-id. Deze query draait vrijwel
     * nooit: alleen als er een klik-herkomst is en nog geen cookie.
     */
    private function magMarketingCookie(Request $request): bool
    {
        $consentId = $request->cookie('cmp_consent_id');
        if (! is_string($consentId) || ! preg_match('/^[0-9a-f-]{36}$/i', $consentId)) {
            return false;
        }

        try {
            $rij = \Illuminate\Support\Facades\DB::table('cmp_consents')
                ->where('consent_id', $consentId)
                ->where('expires_at', '>', now())
                ->first();
        } catch (\Throwable) {
            // Geen CMP-tabellen (of DB-hik): dan zeker geen marketing-cookie zetten.
            return false;
        }

        if (! $rij) {
            return false;
        }

        $keuzes = json_decode((string) ($rij->choices_json ?? ''), true);

        return is_array($keuzes) && ($keuzes['marketing'] ?? false) === true;
    }

    /**
     * De herkomst uit de cookie, of een lege array.
     *
     * @return array<string,string>
     */
    private function uitCookie(Request $request): array
    {
        $raw = $request->cookie(self::COOKIE);
        if (! filled($raw) || ! is_string($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $clean = [];
        foreach (self::PARAMS as $key => $max) {
            $value = $decoded[$key] ?? null;
            if (is_string($value) && $value !== '') {
                $clean[$key] = mb_substr($value, 0, $max);
            }
        }

        return $clean;
    }

    /**
     * De herkomst voor dit request, in volgorde van betrouwbaarheid: de cookie (de
     * eerdere aanraking bij een bezoeker die toestemming gaf), dan de sessie (de
     * aanraking binnen dit bezoek, en de enige drager als marketing geweigerd is),
     * dan de parameters op het request zelf. Die laatste tak vangt het geval waarin
     * iemand in één keer landt én converteert, want dan is de cookie pas onderweg
     * naar de browser en staat hij nog niet in $request.
     *
     * @return array<string,string>
     */
    public static function fromRequest(Request $request): array
    {
        $self = new self;

        if ($uitCookie = $self->uitCookie($request)) {
            return $uitCookie;
        }

        if ($request->hasSession()) {
            $uitSessie = (array) $request->session()->get(self::SESSION_KEY, []);
            $clean = [];
            foreach (self::PARAMS as $key => $max) {
                $value = $uitSessie[$key] ?? null;
                if (is_string($value) && $value !== '') {
                    $clean[$key] = mb_substr($value, 0, $max);
                }
            }
            if ($clean) {
                return $clean;
            }
        }

        return $self->fromParams($request);
    }

    /**
     * De herkende parameters uit de URL, afgekapt op de kolomlengte.
     *
     * @return array<string,string>
     */
    private function fromParams(Request $request): array
    {
        $out = [];
        foreach (self::PARAMS as $key => $max) {
            $value = $request->query($key);
            if (is_string($value) && trim($value) !== '') {
                $out[$key] = mb_substr(trim($value), 0, $max);
            }
        }

        return $out;
    }
}
