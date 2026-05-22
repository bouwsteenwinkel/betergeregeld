<?php

namespace App\Services\AccessGuard\Radar\Fingerprint;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Http\HttpClient;

/**
 * Strategy contract — every fingerprinter inspects an asset and returns
 * zero or more DetectedSoftware rows.
 *
 * Implementations must be:
 *   - Read-only (no POST/PUT/DELETE — SafeHttpClient enforces this)
 *   - Idempotent (no caching state on the instance between calls)
 *   - Failure-tolerant (a 404 or TLS error returns [], never throws)
 *
 * Throwing is reserved for programmer errors. Network errors and
 * suspicious server responses are normal and should yield an empty array.
 */
interface Fingerprinter
{
	/** @return DetectedSoftware[] */
	public function fingerprint(Asset $asset, HttpClient $http): array;
}
