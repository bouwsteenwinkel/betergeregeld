<?php

namespace App\Http\Controllers;

use App\Services\CmpService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * CmpController — public-facing CMP endpoints.
 *
 *   GET  /cmp/loader.js?tenant=X&lang=Y   — JS-loader (banner + runtime)
 *   GET  /cmp/scripts.js?tenant=X&consent_id=...  — third-party scripts (na consent)
 *   POST /cmp/consent                      — accept/reject/custom keuze opslaan
 *
 * Voor cross-origin gebruik: alle responses krijgen CORS-headers zodat
 * de loader vanaf bouwsteenwinkel.nl, brikl.nl, bouwersfeestje.nl, etc.
 * kan worden geladen.
 */
class CmpController extends Controller
{
    public function __construct(private CmpService $cmp) {}

    public function loader(Request $r): Response
    {
        $tenant = $this->safeTenant($r);
        $lang   = $this->safeLang($r);

        $config = $this->cmp->buildConfig($tenant, $lang);
        if (!$config) {
            return response('// CMP: tenant niet gevonden of niet geconfigureerd', 404)
                ->header('Content-Type', 'application/javascript; charset=utf-8');
        }

        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $template = $this->loadLoaderTemplate();
        $body = preg_replace('#/\*@CMP_CONFIG@\*/null/\*@CMP_CONFIG_END@\*/#', $configJson, $template, 1);

        return response($body, 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300, s-maxage=600')
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function scripts(Request $r): Response
    {
        $tenant     = $this->safeTenant($r);
        $consentId  = (string)$r->query('consent_id', '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $consentId)) {
            return response('// CMP: ongeldige consent_id', 400)
                ->header('Content-Type', 'application/javascript; charset=utf-8');
        }

        $consent = \DB::table('cmp_consents')
            ->where('tenant_key', $tenant)
            ->where('consent_id', $consentId)
            ->where('expires_at', '>', now())
            ->first();
        if (!$consent) {
            return response('// CMP: consent niet gevonden of verlopen', 200)
                ->header('Content-Type', 'application/javascript; charset=utf-8');
        }

        $choices = json_decode((string)$consent->choices_json, true) ?: [];
        $approved = array_keys(array_filter($choices, fn($v) => $v === true || $v === 1 || $v === '1'));
        // 'necessary' staat altijd aan ongeacht keuze
        if (!in_array('necessary', $approved, true)) $approved[] = 'necessary';

        $scripts = $this->cmp->getApprovedScripts($tenant, $approved);

        $js = "(function(){\n";
        foreach ($scripts as $s) {
            $name = json_encode($s['name']);
            if ($s['type'] === 'src' && $s['src']) {
                $src = json_encode($s['src']);
                $attrs = '';
                foreach ($s['attributes'] as $k => $v) {
                    $attrs .= ".setAttribute(" . json_encode((string)$k) . "," . json_encode((string)$v) . ")";
                }
                $js .= "  try { var s=document.createElement('script'); s.async=true; s.src=$src" . $attrs . "; s.setAttribute('data-cmp-name'," . $name . "); document.head.appendChild(s); } catch(e){}\n";
            } elseif ($s['type'] === 'inline' && $s['inline']) {
                $js .= "  try { (function(){\n" . $s['inline'] . "\n})(); } catch(e){}\n";
            }
        }
        $js .= "})();\n";

        return response($js, 200)
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'private, max-age=60')
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function consent(Request $r)
    {
        $data = $r->all();
        $tenant     = preg_replace('/[^a-z0-9_\-]/i', '', (string)($data['tenant'] ?? '')) ?: 'bouwsteenwinkel';
        $consentId  = (string)($data['consent_id'] ?? '');
        $domain     = isset($data['domain']) ? strtolower(trim((string)$data['domain'])) : null;
        $choices    = is_array($data['choices'] ?? null) ? $data['choices'] : [];
        $status     = (string)($data['status'] ?? 'custom');
        $policyVer  = (int)($data['policy_version'] ?? 1);

        $result = $this->cmp->saveConsent(
            $tenant,
            $domain,
            $consentId ?: null,
            $choices,
            $status,
            $policyVer,
            $r->ip(),
            (string)$r->userAgent(),
        );

        return response()->json($result)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }

    public function consentOptions(): Response
    {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Access-Control-Max-Age', '600');
    }

    private function safeTenant(Request $r): string
    {
        $t = (string)$r->query('tenant', 'bouwsteenwinkel');
        return preg_replace('/[^a-z0-9_\-]/i', '', $t) ?: 'bouwsteenwinkel';
    }

    private function safeLang(Request $r): string
    {
        $l = (string)$r->query('lang', 'nl');
        if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $l)) return 'nl';
        return $l;
    }

    private function loadLoaderTemplate(): string
    {
        $path = resource_path('cmp/loader.js');
        $content = @file_get_contents($path);
        return $content !== false ? $content : '/* CMP loader-template ontbreekt */';
    }
}
