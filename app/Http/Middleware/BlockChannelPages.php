<?php

namespace App\Http\Middleware;

use App\Support\ChannelSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 404't specifieke pagina's per kanaal (config/channel_page_blocklist.php), zodat
 * één kanaal een uitgeklede site kan tonen zonder de gedeelde channel-routes/views
 * voor de andere kanalen te raken.
 *
 * Draait NA ResolveChannelSite (die de actieve ChannelSite bindt). Alleen "veilige"
 * requests (GET/HEAD) worden geblokkeerd; POST-endpoints zoals het lead-formulier
 * (POST /contact) blijven dus altijd werken. Kanalen zonder blocklist-entry worden
 * volledig ongemoeid gelaten.
 */
class BlockChannelPages
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nooit form-/schrijf-endpoints blokkeren (POST /contact = leadStore, enz.).
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        if (! app()->bound(ChannelSite::class)) {
            return $next($request);
        }

        $site    = app(ChannelSite::class);
        $blocked = (array) config('channel_page_blocklist.' . $site->key, []);
        if (! $blocked) {
            return $next($request);
        }

        $segment = $this->firstSegment($request, $site->key);
        if ($segment !== '' && in_array($segment, $blocked, true)) {
            // Soft-404-kanalen: gewoon terug naar de homepage i.p.v. een foutpagina.
            if ($site->redirectsNotFoundToHome()) {
                return redirect($site->url(''), 302);
            }
            // Anders een nette, on-brand 404 (zelfde pagina als de catch-all).
            return response()->view('channels.not-found', ['site' => $site], 404);
        }

        return $next($request);
    }

    /**
     * Eerste padsegment relatief aan de kanaal-root. Onder de preview-prefix
     * ('_site/{key}/...') wordt die prefix eerst gestript; op een live domein is
     * het pad al relatief.
     */
    private function firstSegment(Request $request, string $key): string
    {
        $path   = trim($request->path(), '/');
        $prefix = '_site/' . $key;

        if ($path === $prefix) {
            return '';
        }
        if (str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix) + 1);
        }

        return explode('/', $path, 2)[0];
    }
}
