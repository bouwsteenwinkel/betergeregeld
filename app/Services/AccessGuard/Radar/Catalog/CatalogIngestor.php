<?php

namespace App\Services\AccessGuard\Radar\Catalog;

/**
 * Strategy contract for "go fetch the latest-stable / EOL data for one
 * source and write it into accessguard_radar_software_catalog".
 *
 * Implementations must be:
 *   - Idempotent: re-runnable any time, upsert by (vendor, product)
 *   - Network-failure-tolerant: catch + record into last_sync_error
 *     rather than throwing; one bad source must not abort the others
 *   - Self-pacing: respect rate limits via internal sleep / batching
 *
 * Returns the count of catalog rows it successfully wrote.
 */
interface CatalogIngestor
{
	public function name(): string;

	public function ingest(): int;
}
