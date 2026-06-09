<?php

namespace App\Services\Security;

use App\Models\Security\DependencyAdvisory;
use App\Services\Ai\AnthropicClient;
use Illuminate\Support\Facades\Cache;

/**
 * Genereert een begrijpelijke Nederlandse uitleg van een dependency-advisory
 * via Claude (goedkoop model), gecachet per advisory zodat het niet elke keer
 * een API-call is.
 */
class AdvisoryExplainer
{
	public function explain(DependencyAdvisory $advisory): string
	{
		$key = 'advisory-explain:' . md5($advisory->ecosystem . '|' . $advisory->package . '|' . $advisory->cve . '|' . $advisory->title);

		return Cache::remember($key, now()->addDays(30), function () use ($advisory) {
			$client = app(AnthropicClient::class);

			$user = "Leg de volgende software-kwetsbaarheid uit in begrijpelijk Nederlands voor een niet-technische "
				. "ondernemer of websitebeheerder. Gebruik exact deze drie kopjes (vetgedrukt met **):\n"
				. "**Wat is er aan de hand?** — 1 à 2 zinnen, simpel.\n"
				. "**Waarom is dit belangrijk?** — 1 à 2 zinnen over het risico.\n"
				. "**Wat moet er gebeuren?** — 1 à 2 zinnen concreet advies.\n\n"
				. "Kort en zonder jargon. Gegevens:\n"
				. "- Pakket: {$advisory->package} ({$advisory->ecosystem})\n"
				. '- Ernst: ' . ($advisory->severity ?: 'onbekend') . "\n"
				. '- CVE: ' . ($advisory->cve ?: 'n.v.t.') . "\n"
				. "- Technische titel: {$advisory->title}\n"
				. '- Opgelost in versie: ' . ($advisory->patched_in ?: 'onbekend');

			$text = $client->chat([
				'model'       => 'claude-haiku-4-5-20251001',
				'user'        => $user,
				'max_tokens'  => 400,
				'temperature' => 0.3,
			]);

			return $text ?: ('De uitleg kon niet worden gegenereerd'
				. ($client->lastError ? ' (' . $client->lastError . ')' : '') . '.');
		});
	}
}
