<?php

namespace App\Http\Middleware;

use App\Services\ChannelSiteResolver;
use App\Support\ChannelSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bindt de actieve ChannelSite in de container + deelt 'm met alle views als
 * $site. Resolutie:
 *   - preview-route ('_site/{channelKey}')  → op key (ook drafts)
 *   - live domein                            → op host (alleen live)
 * Geen match → 404.
 */
class ResolveChannelSite
{
    public function __construct(private ChannelSiteResolver $resolver)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key  = $request->route('channelKey');
        $site = $key ? $this->resolver->byKey($key) : $this->resolver->byHost($request->getHost());

        abort_if(! $site instanceof ChannelSite, 404);

        app()->setLocale($site->locale());
        app()->instance(ChannelSite::class, $site);
        view()->share('site', $site);
        view()->share('placesList', $this->resolver->places());

        return $next($request);
    }
}
