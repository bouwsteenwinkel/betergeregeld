<?php

namespace App\Services\Ads;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Dunne REST-client voor de Google Ads API. Bewust hand-rolled (net als de
 * GoogleCalendarGateway) i.p.v. de zware googleads/google-ads-php-library: die
 * leunt op gRPC/protobuf-extensies die op de Plesk-Windows-host niet vanzelf
 * beschikbaar zijn. We praten rechtstreeks REST met googleads.googleapis.com.
 *
 * OAuth deelt de Google-app van de Agenda-koppeling (GOOGLE_CLIENT_ID/SECRET);
 * alleen de scope (adwords) en het refresh-token (google-ads.json) zijn apart.
 */
class GoogleAdsClient
{
    public const SCOPE = 'https://www.googleapis.com/auth/adwords';

    private function ca(): string
    {
        // Windows-PHP heeft geen systeem-CA-bundle; anders faalt elke TLS-call.
        return CaBundle::getSystemCaRootBundlePath();
    }

    private function cfg(string $key, $default = null)
    {
        return config("google_ads.$key", $default);
    }

    /* ─────────────────────────── OAuth ─────────────────────────── */

    public function authUrl(string $state = 'ads'): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => (string) $this->cfg('client_id'),
            'redirect_uri'  => (string) $this->cfg('redirect_uri'),
            'response_type' => 'code',
            'scope'         => self::SCOPE,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ]);
    }

    /** @return array{ok:bool,error:?string} */
    public function exchangeCode(string $code): array
    {
        $resp = Http::asForm()->withOptions(['verify' => $this->ca()])->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => (string) $this->cfg('client_id'),
            'client_secret' => (string) $this->cfg('client_secret'),
            'redirect_uri'  => (string) $this->cfg('redirect_uri'),
            'grant_type'    => 'authorization_code',
        ]);

        if (! $resp->successful() || ! $resp->json('refresh_token')) {
            Log::warning('Google Ads: token-exchange mislukt', ['status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);

            return ['ok' => false, 'error' => 'Geen refresh-token ontvangen. Geef opnieuw expliciet toestemming (prompt=consent).'];
        }

        $written = Storage::put((string) $this->cfg('token_file'), json_encode([
            'refresh_token' => $resp->json('refresh_token'),
            'connected_at'  => now()->toIso8601String(),
        ]));

        if ($written === false) {
            return ['ok' => false, 'error' => 'Refresh-token kon niet worden opgeslagen (schrijfrechten op storage/app).'];
        }

        return ['ok' => true, 'error' => null];
    }

    public function hasRefreshToken(): bool
    {
        return $this->refreshToken() !== null;
    }

    /** Volledig gekoppeld = developer-token + doel-account + OAuth-refresh-token. */
    public function connected(): bool
    {
        return (bool) $this->cfg('developer_token')
            && (bool) $this->cfg('customer_id')
            && $this->hasRefreshToken();
    }

    private function refreshToken(): ?string
    {
        $file = (string) $this->cfg('token_file');
        if (! Storage::exists($file)) {
            return null;
        }

        return json_decode((string) Storage::get($file), true)['refresh_token'] ?? null;
    }

    private function accessToken(): ?string
    {
        $refresh = $this->refreshToken();
        if (! $refresh) {
            return null;
        }

        $resp = Http::asForm()->withOptions(['verify' => $this->ca()])->post('https://oauth2.googleapis.com/token', [
            'client_id'     => (string) $this->cfg('client_id'),
            'client_secret' => (string) $this->cfg('client_secret'),
            'refresh_token' => $refresh,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $resp->successful()) {
            Log::warning('Google Ads: access-token vernieuwen mislukt', ['status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);

            return null;
        }

        return $resp->json('access_token');
    }

    /* ─────────────────────────── REST ─────────────────────────── */

    private function base(): string
    {
        return 'https://googleads.googleapis.com/' . $this->cfg('api_version');
    }

    private function request(?string $access)
    {
        return Http::withToken($access)
            ->withHeaders(array_filter([
                'developer-token'   => (string) $this->cfg('developer_token'),
                'login-customer-id' => (string) $this->cfg('login_customer_id') ?: null,
            ]))
            ->withOptions(['verify' => $this->ca()])
            ->timeout(30);
    }

    private function customer(?string $customerId): string
    {
        return preg_replace('/\D/', '', (string) ($customerId ?: $this->cfg('customer_id')));
    }

    /**
     * Snelle connectiviteitscheck: welke accounts kan dit token/OAuth zien?
     * Heeft géén login-customer-id nodig.
     *
     * @return array{ok:bool,error:?string,customers:array<int,string>}
     */
    public function listAccessibleCustomers(): array
    {
        $access = $this->accessToken();
        if (! $access) {
            return ['ok' => false, 'error' => 'Geen access-token (OAuth nog niet gekoppeld?).', 'customers' => []];
        }

        $resp = Http::withToken($access)
            ->withHeaders(['developer-token' => (string) $this->cfg('developer_token')])
            ->withOptions(['verify' => $this->ca()])
            ->timeout(30)
            ->get($this->base() . '/customers:listAccessibleCustomers');

        if (! $resp->successful()) {
            return ['ok' => false, 'error' => $resp->json('error.message') ?: ('HTTP ' . $resp->status()), 'customers' => []];
        }

        return ['ok' => true, 'error' => null, 'customers' => $resp->json('resourceNames', [])];
    }

    /**
     * GAQL-query tegen customers/{cid}/googleAds:search.
     *
     * @return array{ok:bool,error:?string,results:array}
     */
    public function search(string $gaql, ?string $customerId = null): array
    {
        $resp = $this->request($this->accessToken())
            ->post($this->base() . '/customers/' . $this->customer($customerId) . '/googleAds:search', ['query' => $gaql]);

        if (! $resp->successful()) {
            return ['ok' => false, 'error' => $resp->json('error.message') ?: ('HTTP ' . $resp->status()), 'results' => []];
        }

        return ['ok' => true, 'error' => null, 'results' => $resp->json('results', [])];
    }

    /**
     * Atomische mutate van meerdere resources (budget → campagne → advertentiegroep
     * → zoekwoorden → advertentie) in één call, met tijdelijke resource-namen.
     *
     * @return array{ok:bool,error:?string,results:array}
     */
    public function mutate(array $mutateOperations, ?string $customerId = null): array
    {
        $resp = $this->request($this->accessToken())
            ->post($this->base() . '/customers/' . $this->customer($customerId) . '/googleAds:mutate', [
                'mutateOperations' => $mutateOperations,
            ]);

        if (! $resp->successful()) {
            return ['ok' => false, 'error' => $resp->json('error.message') ?: ('HTTP ' . $resp->status()), 'results' => []];
        }

        return ['ok' => true, 'error' => null, 'results' => $resp->json('mutateOperationResponses', [])];
    }
}
