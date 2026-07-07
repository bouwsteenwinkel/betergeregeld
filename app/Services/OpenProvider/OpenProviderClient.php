<?php

namespace App\Services\OpenProvider;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Minimale OpenProvider-REST-client (v1beta) voor: domein-beschikbaarheid checken,
 * een domein registreren en een DNS-zone met A-records naar de VPS aanmaken.
 *
 * OpenProvider-responses hebben de vorm {"code":0,"data":{...},"desc":"..."};
 * code 0 = succes, anders gooien we een RuntimeException met de desc.
 */
class OpenProviderClient
{
    private string $base;

    public function __construct()
    {
        $this->base = (string) config('openprovider.base_url', 'https://api.openprovider.eu');
    }

    public function isConfigured(): bool
    {
        return (string) config('openprovider.username') !== ''
            && (string) config('openprovider.password') !== ''
            && (string) config('openprovider.owner_handle') !== '';
    }

    /** Splitst 'jouw-klusbedrijf-website.nl' in ['name' => 'jouw-klusbedrijf-website', 'extension' => 'nl']. */
    public function splitDomain(string $domain): array
    {
        $domain = strtolower(trim(preg_replace('#^https?://#', '', $domain), '/. '));
        $dot = strpos($domain, '.');
        if ($dot === false) {
            throw new RuntimeException("Ongeldig domein: {$domain}");
        }
        return ['name' => substr($domain, 0, $dot), 'extension' => substr($domain, $dot + 1)];
    }

    /**
     * Http-opties: forceer IPv4 (zodat OpenProvider het gewhiteliste IPv4 ziet,
     * niet IPv6) en gebruik de CA-bundle (lokale dev mist soms een systeem-bundle).
     */
    private function httpOptions(): array
    {
        $opts = [];
        if (defined('CURLOPT_IPRESOLVE')) {
            $opts['curl'] = [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4];
        }
        $ca = storage_path('cacert.pem');
        $opts['verify'] = is_file($ca) ? $ca : true;
        return $opts;
    }

    /** Bearer-token, ~50 min gecachet. */
    private function token(): string
    {
        return Cache::remember('openprovider.token', now()->addMinutes(50), function (): string {
            $resp = Http::acceptJson()->withOptions($this->httpOptions())->timeout(30)->post($this->base . '/v1beta/auth/login', [
                'username' => (string) config('openprovider.username'),
                'password' => (string) config('openprovider.password'),
            ]);
            $json = $resp->json();
            $token = data_get($json, 'data.token');
            if (! $resp->successful() || ! $token) {
                throw new RuntimeException('OpenProvider-login mislukt: ' . (data_get($json, 'desc') ?: $resp->status()));
            }
            return (string) $token;
        });
    }

    /** Authed request; gooit op een niet-succes-code. */
    private function call(string $method, string $path, array $body = []): array
    {
        $resp = Http::withToken($this->token())->acceptJson()->withOptions($this->httpOptions())->timeout(45)
            ->send($method, $this->base . $path, $body ? ['json' => $body] : []);

        $json = (array) $resp->json();
        $code = (int) data_get($json, 'code', $resp->successful() ? 0 : -1);
        if ($code !== 0) {
            Log::warning('OpenProvider-fout', ['path' => $path, 'code' => $code, 'desc' => data_get($json, 'desc'), 'body' => $body]);
            throw new RuntimeException('OpenProvider: ' . (data_get($json, 'desc') ?: "fout (code {$code})"));
        }
        return $json;
    }

    /** @return array{available:bool,status:?string} */
    public function checkAvailability(string $domain): array
    {
        $d = $this->splitDomain($domain);
        $json = $this->call('POST', '/v1beta/domains/check', [
            'domains' => [['name' => $d['name'], 'extension' => $d['extension']]],
        ]);
        $status = (string) data_get($json, 'data.results.0.status');
        return ['available' => $status === 'free', 'status' => $status ?: null];
    }

    /** Registreert het domein; geeft de OpenProvider domain-id terug. */
    public function registerDomain(string $domain): int
    {
        $d = $this->splitDomain($domain);
        $json = $this->call('POST', '/v1beta/domains', [
            'domain'         => ['name' => $d['name'], 'extension' => $d['extension']],
            'period'         => (int) config('openprovider.period', 1),
            'owner_handle'   => (string) config('openprovider.owner_handle'),
            'admin_handle'   => (string) config('openprovider.admin_handle'),
            'tech_handle'    => (string) config('openprovider.tech_handle'),
            'billing_handle' => (string) config('openprovider.billing_handle'),
            'ns_group'       => (string) config('openprovider.ns_group', 'dns-openprovider'),
            'autorenew'      => 'default',
        ]);
        return (int) data_get($json, 'data.id');
    }

    /** Maakt een DNS-zone met A-records voor @ en www die naar $ip wijzen. */
    public function createDnsZone(string $domain, string $ip): void
    {
        $fqdn = strtolower(trim(preg_replace('#^https?://#', '', $domain), '/. '));
        $ttl  = (int) config('openprovider.ttl', 3600);
        $this->call('POST', '/v1beta/dns/zones', [
            'domain' => ['name' => $fqdn],
            'type'   => 'master',
            'records' => [
                ['type' => 'A', 'name' => $fqdn,           'value' => $ip, 'ttl' => $ttl, 'prio' => 0],
                ['type' => 'A', 'name' => 'www.' . $fqdn,  'value' => $ip, 'ttl' => $ttl, 'prio' => 0],
            ],
        ]);
    }

    /**
     * Hoog-niveau: registreer het domein en zet meteen de DNS-zone naar de VPS.
     * @return array{domain:string,domain_id:int,ip:string,registered_at:string}
     */
    public function registerWithDns(string $domain, ?string $ip = null): array
    {
        $ip = $ip ?: (string) config('openprovider.target_ip');
        if ($ip === '') {
            throw new RuntimeException('Geen doel-IP ingesteld (CHANNEL_TARGET_IP).');
        }

        $check = $this->checkAvailability($domain);
        if (! $check['available']) {
            throw new RuntimeException("Domein niet beschikbaar (status: {$check['status']}).");
        }

        $id = $this->registerDomain($domain);
        $this->createDnsZone($domain, $ip);

        return [
            'domain'        => $domain,
            'domain_id'     => $id,
            'ip'            => $ip,
            'registered_at' => now()->toIso8601String(),
        ];
    }
}
