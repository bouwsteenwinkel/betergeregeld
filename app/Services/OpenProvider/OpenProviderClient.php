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
        // IPv4 forceren zodat OpenProvider het gewhiteliste IPv4 ziet. Uit te zetten
        // (OPENPROVIDER_FORCE_IPV4=false) als de VPS OpenProvider alleen over IPv6
        // kan bereiken; whitelist dan wel het IPv6 van de VPS bij OpenProvider.
        if ((bool) config('openprovider.force_ipv4', true) && defined('CURLOPT_IPRESOLVE')) {
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

    /**
     * Zet de A-records (apex + www) naar $ip. OpenProvider verwacht de domein-naam
     * gesplitst (name + extension) en RELATIEVE record-namen: "" voor de apex
     * (géén "@"), "www" voor de subdomein-A.
     *
     * Bij registratie met een ns_group maakt OpenProvider al (een lege) zone aan;
     * bestaat die al, dan voegen we de records toe via een modify (PUT) i.p.v. de
     * zone opnieuw aan te maken.
     */
    public function configureDnsZone(string $domain, string $ip): void
    {
        $d    = $this->splitDomain($domain);
        $fqdn = $d['name'] . '.' . $d['extension'];
        $ttl  = (int) config('openprovider.ttl', 3600);
        $records = [
            ['type' => 'A', 'name' => '',    'value' => $ip, 'ttl' => $ttl],
            ['type' => 'A', 'name' => 'www', 'value' => $ip, 'ttl' => $ttl],
        ];

        try {
            $this->call('POST', '/v1beta/dns/zones', [
                'domain'  => ['name' => $d['name'], 'extension' => $d['extension']],
                'type'    => 'master',
                'records' => $records,
            ]);
        } catch (RuntimeException $e) {
            if (! str_contains(strtolower($e->getMessage()), 'already exist')) {
                throw $e;
            }
            // Zone bestaat al (auto-aangemaakt bij registratie) -> records toevoegen.
            // "Duplicate record" betekent dat de A-records er al staan: idempotent negeren.
            try {
                $this->call('PUT', '/v1beta/dns/zones/' . $fqdn, [
                    'name'    => $fqdn,
                    'records' => ['add' => $records],
                ]);
            } catch (RuntimeException $e2) {
                if (! str_contains(strtolower($e2->getMessage()), 'duplicate')) {
                    throw $e2;
                }
            }
        }
    }

    /**
     * Voegt één TXT-record toe aan de bestaande DNS-zone (idempotent). Gebruikt
     * voor o.a. Google-site-verificatie (value = 'google-site-verification=...').
     * name '' = apex. Een 'duplicate' (record staat er al) negeren we.
     */
    public function addTxtRecord(string $domain, string $value, string $name = ''): void
    {
        $d    = $this->splitDomain($domain);
        $fqdn = $d['name'] . '.' . $d['extension'];
        $ttl  = (int) config('openprovider.ttl', 3600);

        try {
            $this->call('PUT', '/v1beta/dns/zones/' . $fqdn, [
                'name'    => $fqdn,
                'records' => ['add' => [
                    ['type' => 'TXT', 'name' => $name, 'value' => $value, 'ttl' => $ttl],
                ]],
            ]);
        } catch (RuntimeException $e) {
            if (! str_contains(strtolower($e->getMessage()), 'duplicate')) {
                throw $e;
            }
        }
    }

    /**
     * Status van de DNS-zone in ONS OpenProvider-account (voor de admin-lijst):
     *  - registered: bestaat er een zone met records voor dit domein bij ons?
     *  - dns_ok:     wijzen apex (@) én www naar $ip (VPS)?
     * Doet één GET; een niet-bestaande zone levert registered=false op i.p.v. te gooien.
     *
     * @return array{registered:bool,dns_ok:bool,apex:?string,www:?string}
     */
    public function zoneStatus(string $domain, ?string $ip = null): array
    {
        $ip   = $ip ?: (string) config('openprovider.target_ip');
        $d    = $this->splitDomain($domain);
        $fqdn = $d['name'] . '.' . $d['extension'];

        try {
            $json = $this->call('GET', '/v1beta/dns/zones/' . $fqdn . '/records');
        } catch (RuntimeException $e) {
            return ['registered' => false, 'dns_ok' => false, 'apex' => null, 'www' => null];
        }

        $recs = (array) data_get($json, 'data.results', []);
        if (! $recs) {
            return ['registered' => false, 'dns_ok' => false, 'apex' => null, 'www' => null];
        }

        $apex = null;
        $www = null;
        $wwwCname = null;
        foreach ($recs as $r) {
            $type = (string) data_get($r, 'type');
            $name = (string) data_get($r, 'name');
            $val  = (string) data_get($r, 'value');
            if ($type === 'A' && $name === $fqdn) {
                $apex = $val;
            } elseif ($type === 'A' && $name === 'www.' . $fqdn) {
                $www = $val;
            } elseif ($type === 'CNAME' && $name === 'www.' . $fqdn) {
                $wwwCname = $val;
            }
        }

        // www is goed als er een A → VPS is, of een CNAME naar de (correcte) apex.
        $wwwOk = ($www === $ip) || ($wwwCname === $fqdn && $apex === $ip);
        $dnsOk = ($apex === $ip) && $wwwOk;

        return ['registered' => true, 'dns_ok' => $dnsOk, 'apex' => $apex, 'www' => $www ?: $wwwCname];
    }

    /**
     * PUBLIEKE DNS-verificatie: resolvet apex ÉN www echt naar $ip?
     * Gebruikt de systeem-resolver (publieke DNS), niet ons OpenProvider-account —
     * dít is de enige betrouwbare check dat een net-geregistreerd/gedelegeerd domein
     * daadwerkelijk live is. Een net geregistreerd .nl-domein propageert niet direct,
     * dus vlak na registratie is dit doorgaans (nog) false — dat is de bedoeling.
     *
     * @return array{ok:bool, apex:string, www:string}
     */
    public function dnsResolvesToTarget(string $domain, ?string $ip = null): array
    {
        $ip   = $ip ?: (string) config('openprovider.target_ip');
        $d    = $this->splitDomain($domain);
        $fqdn = $d['name'] . '.' . $d['extension'];

        $apex = $this->resolveA($fqdn);
        $www  = $this->resolveA('www.' . $fqdn);

        return [
            'ok'   => in_array($ip, $apex, true) && in_array($ip, $www, true),
            'apex' => $apex ? implode(',', $apex) : '—',
            'www'  => $www ? implode(',', $www) : '—',
        ];
    }

    /** A-records (IPv4) van een host via de publieke resolver; [] bij NXDOMAIN/geen A. */
    private function resolveA(string $host): array
    {
        $recs = @dns_get_record($host, DNS_A) ?: [];
        return array_values(array_filter(array_map(fn ($r) => $r['ip'] ?? null, $recs)));
    }

    /**
     * Hoog-niveau: registreer het domein en zet meteen de DNS-zone naar de VPS.
     * NB: dit garandeert NIET dat het domein al publiek resolvet (.nl is async +
     * propagatie). Verifieer daarna met dnsResolvesToTarget() vóór je verder gaat.
     * @return array{domain:string,domain_id:int,ip:string,registered_at:string}
     */
    public function registerWithDns(string $domain, ?string $ip = null): array
    {
        $ip = $ip ?: (string) config('openprovider.target_ip');
        if ($ip === '') {
            throw new RuntimeException('Geen doel-IP ingesteld (CHANNEL_TARGET_IP).');
        }

        $check = $this->checkAvailability($domain);

        // Al geregistreerd? Waarschijnlijk van onszelf. Probeer de DNS-zone te
        // verzekeren (idempotent): lukt dat, dan beheren wij het domein en is het
        // "al gedaan"; lukt het niet, dan staat het niet in ons account.
        if (! $check['available']) {
            try {
                $this->configureDnsZone($domain, $ip);
            } catch (RuntimeException $e) {
                throw new RuntimeException("Domein '{$domain}' is al geregistreerd (status: {$check['status']}) en staat niet in ons OpenProvider-account.");
            }

            return [
                'domain'        => $domain,
                'domain_id'     => 0,
                'ip'            => $ip,
                'registered_at' => now()->toIso8601String(),
                'already'       => true,
            ];
        }

        $id = $this->registerDomain($domain);
        $this->configureDnsZone($domain, $ip);

        return [
            'domain'        => $domain,
            'domain_id'     => $id,
            'ip'            => $ip,
            'registered_at' => now()->toIso8601String(),
            'already'       => false,
        ];
    }
}
