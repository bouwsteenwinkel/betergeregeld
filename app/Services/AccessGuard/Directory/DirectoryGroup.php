<?php

namespace App\Services\AccessGuard\Directory;

/**
 * Provider-agnostic group representation. For M365 we pull security-enabled
 * groups; for Google Workspace we'd pull groups of type=user. In both cases
 * the shape is the same.
 *
 * The `memberExternalIds` list is deliberately just IDs — DirectorySyncService
 * resolves them against Person rows using external_source + external_id.
 */
final class DirectoryGroup
{
	public function __construct(
		public readonly string $externalId,
		public readonly string $displayName,
		public readonly ?string $description,
		public readonly bool $isAdminGroup, // roles like "Global Administrators"
		/** @var string[] */
		public readonly array $memberExternalIds,
	) {}
}
