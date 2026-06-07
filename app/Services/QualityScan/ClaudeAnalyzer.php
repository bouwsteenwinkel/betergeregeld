<?php

namespace App\Services\QualityScan;

use App\Services\Ai\AnthropicClient;
use App\Services\QualityScan\Contracts\ClaudeAnalyzerInterface;
use App\Services\QualityScan\Data\ExtractedContent;
use App\Services\QualityScan\Data\Finding;
use Illuminate\Support\Facades\Log;

/**
 * Kwalitatieve AI-analyse via de Anthropic Messages API (hergebruikt de
 * gedeelde AnthropicClient). Vraagt om JSON volgens een vast schema, parset
 * defensief en doet bij een parse-fout exact één retry.
 */
class ClaudeAnalyzer implements ClaudeAnalyzerInterface
{
	public function __construct(private readonly AnthropicClient $client)
	{
	}

	public function analyze(ExtractedContent $content): array
	{
		if (! config('quality-scan.ai.enabled', true)) {
			return $this->fail('AI-analyse is uitgeschakeld.', null);
		}

		$model = (string) config('quality-scan.ai.model');
		$package = json_encode($content->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		$user = "Hier is het JSON-pakket van de pagina:\n\n{$package}\n\n"
			. "Antwoord uitsluitend met geldige JSON volgens dit schema:\n" . SystemPrompt::SCHEMA_JSON;

		// Eerste poging.
		$text = $this->call($user, $model);
		if ($text === null) {
			return $this->fail($this->client->lastError ?? 'AI-aanroep mislukt.', $model);
		}
		$parsed = $this->parse($text);

		// Exact één retry bij een parse-fout.
		if ($parsed === null) {
			$retry = $user . "\n\nJe vorige antwoord was geen geldige JSON. Antwoord opnieuw, uitsluitend met geldige JSON.";
			$text = $this->call($retry, $model);
			if ($text === null) {
				return $this->fail($this->client->lastError ?? 'AI-aanroep mislukt (retry).', $model);
			}
			$parsed = $this->parse($text);
			if ($parsed === null) {
				Log::warning('QualityScan: AI gaf tweemaal ongeldige JSON.', ['model' => $model, 'url' => $content->finalUrl]);

				return $this->fail('De AI gaf tweemaal geen geldige JSON terug.', $model);
			}
		}

		$findings = [];
		foreach ($parsed['checks'] as $chk) {
			$findings[] = new Finding(
				source: 'ai',
				checkId: (string) $chk['id'],
				status: $chk['status'],
				severity: $chk['severity'],
				finding: (string) $chk['finding'],
				suggestion: isset($chk['suggestion']) && $chk['suggestion'] !== null ? (string) $chk['suggestion'] : null,
				element: isset($chk['element']) && $chk['element'] !== null ? (string) $chk['element'] : null,
			);
		}

		$usage = $this->client->lastUsage();

		return [
			'ok'             => true,
			'findings'       => $findings,
			'free_observations' => array_values(array_filter((array) ($parsed['vrije_observaties'] ?? []), 'is_string')),
			'ai_score'       => isset($parsed['score']) ? (int) $parsed['score'] : null,
			'model'          => $this->client->lastResponse['model'] ?? $model,
			'input_tokens'   => $usage['input'],
			'output_tokens'  => $usage['output'],
			'error'          => null,
		];
	}

	private function call(string $user, string $model): ?string
	{
		return $this->client->chat([
			'model'       => $model,
			'system'      => SystemPrompt::SYSTEM,
			'user'        => $user,
			'max_tokens'  => (int) config('quality-scan.ai.max_tokens', 2000),
			'temperature' => (float) config('quality-scan.ai.temperature', 0.2),
		]);
	}

	/** Parset de respons defensief: strip codefences, valideer JSON + schema. */
	private function parse(string $text): ?array
	{
		$t = trim($text);
		$t = preg_replace('/^```(?:json)?\s*/i', '', $t);
		$t = preg_replace('/\s*```\s*$/', '', $t);
		$t = trim($t);

		$data = json_decode($t, true);
		if (! is_array($data) || ! isset($data['checks']) || ! is_array($data['checks'])) {
			return null;
		}

		$valid = [];
		foreach ($data['checks'] as $c) {
			if (! is_array($c)
				|| empty($c['id'])
				|| ! in_array($c['status'] ?? null, ['pass', 'warn', 'fail'], true)
				|| ! in_array($c['severity'] ?? null, ['laag', 'middel', 'hoog'], true)
				|| ! isset($c['finding']) || $c['finding'] === '') {
				continue; // ongeldige check overslaan, niet de hele scan laten falen
			}
			$valid[] = $c;
		}
		$data['checks'] = $valid;

		return $data;
	}

	private function fail(string $error, ?string $model): array
	{
		return [
			'ok'             => false,
			'findings'       => [],
			'free_observations' => [],
			'ai_score'       => null,
			'model'          => $model,
			'input_tokens'   => null,
			'output_tokens'  => null,
			'error'          => $error,
		];
	}
}
