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
 * slots blokkeren. Valt zacht terug (geen crash) als er geen koppeling is.
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

    /** Wissel de OAuth-code in voor tokens en bewaar het refresh-token. */
    public function exchangeCode(string $code, string $redirectUri): bool
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
            return false;
        }
        Storage::put(self::TOKEN_FILE, json_encode(['refresh_token' => $resp->json('refresh_token'), 'connected_at' => now()->toIso8601String()]));
        if ($at = $resp->json('access_token')) {
            Cache::put('google_agenda_access_token', $at, now()->addMinutes(50));
        }
        return true;
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

    public function busyPeriods(CarbonInterface $from, CarbonInterface $to): array
    {
        $token = $this->accessToken();
        if (! $token) {
            return [];
        }
        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(12)
                ->post('https://www.googleapis.com/calendar/v3/freeBusy', [
                    'timeMin' => $from->toRfc3339String(),
                    'timeMax' => $to->toRfc3339String(),
                    'items'   => [['id' => $this->calendarId()]],
                ]);
            $busy = $resp->json('calendars.' . $this->calendarId() . '.busy', []);
            return array_map(fn ($b) => ['start' => Carbon::parse($b['start']), 'end' => Carbon::parse($b['end'])], (array) $busy);
        } catch (\Throwable $e) {
            Log::warning('Google-agenda free/busy fout: ' . $e->getMessage());
            return [];
        }
    }

    public function createMeetEvent(Appointment $appointment): array
    {
        $token = $this->accessToken();
        if (! $token) {
            return ['event_id' => null, 'meet_url' => null];
        }
        $tz = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $body = [
            'summary'     => 'Online kennismaking · ' . $appointment->name,
            'description' => "Online kennismaking, aangevraagd via de website.\nNaam: {$appointment->name}\nE-mail: {$appointment->email}"
                . ($appointment->phone ? "\nTelefoon: {$appointment->phone}" : '')
                . ($appointment->source_site ? "\nVia site: {$appointment->source_site}" : ''),
            'start'       => ['dateTime' => Carbon::parse($appointment->starts_at)->toRfc3339String(), 'timeZone' => $tz],
            'end'         => ['dateTime' => Carbon::parse($appointment->ends_at)->toRfc3339String(), 'timeZone' => $tz],
            'attendees'   => [['email' => $appointment->email]],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => 'appt-' . $appointment->id . '-' . substr(md5((string) ($appointment->cancel_token ?: $appointment->id)), 0, 10),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ];
        try {
            $resp = Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(15)
                ->post('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId()) . '/events?conferenceDataVersion=1&sendUpdates=none', $body);
            if (! $resp->successful()) {
                Log::warning('Google-agenda: event aanmaken mislukt', ['status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);
                return ['event_id' => null, 'meet_url' => null];
            }
            return ['event_id' => $resp->json('id'), 'meet_url' => $resp->json('hangoutLink')];
        } catch (\Throwable $e) {
            Log::warning('Google-agenda event-fout: ' . $e->getMessage());
            return ['event_id' => null, 'meet_url' => null];
        }
    }

    public function deleteEvent(string $eventId): void
    {
        $token = $this->accessToken();
        if (! $token) {
            return;
        }
        try {
            Http::withToken($token)->withOptions(['verify' => $this->ca()])->timeout(12)
                ->delete('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId()) . '/events/' . rawurlencode($eventId) . '?sendUpdates=none');
        } catch (\Throwable $e) {
            Log::warning('Google-agenda delete-fout: ' . $e->getMessage());
        }
    }
}
