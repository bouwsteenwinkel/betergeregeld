<?php

namespace App\Services\Assistant;

use App\Models\User;
use App\Services\Features\FeatureBag;
use App\Services\Features\FeatureResolver;

/**
 * Gating-ingang voor de AI-Assistent. Resolvet features tegen de losstaande
 * 'assistant'-productlijn (i.p.v. de globale 'tools'-resolver die aan
 * EnforceToolRateLimit hangt). Gebruik dit overal binnen de assistant-module.
 */
class AssistantFeatures
{
	private FeatureResolver $resolver;

	public function __construct()
	{
		$this->resolver = new FeatureResolver('assistant');
	}

	public function for(?User $user): FeatureBag
	{
		return $this->resolver->forRequest($user);
	}

	/** Heeft deze gebruiker/tenant de AI-module überhaupt? */
	public function enabled(?User $user): bool
	{
		return $this->for($user)->bool('tool.assistant.enabled');
	}

	/** Is een specifiek kanaal (web|voice|whatsapp|email) toegestaan? */
	public function channelAllowed(?User $user, string $channel): bool
	{
		return $this->for($user)->bool('tool.assistant.channel.' . $channel);
	}

	/** Mag de agent deze functie aanroepen? 'unlimited' = alle functies. */
	public function functionAllowed(?User $user, string $slug): bool
	{
		$bag = $this->for($user);
		if (! $bag->bool('tool.assistant.functions.enabled')) {
			return false;
		}
		$allowed = trim((string) $bag->raw('tool.assistant.functions.allowed', ''));
		if (strtolower($allowed) === 'unlimited') {
			return true;
		}
		$list = array_filter(array_map('trim', explode(',', $allowed)));
		return in_array($slug, $list, true);
	}
}
