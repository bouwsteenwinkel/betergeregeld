<?php

namespace App\Services\AccessGuard\Radar\Fingerprint;

/**
 * One row of "we believe this software runs on this asset". Maps directly
 * onto an accessguard_radar_software_instances row when persisted.
 *
 * detection_method values must match the migration enum-string set:
 *   http_header | generator_meta | wp_json | tls_banner | osv_lookup | manual | agent
 *
 * confidence is 0.0–1.0 — fingerprinters set it on a calibrated scale,
 * the matcher (next slice) uses it to decide whether to surface findings.
 */
final class DetectedSoftware
{
	public function __construct(
		public readonly string $vendor,
		public readonly string $product,
		public readonly ?string $version,
		public readonly string $detectionMethod,
		public readonly float $confidence,
		public readonly ?string $path = null,
	) {}
}
