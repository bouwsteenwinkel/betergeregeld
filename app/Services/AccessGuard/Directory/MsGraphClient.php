<?php

namespace App\Services\AccessGuard\Directory;

use App\Models\AccessGuard\DirectoryConnection;
use Carbon\CarbonImmutable;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Microsoft Graph v1.0 implementation of DirectoryClient.
 *
 * Uses the connection's access_token. If expired, refreshes via the
 * stored refresh_token and re-saves the connection. Paginates through
 * @odata.nextLink automatically.
 */
class MsGraphClient implements DirectoryClient
{
	private const GRAPH = 'https://graph.microsoft.com/v1.0';
	private const TOKEN_ENDPOINT = 'https://login.microsoftonline.com/%s/oauth2/v2.0/token';

	// Fields we actually care about. Graph returns whatever we don't ask for,
	// so $select keeps the payload small.
	private const USER_SELECT = 'id,mail,userPrincipalName,givenName,surname,jobTitle,department,accountEnabled,signInActivity';

	public function __construct(private DirectoryConnection $connection) {}

	public function listUsers(): iterable
	{
		$this->ensureFreshToken();

		$url = self::GRAPH . '/users?$select=' . self::USER_SELECT . '&$top=100';

		while ($url) {
			$resp = $this->http()->get($url);
			if ($resp->failed()) {
				throw new RuntimeException('Graph users call failed: ' . $resp->status() . ' ' . $resp->body());
			}
			$payload = $resp->json();
			foreach ($payload['value'] ?? [] as $u) {
				yield $this->toDirectoryUser($u);
			}
			$url = $payload['@odata.nextLink'] ?? null;
		}
	}

	private function toDirectoryUser(array $u): DirectoryUser
	{
		$lastSignIn = $u['signInActivity']['lastSignInDateTime'] ?? null;
		return new DirectoryUser(
			externalId: (string) $u['id'],
			email: $u['mail'] ?? $u['userPrincipalName'] ?? null,
			firstName: $u['givenName'] ?? null,
			lastName: $u['surname'] ?? null,
			jobTitle: $u['jobTitle'] ?? null,
			department: $u['department'] ?? null,
			accountEnabled: (bool) ($u['accountEnabled'] ?? false),
			lastSignInAt: $lastSignIn ? CarbonImmutable::parse($lastSignIn) : null,
		);
	}

	private function ensureFreshToken(): void
	{
		if (! $this->connection->isExpired()) {
			return;
		}
		if (! $this->connection->refresh_token) {
			throw new RuntimeException('Access token expired and no refresh token on file — user must reconnect M365.');
		}

		$endpoint = sprintf(self::TOKEN_ENDPOINT, $this->connection->external_tenant_id ?: 'common');
		$resp = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->asForm()
			->post($endpoint, [
				'client_id' => (string) config('services.microsoft.client_id'),
				'client_secret' => (string) config('services.microsoft.client_secret'),
				'refresh_token' => $this->connection->refresh_token,
				'grant_type' => 'refresh_token',
				'scope' => (string) config('services.microsoft.scope', 'User.Read.All Directory.Read.All offline_access'),
			]);

		if ($resp->failed()) {
			throw new RuntimeException('Token refresh failed: ' . $resp->status() . ' ' . $resp->body());
		}

		$body = $resp->json();
		$this->connection->access_token = $body['access_token'];
		if (! empty($body['refresh_token'])) {
			$this->connection->refresh_token = $body['refresh_token'];
		}
		$this->connection->expires_at = CarbonImmutable::now()->addSeconds((int) ($body['expires_in'] ?? 3600));
		$this->connection->save();
	}

	private function http()
	{
		return Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->withToken($this->connection->access_token)
			->acceptJson();
	}
}
