<?php

return [

	/*
	 * The platform tenant whose users are super-admins (full VPS monitoring).
	 * Every other tenant only sees the limited SLA/status view for its own server.
	 * Defaults to the "Beter Geregeld" tenant; override per environment via env.
	 */
	'platform_tenant_id' => env('MONITOR_PLATFORM_TENANT_ID', '019dc47a-f084-70e6-b9d0-6c176421af28'),

	/*
	 * How often the VPS collector pushes a sample (seconds). Drives both the
	 * online/offline status thresholds and the expected-sample math behind the
	 * heartbeat-based uptime/SLA percentage.
	 */
	'interval_seconds' => (int) env('MONITOR_INTERVAL_SECONDS', 60),

	/*
	 * Status thresholds, in seconds since the last agent contact.
	 *   age <= online_within  -> online
	 *   age >= offline_after  -> offline
	 *   in between            -> stale (agent late, not yet declared down)
	 */
	'online_within' => (int) env('MONITOR_ONLINE_WITHIN', 150),
	'offline_after' => (int) env('MONITOR_OFFLINE_AFTER', 600),

	/*
	 * Resource-usage warning thresholds (percent) for table/badge colouring.
	 */
	'cpu_warn'  => (int) env('MONITOR_CPU_WARN', 85),
	'mem_warn'  => (int) env('MONITOR_MEM_WARN', 90),
	'disk_warn' => (int) env('MONITOR_DISK_WARN', 90),

	/*
	 * How long raw metric samples are kept (days). A scheduled prune trims older
	 * rows so the time-series table stays bounded.
	 */
	'retention_days' => (int) env('MONITOR_RETENTION_DAYS', 30),

	/*
	 * Where offline/disk alert mails go. Defaults to the platform inbox.
	 */
	'alert_email' => env('MONITOR_ALERT_EMAIL', 'info@bouwsteenwinkel.nl'),
];
