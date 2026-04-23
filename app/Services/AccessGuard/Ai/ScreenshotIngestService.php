<?php

namespace App\Services\AccessGuard\Ai;

use App\Services\AccessGuard\CsvImportService;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Takes a screenshot of a SaaS admin panel (M365 admin center, Google
 * Workspace user list, Salesforce users, ...) and uses GPT-4o vision
 * to extract a list of users with their detected role/licence/email.
 *
 * Returns a list compatible with the CSV-preview cache format so the
 * existing import-map flow can pick it up without re-implementing the
 * commit step.
 */
class ScreenshotIngestService
{
	private const ALLOWED_MIME = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
	private const MAX_BYTES = 8 * 1024 * 1024; // 8 MB

	private const RESPONSE_SCHEMA = [
		'type' => 'object',
		'additionalProperties' => false,
		'required' => ['users', 'source_app'],
		'properties' => [
			'source_app' => ['type' => 'string'],
			'users' => [
				'type' => 'array',
				'items' => [
					'type' => 'object',
					'additionalProperties' => false,
					'required' => ['name', 'email', 'role'],
					'properties' => [
						'name' => ['type' => 'string'],
						'email' => ['type' => ['string', 'null']],
						'role' => ['type' => ['string', 'null']],
						'licence' => ['type' => ['string', 'null']],
					],
				],
			],
		],
	];

	public function isFake(): bool
	{
		$key = (string) config('accessguard.ai.api_key', '');
		return $key === '' || str_starts_with($key, 'fake_');
	}

	/**
	 * Validate + extract users from the uploaded screenshot.
	 *
	 * @return array{source_app:string, users:array, tokens_in:int, tokens_out:int, model:string}
	 */
	public function extract(UploadedFile $file): array
	{
		if ($file->getSize() > self::MAX_BYTES) {
			throw new RuntimeException('Screenshot te groot (max 8 MB)');
		}
		if (! in_array($file->getMimeType(), self::ALLOWED_MIME, true)) {
			throw new RuntimeException('Alleen PNG, JPG of WebP toegestaan');
		}

		if ($this->isFake()) {
			return $this->fakeExtract();
		}

		$base64 = base64_encode(file_get_contents($file->getRealPath()));
		$dataUrl = 'data:' . $file->getMimeType() . ';base64,' . $base64;

		$payload = [
			'model' => (string) config('accessguard.ai.model', 'gpt-4o-mini'),
			'messages' => [
				[
					'role' => 'system',
					'content' => 'You extract user lists from screenshots of SaaS admin panels '
						. '(M365, Google Workspace, Salesforce, Slack, Okta, etc). '
						. 'Return only what is visibly in the screenshot — never invent or hallucinate. '
						. 'For each row: exact display name, exact email (or null), exact role/licence visible (or null). '
						. 'source_app: which SaaS product the screenshot comes from.',
				],
				[
					'role' => 'user',
					'content' => [
						['type' => 'text', 'text' => 'Extract every user visible in this screenshot. Return JSON matching the schema.'],
						['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
					],
				],
			],
			'max_tokens' => 1500,
			'response_format' => [
				'type' => 'json_schema',
				'json_schema' => [
					'name' => 'accessguard_screenshot_extract',
					'schema' => self::RESPONSE_SCHEMA,
					'strict' => true,
				],
			],
		];

		$resp = Http::timeout(60)
			->withHeaders([
				'Authorization' => 'Bearer ' . config('accessguard.ai.api_key'),
				'Content-Type' => 'application/json',
			])
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->post('https://api.openai.com/v1/chat/completions', $payload);

		if (! $resp->successful()) {
			throw new RuntimeException('OpenAI error ' . $resp->status() . ': ' . substr((string) $resp->body(), 0, 200));
		}

		$body = $resp->json();
		$parsed = json_decode((string) ($body['choices'][0]['message']['content'] ?? ''), true);
		if (! is_array($parsed)) {
			throw new RuntimeException('OpenAI returned invalid JSON');
		}

		return [
			'source_app' => (string) ($parsed['source_app'] ?? 'Unknown'),
			'users' => $parsed['users'] ?? [],
			'tokens_in' => (int) ($body['usage']['prompt_tokens'] ?? 0),
			'tokens_out' => (int) ($body['usage']['completion_tokens'] ?? 0),
			'model' => (string) ($body['model'] ?? config('accessguard.ai.model')),
		];
	}

	/**
	 * Convert AI extraction into the CSV-preview cache shape so the
	 * existing map/commit flow can consume it. Presents headers
	 * (Name / Email / Role / Licence) + rows for every detected user.
	 *
	 * @return array{headers:array, rows:array, suggested_mapping:array<int,?string>}
	 */
	public function toCsvPreview(array $users): array
	{
		$headers = ['Name', 'Email', 'Role', 'Licence'];
		$rows = [];
		foreach ($users as $u) {
			$full = trim((string) ($u['name'] ?? ''));
			$parts = preg_split('/\s+/', $full, 2);
			$rows[] = [
				$full,
				(string) ($u['email'] ?? ''),
				(string) ($u['role'] ?? ''),
				(string) ($u['licence'] ?? ''),
			];
		}

		// Suggest mapping directly — no need to re-ask AI; we built the CSV
		// so we know which column is what.
		$suggested = [
			0 => 'first_name',  // we'll split in the commit step, but AI screenshot
								// shots always have "full name" — map it to first_name
								// initially and let user choose; see note below.
			1 => 'email',
			2 => 'job_title',
		];

		return [
			'headers' => $headers,
			'rows' => $rows,
			'suggested_mapping' => $suggested,
		];
	}

	private function fakeExtract(): array
	{
		return [
			'source_app' => 'Fake Admin Panel',
			'users' => [
				['name' => 'Alice Anderson', 'email' => 'alice@fake.example', 'role' => 'Admin', 'licence' => 'E5'],
				['name' => 'Bob Bakker', 'email' => 'bob@fake.example', 'role' => 'User', 'licence' => 'E3'],
				['name' => 'Carol Chen', 'email' => 'carol@fake.example', 'role' => 'User', 'licence' => 'Basic'],
				['name' => 'Daan de Jong', 'email' => 'daan@fake.example', 'role' => 'Global Admin', 'licence' => 'E5'],
			],
			'tokens_in' => 450,
			'tokens_out' => 120,
			'model' => 'fake:vision',
		];
	}
}
