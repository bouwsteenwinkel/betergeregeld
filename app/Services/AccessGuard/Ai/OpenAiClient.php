<?php

namespace App\Services\AccessGuard\Ai;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around OpenAI chat completions.
 *
 * Fake mode kicks in when the API key is empty or starts with "fake_".
 * It returns a canned JSON payload shaped like a successful response so
 * the whole pipeline (service + controller + UI) can be smoke-tested
 * without making real API calls.
 */
class OpenAiClient
{
	public function isFake(): bool
	{
		$key = (string) config('accessguard.ai.api_key', '');
		return $key === '' || str_starts_with($key, 'fake_');
	}

	/**
	 * @return array{json: array, tokens_in: int, tokens_out: int, model: string}
	 */
	public function chat(string $systemPrompt, string $userPrompt, array $responseSchema): array
	{
		$model = (string) config('accessguard.ai.model', 'gpt-4o-mini');

		if ($this->isFake()) {
			return $this->fakeResponse($userPrompt, $model);
		}

		$payload = [
			'model' => $model,
			'messages' => [
				['role' => 'system', 'content' => $systemPrompt],
				['role' => 'user', 'content' => $userPrompt],
			],
			'temperature' => (float) config('accessguard.ai.temperature', 0.2),
			'max_tokens' => (int) config('accessguard.ai.max_tokens', 500),
			'response_format' => [
				'type' => 'json_schema',
				'json_schema' => [
					'name' => 'accessguard_explanation',
					'schema' => $responseSchema,
					'strict' => true,
				],
			],
		];

		$resp = Http::timeout((int) config('accessguard.ai.timeout_seconds', 15))
			->withHeaders([
				'Authorization' => 'Bearer ' . config('accessguard.ai.api_key'),
				'Content-Type' => 'application/json',
			])
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->post('https://api.openai.com/v1/chat/completions', $payload);

		if (! $resp->successful()) {
			throw new RuntimeException('OpenAI error ' . $resp->status() . ': ' . $resp->body());
		}

		$body = $resp->json();
		$content = $body['choices'][0]['message']['content'] ?? '';
		$parsed = json_decode((string) $content, true);
		if (! is_array($parsed)) {
			throw new RuntimeException('OpenAI returned non-JSON content');
		}

		return [
			'json' => $parsed,
			'tokens_in' => (int) ($body['usage']['prompt_tokens'] ?? 0),
			'tokens_out' => (int) ($body['usage']['completion_tokens'] ?? 0),
			'model' => (string) ($body['model'] ?? $model),
		];
	}

	private function fakeResponse(string $userPrompt, string $model): array
	{
		// Reflect a few chars of the prompt so tests can confirm the
		// pipeline carried context through; don't echo sensitive data.
		$excerpt = mb_substr(strip_tags($userPrompt), 0, 60);

		return [
			'json' => [
				'title' => 'AI-uitleg (fake mode)',
				'summary' => 'Dit is een test-antwoord omdat OPENAI_API_KEY niet is gezet. Context: ' . $excerpt,
				'why_it_matters' => 'Uitleg verschijnt hier in de echte call. De pipeline (context → prompt → logging) werkt wel.',
				'recommended_steps' => [
					'Configureer OPENAI_API_KEY in .env',
					'Herstart de app of draai php artisan config:clear',
					'Klik opnieuw op "Leg uit met AI"',
				],
				'warnings' => [],
			],
			'tokens_in' => max(1, (int) round(mb_strlen($userPrompt) / 4)),
			'tokens_out' => 80,
			'model' => 'fake:' . $model,
		];
	}
}
