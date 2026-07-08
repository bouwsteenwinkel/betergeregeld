<?php

namespace App\Services\Plesk;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimale Plesk-REST-client (v2) die de CLI-gateway gebruikt
 * (POST /api/v2/cli/{utility}/call) om `plesk bin`-utilities te draaien.
 * Zo kunnen we op Windows-Plesk betrouwbaar:
 *   - een niche-domein als DOMEIN-ALIAS van betergeregeld.com toevoegen (site_alias)
 *   - het Let's Encrypt-certificaat (her)uitgeven zodat de alias gedekt is (sslit)
 *
 * De app draait op dezelfde VPS als Plesk → base_url = https://127.0.0.1:8443
 * (self-signed paneel-cert → verify standaard uit). Auth via secret key
 * (X-API-Key) of, als terugval, admin basic-auth.
 */
class PleskClient
{
    public function __construct() {}

    public function isConfigured(): bool
    {
        return (string) config('plesk.base_url') !== ''
            && ((string) config('plesk.api_key') !== ''
                || (string) config('plesk.admin_password') !== '');
    }

    /** Domein normaliseren (zonder scheme/pad, lowercase). */
    private function normalize(string $domain): string
    {
        return strtolower(trim(preg_replace('#^https?://#', '', $domain), '/. '));
    }

    /** Het hoofddomein waar aliassen onder komen. */
    public function parentDomain(): string
    {
        return $this->normalize((string) config('plesk.parent_domain', 'betergeregeld.com'));
    }

    private function http()
    {
        $req = Http::acceptJson()
            ->timeout(120) // Let's Encrypt-uitgifte kan even duren
            ->withOptions(['verify' => (bool) config('plesk.verify_ssl', false)]);

        if ($key = (string) config('plesk.api_key')) {
            return $req->withHeaders(['X-API-Key' => $key]);
        }
        return $req->withBasicAuth(
            (string) config('plesk.admin_login', 'admin'),
            (string) config('plesk.admin_password'),
        );
    }

    /**
     * Read-only check: bevestigt auth + bereikbaarheid van de Plesk-API.
     * GET /api/v2/server geeft serverinfo terug (verandert niets).
     *
     * @return array{status:int, ok:bool, hostname:?string, version:?string, error:?string}
     */
    public function ping(): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 0, 'ok' => false, 'hostname' => null, 'version' => null, 'error' => 'Plesk niet geconfigureerd (.env).'];
        }
        try {
            $resp = $this->http()->get(rtrim((string) config('plesk.base_url'), '/') . '/api/v2/server');
            $j = (array) $resp->json();
            return [
                'status'   => $resp->status(),
                'ok'       => $resp->successful(),
                'hostname' => $j['hostname'] ?? null,
                'version'  => $j['version'] ?? null,
                'error'    => $resp->successful() ? null : (string) $resp->body(),
            ];
        } catch (\Throwable $e) {
            return ['status' => 0, 'ok' => false, 'hostname' => null, 'version' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * De id's van beschikbare CLI-commando's (GET /api/v2/cli/commands),
     * zodat we de exacte utility-naam (bv. voor aliassen) kunnen vinden.
     *
     * @return array<int,string>
     */
    public function listCommandIds(): array
    {
        try {
            $resp = $this->http()->get(rtrim((string) config('plesk.base_url'), '/') . '/api/v2/cli/commands');
            if (! $resp->successful()) {
                return [];
            }
            $ids = [];
            foreach ((array) $resp->json() as $c) {
                $id = is_array($c) ? ($c['id'] ?? $c['name'] ?? null) : $c;
                if (is_string($id) && $id !== '') {
                    $ids[] = $id;
                }
            }
            sort($ids);
            return $ids;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Draait een Plesk-CLI-utility via de REST-gateway.
     * POST /api/v2/cli/{utility}/call  body {"params": [...]}.
     *
     * @param  array<int,string> $params
     * @return array{code:int, stdout:string, stderr:string}
     */
    public function cli(string $utility, array $params): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Plesk niet geconfigureerd: zet PLESK_BASE_URL + PLESK_API_KEY (of admin-creds) in .env.');
        }

        $url  = rtrim((string) config('plesk.base_url'), '/') . '/api/v2/cli/' . rawurlencode($utility) . '/call';
        $resp = $this->http()->post($url, ['params' => array_values($params)]);

        if (! $resp->successful()) {
            throw new RuntimeException("Plesk CLI-fout ({$utility}): HTTP {$resp->status()} — " . $resp->body());
        }

        $json = (array) $resp->json();
        return [
            'code'   => (int) ($json['code'] ?? -1),
            'stdout' => (string) ($json['stdout'] ?? ''),
            'stderr' => (string) ($json['stderr'] ?? ''),
        ];
    }

    /**
     * Voegt $aliasDomain toe als domein-alias van het hoofddomein (idempotent).
     * De alias serveert dezelfde webcontent (de app routeert op hostname); DNS
     * beheren we niet in Plesk (dat staat bij OpenProvider) → -dns false.
     */
    public function addAlias(string $aliasDomain): void
    {
        $alias  = $this->normalize($aliasDomain);
        $parent = $this->parentDomain();

        $r = $this->cli((string) config('plesk.alias_utility', 'site-alias'), [
            '--create', $alias,
            '-domain', $parent,
            '-www', 'true',
            '-web', 'true',
            '-mail', 'false',
            '-dns', 'false',
            '-status', 'enabled',
        ]);

        if ($r['code'] !== 0) {
            $msg = strtolower($r['stdout'] . ' ' . $r['stderr']);
            // Bestaat al → idempotent negeren.
            if (str_contains($msg, 'already exist') || str_contains($msg, 'bestaat al')) {
                return;
            }
            throw new RuntimeException('Alias aanmaken mislukt: ' . trim($r['stdout'] . ' ' . $r['stderr']));
        }
    }

    /**
     * (Her)uitgeven van het Let's Encrypt-certificaat via de SSL It!-extensie,
     * zodat de nieuw toegevoegde alias gedekt wordt. Standaard beveiligen we het
     * hoofddomein (dat de aliassen als SAN meeneemt); via config aan te passen.
     *
     * Let op: één cert dekt max. ~100 SAN-namen; bij grote aantallen aliassen
     * is een aparte subscription/cert per domein nodig.
     */
    public function reissueLetsEncrypt(?string $domain = null): void
    {
        $target = $this->normalize($domain ?: $this->parentDomain());

        // Configureerbaar CLI-commando (sslit-syntax verschilt per versie).
        $params = (array) config('plesk.letsencrypt_params', ['--exec', 'sslit', '--secure-domain', '-domain', '{domain}']);
        $params = array_map(fn ($p) => str_replace('{domain}', $target, (string) $p), $params);

        $r = $this->cli('extension', $params);
        if ($r['code'] !== 0) {
            throw new RuntimeException('Let\'s Encrypt (her)uitgeven mislukt: ' . trim($r['stdout'] . ' ' . $r['stderr']));
        }
    }

    /**
     * Hoog-niveau: alias toevoegen én het certificaat (her)uitgeven.
     *
     * @return array{alias:string, steps:array<string,string>, ok:bool}
     */
    public function provisionAlias(string $aliasDomain): array
    {
        $alias = $this->normalize($aliasDomain);
        $steps = [];

        $this->addAlias($alias);
        $steps['alias'] = "toegevoegd als alias van {$this->parentDomain()}";

        $this->reissueLetsEncrypt();
        $steps['ssl'] = 'Let\'s Encrypt (her)uitgegeven';

        return ['alias' => $alias, 'steps' => $steps, 'ok' => true];
    }
}
