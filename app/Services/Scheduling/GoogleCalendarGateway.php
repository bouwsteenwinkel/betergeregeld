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
    public const SCOPE = 'https://www.googleapis.com/auth/calendar';

    private function ca(): string
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

    private function accessToken(): ?string
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
            'summary'     => 'Online kennismaking · ' . $appointment->name,
            'description' => "Online kennismaking, aangevraagd via de website.\nNaam: {$appointment->name}\nE-mail: {$appointment->email}"
                . ($appointment->phone ? "\nTelefoon: {$appointment->phone}" : '')
                . ($appointment->source_site ? "\nVia site: {$appointment->source_site}" : '')
                . ($appointment->note ? "\nBericht: {$appointment->note}" : ''),
            'start'       => ['dateTime' => Carbon::parse($appointment->starts_at)->toRfc3339String(), 'timeZone' => $tz],
            'end'         => ['dateTime' => Carbon::parse($appointment->ends_at)->toRfc3339String(), 'timeZone' => $tz],
            'attendees'   => [['email' => $appointment->email, 'displayName' => $appointment->name]],
            'reminders'   => ['useDefault' => true],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => 'appt-' . $appointment->id . '-' . substr(md5((string) ($appointment->cancel_token ?: $appointment->id)), 0, 10),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ];

        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(15)
                ->post(
                    'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId())
                        . '/events?conferenceDataVersion=1&sendUpdates=' . $this->sendUpdates(),
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
