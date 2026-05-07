<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CmpService — server-side config builder + consent persistence.
 *
 * De public loader (script.js) krijgt een JSON-config met domains,
 * categories, texts, branding en policy_version. Die config wordt
 * client-side gebruikt om de banner te tonen, scripts te gating, en
 * consent-keuzes te POST-en naar /cmp/consent.
 */
class CmpService
{
    /**
     * Bouw de full config voor een tenant + taal. Returnt array klaar
     * voor json_encode in de loader.
     */
    public function buildConfig(string $tenantKey, string $lang = 'nl'): ?array
    {
        $policy = DB::table('cmp_policy')->where('tenant_key', $tenantKey)->first();
        if (!$policy) return null;

        $categories = DB::table('cmp_categories')
            ->where('tenant_key', $tenantKey)
            ->where('is_enabled', 1)
            ->orderBy('sort_order')
            ->get(['key', 'is_required', 'sort_order'])
            ->map(fn($c) => [
                'key'      => (string)$c->key,
                'required' => (bool)$c->is_required,
            ])
            ->all();

        $texts = DB::table('cmp_texts')
            ->where('tenant_key', $tenantKey)
            ->where('lang', $lang)
            ->pluck('text_value', 'text_key')
            ->all();
        // Fallback NL als gevraagde taal leeg is
        if (empty($texts) && $lang !== 'nl') {
            $texts = DB::table('cmp_texts')
                ->where('tenant_key', $tenantKey)
                ->where('lang', 'nl')
                ->pluck('text_value', 'text_key')
                ->all();
        }

        $branding = DB::table('cmp_branding')->where('tenant_key', $tenantKey)->first();
        $brandingArr = $branding && $branding->branding_json
            ? (json_decode($branding->branding_json, true) ?: [])
            : [];

        $domains = DB::table('cmp_domains')
            ->where('tenant_key', $tenantKey)
            ->where('status', 'active')
            ->orderByDesc('is_primary')
            ->pluck('domain')
            ->all();

        // Scripts: alleen via category-injection — frontend leest deze niet
        // direct (security: fetched via separate endpoint na consent), maar
        // we tellen aantal voor diagnostiek.
        $scriptCounts = DB::table('cmp_scripts')
            ->where('tenant_key', $tenantKey)
            ->where('is_enabled', 1)
            ->groupBy('category_key')
            ->select('category_key', DB::raw('COUNT(*) as n'))
            ->pluck('n', 'category_key')
            ->all();

        return [
            'tenant'         => $tenantKey,
            'lang'           => $lang,
            'policy_version' => (int)$policy->policy_version,
            'config_hash'    => substr((string)$policy->config_hash, 0, 12),
            'domains'        => $domains,
            'categories'     => $categories,
            'texts'          => (object)$texts,
            'branding'       => (object)$brandingArr,
            'script_counts'  => (object)$scriptCounts,
            'endpoint_consent' => '/cmp/consent',
            'endpoint_scripts' => '/cmp/scripts.js',
        ];
    }

    /**
     * Sla een consent op (eerste consent of update). Returnt de
     * consent_id (UUID) zodat de client 'm in een cookie kan zetten.
     */
    public function saveConsent(
        string $tenantKey,
        ?string $domain,
        ?string $consentId,
        array $choices,
        string $status,
        int $policyVersion,
        ?string $ip,
        ?string $ua,
        int $expiresInDays = 365
    ): array {
        $allowed = ['accepted', 'rejected', 'custom'];
        if (!in_array($status, $allowed, true)) $status = 'custom';

        // Ensure UUID
        if (!$consentId || !preg_match('/^[0-9a-f-]{36}$/i', $consentId)) {
            $consentId = (string)Str::uuid();
        }

        // Resolve domain → domain_id
        $domainId = null;
        if ($domain) {
            $row = DB::table('cmp_domains')
                ->where('tenant_key', $tenantKey)
                ->where('domain', strtolower($domain))
                ->first();
            $domainId = $row ? (int)$row->id : null;
        }

        $now = now();
        $expiresAt = $now->copy()->addDays($expiresInDays);
        $choicesJson = json_encode($choices, JSON_UNESCAPED_SLASHES);
        $ipHash = $ip ? hash('sha256', $ip . '|' . $tenantKey) : null;
        $uaHash = $ua ? hash('sha256', $ua . '|' . $tenantKey) : null;

        $existing = DB::table('cmp_consents')
            ->where('tenant_key', $tenantKey)
            ->where('consent_id', $consentId)
            ->first();

        if ($existing) {
            DB::table('cmp_consents')
                ->where('id', $existing->id)
                ->update([
                    'policy_version' => $policyVersion,
                    'status'         => $status,
                    'choices_json'   => $choicesJson,
                    'last_updated_at'=> $now,
                    'expires_at'     => $expiresAt,
                    'domain_id'      => $domainId ?: $existing->domain_id,
                ]);
            $event = 'updated';
        } else {
            DB::table('cmp_consents')->insert([
                'tenant_key'      => $tenantKey,
                'domain_id'       => $domainId,
                'consent_id'      => $consentId,
                'policy_version'  => $policyVersion,
                'status'          => $status,
                'choices_json'    => $choicesJson,
                'first_seen_at'   => $now,
                'last_updated_at' => $now,
                'expires_at'      => $expiresAt,
            ]);
            $event = 'created';
        }

        DB::table('cmp_consent_logs')->insert([
            'tenant_key'     => $tenantKey,
            'domain_id'      => $domainId,
            'consent_id'     => $consentId,
            'event'          => $event,
            'policy_version' => $policyVersion,
            'choices_json'   => $choicesJson,
            'ip_hash'        => $ipHash,
            'ua_hash'        => $uaHash,
            'created_at'     => $now,
        ]);

        return [
            'consent_id'     => $consentId,
            'event'          => $event,
            'expires_at'     => $expiresAt->toIso8601String(),
            'policy_version' => $policyVersion,
        ];
    }

    /**
     * Haal scripts op die mogen lopen voor een set categories die
     * zijn goedgekeurd. Returnt array van {category_key, name,
     * script_type, src_url, inline_code, attributes}.
     */
    public function getApprovedScripts(string $tenantKey, array $approvedCategories): array
    {
        if (empty($approvedCategories)) return [];
        return DB::table('cmp_scripts')
            ->where('tenant_key', $tenantKey)
            ->where('is_enabled', 1)
            ->whereIn('category_key', $approvedCategories)
            ->orderBy('sort_order')
            ->get(['category_key', 'name', 'script_type', 'src_url', 'inline_code', 'attributes_json'])
            ->map(function ($s) {
                $attrs = $s->attributes_json ? json_decode($s->attributes_json, true) : [];
                return [
                    'category_key' => $s->category_key,
                    'name'         => $s->name,
                    'type'         => $s->script_type,
                    'src'          => $s->src_url,
                    'inline'       => $s->inline_code,
                    'attributes'   => is_array($attrs) ? $attrs : [],
                ];
            })
            ->all();
    }
}
