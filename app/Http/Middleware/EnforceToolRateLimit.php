<?php

namespace App\Http\Middleware;

use App\Services\Features\ToolUsageTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware:  'tool.limit:iban'  (tool slug without 'tool.' prefix).
 *
 * On GET: attaches usage info to the request so the view can show remaining credits.
 * On POST: blocks with a soft-paywall redirect back if the user/IP is over the daily limit.
 * Successful (non-redirect, non-4xx) POSTs are counted after the controller runs.
 */
class EnforceToolRateLimit
{
	public function __construct(private readonly ToolUsageTracker $tracker) {}

	public function handle(Request $request, Closure $next, string $tool): Response
	{
		$check = $this->tracker->check($request, $tool);
		$request->attributes->set('tool_usage', $check + ['tool' => $tool]);

		if ($request->isMethod('POST') && ! $check['allowed']) {
			return back()
				->withInput($request->except('_token'))
				->with('tool_limit_reached', [
					'tool' => $tool,
					'limit' => $check['limit'],
					'scope' => $check['scope'],
				]);
		}

		$response = $next($request);

		if ($request->isMethod('POST')
			&& $response->getStatusCode() < 400
			&& ! $response->isRedirection()) {
			$this->tracker->record($request, $tool);
		} elseif ($request->isMethod('POST') && $response->isRedirection()) {
			// Laravel's validation redirect is handled before this, but for controller
			// redirects (e.g. success flash) we still want to count a real submission.
			$this->tracker->record($request, $tool);
		}

		return $response;
	}
}
