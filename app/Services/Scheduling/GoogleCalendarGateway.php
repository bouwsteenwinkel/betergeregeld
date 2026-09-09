<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Services\Scheduling\Contracts\CalendarGateway;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Echte Google Calendar-koppeling (fase 1b): OAuth met één account (refresh-token),
 * agenda-events met Google Meet-link, en free/busy zodat eigen agenda-afspraken de
 * slots blokkeren.
 *
 * Faalt hard, niet zacht: kan de agenda niet gelezen of geschreven worden, dan gooit
 * deze klasse. Zacht terugvallen betekende hier "de agenda lijkt leeg", en dat is de
 * ene fout die je niet wilt maken met een agenda: er wordt dan dwars over bestaande
 * afspraken heen geboekt.
 */
class GoogleCalendarGateway implements CalendarGateway
{
    private const TOKEN_FILE = 'google-agenda.json';
    /**
     * De rechten die de koppeling vraagt.
     *
     * Het drive-recht zit erbij om bijlagen te kunnen kiezen uit een gedeelde
     * Drive-map. Google laat aan een agenda-item namelijk alleen Drive-bestanden
     * hangen -- een los bestand meesturen kan niet.
     *
     * WAAROM VOLLEDIG EN NIET ALLEEN LEZEN. De bestanden staan in klantmappen op
     * een gedeelde Drive, en een externe genodigde komt daar niet bij. Bij het
     * versturen geven wij hem leesrecht op precies dat ene bestand -- en een
     * rechtenwijziging kan niet met een leesrecht. Het alternatief was de map op
     * "iedereen met de link" zetten, en dat is bij klantstukken geen optie.
     *
     * LET OP: dit geldt pas na een NIEUWE koppeling. Een bestaand token houdt de
     * rechten waarmee het is afgegeven, dus zonder opnieuw koppelen blijft het
     * bij agenda alleen -- en dan mislukt het ophalen van de map.
     */
    public const SCOPE = 'https://www.googleapis.com/auth/calendar'
        . ' https://www.googleapis.com/auth/drive';

    /** Het CA-bundel, gedeeld met de Drive-koppeling. */
    public function ca(): string
    {
        return CaBundle::getSystemCaRootBundlePath();
    }

    private function calendarId(): string
    {
        return (string) config('scheduling.google.calendar_id', 'primary');
    }

    /** Nodigt Google de klant zelf uit? 'all' = uitnodiging én automatische afzegging. */
    private function sendUpdates(): string
    {
        return (string) config('scheduling.google.send_updates', 'all');
    }

    /* ───────────────────────────── OAuth-koppeling ───────────────────────────── */

    public function authUrl(string $redirectUri, string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => (string) config('scheduling.google.client_id'),
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => self::SCOPE,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'include_granted_scopes' => 'true',
            'state'         => $state,
        ]);
    }

    /**
     * Wissel de OAuth-code in voor tokens en bewaar het refresh-token.
     *
     * @return array{ok:bool,error:?string}
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        $resp = Http::asForm()->withOptions(['verify' => $this->ca()])->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => (string) config('scheduling.google.client_id'),
            'client_secret' => (string) config('scheduling.google.client_secret'),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        if (! $resp->successful() || ! $resp->json('refresh_token')) {
            Log::warning('Google-agenda: token-exchange mislukt', ['status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);

            return ['ok' => false, 'error' => 'Geen refresh-token ontvangen. Probeer opnieuw en geef expliciet opnieuw toestemming.'];
        }

        // De local-disk staat op throw=false/report=false: een mislukte schrijfactie
        // geeft daar `false` terug in plaats van te gooien. Zonder deze controle meldde
        // het koppelscherm "gekoppeld" terwijl het token nergens stond en elke boeking
        // daarna stil zonder agenda-item bleef.
        $written = Storage::put(self::TOKEN_FILE, json_encode([
            'refresh_token' => $resp->json('refresh_token'),
            'connected_at'  => now()->toIso8601String(),
        ]));

        if ($written === false) {
            Log::error('Google-agenda: tokenbestand kon niet worden weggeschreven', ['pad' => self::TOKEN_FILE]);

            return ['ok' => false, 'error' => 'Het tokenbestand kon niet worden opgeslagen (schrijfrechten op storage/app). De koppeling is niet bewaard.'];
        }

        if ($at = $resp->json('access_token')) {
            Cache::put('google_agenda_access_token', $at, now()->addMinutes(50));
        }

        // Koppelen met het verkeerde Google-account is een fout die pas opvalt als je
        // je afwezig waant terwijl de afspraken in een andere agenda staan. Liever hier
        // weigeren dan maanden later ontdekken.
        $expected = (string) config('scheduling.google.expected_account');

        if ($expected === '') {
            return ['ok' => true, 'error' => null];
        }

        $actual = $this->connectedEmail();

        // Geen account kunnen ophalen betekent hier weigeren, niet doorlaten. Anders is
        // één time-out van Google genoeg om precies de vergissing te laten passeren die
        // deze controle moet tegenhouden — en dan staat het verkeerde account gekoppeld
        // met een geldig token, dus zonder enig later signaal.
        if ($actual === null) {
            $this->disconnect();

            return ['ok' => false, 'error' => 'Kon bij Google niet controleren wélk account je koppelde, dus de koppeling is niet bewaard. Probeer het zo opnieuw.'];
        }

        if (strcasecmp($expected, $actual) !== 0) {
            $this->disconnect();

            return ['ok' => false, 'error' => "Je koppelde {$actual}, maar de afspraken horen in de agenda van {$expected}. De koppeling is ongedaan gemaakt; log in met het juiste account."];
        }

        return ['ok' => true, 'error' => null];
    }

    public function isConnected(): bool
    {
        if (! config('scheduling.google.client_id') || ! Storage::exists(self::TOKEN_FILE)) {
            return false;
        }
        $d = json_decode((string) Storage::get(self::TOKEN_FILE), true);

        return ! empty($d['refresh_token']);
    }

    public function disconnect(): void
    {
        Storage::delete(self::TOKEN_FILE);
        Cache::forget('google_agenda_access_token');
    }

    /** E-mailadres van het gekoppelde account (= id van de primary agenda). Null als het niet lukt. */
    public function connectedEmail(): ?string
    {
        $token = $this->accessToken();
        if (! $token) {
            return null;
        }
        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(10)
                ->get('https://www.googleapis.com/calendar/v3/calendars/primary');

            // Zonder statuscontrole gaf een 401 hier gewoon null terug en toonde het
            // koppelscherm "gekoppeld" zonder account, wat niet te onderscheiden was
            // van een werkende koppeling.
            return $resp->successful() ? $resp->json('id') : null;
        } catch (\Throwable $e) {
            Log::warning('Google-agenda: account opvragen mislukt: ' . $e->getMessage());

            return null;
        }
    }

    /** Ook de Drive-koppeling heeft dit token nodig; het is dezelfde koppeling. */
    public function accessToken(): ?string
    {
        if ($t = Cache::get('google_agenda_access_token')) {
            return $t;
        }
        if (! $this->isConnected()) {
            return null;
        }
        $refresh = json_decode((string) Storage::get(self::TOKEN_FILE), true)['refresh_token'] ?? null;
        if (! $refresh) {
            return null;
        }
        $resp = Http::asForm()->withOptions(['verify' => $this->ca()])->post('https://oauth2.googleapis.com/token', [
            'client_id'     => (string) config('scheduling.google.client_id'),
            'client_secret' => (string) config('scheduling.google.client_secret'),
            'refresh_token' => $refresh,
            'grant_type'    => 'refresh_token',
        ]);
        $token = $resp->json('access_token');
        if ($token) {
            Cache::put('google_agenda_access_token', $token, now()->addMinutes(50));
        } else {
            Log::warning('Google-agenda: access-token verversen mislukt', ['status' => $resp->status()]);
        }

        return $token ?: null;
    }

    /* ───────────────────────────── Agenda-acties ─────────────────────────────── */

    /**
     * @throws CalendarUnavailableException als de bezetting niet op te halen is
     */
    public function busyPeriods(CarbonInterface $from, CarbonInterface $to): array
    {
        $token = $this->accessToken();
        if (! $token) {
            throw new CalendarUnavailableException('Geen geldig Google-token; de agenda-bezetting is onbekend.');
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(12)
                ->post('https://www.googleapis.com/calendar/v3/freeBusy', [
                    'timeMin' => $from->toRfc3339String(),
                    'timeMax' => $to->toRfc3339String(),
                    'items'   => [['id' => $this->calendarId()]],
                ]);
        } catch (\Throwable $e) {
            throw new CalendarUnavailableException('Google free/busy onbereikbaar: ' . $e->getMessage(), 0, $e);
        }

        if (! $resp->successful()) {
            throw new CalendarUnavailableException('Google free/busy gaf status ' . $resp->status() . '.');
        }

        // Bewust array-toegang en geen $resp->json('calendars.' . $id . '.busy'): die
        // dot-notatie splitst het pad op punten, dus een agenda-id met punten erin
        // (elk e-mailadres) verwees naar een niet-bestaande sleutel en gaf altijd een
        // lege bezetting terug — een agenda die stilzwijgend "helemaal vrij" leek.
        $calendars = (array) $resp->json('calendars', []);

        // We vragen precies één agenda op, dus er hoort precies één antwoord te zijn.
        // Niet blind op $calendars[$id] vertrouwen: bij de alias 'primary' (de
        // productiewaarde) mag Google de map keyen op de opgelóste id
        // (info@bouwsteenwinkel.nl). Een strikte sleutel-lookup zou dan elke keer mislukken
        // en, dankzij fail-closed, de hele widget op 503 zetten. Vandaar: eerst de
        // gevraagde sleutel, anders het enige antwoord dat er is.
        $calendar = $calendars[$this->calendarId()] ?? (count($calendars) === 1 ? reset($calendars) : null);

        if (! is_array($calendar)) {
            throw new CalendarUnavailableException('Google free/busy gaf geen bruikbare bezetting terug voor agenda ' . $this->calendarId() . '.');
        }

        if (! empty($calendar['errors'])) {
            throw new CalendarUnavailableException('Google free/busy meldt een fout voor agenda ' . $this->calendarId() . ': ' . json_encode($calendar['errors']));
        }

        return array_map(
            fn ($b) => ['start' => Carbon::parse($b['start']), 'end' => Carbon::parse($b['end'])],
            (array) ($calendar['busy'] ?? [])
        );
    }

    /**
     * @throws CalendarSyncException als het event niet aangemaakt kon worden
     */
    public function createMeetEvent(Appointment $appointment): array
    {
        $token = $this->accessToken();
        if (! $token) {
            throw new CalendarSyncException('Geen geldig Google-token; agenda-event niet aangemaakt.');
        }

        $tz   = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $body = [
            'summary'     => $this->samenvatting($appointment),
            'description' => $this->omschrijving($appointment),
            'start'       => ['dateTime' => Carbon::parse($appointment->starts_at)->toRfc3339String(), 'timeZone' => $tz],
            'end'         => ['dateTime' => Carbon::parse($appointment->ends_at)->toRfc3339String(), 'timeZone' => $tz],
            'attendees'   => [['email' => $appointment->email, 'displayName' => $appointment->name]],
            'reminders'   => ['useDefault' => true],
        ];

        // ALLEEN EEN MEET-LINK BIJ EEN MEET-AFSPRAAK. Wie op locatie afspreekt of
        // gebeld wordt, heeft niets aan een videolink in zijn uitnodiging -- die
        // nodigt juist uit om op het verkeerde moment op de verkeerde plek te zijn.
        // Dan zetten we in plaats daarvan de plek erbij.
        if ($appointment->type === 'meet') {
            $body['conferenceData'] = [
                'createRequest' => [
                    'requestId' => 'appt-' . $appointment->id . '-' . substr(md5((string) ($appointment->cancel_token ?: $appointment->id)), 0, 10),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
        } elseif ($plek = $appointment->locatie()) {
            $body['location'] = $plek;
        }

        $body['attachments'] = $this->bijlagenVoor($appointment);

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(15)
                ->post(
                    'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId())
                        . '/events?conferenceDataVersion=1&supportsAttachments=true&sendUpdates=' . $this->sendUpdates(),
                    $body
                );
        } catch (\Throwable $e) {
            throw new CalendarSyncException('Google-agenda onbereikbaar bij het aanmaken van het event: ' . $e->getMessage(), 0, $e);
        }

        if (! $resp->successful()) {
            throw new CalendarSyncException('Google-agenda weigerde het event (status ' . $resp->status() . '): ' . substr($resp->body(), 0, 200));
        }

        $eventId = $resp->json('id');
        if (! $eventId) {
            throw new CalendarSyncException('Google-agenda gaf geen event-id terug.');
        }

        return ['event_id' => $eventId, 'meet_url' => $resp->json('hangoutLink')];
    }

    /**
     * Werk een bestaand agenda-item bij en laat Google de genodigde inlichten.
     *
     * WAAROM DIT ER IS. Het bewerkscherm schreef alleen onze eigen tabel bij. Wie
     * daar een afspraak verzette, hield daarna twee waarheden over: bij ons 15:00,
     * in de agenda van de klant nog 10:00 -- en de klant hoorde niets. Hetzelfde
     * gold voor een stuk dat er achteraf bij moest.
     *
     * De bijgewerkte uitnodiging die Google hierna stuurt IS het signaal aan de
     * klant. Daarom staat sendUpdates hier op dezelfde waarde als bij het
     * aanmaken, en niet op 'none'.
     *
     * PATCH en geen PUT: wat wij niet noemen blijft staan zoals het stond.
     * `attachments` gaat wel altijd mee, ook leeg -- anders krijg je een bijlage
     * er nooit meer af.
     *
     * @return array{meet_url: string|null}
     *
     * @throws CalendarSyncException als het event niet bijgewerkt kon worden
     */
    public function updateEvent(Appointment $appointment): array
    {
        $token = $this->accessToken();
        if (! $token) {
            throw new CalendarSyncException('Geen geldig Google-token; agenda-event niet bijgewerkt.');
        }

        $eventId = (string) $appointment->google_event_id;
        if ($eventId === '') {
            throw new CalendarSyncException('Deze afspraak heeft geen agenda-item bij Google om bij te werken.');
        }

        $tz   = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $body = [
            'summary'     => $this->samenvatting($appointment),
            'description' => $this->omschrijving($appointment),
            'start'       => ['dateTime' => Carbon::parse($appointment->starts_at)->toRfc3339String(), 'timeZone' => $tz],
            'end'         => ['dateTime' => Carbon::parse($appointment->ends_at)->toRfc3339String(), 'timeZone' => $tz],
            'attendees'   => [['email' => $appointment->email, 'displayName' => $appointment->name]],
            'attachments' => $this->bijlagenVoor($appointment),
        ];

        // DE SOORT AFSPRAAK KAN VERANDERD ZIJN. Van Meet naar 'op locatie' betekent:
        // videolink weg, plek erbij. Andersom precies omgekeerd. Blijft die link
        // staan bij een bezoek op locatie, dan zit de klant achter zijn scherm te
        // wachten terwijl jij voor zijn deur staat.
        if ($appointment->type === 'meet') {
            $body['location'] = '';
            if (! $appointment->meet_url) {
                $body['conferenceData'] = [
                    'createRequest' => [
                        'requestId' => 'appt-' . $appointment->id . '-' . substr(md5((string) ($appointment->cancel_token ?: $appointment->id)), 0, 10),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ];
            }
        } else {
            $body['location'] = $appointment->locatie();
            $body['conferenceData'] = null;
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(15)
                ->patch(
                    'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId())
                        . '/events/' . rawurlencode($eventId)
                        . '?conferenceDataVersion=1&supportsAttachments=true&sendUpdates=' . $this->sendUpdates(),
                    $body
                );
        } catch (\Throwable $e) {
            throw new CalendarSyncException('Google-agenda onbereikbaar bij het bijwerken van het event: ' . $e->getMessage(), 0, $e);
        }

        if (! $resp->successful()) {
            throw new CalendarSyncException('Google-agenda weigerde de wijziging (status ' . $resp->status() . '): ' . substr($resp->body(), 0, 200));
        }

        return ['meet_url' => $resp->json('hangoutLink')];
    }

    /** De titel van het agenda-item. */
    private function samenvatting(Appointment $a): string
    {
        return (Appointment::SOORTEN[$a->type] ?? 'Kennismaking') . ' · ' . $a->name
            . ($a->company ? ' (' . $a->company . ')' : '');
    }

    /** Wat er in het agenda-item onder de titel staat. */
    private function omschrijving(Appointment $a): string
    {
        return 'Kennismaking (' . (Appointment::SOORTEN[$a->type] ?? $a->type) . ").\nNaam: {$a->name}"
            . ($a->company ? "\nBedrijf: {$a->company}" : '')
            . "\nE-mail: {$a->email}"
            . ($a->phone ? "\nTelefoon: {$a->phone}" : '')
            . ($a->source_site ? "\nVia site: {$a->source_site}" : '')
            . ($a->note ? "\nBericht: {$a->note}" : '');
    }

    /**
     * De Drive-bestanden zoals Google ze aan een agenda-item wil hebben.
     *
     * Google hangt alleen Drive-bestanden aan een event, en de genodigde moet
     * erbij kunnen -- een externe klant komt niet in een gedeelde Drive. Daarom
     * eerst leesrecht op precies dat ene bestand, en pas dan de verwijzing.
     *
     * Lukt dat leesrecht niet, dan laten we die bijlage weg. Een bestand dat in de
     * uitnodiging staat maar niet te openen is, is vervelender dan geen bijlage:
     * de klant denkt dat hij iets mist en gaat bellen.
     */
    private function bijlagenVoor(Appointment $appointment): array
    {
        $drive = app(DriveClient::class);
        $uit = [];

        foreach ((array) ($appointment->attachments ?? []) as $bestandId) {
            $bestandId = (string) $bestandId;
            if ($bestandId === '') {
                continue;
            }

            if (! $drive->geefLeesrecht($bestandId, (string) $appointment->email)) {
                Log::warning("appointment_attachment (#{$appointment->id}): leesrecht op {$bestandId} mislukt, bijlage weggelaten.");

                continue;
            }

            $info = $drive->bestand($bestandId);
            if (! $info || empty($info['webViewLink'])) {
                Log::warning("appointment_attachment (#{$appointment->id}): {$bestandId} niet op te halen, bijlage weggelaten.");

                continue;
            }

            $uit[] = array_filter([
                'fileUrl'  => $info['webViewLink'],
                'title'    => $info['name'] ?? null,
                'mimeType' => $info['mimeType'] ?? null,
                'iconLink' => $info['iconLink'] ?? null,
            ]);
        }

        return $uit;
    }

    /**
     * @throws CalendarSyncException als het event niet verwijderd kon worden
     */
    public function deleteEvent(string $eventId): void
    {
        $token = $this->accessToken();
        if (! $token) {
            throw new CalendarSyncException('Geen geldig Google-token; agenda-event niet verwijderd.');
        }

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(12)
                ->delete('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId())
                    . '/events/' . rawurlencode($eventId) . '?sendUpdates=' . $this->sendUpdates());
        } catch (\Throwable $e) {
            throw new CalendarSyncException('Google-agenda onbereikbaar bij het verwijderen van het event: ' . $e->getMessage(), 0, $e);
        }

        // 410 = al weg, 404 = bestaat niet (meer). Allebei de gewenste eindtoestand:
        // de annuleerlink uit de mail mag twee keer aangeklikt worden.
        if ($resp->successful() || in_array($resp->status(), [404, 410], true)) {
            return;
        }

        throw new CalendarSyncException('Google-agenda weigerde het verwijderen (status ' . $resp->status() . '): ' . substr($resp->body(), 0, 200));
    }
}
