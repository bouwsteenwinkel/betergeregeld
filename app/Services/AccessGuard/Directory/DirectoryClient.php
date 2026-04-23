<?php

namespace App\Services\AccessGuard\Directory;

/**
 * Contract for any directory provider (M365, Google Workspace, …).
 * DirectorySyncService depends on this interface, not a concrete client,
 * so we can swap providers or inject a fake in tests.
 *
 * Implementations must translate provider-specific responses into
 * DirectoryUser value objects.
 *
 * @phpstan-type UserPage iterable<DirectoryUser>
 */
interface DirectoryClient
{
	/**
	 * Stream users page-by-page. Yielding is a requirement, not a suggestion —
	 * large tenants can have 10k+ users and we don't want to hold them all
	 * in memory at once.
	 *
	 * @return iterable<DirectoryUser>
	 */
	public function listUsers(): iterable;
}
