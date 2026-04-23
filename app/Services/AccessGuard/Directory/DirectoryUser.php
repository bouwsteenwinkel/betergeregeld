<?php

namespace App\Services\AccessGuard\Directory;

use Carbon\CarbonImmutable;

/**
 * Provider-agnostic user representation used by DirectorySyncService.
 * Both Microsoft Graph and (future) Google Directory are mapped to this
 * shape before we touch the AccessGuard Person table.
 */
final class DirectoryUser
{
	public function __construct(
		public readonly string $externalId,
		public readonly ?string $email,
		public readonly ?string $firstName,
		public readonly ?string $lastName,
		public readonly ?string $jobTitle,
		public readonly ?string $department,
		public readonly bool $accountEnabled,
		public readonly ?CarbonImmutable $lastSignInAt,
	) {}
}
