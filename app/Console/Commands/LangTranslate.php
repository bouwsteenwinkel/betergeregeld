<?php

namespace App\Console\Commands;

use App\Services\Ai\AnthropicClient;
use Illuminate\Console\Command;
use Throwable;

/**
 * Vertaal lang/nl.json (bron) naar één of meer target-locales via
 * Claude. Bestaande lang/{loc}.json wordt aangevuld met ontbrekende
 * keys — bestaande vertalingen worden niet overschreven.
 *
 * Gebruik:
 *   php artisan lang:translate de fr es
 *   php artisan lang:translate de --force      # overschrijf bestaande
 *   php artisan lang:translate de --missing-only # alleen ontbrekende keys
 */
class LangTranslate extends Command
{
	protected $signature = 'lang:translate
		{locales* : Doel-locales (bv. de fr es)}
		{--source=nl : bron-locale}
		{--force : overschrijf bestaande vertalingen}
		{--missing-only : alleen keys die nog ontbreken (default true; --force overrulet dit)}';

	protected $description = 'Vertaal lang/{source}.json naar andere locales via Claude.';

	public function handle(AnthropicClient $ai): int
	{
		$source = (string) $this->option('source');
		$sourcePath = lang_path("{$source}.json");
		if (!is_file($sourcePath)) {
			$this->error("lang/{$source}.json bestaat niet.");
			return self::FAILURE;
		}
		$sourceData = json_decode((string) file_get_contents($sourcePath), true);
		if (!is_array($sourceData)) {
			$this->error("lang/{$source}.json is geen geldig JSON.");
			return self::FAILURE;
		}

		$languageNames = [
			'nl' => 'Dutch',
			'en' => 'English',
			'de' => 'German',
			'fr' => 'French',
			'es' => 'Spanish',
		];
		$sourceLang = $languageNames[$source] ?? $source;

		$force = (bool) $this->option('force');
		$totalOk = 0; $totalFail = 0;

		foreach ($this->argument('locales') as $target) {
			$target = (string) $target;
			$targetLang = $languageNames[$target] ?? $target;
			$targetPath = lang_path("{$target}.json");

			$existing = is_file($targetPath)
				? (json_decode((string) file_get_contents($targetPath), true) ?: [])
				: [];

			$toTranslate = [];
			foreach ($sourceData as $key => $val) {
				if ($force || !isset($existing[$key]) || $existing[$key] === '' || $existing[$key] === $key) {
					$toTranslate[$key] = $val;
				}
			}
			if (empty($toTranslate)) {
				$this->info("[{$target}] up-to-date, geen vertalingen nodig.");
				continue;
			}

			$this->info("[{$target}] " . count($toTranslate) . ' keys te vertalen via Claude…');

			// Chunk in batches om binnen max_tokens te blijven. 100 is kleiner
			// dan ideaal qua doorvoer, maar Claude's tool_use kan sporadisch
			// een grote chunk afkappen — eerder gezien op 200.
			$chunks = array_chunk($toTranslate, 100, true);
			$merged = $existing;
			$chunkIdx = 0;
			foreach ($chunks as $chunk) {
				$chunkIdx++;
				$result = null;
				$attempts = 0;
				// Retry tot 3× bij null-response (eerste Claude-call in een
				// batch faalt soms cold-start; warming up helpt).
				while ($result === null && $attempts < 3) {
					$attempts++;
					$result = $this->translateChunk($ai, $chunk, $sourceLang, $targetLang);
					if ($result === null && $attempts < 3) {
						$this->line(sprintf('  … chunk %d retry %d/2 (last: %s)', $chunkIdx, $attempts, $ai->lastError ?? 'unknown'));
						usleep(500_000); // 500ms backoff
					}
				}
				if ($result === null) {
					$this->error("  ✕ chunk {$chunkIdx} faalde na 3 pogingen: " . ($ai->lastError ?? 'unknown'));
					$totalFail++;
					continue;
				}
				$merged = array_merge($merged, $result);
				$this->line(sprintf('  ✓ chunk %d/%d: %d keys vertaald', $chunkIdx, count($chunks), count($result)));
				$totalOk += count($result);
			}

			// Behoud key-order van source voor diffability
			$ordered = [];
			foreach ($sourceData as $key => $_) {
				$ordered[$key] = $merged[$key] ?? $key; // fallback naar key zelf als nog geen vertaling
			}

			file_put_contents(
				$targetPath,
				json_encode($ordered, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
			);
			$this->info("[{$target}] geschreven naar lang/{$target}.json (" . count($ordered) . ' totaal)');
		}

		$this->info("Klaar: {$totalOk} vertalingen, {$totalFail} fouten.");
		return $totalFail === 0 ? self::SUCCESS : self::FAILURE;
	}

	/**
	 * Vertaal één chunk van source→target via Claude tool_use.
	 *
	 * @return array<string,string>|null
	 */
	private function translateChunk(AnthropicClient $ai, array $chunk, string $sourceLang, string $targetLang): ?array
	{
		$system = <<<TXT
You are a professional translator. Translate UI strings from $sourceLang to $targetLang for a Dutch SMB-focused tech consultancy website (Beter Geregeld ICT). The strings appear in buttons, navigation, headers, form labels, and short marketing copy.

RULES:
1. Keep brand names, product names and proper nouns unchanged: Beter Geregeld ICT, VIES, IBAN, SEPA, AccessGuard, M365, Entra ID, BTW/VAT, AVG/GDPR.
2. Localize where the equivalent is clearly common: "BTW" → "VAT" in English, "Mehrwertsteuer" in German, "TVA" in French, "IVA" in Spanish.
3. Keep technical terms in the form most readers in $targetLang expect (e.g., "Cookie banner" stays "Cookie banner" in German/French/Spanish).
4. Preserve placeholder tokens like :name, {count}, %s exactly.
5. Keep the SAME punctuation style and capitalization style.
6. Short labels (1-3 words) stay short. Sentences stay sentences.
7. If a key is identical to its value (= source already in target language, or proper noun), KEEP IT as-is.
TXT;

		$user = "Translate the following $sourceLang UI strings to $targetLang. The JSON keys are the source $sourceLang strings; provide a JSON object back where each key maps to its $targetLang translation.\n\nInput:\n" . json_encode($chunk, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		$result = $ai->structuredCall([
			'model'             => $ai->translatorModel(),
			'system'            => $system,
			'user'              => $user,
			'max_tokens'        => 16000,
			'tool_name'         => 'submit_translations',
			'tool_description'  => 'Submit the translated JSON object via this tool. Same keys as input, values in target language.',
			'tool_input_schema' => [
				'type' => 'object',
				'properties' => [
					'translations' => [
						'type' => 'object',
						'description' => 'Map of source-string → target-string. Keys must match input keys 1-on-1.',
						'additionalProperties' => ['type' => 'string'],
					],
				],
				'required' => ['translations'],
			],
		]);

		if (!is_array($result) || !is_array($result['translations'] ?? null)) {
			return null;
		}
		return $result['translations'];
	}
}
