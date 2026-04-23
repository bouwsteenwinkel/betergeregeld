<?php

return [
	'ai' => [
		'enabled' => env('ACCESSGUARD_AI_ENABLED', true),
		'model' => env('ACCESSGUARD_AI_MODEL', 'gpt-4o-mini'),
		'api_key' => env('OPENAI_API_KEY'),
		'timeout_seconds' => 15,
		'temperature' => 0.2,
		'max_tokens' => 500,

		// Rate limit: calls per tenant per hour.
		'rate_limit_per_hour' => 10,

		// Cache identical (prompt_hash, tenant) answers for N seconds.
		'cache_ttl_seconds' => 86400, // 24 h
	],

	'demo' => [
		// Dedicated tenant UUID used by the public /accessguard/demo page.
		// Never assigned to a real customer. Seeded lazily on first visit
		// and refreshed daily by the scheduler so visitors always see
		// a realistic, clean dataset.
		'tenant_id' => '11111111-1111-1111-1111-111111111111',
		'actor_user_id' => 'demo-visitor',
		'reseed_after_hours' => 24,
	],
];
