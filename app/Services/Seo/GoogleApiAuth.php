<?php

namespace App\Services\Seo;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google API auth via service-account JWT bearer.
 *
 * Werkwijze (zonder externe libs):
 *   1. Lees config/google-api.json (service-account-key, gitignored).
 *   2. Maak een JWT (header.payload, RS256-signed met de private_key).
 *   3. POST naar https://oauth2.googleapis.com/token met grant_type=
 *      'urn:ietf:params:oauth:grant-type:jwt-bearer' en de assertion.
 *   4. Cache het access_token tot 60s vóór verloop.
 *
 * Patroon overgenomen van BSW V3 (includes/google-api-auth.php).
 */
class GoogleApiAuth
{
	public const DEFAULT_SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';
	public const RW_SCOPE      = 'https://www.googleapis.com/auth/webmasters';

	/**
	 * Pad naar de service-account-JSON. Override via .env GOOGLE_API_KEY_PATH.
	 */
	public function keyPath(): string
	{
		$path = env('GOOGLE_API_KEY_PATH', storage_path('app/google-api.json'));
		return is_string($path) && $path !== '' ? $path : storage_path('app/google-api.json');
	}

	public function loadServiceAccount(): array
	{
		$path = $this->keyPath();
		if (!is_file($path)) {
			throw new RuntimeException("Google service-account key ontbreekt: {$path}. Zet GOOGLE_API_KEY_PATH in .env of plaats de JSON in storage/app/google-api.json.");
		}
		$data = json_decode((string) file_get_contents($path), true);
		if (!is_array($data) || empty($data['private_key']) || empty($data['client_email'])) {
			throw new RuntimeException('Google service-account JSON is ongeldig (verwacht: private_key + client_email).');
		}
		return $data;
	}

	/**
	 * Returnt een geldig access-token. Hergebruikt cache zolang er nog
	 * minstens 60s leven in zit.
	 */
	public function accessToken(string $scope = self::DEFAULT_SCOPE, ?string $subject = null): string
	{
		$cacheKey = 'seo:gsc:token:' . sha1($scope . '|' . ($subject ?? ''));
		$cached = Cache::get($cacheKey);
		if (is_array($cached) && !empty($cached['token']) && (int) ($cached['expires_at'] ?? 0) > time() + 60) {
			return (string) $cached['token'];
		}

		$sa  = $this->loadServiceAccount();
		$now = time();
		$exp = $now + 3600;

		$header = ['alg' => 'RS256', 'typ' => 'JWT'];
		$payload = [
			'iss'   => $sa['client_email'],
			'scope' => $scope,
			'aud'   => 'https://oauth2.googleapis.com/token',
			'iat'   => $now,
			'exp'   => $exp,
		];
		if ($subject) {
			$payload['sub'] = $subject;
		}

		$hB64 = self::b64url(json_encode($header));
		$pB64 = self::b64url(json_encode($payload));
		$signing = $hB64 . '.' . $pB64;

		$pkey = openssl_pkey_get_private($sa['private_key']);
		if (!$pkey) {
			throw new RuntimeException('Kon private_key niet lezen uit service-account JSON.');
		}
		$signature = '';
		if (!openssl_sign($signing, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
			throw new RuntimeException('JWT signing mislukt.');
		}
		$jwt = $signing . '.' . self::b64url($signature);

		$resp = Http::asForm()
			->timeout(20)
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->post('https://oauth2.googleapis.com/token', [
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion'  => $jwt,
			]);

		$body = $resp->json();
		if (!$resp->ok() || empty($body['access_token'])) {
			$err = $body['error_description'] ?? ($body['error'] ?? 'unknown');
			throw new RuntimeException('Google token-exchange mislukt: HTTP ' . $resp->status() . ' — ' . $err);
		}

		$token = (string) $body['access_token'];
		$expiresAt = $now + (int) ($body['expires_in'] ?? 3600);

		Cache::put($cacheKey, ['token' => $token, 'expires_at' => $expiresAt], $expiresAt - $now - 30);

		return $token;
	}

	private static function b64url(string $input): string
	{
		return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
	}
}
