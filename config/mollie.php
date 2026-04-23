<?php

return [
	// Read at config:cache build time, so production reads it from
	// the .env snapshot taken during deploy. MollieGateway::isFake()
	// treats empty / fake_ / "xxxx" as fake-mode, meaning the full
	// checkout flow stays usable without a real Mollie key.
	'key' => env('MOLLIE_KEY'),
];
