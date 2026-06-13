<?php

return [

	/*
	 * SocketLabs ServerID en de webhook-SecretKey. Elke event-webhook-POST bevat
	 * deze twee; we weigeren (401) als ze niet matchen. Stel in op de prod-server
	 * via .env. De SecretKey kies je zelf bij het aanmaken van de webhook in het
	 * SocketLabs-dashboard (Account-level Event Webhook).
	 */
	'server_id'      => env('SOCKETLABS_SERVER_ID'),
	'webhook_secret' => env('SOCKETLABS_WEBHOOK_SECRET'),

	/*
	 * SocketLabs API v2 (periodieke poll als vangnet naast de webhooks).
	 * api_key = een v2 API-key met leesrechten op rapportage. Leeg laten schakelt
	 * de poll uit (alleen webhooks blijven dan werken).
	 */
	'api_key'  => env('SOCKETLABS_API_KEY'),
	'api_base' => env('SOCKETLABS_API_BASE', 'https://api.socketlabs.com/v2'),

	/*
	 * Drempels voor de alert-evaluatie (state-based: mail alleen bij overgang,
	 * net als de overige monitor-alerts). Vensters in minuten.
	 */
	'window_minutes'   => (int) env('SOCKETLABS_WINDOW_MINUTES', 30),

	// Queue loopt vol: # Deferred (+ Queued) events in het venster boven deze grens.
	'deferred_threshold' => (int) env('SOCKETLABS_DEFERRED_THRESHOLD', 50),

	// Storingen: Failed-aandeel van (Delivered + Failed) boven dit %, mits er
	// minstens min_volume events zijn (anders ruis bij lage aantallen).
	'failure_rate_pct' => (int) env('SOCKETLABS_FAILURE_RATE_PCT', 20),
	'min_volume'       => (int) env('SOCKETLABS_MIN_VOLUME', 20),

	// Reputatie: # Complaint-events in het venster boven deze grens.
	'complaint_threshold' => (int) env('SOCKETLABS_COMPLAINT_THRESHOLD', 1),

	// Stilte (dead-man): geen enkel event meer ontvangen terwijl dat wél verwacht
	// wordt. 0 = uit (lastig zonder baseline). Zet bv. op 360 (6u) als er altijd
	// verkeer is.
	'silence_minutes' => (int) env('SOCKETLABS_SILENCE_MINUTES', 0),

	// Hoe lang ruwe events bewaard blijven (dagen) voor de prune-job.
	'retention_days' => (int) env('SOCKETLABS_RETENTION_DAYS', 30),

	// Tracking-events (opens/clicks) negeren we voor monitoring — engagement,
	// geen bezorg-/queue-signaal, en potentieel hoog volume.
	'store_tracking' => (bool) env('SOCKETLABS_STORE_TRACKING', false),
];
