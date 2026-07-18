<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Models\ChannelEvent;
use App\Support\ChannelSite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * First-party beacon voor de funnel-events (zie App\Models\ChannelEvent + migratie).
 * De site pusht sleutel-events hierheen zodat we een eigen, controleerbare grondwaarheid
 * hebben naast Meta/Google. Dataminimaal: geen IP/UA, geen query-string, geen PII.
 */
class ChannelEventController extends Controller
{
    public function store(Request $request): Response
    {
        $event = (string) $request->input('e', '');

        // Allowlist: onbekende/micro-events stil laten vallen (204), geen ruis of misbruik.
        if (! in_array($event, ChannelEvent::ALLOWED, true)) {
            return response('', 204);
        }

        // Groepering per bezoek via een sessie-scoped willekeurige ref. Staat in de sessie
        // (server-side), niet als eigen device-cookie → geen aparte toestemming nodig.
        $visitRef = $request->session()->get('bg_ev_ref');
        if (! is_string($visitRef) || strlen($visitRef) !== 32) {
            $visitRef = Str::random(32);
            $request->session()->put('bg_ev_ref', $visitRef);
        }

        // Site-key uit de opgeloste channel-site (indien beschikbaar).
        $siteKey = null;
        if (app()->bound(ChannelSite::class)) {
            $siteKey = app(ChannelSite::class)->key;
        }

        // Pad zonder query-string (geen gclid/utm/PII).
        $path = strtok((string) $request->input('p', ''), '?') ?: null;

        ChannelEvent::create([
            'event' => $event,
            'site_key' => $siteKey ? mb_substr($siteKey, 0, 80) : null,
            'visit_ref' => $visitRef,
            'path' => $path ? mb_substr($path, 0, 255) : null,
            'params' => $this->cleanParams($request->input('d')),
            'created_at' => now(),
        ]);

        return response('', 204);
    }

    /**
     * Alleen niet-persoonlijke, scalaire event-data (seconds, step, site, reason).
     * Alles wat geen string/number/bool is valt weg; strings worden afgekapt.
     *
     * @return array<string,mixed>|null
     */
    private function cleanParams(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }
        $out = [];
        foreach ($data as $k => $v) {
            if (count($out) >= 12) {
                break;
            }
            if (! is_string($k) || ! preg_match('/^[a-z0-9_]{1,32}$/i', $k)) {
                continue;
            }
            if (is_string($v)) {
                $out[$k] = mb_substr($v, 0, 120);
            } elseif (is_int($v) || is_float($v) || is_bool($v)) {
                $out[$k] = $v;
            }
        }

        return $out ?: null;
    }
}
