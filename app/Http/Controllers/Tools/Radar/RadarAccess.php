<?php

namespace App\Http\Controllers\Tools\Radar;

use App\Services\Features\FeatureBag;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;

/**
 * Plan-gate helper voor Radar klant-controllers. Returnt de FeatureBag van
 * de huidige user zodat de controller meteen plan-limieten kan lezen
 * (max_assets, checks_allowed, scans_per_day). Abort 401 als niet ingelogd,
 * 402 als de tool niet in 't huidige plan zit.
 */
final class RadarAccess
{
	public static function require(Request $request, FeatureResolver $features): FeatureBag
	{
		abort_unless($request->user(), 401);
		$bag = $features->forUser($request->user());
		abort_unless($bag->bool('tool.radar.enabled'), 402);
		return $bag;
	}
}
