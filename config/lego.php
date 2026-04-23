<?php

return [
	/*
	 * LEGO Element Finder — Rebrickable API.
	 *
	 * Get a key at https://rebrickable.com/users/profile/.
	 */
	'rebrickable' => [
		'api_key' => env('REBRICKABLE_API_KEY'),
		'base_url' => env('REBRICKABLE_BASE_URL', 'https://rebrickable.com'),
	],

	'lookup' => [
		// Cached enrichment is considered fresh for this long. After it
		// expires the next lookup re-fetches from Rebrickable.
		'cache_days' => 30,
		// HTTP timeout for Rebrickable calls (seconds).
		'timeout' => 8,
	],
];
