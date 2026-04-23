<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\VaultAccessLog;
use App\Models\AccessGuard\VaultAcl;
use App\Models\AccessGuard\VaultCredential;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Vault: secret storage with per-user ACL and an append-only access log.
 *
 * Encryption uses Laravel's Crypt facade (AES-256-CBC + HMAC, key =
 * APP_KEY). Tenant-scoped, but keyed against a single master key —
 * envelope-encryption per tenant is a future upgrade.
 *
 * Access model:
 *   - The creator is implicit admin/view/decrypt/rotate (no ACL row needed).
 *   - Any other user needs an explicit ACL row with the relevant flag(s).
 *   - Every view/decrypt/rotate writes to the access log, including
 *     a hashed client IP so audit stays useful without storing PII.
 */
class VaultService
{
	public function encrypt(string $plaintext): string
	{
		return Crypt::encryptString($plaintext);
	}

	public function decrypt(string $payload): string
	{
		return Crypt::decryptString($payload);
	}

	/**
	 * Create a credential. The caller is implicit owner — they don't need
	 * an ACL row to view/decrypt/rotate their own secret.
	 */
	public function create(
		string $tenantId,
		string $actorUserId,
		string $name,
		string $type,
		string $plaintextSecret,
		array $options = [],
		?Request $request = null,
	): VaultCredential {
		if (! in_array($type, VaultCredential::TYPES, true)) {
			throw new RuntimeException("Invalid credential type: {$type}");
		}

		return DB::transaction(function () use ($tenantId, $actorUserId, $name, $type, $plaintextSecret, $options, $request) {
			$cred = VaultCredential::create([
				'tenant_id' => $tenantId,
				'system_id' => $options['system_id'] ?? null,
				'access_item_id' => $options['access_item_id'] ?? null,
				'name' => $name,
				'type' => $type,
				'username' => $options['username'] ?? null,
				'secret_enc' => $this->encrypt($plaintextSecret),
				'metadata' => $options['metadata'] ?? null,
				'expires_at' => $options['expires_at'] ?? null,
				'rotation_interval_days' => $options['rotation_interval_days'] ?? null,
				'last_rotated_at' => CarbonImmutable::now(),
				'created_by_user_id' => $actorUserId,
			]);

			$this->log($tenantId, $actorUserId, $cred->id, 'created', $request, [
				'type' => $type,
			]);

			return $cred;
		});
	}

	public function update(
		string $tenantId,
		string $actorUserId,
		VaultCredential $cred,
		array $fields,
		?string $newSecret = null,
		?Request $request = null,
	): VaultCredential {
		$this->mustAdmin($tenantId, $actorUserId, $cred);

		return DB::transaction(function () use ($tenantId, $actorUserId, $cred, $fields, $newSecret, $request) {
			$fields = array_intersect_key($fields, array_flip([
				'name', 'type', 'username', 'metadata',
				'system_id', 'access_item_id',
				'expires_at', 'rotation_interval_days',
			]));
			if ($newSecret !== null && $newSecret !== '') {
				$fields['secret_enc'] = $this->encrypt($newSecret);
				$fields['last_rotated_at'] = CarbonImmutable::now();
			}
			$cred->update($fields);

			$this->log($tenantId, $actorUserId, $cred->id, $newSecret ? 'rotated' : 'updated', $request);

			return $cred;
		});
	}

	public function delete(string $tenantId, string $actorUserId, VaultCredential $cred, ?Request $request = null): void
	{
		$this->mustAdmin($tenantId, $actorUserId, $cred);
		$this->log($tenantId, $actorUserId, $cred->id, 'deleted', $request);
		$cred->delete();
	}

	public function reveal(string $tenantId, string $actorUserId, VaultCredential $cred, ?Request $request = null): string
	{
		if (! $this->canDecrypt($tenantId, $actorUserId, $cred)) {
			throw new RuntimeException('No decrypt permission for this credential');
		}
		$this->log($tenantId, $actorUserId, $cred->id, 'decrypted', $request);
		return $this->decrypt($cred->secret_enc);
	}

	public function grant(
		string $tenantId,
		string $actorUserId,
		VaultCredential $cred,
		string $userId,
		array $perms,
		?Request $request = null,
	): VaultAcl {
		$this->mustAdmin($tenantId, $actorUserId, $cred);

		$perms = array_merge(
			['can_view' => true, 'can_decrypt' => false, 'can_rotate' => false, 'can_admin' => false],
			$perms,
		);

		$acl = VaultAcl::updateOrCreate(
			['credential_id' => $cred->id, 'user_id' => $userId],
			$perms + [
				'tenant_id' => $tenantId,
				'granted_at' => CarbonImmutable::now(),
				'granted_by_user_id' => $actorUserId,
				'revoked_at' => null,
				'revoked_by_user_id' => null,
			],
		);

		$this->log($tenantId, $actorUserId, $cred->id, 'acl_granted', $request, [
			'target_user_id' => $userId,
			'perms' => $perms,
		]);

		return $acl;
	}

	public function revoke(string $tenantId, string $actorUserId, VaultAcl $acl, ?Request $request = null): void
	{
		if ($acl->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on ACL');
		}
		$this->mustAdmin($tenantId, $actorUserId, $acl->credential);

		$acl->update([
			'revoked_at' => CarbonImmutable::now(),
			'revoked_by_user_id' => $actorUserId,
			'can_view' => false,
			'can_decrypt' => false,
			'can_rotate' => false,
			'can_admin' => false,
		]);

		$this->log($tenantId, $actorUserId, $acl->credential_id, 'acl_revoked', $request, [
			'target_user_id' => $acl->user_id,
		]);
	}

	/**
	 * Revoke every ACL this user holds (called from offboarding-complete).
	 * @return int number of rows revoked
	 */
	public function revokeAllForUser(string $tenantId, string $actorUserId, string $targetUserId, ?Request $request = null): int
	{
		$acls = VaultAcl::query()
			->where('tenant_id', $tenantId)
			->where('user_id', $targetUserId)
			->whereNull('revoked_at')
			->get();

		$n = 0;
		foreach ($acls as $acl) {
			$acl->update([
				'revoked_at' => CarbonImmutable::now(),
				'revoked_by_user_id' => $actorUserId,
				'can_view' => false,
				'can_decrypt' => false,
				'can_rotate' => false,
				'can_admin' => false,
			]);
			$this->log($tenantId, $actorUserId, $acl->credential_id, 'acl_revoked', $request, [
				'target_user_id' => $targetUserId,
				'reason' => 'offboarding_bulk',
			]);
			$n++;
		}
		return $n;
	}

	// ----- authorization helpers -----

	public function canView(string $tenantId, string $userId, VaultCredential $cred): bool
	{
		if ($cred->tenant_id !== $tenantId) return false;
		if ((string) $cred->created_by_user_id === $userId) return true;
		return $this->activeAcl($cred->id, $userId)?->can_view === true;
	}

	public function canDecrypt(string $tenantId, string $userId, VaultCredential $cred): bool
	{
		if ($cred->tenant_id !== $tenantId) return false;
		if ((string) $cred->created_by_user_id === $userId) return true;
		return $this->activeAcl($cred->id, $userId)?->can_decrypt === true;
	}

	public function canAdmin(string $tenantId, string $userId, VaultCredential $cred): bool
	{
		if ($cred->tenant_id !== $tenantId) return false;
		if ((string) $cred->created_by_user_id === $userId) return true;
		return $this->activeAcl($cred->id, $userId)?->can_admin === true;
	}

	private function mustAdmin(string $tenantId, string $userId, VaultCredential $cred): void
	{
		if (! $this->canAdmin($tenantId, $userId, $cred)) {
			throw new RuntimeException('No admin permission for this credential');
		}
	}

	private function activeAcl(int $credentialId, string $userId): ?VaultAcl
	{
		return VaultAcl::query()
			->where('credential_id', $credentialId)
			->where('user_id', $userId)
			->whereNull('revoked_at')
			->first();
	}

	private function log(
		string $tenantId,
		string $actorUserId,
		?int $credentialId,
		string $action,
		?Request $request,
		array $meta = [],
	): void {
		VaultAccessLog::create([
			'tenant_id' => $tenantId,
			'credential_id' => $credentialId,
			'user_id' => $actorUserId,
			'action' => $action,
			'ip_hash' => $request ? hash('sha256', ($request->ip() ?? '') . '|' . config('app.key')) : null,
			'meta' => $meta ?: null,
			'occurred_at' => CarbonImmutable::now(),
		]);
	}
}
