<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
	public const SUPPORTED = ['nl', 'en'];

	public function handle(Request $request, Closure $next): Response
	{
		$locale = $request->route('locale');

		if (! in_array($locale, self::SUPPORTED, true)) {
			$locale = config('app.locale', 'nl');
		}

		App::setLocale($locale);
		URL::defaults(['locale' => $locale]);

		return $next($request);
	}
}
