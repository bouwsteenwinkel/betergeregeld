<?php

namespace App\Console\Commands;

use App\Services\Ai\AnthropicClient;
use Illuminate\Console\Command;
use Throwable;

/**
 * Vertaal config/services_catalog.php (NL/EN-source) naar extra
 * locales (DE/FR/ES) en schrijf naar config/services_catalog_extra.php.
 *
 * Idempotent: bestaande vertalingen in extra-config worden behouden
 * tenzij --force. Per service per locale: één Claude tool_use-call met
 * h1/lead/pill/bullets/cta_title/cta_text in één keer.
 */
class ServicesTranslateCatalog extends Command
{
	protected $signature = 'services:translate-catalog
		{locales* : Doel-locales (bv. de fr es)}
		{--force : Overschrijf bestaande vertalingen}';

	protected $description = 'Vertaal services_catalog naar extra locales en schrijf config/services_catalog_extra.php';

	public function handle(AnthropicClient $ai): int
	{
		$catalog = config('services_catalog');
		if (!is_array($catalog)) {
			$this->error('config/services_catalog.php ontbreekt.');
			return self::FAILURE;
		}

		$extraPath = config_path('services_catalog_extra.php');
		$extra = is_file($extraPath) ? require $extraPath : [];
		if (!is_array($extra)) $extra = [];

		$force = (bool) $this->option('force');
		$languageNames = [
			'de' => 'German',
			'fr' => 'French',
			'es' => 'Spanish',
			'en' => 'English',
		];

		$totalOk = 0; $totalSkip = 0; $totalFail = 0;

		foreach ($this->argument('locales') as $target) {
			$target = (string) $target;
			$targetLang = $languageNames[$target] ?? $target;
			$this->info("=== Locale: {$target} ({$targetLang}) ===");

			foreach ($catalog as $slug => $service) {
				if (!$force && !empty($extra[$slug][$target])) {
					$totalSkip++;
					$this->line("  · {$slug} → {$target}: skip (al vertaald)");
					continue;
				}

				try {
					$translated = $this->translateService($ai, $service, $target, $targetLang);
				} catch (Throwable $e) {
					$totalFail++;
					$this->warn("  ✕ {$slug} → {$target}: " . $e->getMessage());
					continue;
				}

				$extra[$slug][$target] = $translated;
				$totalOk++;
				$this->line("  ✓ {$slug} → {$target}: " . mb_strimwidth($translated['h1'] ?? '?', 0, 60, '…'));
			}
		}

		$this->writeExtraConfig($extraPath, $extra);
		$this->info("\nKlaar. OK={$totalOk} skip={$totalSkip} fail={$totalFail}");
		$this->info("Geschreven: {$extraPath}");
		return $totalFail === 0 ? self::SUCCESS : self::FAILURE;
	}

	private function translateService(AnthropicClient $ai, array $service, string $target, string $targetLang): array
	{
		$source = [
			'h1'        => $service['h1_nl'] ?? '',
			'lead'      => $service['lead_nl'] ?? '',
			'pill'      => $service['pill_nl'] ?? '',
			'bullets'   => $service['bullets_nl'] ?? [],
			'cta_title' => $service['cta_title_nl'] ?? '',
			'cta_text'  => $service['cta_text_nl'] ?? '',
		];

		$result = $ai->structuredCall([
			'model'             => $ai->translatorModel(),
			'system'            => "You are a professional translator for Beter Geregeld ICT (Dutch SMB IT consultancy). Translate the service-page fields from Dutch to {$targetLang}. Keep brand names unchanged: Beter Geregeld ICT, CookieGeregeld, M365, Entra ID, AccessGuard. Localize technical terms naturally (BTW → VAT/MwSt/TVA/IVA). Keep the same tone: practical, no jargon-for-jargon's-sake. Don't add fluff; match source length.",
			'user'              => "Translate the following Dutch service-page fields to {$targetLang}. Submit via the tool.\n\n" . json_encode($source, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
			'max_tokens'        => 4000,
			'tool_name'         => 'submit_service_translation',
			'tool_description'  => "Submit the translated service-page fields. Same keys as input, values in {$targetLang}.",
			'tool_input_schema' => [
				'type' => 'object',
				'properties' => [
					'h1'        => ['type' => 'string'],
					'lead'      => ['type' => 'string'],
					'pill'      => ['type' => 'string'],
					'bullets'   => ['type' => 'array', 'items' => ['type' => 'string']],
					'cta_title' => ['type' => 'string'],
					'cta_text'  => ['type' => 'string'],
				],
				'required' => ['h1', 'lead', 'pill', 'bullets', 'cta_title', 'cta_text'],
			],
		]);

		if (!is_array($result) || empty($result['h1'])) {
			throw new \RuntimeException($ai->lastError ?? 'no response');
		}
		return $result;
	}

	private function writeExtraConfig(string $path, array $extra): void
	{
		$header = "<?php\n\n"
			. "/**\n"
			. " * AUTO-GEGENEREERD door 'php artisan services:translate-catalog'.\n"
			. " * Per service-slug staat hier per locale (de/fr/es) een set vertaalde\n"
			. " * velden: h1, lead, pill, bullets[], cta_title, cta_text.\n"
			. " * ServiceController merget dit met services_catalog.php op basis van slug.\n"
			. " * Niet handmatig bewerken — opnieuw 'services:translate-catalog --force' draaien.\n"
			. " */\n\n"
			. "return ";

		$body = var_export($extra, true);
		// PSR-12 style: gebruik short array syntax i.p.v. array()
		$body = preg_replace('/array \(/', '[', $body);
		$body = preg_replace('/^(\s*)\)/m', '$1]', $body);

		file_put_contents($path, $header . $body . ";\n");
	}
}
